<style>
	/* Estilos festivos para la página de promociones */
	.contenido-principal {
		background: linear-gradient(135deg, #FFD700 0%, #FFA500 25%, #FF6B9D 50%, #C44569 75%, #F8B195 100%);
		background-size: 400% 400%;
		animation: festivo-bg 15s ease infinite;
	}

	@keyframes festivo-bg {
		0% { background-position: 0% 50%; }
		50% { background-position: 100% 50%; }
		100% { background-position: 0% 50%; }
	}

	.contenido-principal h2 {
		color: #fff;
		text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.5), 0 0 20px rgba(255, 255, 0, 0.8);
		font-size: 2.5em;
		letter-spacing: 2px;
	}

	.contenido-principal > p {
		color: #fff;
		font-size: 1.2em;
		font-weight: bold;
		text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.5);
	}

	/* Grilla de promociones */
	.promociones-grid {
		display: grid;
		grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
		gap: 24px;
		padding: 24px;
		max-width: 1400px;
		margin: 0 auto;
	}

	.promociones-grid .producto-tarjeta,
	.menu-contenedor .producto-tarjeta {
		background: linear-gradient(135deg, #fff9e6 0%, #ffe6f0 100%);
		border: 3px solid #FFD700;
		box-shadow: 0 8px 20px rgba(255, 107, 157, 0.6), 0 0 15px rgba(255, 215, 0, 0.4);
		transform: translateY(0);
		transition: all 0.3s ease;
		border-radius: 15px;
	}

	.producto-footer { display:flex; justify-content:space-between; align-items:center; gap:8px; padding-top:12px; }

	.precio-wrap { display:flex; flex-direction:column; gap:4px; min-width:0; }

	.precio-original { text-decoration: line-through; color: #9a9a9a; font-size: 0.9em; }

	.btn-pedir { white-space: nowrap; flex-shrink: 0; }

	.promociones-grid .producto-tarjeta:hover,
	.menu-contenedor .producto-tarjeta:hover {
		transform: translateY(-10px) scale(1.03);
		box-shadow: 0 12px 30px rgba(255, 107, 157, 0.8), 0 0 25px rgba(255, 215, 0, 0.6);
	}

	.producto-tarjeta img {
		border-radius: 15px 15px 0 0;
	}

	.producto-tarjeta h3 {
		color: #C44569;
		font-size: 1.3em;
		text-shadow: 1px 1px 2px rgba(255, 215, 0, 0.5);
	}

	/* Asegurar que el modal muestre título con estilo neutro (anular estilos festivos)
	   y que el tamaño de fuente sea consistente con el resto de modales (menu) */
	.modal-contenido h2, .modal-contenido > h2 {
		color: #333 !important;
		text-shadow: none !important;
		background: none !important;
		border: none !important;
		font-size: 1.5em !important;
		font-family: inherit !important;
		margin: 0 0 8px 0 !important;
		padding: 0 !important;
		letter-spacing: normal !important;
	}

	.producto-precio {
		color: #C44569 !important;
		font-size: 1.5em !important;
		text-shadow: 1px 1px 3px rgba(255, 215, 0, 0.6) !important;
		font-weight: bold !important;
	}

	.btn-pedir {
		background: linear-gradient(135deg, #FFD700, #FFA500) !important;
		color: #333 !important;
		font-weight: bold !important;
		box-shadow: 0 4px 10px rgba(255, 107, 157, 0.4) !important;
		border: 2px solid #FF6B9D !important;
		transition: all 0.3s ease !important;
	}

	.btn-pedir:hover {
		transform: scale(1.1) !important;
		box-shadow: 0 6px 15px rgba(255, 107, 157, 0.8) !important;
	}
</style>

<main class="contenido-principal">
    <div class="menu-header">
        <div>
            <h2>Promociones Especiales</h2>
            <p>¡Aprovecha nuestras ofertas por tiempo limitado!</p>
        </div>
        <button id="verCarritoBtn" class="btn-ver-carrito">Ver Carrito</button>
    </div>

    <div class="promociones-grid">
		<?php
		// Mostrar promociones reales desde la BD (campo en_promocion)
		if (isset($ofertas) && count($ofertas) > 0) {
			foreach ($ofertas as $oferta) {
			$id_producto = $oferta['id_producto'] ?? ($oferta['id'] ?? null);
			$nombre = $oferta['nombre'] ?? '';
			$descripcion = $oferta['descripcion'] ?? '';
			$precio = isset($oferta['precio']) ? (float)$oferta['precio'] : 0.0;
			$imagen = $oferta['imagen'] ?? 'placeholder.jpg';
			$stock = $oferta['stock'] ?? 0;
			// Si existe precio_oferta y en_promocion, mostrarlo; si no, mantener precio
				$precio_oferta = (!empty($oferta['en_promocion']) && !empty($oferta['precio_oferta'])) ? (float)$oferta['precio_oferta'] : null;

				// Si no hay precio_oferta calculado en DB, como fallback aplicar 20% de descuento
				if (empty($precio_oferta) && !empty($oferta['en_promocion'])) {
					$precio_oferta = round($precio * 0.80, 2);
				}
		?>
		<div class="producto-tarjeta<?php echo ((int)$stock <= 0) ? ' agotado' : ''; ?>" data-id="<?php echo $id_producto; ?>" data-nombre="<?php echo htmlspecialchars($nombre); ?>" data-precio="<?php echo htmlspecialchars(number_format($precio_oferta ?? $precio, 2, '.', '')); ?>" data-precio-original="<?php echo htmlspecialchars(number_format($precio, 2, '.', '')); ?>" data-stock="<?php echo (int)$stock; ?>">
			<img src="<?php echo BASE_URL; ?>Public/img/<?php echo htmlspecialchars($imagen); ?>" alt="<?php echo htmlspecialchars($nombre); ?>" style="width: 100%; height: 300px; object-fit: cover;">
			<div class="producto-info">
				<h3><?php echo htmlspecialchars($nombre); ?></h3>
				<p><?php echo htmlspecialchars($descripcion); ?></p>
			</div>
			<div class="producto-footer">
				<div class="precio-wrap">
					<span class="precio-original">$<?php echo number_format($precio, 2); ?></span>
					<span class="producto-precio">$<?php echo number_format($precio_oferta ?? $precio, 2); ?></span>
					<?php if ((int)$stock > 0 && (int)$stock <= 5): ?>
						<span style="font-size: 0.85em; color: #666;">stock: <?php echo (int)$stock; ?></span>
					<?php endif; ?>
				</div>
				<?php if ((int)$stock <= 0): ?>
					<span class="agotado-badge">Agotado</span>
				<?php else: ?>
					<a href="#" class="btn-pedir">Agregar</a>
				<?php endif; ?>
			</div>
		</div>
		<?php
			}
		} else {
			echo "<p class='no-productos'>No hay promociones disponibles en este momento.</p>";
		}
		?>
	</div>
</main>
