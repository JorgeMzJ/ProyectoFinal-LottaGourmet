<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: POST');

$host = "localhost"; $user = "root"; $pass = ""; $db = "pastelesupbc";
$conn = new mysqli($host, $user, $pass, $db);

$input = file_get_contents('php://input');
$data = json_decode($input, true);

$id_usuario = $data['id_usuario'];
$total = $data['total'];
$productos = $data['productos'];

// 1. Obtener datos del cliente
$sqlUser = "SELECT nombre, email FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sqlUser);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$resUser = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$resUser) {
    die(json_encode(["success" => false, "message" => "Usuario no encontrado"]));
}

// 2. Insertar Compra
$sqlCompra = "INSERT INTO compras (total, nombre_cliente, email_cliente) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sqlCompra);
$stmt->bind_param("dss", $total, $resUser['nombre'], $resUser['email']);

if ($stmt->execute()) {
    $id_compra = $stmt->insert_id;
    $stmt->close();

    // 3. Insertar Detalles y Descontar Stock
    $sqlDetalle = "INSERT INTO compra_detalles (id_compra, id_producto, cantidad, precio_unitario) VALUES (?, ?, ?, ?)";
    $stmtDet = $conn->prepare($sqlDetalle);

    // SQL para descontar stock
    $sqlStock = "UPDATE productos SET stock = stock - ? WHERE id_producto = ?";
    $stmtStock = $conn->prepare($sqlStock);

    foreach ($productos as $prod) {
        // Insertar detalle
        $stmtDet->bind_param("iiid", $id_compra, $prod['id'], $prod['cantidad'], $prod['precio']);
        $stmtDet->execute();

        // Descontar stock
        $stmtStock->bind_param("ii", $prod['cantidad'], $prod['id']);
        $stmtStock->execute();
    }
    $stmtDet->close();
    $stmtStock->close();

    echo json_encode(["success" => true, "message" => "Compra registrada"]);
} else {
    echo json_encode(["success" => false, "message" => "Error al guardar"]);
}
$conn->close();
?>