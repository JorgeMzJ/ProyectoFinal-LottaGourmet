<?php
class Producto {
    private $conn;
    private $table_name = "productos";

    public $id_producto;
    public $nombre;
    public $descripcion;
    public $precio;
    public $stock;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Obtiene todos los productos de la tabla 'productos'
     */
    public function obtenerTodos() {
        $query = "SELECT id_producto, nombre, descripcion, precio, stock, imagen
                  FROM " . $this->table_name . "
                  WHERE (en_promocion = 0 OR en_promocion IS NULL)
                  ORDER BY nombre ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca productos por nombre o descripción
     */
    public function buscar($q) {
        $query = "SELECT id_producto, nombre, descripcion, precio, stock, imagen
                  FROM " . $this->table_name . "
                  WHERE (nombre LIKE :term OR descripcion LIKE :term)
                    AND (en_promocion = 0 OR en_promocion IS NULL)
                  ORDER BY nombre ASC";

        $stmt = $this->conn->prepare($query);
        $term = '%' . $q . '%';
        $stmt->bindValue(':term', $term);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene los productos en oferta
     */
    public function obtenerOfertas() {
        $query = "SELECT id_producto, nombre, descripcion, precio, stock, imagen
                  FROM " . $this->table_name . "
                  LIMIT 3";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene los productos que están marcados como en promoción
     */
    public function obtenerPromociones() {
        $query = "SELECT id_producto, nombre, descripcion, precio, stock, imagen, en_promocion, precio_oferta
                  FROM " . $this->table_name . "
                  WHERE en_promocion = 1
                  ORDER BY id_producto DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene los productos más vendidos
     */
    public function obtenerMasVendidos($limit = 4) {
        $query = "SELECT id_producto, nombre, descripcion, precio, stock, imagen
                  FROM " . $this->table_name . "
                  ORDER BY id_producto DESC
                  LIMIT :lim";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':lim', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene otros productos
     */
    public function obtenerOtros($limit = 8) {
        $query = "SELECT id_producto, nombre, descripcion, precio, stock, imagen
                  FROM " . $this->table_name . "
                  ORDER BY nombre ASC
                  LIMIT :lim";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':lim', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // --- Métodos administrativos: CRUD ---
    public function obtenerTodosAdmin() {
        $query = "SELECT id_producto, nombre, descripcion, precio, stock, imagen, en_promocion, precio_oferta
                  FROM " . $this->table_name . "
                  ORDER BY id_producto DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($id) {
        $query = "SELECT id_producto, nombre, descripcion, precio, stock, imagen, en_promocion, precio_oferta
                  FROM " . $this->table_name . " WHERE id_producto = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function crear($data) {
        $query = "INSERT INTO " . $this->table_name . " (nombre, descripcion, precio, stock, imagen, en_promocion, precio_oferta)
                  VALUES (:nombre, :descripcion, :precio, :stock, :imagen, :en_promocion, :precio_oferta)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':nombre', $data['nombre']);
        $stmt->bindValue(':descripcion', $data['descripcion']);
        $stmt->bindValue(':precio', $data['precio']);
        $stmt->bindValue(':stock', (int)$data['stock'], PDO::PARAM_INT);
        $stmt->bindValue(':imagen', $data['imagen']);
        $stmt->bindValue(':en_promocion', !empty($data['en_promocion']) ? 1 : 0, PDO::PARAM_INT);
        $stmt->bindValue(':precio_oferta', $data['precio_oferta'] !== '' ? $data['precio_oferta'] : null);
        return $stmt->execute();
    }

    public function actualizar($id, $data) {
        $query = "UPDATE " . $this->table_name . " SET nombre = :nombre, descripcion = :descripcion, precio = :precio, stock = :stock, en_promocion = :en_promocion, precio_oferta = :precio_oferta";
        if (isset($data['imagen']) && $data['imagen'] !== null) {
            $query .= ", imagen = :imagen";
        }
        $query .= " WHERE id_producto = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':nombre', $data['nombre']);
        $stmt->bindValue(':descripcion', $data['descripcion']);
        $stmt->bindValue(':precio', $data['precio']);
        $stmt->bindValue(':stock', (int)$data['stock'], PDO::PARAM_INT);
        $stmt->bindValue(':en_promocion', !empty($data['en_promocion']) ? 1 : 0, PDO::PARAM_INT);
        $stmt->bindValue(':precio_oferta', $data['precio_oferta'] !== '' ? $data['precio_oferta'] : null);
        if (isset($data['imagen']) && $data['imagen'] !== null) {
            $stmt->bindValue(':imagen', $data['imagen']);
        }
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function eliminar($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id_producto = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function actualizarImagen($id, $filename) {
        $query = "UPDATE " . $this->table_name . " SET imagen = :imagen WHERE id_producto = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':imagen', $filename);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>