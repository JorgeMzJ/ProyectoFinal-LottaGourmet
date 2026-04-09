<?php
// Iniciar sesión al principio de la aplicación
session_start();

// Habilitar visualización de errores temporalmente (eliminar en producción)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: text/html; charset=utf-8');
mb_internal_encoding('UTF-8');

require_once 'Config/App.php';

$url = $_GET['url'] ?? '/';

$url = rtrim($url, '/');
$url = filter_var($url, FILTER_SANITIZE_URL);
$url = explode('/', $url);

$controlador = 'PaginasController';
$metodo = 'inicio';

// Verificar acceso admin ANTES de enrutamiento
if (isset($url[0]) && !empty($url[0])) {
    // Bloquear cualquier ruta que contenga "admin" sin permisos
    if (stripos($url[0], 'admin') !== false) {
        if (!isset($_SESSION['usuario_admin']) || $_SESSION['usuario_admin'] != 1) {
            header('Location: ' . BASE_URL . 'inicio');
            exit;
        }
    }
}

if (isset($url[0]) && !empty($url[0])) {
    if ($url[0] == 'menu') {
        $controlador = 'ProductosController';
        $metodo = 'menu';
    } 
    elseif ($url[0] == 'promociones') {
        $controlador = 'PromosController';
        $metodo = 'mostrar';
    } 
    elseif ($url[0] == 'citas') {
        $controlador = 'CitasController';
        // Convertir guiones a camelCase
        if (isset($url[1])) {
            if ($url[1] === 'formulario-paquete') {
                $metodo = 'formularioPaquete';
            } elseif ($url[1] === 'guardar-paquete') {
                $metodo = 'guardarPaquete';
            } elseif ($url[1] === 'confirmar-paquete') {
                $metodo = 'confirmarPaquete';
            } elseif ($url[1] === 'crearStripeSession') {
                $metodo = 'crearStripeSession';
            } elseif ($url[1] === 'crearStripeSessionPaquete') {
                $metodo = 'crearStripeSessionPaquete';
            } elseif ($url[1] === 'stripe-success') {
                $metodo = 'stripeSuccess';
            } else {
                $metodo = $url[1];
            }
        } else {
            $metodo = 'formulario';
        }
    }
    elseif ($url[0] == 'registro') {
        $controlador = 'RegistroController';
        $metodo = isset($url[1]) && !empty($url[1]) ? $url[1] : 'mostrar';
    }
    elseif ($url[0] == 'login') {
        $controlador = 'LoginController';
        $metodo = isset($url[1]) && !empty($url[1]) ? $url[1] : 'mostrar';
    }
    elseif ($url[0] == 'nosotros') {
        $controlador = 'PaginasController';
        $metodo = 'nosotros';
    }

     // habilitar ruta http://localhost/LottaGourmet/api/cotizar
    elseif ($url[0] == 'api' && isset($url[1])) {
        $controlador = 'ApiController';
        if ($url[1] == 'cotizar') {
            $metodo = 'cotizar';
        } elseif ($url[1] == 'opciones') {
            $metodo = 'opciones';
        }
    }
    
    elseif ($url[0] == 'admin') {
        $controlador = 'AdminController';
        $metodo = isset($url[1]) && !empty($url[1]) ? $url[1] : 'panel';
    }
    elseif ($url[0] == 'pedidos') {
        $controlador = 'PedidosController';
        // Convertir guiones a camelCase: mis-pedidos -> misPedidos
        if (isset($url[1]) && $url[1] === 'mis-pedidos') {
            $metodo = 'misPedidos';
        } else {
            $metodo = isset($url[1]) && !empty($url[1]) ? $url[1] : 'misPedidos';
        }
    }
    elseif ($url[0] == 'compras') {
        $controlador = 'ComprasController';
        if (isset($url[1]) && $url[1] === 'stripe-success') {
            $metodo = 'stripeSuccess';
        } else {
            $metodo = isset($url[1]) && !empty($url[1]) ? $url[1] : 'confirmar';
        }
    }
}

$rutaControlador = "Controller/{$controlador}.php";

if (file_exists($rutaControlador)) {
    require_once $rutaControlador;
    $controlador = new $controlador;
    if (method_exists($controlador, $metodo)) {
        // Pasar parámetros adicionales de la URL al método
        $parametros = array_slice($url, 2); // Obtener parámetros después del método
        if (!empty($parametros)) {
            call_user_func_array([$controlador, $metodo], $parametros);
        } else {
            $controlador->$metodo();
        }
    } else {
        echo "Error: El método '{$metodo}' no existe.";
    }
} else {
    echo "Error: El controlador '{$rutaControlador}' no se encontró.";
}

?>