<?php
// View/paginas/citas.php
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}
$errors = $_SESSION['citas_errors'] ?? [];
$old = $_SESSION['citas_old'] ?? [];
$success = isset($_GET['success']) || isset($_SESSION['citas_success']);
?>

<!-- Simple notification bar -->
<div id="notificationBar" class="notification-bar">¡Pedido realizado exitosamente! Nos contactaremos pronto.</div>

<!-- Overlay and Modals Removed -->
<main class="contenido-principal">
	<h1>Pedidos y Citas Personalizadas</h1>

	<?php
	if (session_status() === PHP_SESSION_NONE) {
		session_start();
	}
	$nombreUsuario = $_SESSION['usuario_nombre'] ?? '';
	$emailUsuario = $_SESSION['usuario_email'] ?? '';
	?>
	<div class="usuario-info-cita" style="margin-bottom:18px;padding:12px 18px;background:#f9f9f9;border-radius:8px;">
		<strong>Pedido especial para:</strong><br>
		<span>Nombre: <?php echo htmlspecialchars($nombreUsuario); ?></span><br>
		<span>Email: <?php echo htmlspecialchars($emailUsuario); ?></span>
	</div>

	<?php if ($success): ?>
		<div class="mensaje-exito">
			<h2>¡Pedido recibido!</h2>
			<p><?php echo htmlspecialchars($_SESSION['citas_success'] ?? 'Gracias, revisaremos tu pedido.'); ?></p>
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

	<form action="<?php echo BASE_URL; ?>citas/guardar" method="post" id="citasForm" class="form-card">
		<div class="form-row">
			<div class="form-group">
				<label for="tipoEvento">Tipo de evento</label>
				<select id="tipoEvento" name="tipoEvento" required>
					<?php $sel = $old['tipoEvento'] ?? 'Pedido'; ?>
					<option value="Pedido" <?php echo ($sel=='Pedido')? 'selected':''; ?>>Pedido</option>
					<option value="Cumpleaños" <?php echo ($sel=='Cumpleaños')? 'selected':''; ?>>Cumpleaños</option>
					<option value="Boda" <?php echo ($sel=='Boda')? 'selected':''; ?>>Boda</option>
					<option value="Corporativo" <?php echo ($sel=='Corporativo')? 'selected':''; ?>>Evento corporativo</option>
					<option value="Otro" <?php echo ($sel=='Otro')? 'selected':''; ?>>Otro</option>
				</select>
			</div>
		</div>

		<div class="form-row" style="margin-top: 24px;">
			<div class="form-group" id="otroContainer" style="display:<?php echo (isset($old['tipoEvento']) && $old['tipoEvento']=='Otro')? 'block':'none'; ?>;">
				<label for="otroEvento">Especificar (si eliges Otro)</label>
				<input type="text" id="otroEvento" name="tipoEventoOtro" maxlength="120" value="<?php echo htmlspecialchars($old['tipoEventoOtro'] ?? ''); ?>">
			</div>
		</div>

		<div id="productosContainer">
			<h3 style="margin: 24px 0 16px 0; color: #1f2937; font-size: 1.3em;">Selecciona productos para tu pedido</h3>
			<div class="modal-productos-grid">
				<?php if (isset($productos) && is_array($productos) && count($productos) > 0): ?>
					<?php foreach ($productos as $p): ?>
						<div class="modal-producto" data-product-id="<?php echo $p['id_producto']; ?>" data-price="<?php echo $p['precio']; ?>">
							<div class="modal-producto-name"><?php echo htmlspecialchars($p['nombre']); ?></div>
							<div class="modal-producto-price">$<?php echo number_format($p['precio'], 2); ?></div>
							<?php if (!empty($p['descripcion'])): ?>
								<div class="producto-desc-wrapper" style="font-size: 0.85em; color: #555555; margin-bottom: 8px; text-align: center;">
									<div class="producto-desc-text" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; line-height: 1.4em; max-height: 2.8em; transition: max-height 0.3s ease;">
										<?php echo htmlspecialchars($p['descripcion']); ?>
									</div>
									<button type="button" class="btn-ver-mas-desc" aria-expanded="false" style="display: none; color: var(--accent); background: none; border: none; padding: 0; text-decoration: underline; cursor: pointer; font-weight: 700; margin-top: 4px; font-size: 0.9em;">Ver más...</button>
								</div>
							<?php endif; ?>
							<div class="producto-qty-controls" style="display: flex; align-items: center; justify-content: space-between; margin-top: 12px; background: #f3f4f6; border-radius: 8px; padding: 4px;">
								<button type="button" class="btn-qty-minus" aria-label="Disminuir cantidad de <?php echo htmlspecialchars($p['nombre']); ?>" style="width: 32px; height: 32px; border-radius: 6px; border: 1px solid var(--accent); background: #fff; color: var(--accent); font-weight: bold; font-size: 1.2em; cursor: pointer; box-shadow: 0 1px 3px rgba(0,0,0,0.1); transition: all 0.2s;">-</button>
								<span class="qty-display" aria-live="polite" style="font-weight: 600; font-size: 1.1em; color: #1f2937;">0</span>
								<button type="button" class="btn-qty-plus" aria-label="Aumentar cantidad de <?php echo htmlspecialchars($p['nombre']); ?>" style="width: 32px; height: 32px; border-radius: 6px; border: none; background: var(--accent); color: #fff; font-weight: bold; font-size: 1.2em; cursor: pointer; box-shadow: 0 1px 3px rgba(0,0,0,0.1); transition: all 0.2s;">+</button>
							</div>
						</div>
					<?php endforeach; ?>
				<?php else: ?>
					<p>No hay productos disponibles.</p>
				<?php endif; ?>
			</div>
		</div>

		<!-- Resumen del carrito -->
		<div class="form-row">
			<div class="form-group full">
				<div id="carritoResumen" class="carrito-resumen">
					<p style="text-align: center; color: #555555; font-weight: bold;">No hay productos seleccionados.</p>
				</div>
			</div>
		</div>
		<div class="form-row">
			<div class="form-group">
				<button type="submit" class="btn-primary">Enviar Pedido</button>
			</div>
		</div>

	</form>
	<script src="<?php echo BASE_URL; ?>Public/js/citas-pedido.js?v=3.4"></script>
	<script src="<?php echo BASE_URL; ?>Public/js/citas.js?v=1.1"></script>
</main>

<?php
// limpiar mensajes (se hace en controlador tambien)
unset($_SESSION['citas_errors'], $_SESSION['citas_old'], $_SESSION['citas_success']);
?>


