<?php
// Script de prueba para verificar qué productos retorna obtenerTodos()
require_once 'Config/Database.php';
require_once 'Model/Producto.php';

$database = new Database();
$db = $database->getConnection();
$producto = new Producto($db);

echo "<h2>Productos retornados por obtenerTodos() (debería excluir en_promocion=1)</h2>";
$productos = $producto->obtenerTodos();

echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>ID</th><th>Nombre</th><th>Precio</th><th>En Promoción</th></tr>";

foreach ($productos as $p) {
    // Hacer una consulta adicional para verificar en_promocion
    $stmt = $db->prepare("SELECT en_promocion, precio_oferta FROM productos WHERE id_producto = ?");
    $stmt->execute([$p['id_producto']]);
    $promo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<tr>";
    echo "<td>" . $p['id_producto'] . "</td>";
    echo "<td>" . htmlspecialchars($p['nombre']) . "</td>";
    echo "<td>$" . number_format($p['precio'], 2) . "</td>";
    echo "<td>" . ($promo['en_promocion'] ? 'SÍ' : 'NO') . "</td>";
    echo "</tr>";
}

echo "</table>";

echo "<h3>Total productos: " . count($productos) . "</h3>";
?>
