<?php require_once 'Config/App.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lotta Gourmet</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@300..700&family=IBM+Plex+Sans:ital,wght@0,100..700;1,100..700&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>Public/css/styles.css?v=3.0">
    <script src="<?php echo BASE_URL; ?>Public/js/custom-modal.js?v=1.1"></script>
    <base href="<?php echo BASE_URL; ?>">
    <script>
        // Expose BASE_URL to client-side scripts
        window.BASE_URL = '<?php echo BASE_URL; ?>';
        // Sincronizar estado de sesión PHP con sessionStorage
        <?php
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (isset($_SESSION['usuario_id'])) {
            echo "try { sessionStorage.setItem('usuario_logueado', '1'); } catch(e){}";
        } else {
            echo "try { sessionStorage.removeItem('usuario_logueado'); } catch(e){}";
        }
        ?>
    </script>
    

</head>
<body>

    <?php
    // Detectar si la ruta actual corresponde al área de admin
    $requestPath = $_SERVER['REQUEST_URI'];
    $isAdminRoute = (strpos($requestPath, '/admin') !== false);
    // Detectar si estamos exactamente en /admin o /admin/ (sin subrutas)
    $isAdminHome = preg_match('#/admin/?(\?.*)?$#', $requestPath);
    ?>

    <header class="header-principal<?php echo $isAdminRoute ? ' admin' : ''; ?>">
        <?php if ($isAdminRoute && !$isAdminHome): ?>
            <div class="admin-nav-left">
                <a href="<?php echo BASE_URL; ?>admin" class="btn-admin-home">← Panel Admin</a>
            </div>
        <?php endif; ?>
        <div class="header-left">
            <h1><?php echo $isAdminRoute ? 'Panel de Administrador' : 'Lotta Gourmet'; ?></h1>
        </div>
        <?php if (!isset($isAdminRoute) || !$isAdminRoute): ?>
        <nav class="header-nav" style="margin-left:auto;">
            <ul style="display:flex;gap:22px;align-items:center;justify-content:flex-end;margin:0;padding:0;">
                <li><a href="<?php echo BASE_URL; ?>">Inicio</a></li>
                <li><a href="<?php echo BASE_URL; ?>menu">Menú</a></li>
                <li><a href="<?php echo BASE_URL; ?>promociones">Promociones</a></li>
                <li><a href="<?php echo BASE_URL; ?>citas">Citas (Pedidos)</a></li>
                <li><a href="<?php echo BASE_URL; ?>nosotros">Sobre Nosotros</a></li>
            </ul>
        </nav>
        <?php endif; ?>
        <?php if (!isset($isAdminRoute) || !$isAdminRoute): ?>
        <button id="headerCarritoBtn" class="header-cart-btn" style="margin-left:auto;">
            🛒 <span id="headerCarritoCount" class="cart-count">0</span>
        </button>
        <?php endif; ?>
        <div class="header-profile" style="margin-left:16px;">
            <?php
            // La sesión ya está iniciada arriba
                if (isset($_SESSION['usuario_id'])) {
                    $nombreCompleto = htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Usuario');
                    // Extraer solo el primer nombre
                    $primerNombre = explode(' ', $nombreCompleto)[0];
                    // Limitar a 12 caracteres máximo
                    if (strlen($primerNombre) > 12) {
                        $primerNombre = substr($primerNombre, 0, 12) . '...';
                    }
                    $esAdmin = !empty($_SESSION['usuario_admin']);
                    echo '<div class="profile-dropdown">
                            <button class="profile-btn">👤 ' . $primerNombre . '</button>
                            <div class="profile-menu">'; 
                    if ($esAdmin) {
                        // Enlazar a la ruta manejada por el router: /admin (AdminController::panel)
                        echo '<a href="' . BASE_URL . 'admin">Panel de Administrador</a>';
                    } else {
                        echo '<a href="' . BASE_URL . 'pedidos/mis-pedidos">Mis Pedidos</a>';
                    }
                    echo '<a href="' . BASE_URL . 'login/logout">Cerrar sesión</a>';
                    echo '</div></div>';
            } else {
                echo '<div class="profile-dropdown">
                        <button class="profile-btn">👤 Perfil</button>
                        <div class="profile-menu">
                            <a href="' . BASE_URL . 'login">Iniciar sesión</a>
                            <a href="' . BASE_URL . 'registro">Registrarse</a>
                        </div>
                    </div>';
            }
            ?>
        </div>
        <?php if ($isAdminRoute): ?>
            <div style="position: absolute; right: 16px; top: 14px;">
                <a href="<?php echo BASE_URL; ?>" class="btn-return">❮ Volver al sitio público</a>
            </div>
        <?php endif; ?>
        <style>
        .header-profile { position: relative; display: flex; align-items: center; }
        .profile-btn { 
            background: #fff; 
            border: 1px solid #eee; 
            border-radius: 20px; 
            padding: 8px 18px; 
            font-weight: 700; 
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .profile-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.12);
            border-color: #E91E63;
        }
        .profile-dropdown { position: relative; }
        .profile-menu { display: none; position: absolute; right: 0; top: 110%; background: #fff; border: 1px solid #eee; border-radius: 10px; box-shadow: 0 4px 16px rgba(0,0,0,0.08); min-width: 160px; z-index: 100; }
        .profile-menu a { display: block; padding: 10px 18px; color: #333; text-decoration: none; font-weight: 600; border-bottom: 1px solid #f4f4f4; }
        .profile-menu a:last-child { border-bottom: none; }
        .profile-menu a:hover { background: #f9f9f9; }
        .profile-dropdown:hover .profile-menu, .profile-btn:focus + .profile-menu { display: block; }
        
        /* Botón del carrito en header */
        .header-cart-btn {
            position: relative;
            background: linear-gradient(135deg, #E91E63 0%, #c2185b 100%);
            color: #fff;
            border: none;
            border-radius: 20px;
            padding: 8px 18px;
            font-weight: 700;
            font-size: 1em;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 2px 8px rgba(233, 30, 99, 0.3);
        }
        .header-cart-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(233, 30, 99, 0.4);
        }
        .cart-count {
            background: #fff;
            color: #E91E63;
            border-radius: 50%;
            width: 22px;
            height: 22px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85em;
            font-weight: 700;
        }
        
        /* Estilos específicos para el header en la zona admin */
        .header-principal.admin { background: #f6f8fb; position: relative; }
        .header-principal.admin .header-left { margin: 0 auto; text-align: center; }
        .header-principal.admin .header-logo { display: none; }
        .admin-nav-left { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); z-index: 10; }
        .btn-admin-home { display: inline-block; background: #2563eb; color: #fff; border: none; padding: 10px 16px; border-radius: 8px; text-decoration: none; font-weight: 700; box-shadow: 0 2px 8px rgba(37, 99, 235, 0.3); transition: all 0.2s; }
        .btn-admin-home:hover { background: #1d4ed8; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4); }
        .btn-return { display:inline-block; background:#fff; border:1px solid #e1e6ef; padding:8px 12px; border-radius:8px; text-decoration:none; color:#1f2b46; font-weight:700; box-shadow:0 2px 8px rgba(0,0,0,0.06); }
        .btn-return:hover { transform:translateY(-1px); box-shadow:0 6px 18px rgba(0,0,0,0.08); }
        </style>
    </header>
    <div class="main-container">