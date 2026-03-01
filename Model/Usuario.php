<?php
class Usuario {
    private $conn;
    private $table_name = 'usuarios';

    public $id;
    public $nombre;
    public $email;
    public $edad;
    public $telefono;
    public $password_hash;
    public $pais;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Crea un nuevo usuario en la base de datos.
     * Devuelve array con keys: success(bool) y message(string)
     */
    public function crear($data) {
        // Comprobar si el email ya existe
        $queryCheck = "SELECT id FROM " . $this->table_name . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($queryCheck);
        $stmt->bindValue(':email', $data['email']);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            return ['success' => false, 'message' => 'El correo ya está registrado.'];
        }

        // Insertar
        $query = "INSERT INTO " . $this->table_name . "
            (nombre, email, telefono, password_hash, creado_en)
            VALUES (:nombre, :email, :telefono, :password_hash, NOW())";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':nombre', $data['nombre']);
        $stmt->bindValue(':email', $data['email']);
        $stmt->bindValue(':telefono', $data['telefono']);
        $stmt->bindValue(':password_hash', $data['password_hash']);

        try {
            $stmt->execute();
            return ['success' => true, 'message' => 'Usuario creado correctamente.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error al guardar: ' . $e->getMessage()];
        }
    }
}

?>