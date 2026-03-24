<?php
// Controller/RegistroController.php
require_once 'Config/Database.php';
require_once 'Model/Usuario.php';

class RegistroController {
    public function mostrar() {
        require_once 'View/plantillas/header.php';
        require_once 'View/plantillas/sidebar.php';
        // Mostrar la vista del formulario
        // Si hay mensajes en sesión, la vista los leerá
        require_once 'View/paginas/registro.php';
        require_once 'View/plantillas/footer.php';
    }

    public function guardar() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $errors = [];
        $old = [];

        // Simple validación del servidor
        $nombre = trim($_POST['nombre'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirmPassword'] ?? '';

        $old = [
            'nombre' => $nombre,
            'email' => $email,
            'telefono' => $telefono
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($nombre === '') $errors['nombre'] = 'El nombre es obligatorio.';
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Email inválido.';
            if ($telefono === '') {
                $errors['telefono'] = 'El teléfono es requerido.';
            } elseif (!preg_match('/^\d{10,15}$/', $telefono)) {
                $errors['telefono'] = 'El teléfono debe contener solo dígitos (10 a 15).';
            }
            if (strlen($password) < 6) $errors['password'] = 'La contraseña debe tener al menos 6 caracteres.';
            if ($password !== $confirm) $errors['confirmPassword'] = 'Las contraseñas no coinciden.';
        }

        if (!empty($errors)) {
            $_SESSION['registro_errors'] = $errors;
            $_SESSION['registro_old'] = $old;
            header('Location: ' . BASE_URL . 'registro');
            exit;
        }

        try {
            // Preparar datos para modelo
            $database = new Database();
            $db = $database->getConnection();
            if ($db === null) {
                throw new Exception("Error: No se pudo establecer conexión con la base de datos.");
            }
            $db->exec("SET NAMES 'utf8mb4'");
            $usuario = new Usuario($db);

        $data = [
            'nombre' => $nombre,
            'email' => $email,
            'telefono' => $telefono,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT)
        ];

        $result = $usuario->crear($data);
        if ($result['success']) {
            $_SESSION['registro_success'] = $result['message'];
            // Limpiar antiguos
            unset($_SESSION['registro_errors'], $_SESSION['registro_old']);
            header('Location: ' . BASE_URL . 'registro?success=1');
            exit;
        } else {
            $_SESSION['registro_errors'] = ['general' => $result['message']];
            $_SESSION['registro_old'] = $old;
            header('Location: ' . BASE_URL . 'registro');
            exit;
        }
        } catch (Exception $e) {
            error_log("Error en RegistroController::guardar - " . $e->getMessage());
            $_SESSION['registro_errors'] = ['general' => 'Error del sistema. Por favor, intenta más tarde.'];
            $_SESSION['registro_old'] = $old;
            header('Location: ' . BASE_URL . 'registro');
            exit;
        }
    }
}

?>