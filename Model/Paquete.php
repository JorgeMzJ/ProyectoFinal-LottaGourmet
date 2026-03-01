<?php
class Paquete {
    private $conn;
    private $table_name = "paquetes_eventos";

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Obtiene todos los paquetes activos
     */
    public function obtenerTodos() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE activo = 1 ORDER BY tipo_evento, precio ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene paquetes por tipo de evento
     */
    public function obtenerPorTipoEvento($tipoEvento) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE activo = 1 AND tipo_evento = :tipo ORDER BY precio ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':tipo', $tipoEvento);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene un paquete por ID con sus productos incluidos
     */
    public function obtenerPorId($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id_paquete = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $stmt->execute();
        $paquete = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($paquete) {
            // Obtener productos del paquete
            $queryProductos = "SELECT pp.cantidad, pp.id_producto, p.nombre, p.precio 
                              FROM paquete_productos pp 
                              JOIN productos p ON pp.id_producto = p.id_producto 
                              WHERE pp.id_paquete = :id";
            $stmtProductos = $this->conn->prepare($queryProductos);
            $stmtProductos->bindValue(':id', (int)$id, PDO::PARAM_INT);
            $stmtProductos->execute();
            $paquete['productos'] = $stmtProductos->fetchAll(PDO::FETCH_ASSOC);
        }
        
        return $paquete;
    }

    /**
     * Calcula el ahorro del paquete vs comprar productos individuales
     */
    public function calcularAhorro($id) {
        $paquete = $this->obtenerPorId($id);
        if (!$paquete) return 0;
        
        $precioIndividual = 0;
        foreach ($paquete['productos'] as $prod) {
            $precioIndividual += $prod['precio'] * $prod['cantidad'];
        }
        
        return $precioIndividual - $paquete['precio'];
    }
}
?>
