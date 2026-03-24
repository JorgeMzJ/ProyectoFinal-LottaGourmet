<?php
// View/paginas/login.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$errors = $_SESSION['login_errors'] ?? [];
$old = $_SESSION['login_old'] ?? [];
$success = isset($_GET['success']) || isset($_SESSION['login_success']);
?>
<main class="contenido-principal">
    <h1>Iniciar Sesión</h1>
    <?php if ($success): ?>
        <div class="mensaje-exito">
            <h2>¡Sesión iniciada!</h2>
            <p><?php echo htmlspecialchars($_SESSION['login_success'] ?? 'Bienvenido.'); ?></p>
        </div>
        <script>
        // Marcar usuario como logueado en sessionStorage
        try { sessionStorage.setItem('usuario_logueado', '1'); } catch(e){}
        </script>
    <?php endif; ?>
    <?php if (!empty($errors)): ?>
        <div class="mensaje-error">
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?php echo htmlspecialchars($e); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <form action="<?php echo BASE_URL; ?>login/validar" method="post" id="loginForm" novalidate>
        <div class="form-group">
            <label for="email">Correo electrónico:</label>
            <input type="email" id="email" name="email" required style="width: 100%; box-sizing: border-box;" value="<?php echo htmlspecialchars($old['email'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required class="input-password">
        </div>
        <button type="submit" class="btn-primary">Iniciar Sesión</button>
    </form>
</main>
<?php unset($_SESSION['login_errors'], $_SESSION['login_old'], $_SESSION['login_success']); ?>
<script src="<?php echo BASE_URL; ?>Public/js/registro.js?v=1.0"></script>
<script>
// Si se visita la página de login y no hay éxito, limpiar el flag
if (!window.location.search.includes('success=1')) {
    try { sessionStorage.removeItem('usuario_logueado'); } catch(e){}
}
</script>
