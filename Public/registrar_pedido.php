<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: POST');

$host = "localhost"; $user = "root"; $pass = ""; $db = "pastelesupbc";
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) die(json_encode(["success" => false, "message" => "Error DB"]));

$json = file_get_contents('php://input');
$data = json_decode($json, true);

$id_usuario = $data['id_usuario'];
$tipo = $data['tipo'];
$fecha = $data['fecha'];
$notas = $data['notas'] ?? ''; 
$id_paquete = $data['id_paquete'] ?? null;

$sql = "INSERT INTO pedidos (id_usuario, tipoEvento, fechaEvento, notas, id_paquete) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("isssi", $id_usuario, $tipo, $fecha, $notas, $id_paquete);

if ($stmt->execute()) {
    $id_pedido = $stmt->insert_id;

    if ($id_paquete != null) {
        // 1. Obtener precio del paquete para calcular unitario
        $sqlInfo = "SELECT precio, cantidad_postres FROM paquetes_eventos WHERE id_paquete = ?";
        $stmtInfo = $conn->prepare($sqlInfo);
        $stmtInfo->bind_param("i", $id_paquete);
        $stmtInfo->execute();
        $resInfo = $stmtInfo->get_result()->fetch_assoc();
        
        $precio_unitario = 0;
        if ($resInfo && $resInfo['cantidad_postres'] > 0) {
            $precio_unitario = $resInfo['precio'] / $resInfo['cantidad_postres'];
        }

        // 2. Obtener productos
        $sqlItems = "SELECT id_producto, cantidad FROM paquete_productos WHERE id_paquete = ?";
        $stmtItems = $conn->prepare($sqlItems);
        $stmtItems->bind_param("i", $id_paquete);
        $stmtItems->execute();
        $resItems = $stmtItems->get_result();

        // 3. Insertar con precio calculado
        $stmtDet = $conn->prepare("INSERT INTO detalle_pedidos (id_pedido, id_producto, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
        
        while ($prod = $resItems->fetch_assoc()) {
            $stmtDet->bind_param("iiid", $id_pedido, $prod['id_producto'], $prod['cantidad'], $precio_unitario);
            $stmtDet->execute();
        }
    }
    echo json_encode(["success" => true, "message" => "Pedido registrado"]);
} else {
    echo json_encode(["success" => false, "message" => "Error: " . $conn->error]);
}
$stmt->close();
$conn->close();
?>