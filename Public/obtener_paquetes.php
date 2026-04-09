<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: POST');

$host = "localhost"; $user = "root"; $pass = ""; $db = "lottagourmet";
$conn = new mysqli($host, $user, $pass, $db);
$conn->set_charset("utf8");

$json = file_get_contents('php://input');
$datos = json_decode($json, true);
$tipo = $datos['tipo'] ?? '';

if (empty($tipo)) { echo json_encode([]); exit; }

$sql = "SELECT id_paquete, nombre, descripcion, precio, cantidad_postres 
        FROM paquetes_eventos WHERE tipo_evento = ? AND activo = 1";

$stmt = $conn->prepare($sql);   
$stmt->bind_param("s", $tipo);
$stmt->execute();
$result = $stmt->get_result();
$paquetes = array();

while($row = $result->fetch_assoc()) {
    $paquetes[] = array(
        "Id" => (int)$row['id_paquete'],
        "Nombre" => $row['nombre'],
        "Descripcion" => $row['descripcion'],
        "PrecioValor" => (float)$row['precio'],
        "Cantidad" => (int)$row['cantidad_postres']
    );
}
echo json_encode($paquetes);
$stmt->close();
$conn->close();
?>