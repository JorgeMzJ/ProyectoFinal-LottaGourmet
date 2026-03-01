<?php
require_once 'Config/Database.php';
require_once 'Model/Producto.php';

class PaginasController {

    public function inicio() {
        try {
            $database = new Database();
            $db = $database->getConnection();
            if ($db === null) {
                throw new Exception("Error: No se pudo establecer conexión con la base de datos.");
            }
            $db->exec("SET NAMES 'utf8mb4'");
            $producto = new Producto($db);

            $masVendidos = $producto->obtenerMasVendidos(4);
            $otrosProductos = $producto->obtenerOtros(8);
            $carruselOfertas = $producto->obtenerPromociones(); // Usar obtenerPromociones() que filtra por en_promocion = 1

            require_once 'View/plantillas/header.php';
            require_once 'View/plantillas/sidebar.php';
            require_once 'View/paginas/inicio.php';
            require_once 'View/plantillas/footer.php';
        } catch (Exception $e) {
            error_log("Error en PaginasController::inicio - " . $e->getMessage());
            die("<h1>Error del sistema</h1><p>No se pudo cargar la página. Por favor, intenta más tarde.</p><p style='color:#666;font-size:0.9em;'>" . htmlspecialchars($e->getMessage()) . "</p>");
        }
    }

    public function nosotros() {
        require_once 'View/plantillas/header.php';
        require_once 'View/plantillas/sidebar.php';
        require_once 'View/paginas/nosotros.php';
        require_once 'View/plantillas/footer.php';
    }
}
?>