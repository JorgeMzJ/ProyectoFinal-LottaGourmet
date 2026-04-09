<?php
// View/paginas/registro.php
// Mostrar mensajes y valores anteriores desde la sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$errors = $_SESSION['registro_errors'] ?? [];
$old = $_SESSION['registro_old'] ?? [];
$success = isset($_GET['success']) || isset($_SESSION['registro_success']);
?>

<main class="contenido-principal">
    <h1>Formulario de Registro</h1>

    <?php if ($success): ?>
        <div class="mensaje-exito">
            <h2>¡Registro exitoso!</h2>
            <p><?php echo htmlspecialchars($_SESSION['registro_success'] ?? 'Tu cuenta ha sido creada.'); ?></p>
        </div>
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

    <form action="<?php echo BASE_URL; ?>registro/guardar" method="post" id="registroForm" novalidate>
        <div class="form-group">
            <label for="nombre">Nombre completo:</label>
            <input type="text" id="nombre" name="nombre" required
                value="<?php echo htmlspecialchars($old['nombre'] ?? ''); ?>">
        </div>

        <div class="form-group">
            <label for="email">Correo electrónico:</label>
            <input type="email" id="email" name="email" required
                value="<?php echo htmlspecialchars($old['email'] ?? ''); ?>">
        </div>


        <div class="form-group">
            <label for="telefono">Teléfono:</label>
            <input type="tel" id="telefono" name="telefono" required pattern="\+?\d{10,13}" maxlength="13"
                inputmode="tel" title="Ingresa de 10 a 13 dígitos (puede iniciar con +)"
                value="<?php echo htmlspecialchars($old['telefono'] ?? ''); ?>">
        </div>

        <div class="form-group">
            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required class="input-password">
        </div>

        <div class="form-group">
            <label for="confirmPassword">Confirmar contraseña:</label>
            <input type="password" id="confirmPassword" name="confirmPassword" required class="input-password">
        </div>


        <button type="submit" class="btn-primary">Registrarse</button>
    </form>

</main>

<?php
// Limpiar mensajes de sesión para evitar que persistan
unset($_SESSION['registro_errors'], $_SESSION['registro_old'], $_SESSION['registro_success']);
?>

<script src="<?php echo BASE_URL; ?>Public/js/registro.js?v=1.0"></script>