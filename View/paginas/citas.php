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

<!-- Overlay del modal -->
<div id="modalOverlay" class="modal-overlay"></div>

<!-- Modal de fecha para paquetes -->
<div id="modalFechaPaquete" class="modal-fecha-paquete" style="display: none;">
	<div class="modal-fecha-content">
		<button class="modal-close" id="btnCerrarModalFecha">&times;</button>
		<h2>Selecciona la fecha del evento</h2>
		<p class="modal-fecha-desc">¿Cuándo necesitas tu paquete?</p>
		
		<div class="paquete-info-mini">
			<strong id="paqueteNombreModal"></strong>
			<span id="paquetePrecioModal"></span>
		</div>
		
		<div class="form-group-modal">
			<label for="fechaEventoPaquete">Fecha del evento *</label>
			<input type="date" id="fechaEventoPaquete" name="fechaEventoPaquete" required>
			<small class="form-help-modal">La fecha debe ser al menos 1 día después de hoy</small>
		</div>
		
		<div class="modal-fecha-actions">
			<button type="button" class="btn-cancelar-modal" id="btnCancelarFecha">Cancelar</button>
			<button type="button" class="btn-continuar-modal" id="btnContinuarPaquete">Continuar</button>
		</div>
	</div>
</div>

<!-- Modal de productos -->
<div id="productosModal" class="modal-content" style="display: none;">
	<button class="modal-close" id="btnCerrarModal">&times;</button>
	<h2>Selecciona productos para tu pedido</h2>
	
	<div class="modal-productos-grid">
		<?php if (isset($productos) && is_array($productos) && count($productos) > 0): ?>
			<?php foreach ($productos as $p): ?>
				<div class="modal-producto" data-product-id="<?php echo $p['id_producto']; ?>" data-price="<?php echo $p['precio']; ?>">
					<div class="modal-producto-name"><?php echo htmlspecialchars($p['nombre']); ?></div>
					<div class="modal-producto-price">$<?php echo number_format($p['precio'], 2); ?></div>
					<?php if (!empty($p['descripcion'])): ?>
						<div style="font-size: 0.85em; color: #666; margin-bottom: 8px;"><?php echo htmlspecialchars(substr($p['descripcion'], 0, 60)); ?></div>
					<?php endif; ?>
					<div class="modal-producto-qty">
						<label style="font-size: 0.85em;">Cantidad:</label>
						<input type="number" value="1" min="1" max="999" step="1">
					</div>
					<button class="btn-add-carrito">Agregar al carrito</button>
				</div>
			<?php endforeach; ?>
		<?php else: ?>
			<p>No hay productos disponibles.</p>
		<?php endif; ?>
	</div>
