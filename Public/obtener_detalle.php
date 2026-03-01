<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: POST');

$host = "localhost"; $user = "root"; $pass = ""; $db = "pastelesupbc";
$conn = new mysqli($host, $user, $pass, $db);
$conn->set_charset("utf8");

$json = file_get_contents('php://input');
$datos = json_decode($json, true);
$id = $datos['id'] ?? 0;
$tipo = $datos['tipo'] ?? ''; 

$detalles = array();

if ($tipo == 'compra') {
    // --- COMPRA TIENDA ---
    $sql = "SELECT p.nombre, p.imagen, cd.cantidad, cd.precio_unitario 
            FROM compra_detalles cd JOIN productos p ON cd.id_producto = p.id_producto
            WHERE cd.id_compra = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    
    while($row = $res->fetch_assoc()) {
        $detalles[] = array(
            "Producto" => $row['nombre'],
            "Imagen" => $row['imagen'], // Solo nombre
            "Cantidad" => $row['cantidad'],
            "Precio" => "$" . number_format($row['precio_unitario'], 2),
            "Subtotal" => "$" . number_format($row['cantidad'] * $row['precio_unitario'], 2)
        );
    }
} else {
    // --- EVENTO ---
    $sqlPaq = "SELECT id_paquete FROM pedidos WHERE id_pedido = ?";
    $stmtPaq = $conn->prepare($sqlPaq);
    $stmtPaq->bind_param("i", $id);
    $stmtPaq->execute();
    $rowPaq = $stmtPaq->get_result()->fetch_assoc();

    if ($rowPaq && $rowPaq['id_paquete'] != null) {
        // ES PAQUETE
        $sqlInfo = "SELECT nombre, precio FROM paquetes_eventos WHERE id_paquete = ?";
        $stmtInfo = $conn->prepare($sqlInfo);
        $stmtInfo->bind_param("i", $rowPaq['id_paquete']);
        $stmtInfo->execute();
        $infoPaq = $stmtInfo->get_result()->fetch_assoc();

        $detalles[] = array(
            "Producto" => "PAQUETE: " . $infoPaq['nombre'],
            "Imagen" => "", 
            "Cantidad" => 1,
            "Precio" => "$" . number_format($infoPaq['precio'], 2),
            "Subtotal" => "$" . number_format($infoPaq['precio'], 2)
        );
    } else {
        // ES PERSONALIZADO
        $sql = "SELECT p.nombre, p.imagen, dp.cantidad, dp.precio_unitario 
                FROM detalle_pedidos dp JOIN productos p ON dp.id_producto = p.id_producto
                WHERE dp.id_pedido = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        while($row = $res->fetch_assoc()) {
            $detalles[] = array(
                "Producto" => $row['nombre'],
                "Imagen" => $row['imagen'],
                "Cantidad" => $row['cantidad'],
                "Precio" => "$" . number_format($row['precio_unitario'], 2),
                "Subtotal" => "$" . number_format($row['cantidad'] * $row['precio_unitario'], 2)
            );
        }
    }
}
echo json_encode($detalles);
?>