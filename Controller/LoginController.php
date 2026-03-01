<?php
// Controller/LoginController.php
require_once 'Config/Database.php';
require_once 'Model/Usuario.php';

class LoginController {
    public function mostrar() {
        require_once 'View/plantillas/header.php';
        require_once 'View/plantillas/sidebar.php';
        require_once 'View/paginas/login.php';
        require_once 'View/plantillas/footer.php';
    }

    public function validar() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $errors = [];
        $old = [];

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $old = ['email' => $email];

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email inválido.';
        if ($password === '') $errors[] = 'Contraseña requerida.';

        if (!empty($errors)) {
            $_SESSION['login_errors'] = $errors;
            $_SESSION['login_old'] = $old;
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        try {
            $database = new Database();
            $db = $database->getConnection();
            if ($db === null) {
                throw new Exception("Error: No se pudo establecer conexión con la base de datos.");
            }
            $db->exec("SET NAMES 'utf8mb4'");
            $usuario = new Usuario($db);

            // Buscar usuario por email
            $stmt = $db->prepare('SELECT * FROM usuarios WHERE email = :email LIMIT 1');
            $stmt->bindValue(':email', $email);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['usuario_id'] = $user['id'];
                $_SESSION['usuario_nombre'] = $user['nombre'];
                $_SESSION['usuario_email'] = $user['email'];
                $_SESSION['usuario_admin'] = $user['es_admin'] ?? 0;
                $_SESSION['login_success'] = 'Bienvenido, ' . htmlspecialchars($user['nombre']) . '!';
                header('Location: ' . BASE_URL . 'inicio');
                exit;
            } else {
                $_SESSION['login_errors'] = ['Credenciales incorrectas.'];
                $_SESSION['login_old'] = $old;
                header('Location: ' . BASE_URL . 'login');
                exit;
            }
        } catch (Exception $e) {
            error_log("Error en LoginController::autenticar - " . $e->getMessage());
            $_SESSION['login_errors'] = ['general' => 'Error del sistema. Por favor, intenta más tarde.'];
            $_SESSION['login_old'] = $old;
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
    }

    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_destroy();
        
        // Limpiar carrito del localStorage mediante JavaScript
        echo '<!DOCTYPE html>
        <html>
        <head><title>Cerrando sesión...</title></head>
        <body>
        <script>
            try {
                localStorage.removeItem("pastelesupbc_carrito");
                sessionStorage.removeItem("usuario_logueado");
            } catch(e) {}
            window.location.href = "' . BASE_URL . 'inicio";
        </script>
        </body>
        </html>';
        exit;
    }
}
