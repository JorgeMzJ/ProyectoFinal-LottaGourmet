<?php
class PedidosController {

    /**
     * Muestra los pedidos del usuario logueado
     * URL: /pedidos/mis-pedidos
     */
    public function misPedidos() {
        require_once 'Config/App.php';
        require_once 'Config/Database.php';

        // Verificar que el usuario esté logueado
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        $database = new Database();
        $db = $database->getConnection();
        $db->exec("SET NAMES 'utf8mb4'");

        // Obtener el id del usuario desde la sesión
        $usuario_id = $_SESSION['usuario_id'];

        $pedidos = [];
        $compras = [];

        // Obtener email del usuario para las compras
        $stmtUsuario = $db->prepare('SELECT email FROM usuarios WHERE id = :id LIMIT 1');
        $stmtUsuario->bindValue(':id', $usuario_id, PDO::PARAM_INT);
        $stmtUsuario->execute();
        $usuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC);

        if ($usuario) {
            $email = $usuario['email'];

            // Obtener pedidos (citas/eventos) directamente por id_usuario
            $queryPedidos = "SELECT p.*, 
                                   pa.nombre as nombre_paquete,
                                   (SELECT SUM(dp.cantidad * dp.precio_unitario) 
                                    FROM detalle_pedidos dp 
                                    WHERE dp.id_pedido = p.id_pedido) as total
                            FROM pedidos p
                            LEFT JOIN paquetes_eventos pa ON p.id_paquete = pa.id_paquete
                            WHERE p.id_usuario = :id_usuario
                            ORDER BY p.fecha DESC";
            $stmtPedidos = $db->prepare($queryPedidos);
            $stmtPedidos->bindValue(':id_usuario', $usuario_id, PDO::PARAM_INT);
            $stmtPedidos->execute();
            $pedidos = $stmtPedidos->fetchAll(PDO::FETCH_ASSOC);

            // Para cada pedido, obtener sus productos
            foreach ($pedidos as &$pedido) {
                $queryDetalles = "SELECT dp.cantidad, dp.precio_unitario, pr.nombre
                                 FROM detalle_pedidos dp
                                 JOIN productos pr ON dp.id_producto = pr.id_producto
                                 WHERE dp.id_pedido = :id_pedido";
                $stmtDetalles = $db->prepare($queryDetalles);
                $stmtDetalles->bindValue(':id_pedido', $pedido['id_pedido'], PDO::PARAM_INT);
                $stmtDetalles->execute();
                $pedido['productos'] = $stmtDetalles->fetchAll(PDO::FETCH_ASSOC);
            }

            // Obtener compras directas
            $queryCompras = "SELECT c.*, SUM(cd.cantidad * cd.precio_unitario) as total
                            FROM compras c
                            LEFT JOIN compra_detalles cd ON c.id_compra = cd.id_compra
                            WHERE c.email_cliente = :email
                            GROUP BY c.id_compra
                            ORDER BY c.fecha DESC";
            $stmtCompras = $db->prepare($queryCompras);
            $stmtCompras->bindValue(':email', $email);
            $stmtCompras->execute();
            $compras = $stmtCompras->fetchAll(PDO::FETCH_ASSOC);

            // Para cada compra, obtener sus productos
            foreach ($compras as &$compra) {
                $queryDetallesCompra = "SELECT cd.cantidad, cd.precio_unitario, pr.nombre
                                       FROM compra_detalles cd
                                       JOIN productos pr ON cd.id_producto = pr.id_producto
                                       WHERE cd.id_compra = :id_compra";
                $stmtDetallesCompra = $db->prepare($queryDetallesCompra);
                $stmtDetallesCompra->bindValue(':id_compra', $compra['id_compra'], PDO::PARAM_INT);
                $stmtDetallesCompra->execute();
                $compra['productos'] = $stmtDetallesCompra->fetchAll(PDO::FETCH_ASSOC);
            }
        }

        require_once 'View/plantillas/header.php';
        require_once 'View/plantillas/sidebar.php';
        require_once 'View/paginas/mis_pedidos.php';
        require_once 'View/plantillas/footer.php';
    }
}
?>
