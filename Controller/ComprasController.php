<?php
// Controller/ComprasController.php
class ComprasController {
    
    public function confirmar() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        // Verificar que haya items en el carrito desde POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['carrito'])) {
            $_SESSION['carrito_compra'] = json_decode($_POST['carrito'], true);
        }
        
        if (empty($_SESSION['carrito_compra'])) {
            header('Location: ' . BASE_URL . 'menu');
            exit;
        }
        
        require_once 'View/plantillas/header.php';
        require_once 'View/plantillas/sidebar.php';
        require_once 'View/paginas/confirmacion_compra.php';
        require_once 'View/plantillas/footer.php';
    }
    
    public function procesar() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            require_once 'Config/Database.php';
            
            // Verificar que el usuario esté logueado
            if (!isset($_SESSION['usuario_id'])) {
                echo json_encode(['success' => false, 'error' => 'Debes iniciar sesión para realizar una compra']);
                exit;
            }
            
            if (empty($_SESSION['carrito_compra'])) {
                echo json_encode(['success' => false, 'error' => 'El carrito está vacío']);
                exit;
            }
            
            $carrito = $_SESSION['carrito_compra'];
            $database = new Database();
            $db = $database->getConnection();
            $db->exec("SET NAMES 'utf8mb4'");
            
            // Obtener datos del usuario
            $usuario_id = $_SESSION['usuario_id'];
            $stmtUsuario = $db->prepare('SELECT nombre, email FROM usuarios WHERE id = :id LIMIT 1');
            $stmtUsuario->bindValue(':id', $usuario_id, PDO::PARAM_INT);
            $stmtUsuario->execute();
            $usuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC);
            
            if (!$usuario) {
                echo json_encode(['success' => false, 'error' => 'Usuario no encontrado']);
                exit;
            }
            
            // Validar stock y calcular total
            $total = 0;
            $insuficientes = [];
            foreach ($carrito as $item) {
                // Obtener stock actual del producto
                $stmtStock = $db->prepare('SELECT stock, nombre FROM productos WHERE id_producto = :id LIMIT 1');
                $stmtStock->bindValue(':id', $item['id'], PDO::PARAM_INT);
                $stmtStock->execute();
                $row = $stmtStock->fetch(PDO::FETCH_ASSOC);
                if (!$row) {
                    $insuficientes[] = 'Producto no encontrado (ID ' . (int)$item['id'] . ')';
                    continue;
                }
                $stockActual = (int)$row['stock'];
                $nombreProd = $row['nombre'];
                if ($stockActual < (int)$item['cantidad']) {
                    $insuficientes[] = $nombreProd . ' (stock disponible: ' . $stockActual . ')';
                }
                $total += ($item['precio'] * $item['cantidad']);
            }
            if (!empty($insuficientes)) {
                echo json_encode(['success' => false, 'error' => 'Stock insuficiente para: ' . implode(', ', $insuficientes)]);
                exit;
            }
            
            $db->beginTransaction();
            
            // Insertar compra
            $stmtCompra = $db->prepare('INSERT INTO compras (fecha, total, nombre_cliente, email_cliente) VALUES (NOW(), :total, :nombre, :email)');
            $stmtCompra->bindValue(':total', $total);
            $stmtCompra->bindValue(':nombre', $usuario['nombre']);
            $stmtCompra->bindValue(':email', $usuario['email']);
            $stmtCompra->execute();
            $id_compra = $db->lastInsertId();
            
            // Insertar detalles de compra
            $stmtDetalle = $db->prepare('INSERT INTO compra_detalles (id_compra, id_producto, cantidad, precio_unitario) VALUES (:id_compra, :id_producto, :cantidad, :precio_unitario)');
            
            foreach ($carrito as $item) {
                $stmtDetalle->bindValue(':id_compra', $id_compra, PDO::PARAM_INT);
                $stmtDetalle->bindValue(':id_producto', $item['id'], PDO::PARAM_INT);
                $stmtDetalle->bindValue(':cantidad', $item['cantidad'], PDO::PARAM_INT);
                $stmtDetalle->bindValue(':precio_unitario', $item['precio']);
                $stmtDetalle->execute();

                // Descontar stock de forma segura
                $stmtUpdate = $db->prepare('UPDATE productos SET stock = stock - :cantidad WHERE id_producto = :id AND stock >= :cantidad');
                $stmtUpdate->bindValue(':cantidad', (int)$item['cantidad'], PDO::PARAM_INT);
                $stmtUpdate->bindValue(':id', (int)$item['id'], PDO::PARAM_INT);
                $stmtUpdate->execute();
                if ($stmtUpdate->rowCount() === 0) {
                    throw new Exception('No se pudo actualizar stock para el producto ID ' . (int)$item['id']);
                }
            }
            
            $db->commit();
            
            // Limpiar sesión
            unset($_SESSION['carrito_compra']);
            
            echo json_encode(['success' => true]);
            exit;
            
        } catch (Exception $e) {
            if (isset($db)) {
                $db->rollBack();
            }
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }
}
?>