</div>

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

		<!-- Sección de paquetes según el tipo de evento -->
		<div id="paquetesContainer" style="display: none;">
			<h3 style="margin: 24px 0 16px 0; color: #1f2937; font-size: 1.3em;">Paquetes Disponibles</h3>
			<div class="paquetes-grid" id="paquetesGrid">
				<!-- Los paquetes se cargarán dinámicamente aquí -->
			</div>
		</div>

		<div id="formularioPersonalizado">
		<div class="form-row">
			<div class="form-group" id="otroContainer" style="display:<?php echo (isset($old['tipoEvento']) && $old['tipoEvento']=='Otro')? 'block':'none'; ?>;">
				<label for="otroEvento">Especificar (si eliges Otro)</label>
				<input type="text" id="otroEvento" name="tipoEventoOtro" maxlength="120" value="<?php echo htmlspecialchars($old['tipoEventoOtro'] ?? ''); ?>">
			</div>
		</div>

		<!-- Resumen del carrito -->
		<div class="form-row">
			<div class="form-group full">
				<div id="carritoResumen" class="carrito-resumen">
					<p style="text-align: center; color: #999;">No hay productos en el carrito. Abre el menú para agregar.</p>
				</div>
			</div>
		</div>
		<div class="form-row">
			<div class="form-group">
				<button type="submit" class="btn-primary">Enviar Pedido</button>
			</div>
		</div>
		</div> <!-- Cierre de formularioPersonalizado -->
	</form>
	<script>
	// Inyectar datos de paquetes para JavaScript
	window.paquetesEventos = <?php echo json_encode($paquetes ?? []); ?>;
	</script>
	<script src="<?php echo BASE_URL; ?>Public/js/citas-pedido.js?v=2.0"></script>
	<script src="<?php echo BASE_URL; ?>Public/js/citas.js?v=1.0"></script>

	<style>
	/* Estilos para paquetes de eventos */
	.paquetes-grid {
		display: grid;
		grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
		gap: 24px;
		margin-bottom: 24px;
	}
	
	.paquete-card {
		background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%);
		border-radius: 16px;
		padding: 24px;
		box-shadow: 0 4px 16px rgba(0,0,0,0.08);
		border: 2px solid #e5e7eb;
		transition: all 0.3s;
		display: flex;
		flex-direction: column;
	}
	
	.paquete-card:hover {
		transform: translateY(-4px);
		box-shadow: 0 8px 24px rgba(59, 130, 246, 0.15);
		border-color: #3b82f6;
	}
	
	.paquete-header {
		display: flex;
		justify-content: space-between;
		align-items: flex-start;
		margin-bottom: 12px;
		gap: 12px;
	}
	
	.paquete-header h4 {
		margin: 0;
		color: #1f2937;
		font-size: 1.3em;
		font-weight: 700;
		flex: 1;
	}
	
	.badge-ahorro {
		background: linear-gradient(135deg, #f59e0b 0%, #ef4444 100%);
		color: #fff;
		padding: 6px 12px;
		border-radius: 8px;
		font-size: 0.8em;
		font-weight: 700;
		white-space: nowrap;
	}
	
	.paquete-desc {
		color: #6b7280;
		font-size: 0.95em;
		line-height: 1.5;
		margin-bottom: 16px;
		flex-grow: 1;
	}
	
	.paquete-info {
		display: flex;
		justify-content: space-between;
		align-items: center;
		margin-bottom: 16px;
		padding: 12px 0;
		border-top: 2px solid #f3f4f6;
	}
	
	.paquete-cantidad {
		color: #6b7280;
		font-weight: 600;
		font-size: 0.95em;
	}
	
	.paquete-precio {
		color: #10b981;
		font-weight: 700;
		font-size: 1.6em;
	}
	
	.btn-seleccionar-paquete {
		background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
		color: #fff;
		border: none;
		padding: 14px 20px;
		border-radius: 10px;
		font-weight: 700;
		font-size: 1em;
		cursor: pointer;
		transition: all 0.2s;
		width: 100%;
	}
	
	.btn-seleccionar-paquete:hover {
		transform: translateY(-2px);
		box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
	}
	
	.btn-personalizado {
		background: #fff;
		color: #3b82f6;
		border: 2px solid #3b82f6;
		padding: 12px 24px;
		border-radius: 10px;
		font-weight: 600;
		font-size: 1em;
		cursor: pointer;
		transition: all 0.2s;
	}
	
	.btn-personalizado:hover {
		background: #3b82f6;
		color: #fff;
	}
	
	@media (max-width: 768px) {
		.paquetes-grid {
			grid-template-columns: 1fr;
		}
	}
	
	/* Estilos para modal de fecha */
	.modal-fecha-paquete {
		position: fixed;
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
		display: flex;
		align-items: center;
		justify-content: center;
		z-index: 10001;
	}
	
	.modal-fecha-content {
		background: #fff;
		border-radius: 20px;
		padding: 40px;
		max-width: 500px;
		width: 90%;
		box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
		position: relative;
		animation: slideIn 0.3s ease;
	}
	
	@keyframes slideIn {
		from {
			opacity: 0;
			transform: translateY(-30px);
		}
		to {
			opacity: 1;
			transform: translateY(0);
		}
	}
	
	.modal-fecha-content h2 {
		margin: 0 0 12px 0;
		color: #1f2937;
		font-size: 1.8em;
		font-weight: 700;
	}
	
	.modal-fecha-desc {
		color: #6b7280;
		margin-bottom: 24px;
		font-size: 1em;
	}
	
	.paquete-info-mini {
		background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
		border-left: 4px solid #3b82f6;
		padding: 16px;
		border-radius: 8px;
		margin-bottom: 24px;
		display: flex;
		justify-content: space-between;
		align-items: center;
	}
	
	.paquete-info-mini strong {
		color: #1e40af;
		font-size: 1.1em;
	}
	
	.paquete-info-mini span {
		color: #10b981;
		font-weight: 600;
		font-size: 1em;
	}
	
	.form-group-modal {
		margin-bottom: 28px;
	}
	
	.form-group-modal label {
		display: block;
		margin-bottom: 8px;
		color: #374151;
		font-weight: 600;
		font-size: 0.95em;
	}
	
	.form-group-modal input[type="date"] {
		width: 100%;
		padding: 14px;
		border: 2px solid #e5e7eb;
		border-radius: 10px;
		font-size: 1em;
		transition: all 0.2s;
	}
	
	.form-group-modal input[type="date"]:focus {
		outline: none;
		border-color: #3b82f6;
		box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
	}
	
	.form-help-modal {
		display: block;
		margin-top: 6px;
		font-size: 0.85em;
		color: #6b7280;
	}
	
	.modal-fecha-actions {
		display: flex;
		gap: 12px;
		justify-content: flex-end;
	}
	
	.btn-cancelar-modal,
	.btn-continuar-modal {
		padding: 12px 24px;
		border-radius: 10px;
		font-weight: 600;
		font-size: 1em;
		cursor: pointer;
		transition: all 0.2s;
		border: none;
	}
	
	.btn-cancelar-modal {
		background: #f3f4f6;
		color: #6b7280;
	}
	
	.btn-cancelar-modal:hover {
		background: #e5e7eb;
	}
	
	.btn-continuar-modal {
		background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
		color: #fff;
	}
	
	.btn-continuar-modal:hover {
		transform: translateY(-2px);
		box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
	}
	</style>



</main>

<?php
// limpiar mensajes (se hace en controlador tambien)
unset($_SESSION['citas_errors'], $_SESSION['citas_old'], $_SESSION['citas_success']);
?>


