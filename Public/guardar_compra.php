<?php
// Public/guardar_compra.php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once '../Config/Database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// Verificar usuario logueado
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['carrito']) || !is_array($data['carrito'])) {
    echo json_encode(['error' => 'Datos de carrito inválidos']);
    exit;
}

$carrito = $data['carrito'];
$total = 0;
foreach ($carrito as $item) {
    $total += (float)$item['precio'] * (int)$item['cantidad'];
}

$nombre_cliente = $_SESSION['usuario_nombre'] ?? '';
$email_cliente = $_SESSION['usuario_email'] ?? '';

try {
    $db = (new Database())->getConnection();
    $db->exec("SET NAMES 'utf8mb4'");
    $db->beginTransaction();
    // Insertar compra
    $stmt = $db->prepare('INSERT INTO compras (total, nombre_cliente, email_cliente) VALUES (?, ?, ?)');
    $stmt->execute([$total, $nombre_cliente, $email_cliente]);
    $id_compra = $db->lastInsertId();
    // Insertar detalles
    $detStmt = $db->prepare('INSERT INTO compra_detalles (id_compra, id_producto, cantidad, precio_unitario) VALUES (?, ?, ?, ?)');
    foreach ($carrito as $item) {
        $detStmt->execute([
            $id_compra,
            $item['id'],
            $item['cantidad'],
            $item['precio']
        ]);
    }
    $db->commit();
    echo json_encode(['success' => true, 'id_compra' => $id_compra]);
} catch (PDOException $e) {
    if ($db) $db->rollBack();
    echo json_encode(['error' => 'Error al guardar: ' . $e->getMessage()]);
}
