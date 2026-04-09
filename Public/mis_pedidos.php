<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: POST');

$host = "localhost"; $user = "root"; $pass = ""; $db = "lottagourmet";
$conn = new mysqli($host, $user, $pass, $db);
$conn->set_charset("utf8");

$json = file_get_contents('php://input');
$datos = json_decode($json, true);
$id_usuario = $datos['id_usuario'] ?? 0;

$lista_final = array();

// A: Obtener Email
$sqlUser = "SELECT email FROM usuarios WHERE id = ?";
$stmtUser = $conn->prepare($sqlUser);
$stmtUser->bind_param("i", $id_usuario);
$stmtUser->execute();
$resUser = $stmtUser->get_result();
$email_usuario = ($rowUser = $resUser->fetch_assoc()) ? $rowUser['email'] : "";
$stmtUser->close();

// B: Pedidos de Eventos (CORREGIDO)
// Usamos CASE WHEN para priorizar el precio del paquete si existe
$sqlPedidos = "SELECT p.id_pedido, p.fecha, p.tipoEvento, 
               CASE 
                   WHEN p.id_paquete IS NOT NULL THEN pq.precio 
                   ELSE COALESCE(SUM(dp.cantidad * dp.precio_unitario), 0) 
               END as total_final,
               COALESCE(SUM(dp.cantidad), 0) as items, pq.nombre as nombre_paquete
        FROM pedidos p
        LEFT JOIN detalle_pedidos dp ON p.id_pedido = dp.id_pedido
        LEFT JOIN paquetes_eventos pq ON p.id_paquete = pq.id_paquete
        WHERE p.id_usuario = ? 
        GROUP BY p.id_pedido";

$stmtP = $conn->prepare($sqlPedidos);
$stmtP->bind_param("i", $id_usuario);
$stmtP->execute();
$resP = $stmtP->get_result();

while($row = $resP->fetch_assoc()) {
    $desc = (int)$row['items'] . " artículos";
    if ($row['nombre_paquete']) $desc = "Paquete: " . $row['nombre_paquete'];

    $lista_final[] = array(
        "Id" => $row['id_pedido'],
        "Fecha" => $row['fecha'],
        "Tipo" => "Evento: " . $row['tipoEvento'],
        "Total" => "$" . number_format($row['total_final'], 2),
        "Descripcion" => $desc,
        "Timestamp" => strtotime($row['fecha'])
    );
}
$stmtP->close();

// C: Compras de Tienda
if (!empty($email_usuario)) {
    $sqlCompras = "SELECT c.id_compra, c.fecha, c.total, COALESCE(SUM(cd.cantidad), 0) as items
                   FROM compras c
                   LEFT JOIN compra_detalles cd ON c.id_compra = cd.id_compra
                   WHERE c.email_cliente = ? GROUP BY c.id_compra";

    $stmtC = $conn->prepare($sqlCompras);
    $stmtC->bind_param("s", $email_usuario);
    $stmtC->execute();
    $resC = $stmtC->get_result();

    while($row = $resC->fetch_assoc()) {
        $lista_final[] = array(
            "Id" => $row['id_compra'],
            "Fecha" => $row['fecha'],
            "Tipo" => "Compra en Tienda",
            "Total" => "$" . number_format($row['total'], 2),
            "Descripcion" => (int)$row['items'] . " productos",
            "Timestamp" => strtotime($row['fecha'])
        );
    }
    $stmtC->close();
}

// Ordenar
usort($lista_final, function($a, $b) { return $b['Timestamp'] - $a['Timestamp']; });
echo json_encode($lista_final);
$conn->close();
?>