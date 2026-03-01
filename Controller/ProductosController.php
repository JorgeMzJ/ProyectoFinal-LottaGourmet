<?php
require_once 'Config/Database.php';
require_once 'Model/Producto.php';

class ProductosController {
    private $db;
    private $producto;

    public function __construct() {
        try {
            $database = new Database();
            $this->db = $database->getConnection();
            if ($this->db === null) {
                throw new Exception("Error: No se pudo establecer conexión con la base de datos.");
            }
            $this->db->exec("SET NAMES 'utf8mb4'");
            $this->producto = new Producto($this->db);
        } catch (Exception $e) {
            error_log("Error en ProductosController::__construct - " . $e->getMessage());
            die("<h1>Error del sistema</h1><p>No se pudo cargar el menú. Por favor, intenta más tarde.</p>");
        }
    }

    /**
     * Muestra la página de "Menú" con todos los productos
     * Este método es llamado cuando la URL es /menu
     */
    public function menu() {
        $q = trim($_GET['q'] ?? '');
        if ($q !== '') {
            // Búsqueda solo en productos no promocionados para mantener semántica
            $listaProductos = $this->producto->buscar($q);
            // También buscar promociones que coincidan
            $promos = array_filter($this->producto->obtenerPromociones(), function($p) use ($q) {
                $term = mb_strtolower($q);
                return strpos(mb_strtolower($p['nombre']), $term) !== false || strpos(mb_strtolower($p['descripcion']), $term) !== false;
            });
            // Combinar, manteniendo promos con sus campos
            $listaProductos = array_values(array_merge($promos, $listaProductos));
        } else {
            // Incluir todos los productos normales + promociones
            $normales = $this->producto->obtenerTodos();
            $promos = $this->producto->obtenerPromociones();
            $listaProductos = array_values(array_merge($promos, $normales));
        }

        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($listaProductos);
            exit;
        }

        require_once 'View/plantillas/header.php';
        require_once 'View/plantillas/sidebar.php';
        require_once 'View/paginas/menu.php';
        require_once 'View/plantillas/footer.php';
    }
}
?>