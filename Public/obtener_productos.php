<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

// Credenciales XAMPP Local
$host = "localhost"; $user = "root"; $pass = ""; $db_name = "lottagourmet";

$conn = new mysqli($host, $user, $pass, $db_name);

if ($conn->connect_error) {
    die(json_encode(["error" => "Fallo DB: " . $conn->connect_error]));
}
$conn->set_charset("utf8");

// Consulta incluyendo el Stock
$sql = "SELECT id_producto, nombre, descripcion, precio, imagen, en_promocion, precio_oferta, stock FROM productos";
$result = $conn->query($sql);

$productos = array();

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $item = array(
            "Id" => (int)$row['id_producto'],
            "Titulo" => $row['nombre'],
            "Descripcion" => $row['descripcion'],
            "PrecioValor" => (float)$row['precio'],
            // Enviamos solo el nombre del archivo. La App (C#) le pondrá la IP/URL.
            "imagen" => $row['imagen'], 
            "EnPromocion" => (int)$row['en_promocion'],
            "PrecioOferta" => $row['precio_oferta'] != null ? (float)$row['precio_oferta'] : null,
            "Stock" => (int)$row['stock']
        );
        array_push($productos, $item);
    }
}

echo json_encode($productos);
$conn->close();
?>