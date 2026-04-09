<?php

if (session_status() === PHP_SESSION_NONE) {
	session_start();
}
$nombreUsuario = $_SESSION['usuario_nombre'] ?? '';
$emailUsuario = $_SESSION['usuario_email'] ?? '';
?>

<div id="notificationBar" class="notification-bar">¡Acción realizada exitosamente!</div>

<main class="contenido-principal">
	<h1>Pedidos y Citas Personalizadas</h1>

	<div class="usuario-info-cita" style="margin-bottom:18px;padding:12px 18px;background:#f9f9f9;border-radius:8px;">
		<strong>Atendiendo a:</strong><br>
		<span>Nombre: <?php echo htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Cliente'); ?></span><br>
		<span>Email: <?php echo htmlspecialchars($_SESSION['usuario_email'] ?? 'correo@ejemplo.com'); ?></span>
	</div>

	<?php if (isset($_GET['stripe']) && $_GET['stripe'] === 'cancel'): ?>
		<div
			style="margin-bottom: 18px; padding: 14px 18px; border-radius: 10px; background: #fff4f4; color: #a94442; border: 1px solid #f5c6cb;">
			<strong>Pago cancelado:</strong> Tu cotización no fue procesada. Puedes intentar de nuevo cuando estés listo.
		</div>
	<?php endif; ?>

	<div class="citas-section-block form-card">
		<h2 style="color: #2c3e50; margin-bottom: 16px;">Arma tu Pastel (Cotizador)</h2>

		<div class="citas-inner-split">
			<div class="citas-form-col">
				<form id="form-cotizador-api">
					<div class="form-group">
						<label for="api-personas">Número de Personas</label>
						<input type="number" id="api-personas" name="personas" min="10" value="10" required>
					</div>

					<div style="display: flex; gap: 10px;">
						<div class="form-group" style="flex: 1;">
							<label for="api-pan">Tipo de Pan</label>
							<select id="api-pan" name="pan" required>
								<option value="Vainilla" selected>Vainilla</option>
								<option value="Chocolate">Chocolate</option>
								<option value="Zanahoria">Zanahoria</option>
							</select>
						</div>
						<div class="form-group" style="flex: 1;">
							<label for="api-relleno">Relleno</label>
							<select id="api-relleno" name="relleno" required>
								<option value="Fresa" selected>Fresa</option>
								<option value="Nutella">Nutella</option>
								<option value="Cajeta">Cajeta</option>
							</select>
						</div>
						<div class="form-group" style="flex: 1;">
							<label for="api-cobertura">Cobertura</label>
							<select id="api-cobertura" name="cobertura" required>
								<option value="Crema" selected>Crema</option>
								<option value="Fondant">Fondant</option>
								<option value="Chocolate">Chocolate</option>
							</select>
						</div>
					</div>

					<div class="form-group">
						<label>Omitir Ingredientes (Aplica descuento en el total)</label>
						<div style="display: flex; gap: 15px; font-size: 0.9em; color: #444; margin-top: 5px;">
							<label><input type="checkbox" name="eliminar_ingrediente" value="Nuez"> Sin Nuez</label>
							<label><input type="checkbox" name="eliminar_ingrediente" value="Almendra"> Sin
								Almendra</label>
							<label><input type="checkbox" name="eliminar_ingrediente" value="Lactosa"> Sin
								Lactosa</label>
						</div>
					</div>

					<div class="form-group">
						<label for="api-notas">Notas Adicionales</label>
						<textarea id="api-notas" name="notas" rows="2"
							placeholder="Ej. Escribir 'Feliz Cumpleaños', sin fruta en la decoración..."
							style="padding: 10px; border-radius: 8px; border: 1px solid #e6e6e6; resize: vertical;"></textarea>
					</div>

					<div class="form-group" id="fecha-entrega-api-container"
						style="display: none; background: #e8f5e9; padding: 10px; border-radius: 8px; border: 1px solid #c8e6c9; margin-top: 10px;">
						<label for="api-fecha-entrega" style="color: #2e7d32;">📅 Selecciona tu fecha de
							recolección</label>
						<input type="date" id="api-fecha-entrega" name="fecha_entrega" required>
					</div>

					<div style="display: flex; gap: 10px; margin-top: 15px;">
						<button type="button" id="btn-calcular-api" class="btn-primary" style="flex: 1;">Calcular
							Cotización</button>
						<button type="button" id="btn-realizar-pedido" class="btn-primary"
							style="flex: 1; background-color: #28a745; display: none;">Realizar Pedido</button>
					</div>
				</form>
			</div>

			<div class="citas-visual-col">
				<div class="pastel-ilustracion">
					<div id="capa-cobertura" class="pastel-capa cobertura crema"></div>
					<div id="capa-relleno" class="pastel-capa relleno fresa"></div>
					<div id="capa-pan" class="pastel-capa pan vainilla"></div>
					<div class="base-pastel"></div>
				</div>

				<div id="ticket-cotizacion" class="ticket-resumen">
					<p style="text-align:center; color:#3d3d3dff; font-size:0.9em;">Haz clic en calcular para ver el
						desglose.</p>
				</div>
			</div>
		</div>
	</div>

	<div class="citas-section-block form-card" style="margin-top: 30px;">
		<h2 style="color: #2c3e50; margin-bottom: 16px;">Paquetes Predefinidos</h2>

		<div class="citas-inner-split">
			<div class="citas-form-col paquetes-grid">
				<?php if (!empty($paquetes)): ?>
					<?php foreach ($paquetes as $paquete): ?>
						<?php
						$icon = '🎁';
						if ($paquete['tipo_evento'] === 'Cumpleaños')
							$icon = '🎂';
						elseif ($paquete['tipo_evento'] === 'Boda')
							$icon = '💍';
						elseif ($paquete['tipo_evento'] === 'Corporativo')
							$icon = '🏢';
						?>
						<div class="paquete-card" data-paquete-id="<?php echo $paquete['id_paquete']; ?>"
							data-paquete-nombre="<?php echo htmlspecialchars($paquete['nombre']); ?>"
							data-paquete-precio="<?php echo htmlspecialchars($paquete['precio']); ?>"
							data-paquete-desc="<?php echo htmlspecialchars($paquete['descripcion']); ?>">
							<div>
								<h3><?php echo $icon . ' ' . htmlspecialchars($paquete['tipo_evento']); ?></h3>
								<p class="paquete-desc-corta"><?php echo htmlspecialchars($paquete['descripcion']); ?></p>
							</div>
							<span class="paquete-precio">$<?php echo number_format((float) $paquete['precio'], 0); ?></span>
						</div>
					<?php endforeach; ?>
				<?php else: ?>
					<div class="citas-form-col paquetes-grid">
						<div class="paquete-card" data-paquete="Cumpleaños" data-id="2" data-precio="2200"
							data-desc="1 Pastel mediano + 15 postres">
							<div>
								<h3>🎂 Cumpleaños</h3>
								<p class="paquete-desc-corta">1 Pastel mediano + 15 postres</p>
							</div>
							<span class="paquete-precio">$2,200</span>
						</div>
						<div class="paquete-card" data-paquete="Boda" data-id="3" data-precio="3200"
							data-desc="20 postres elegantes">
							<div>
								<h3>💍 Boda</h3>
								<p class="paquete-desc-corta">20 postres elegantes</p>
							</div>
							<span class="paquete-precio">$3,200</span>
						</div>
						<div class="paquete-card" data-paquete="Corporativo" data-id="6" data-precio="2800"
							data-desc="20 postres premium">
							<div>
								<h3>🏢 Corporativo</h3>
								<p class="paquete-desc-corta">20 postres premium</p>
							</div>
							<span class="paquete-precio">$2,800</span>
						</div>
					</div>
				<?php endif; ?>
			</div>

			<div class="citas-visual-col" style="display: flex; flex-direction: column; justify-content: center;">
				<div id="panel-reserva-vacio" style="text-align: center; color: #3d3d3dff;">
					<p>👈 Selecciona un paquete para ver los detalles y agendar.</p>
				</div>

				<div id="form-reserva-paquete"
					style="display: none; background: #fdfbf7; padding: 20px; border-radius: 10px; border: 2px dashed #ccc;">
					<h3 id="paquete-seleccionado-titulo" style="margin-top:0; color: var(--accent);"></h3>
					<p id="paquete-seleccionado-desc" style="color: #666; margin-bottom: 20px; font-size: 0.9em;"></p>

					<form id="form-paquete-rapido">
						<div class="form-group">
							<label for="paquete-personas">Número de Personas</label>
							<input type="number" id="paquete-personas" min="10" value="20" required>
						</div>
						<div class="form-group">
							<label for="paquete-fecha">Fecha del Evento</label>
							<input type="date" id="paquete-fecha" required>
						</div>
						<div style="font-size: 1.2em; font-weight: bold; text-align: right; margin: 15px 0;">
							Total estimado: <span id="paquete-total-estimado" style="color: var(--accent);">$0</span>
							MXN
						</div>
						<button type="button" id="btn-reservar-paquete" class="btn-primary"
							style="background-color: #28a745;">Reservar Paquete</button>
					</form>
				</div>
			</div>
		</div>
	</div>
</main>
<script>
	window.BASE_URL = '<?php echo BASE_URL; ?>';
	window.STRIPE_PUBLIC_KEY = '<?php echo STRIPE_PUBLIC_KEY; ?>';
</script>
<script src="https://js.stripe.com/v3/"></script>
<script src="<?php echo BASE_URL; ?>Public/js/citas-pedido.js?v=5.0"></script>