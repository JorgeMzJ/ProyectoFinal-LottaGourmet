<main class="contenido-principal">
    <div class="admin-panel-card">
        <h1>🛠️ Bienvenido Administrador</h1>
        <?php if (isset($lowStock) && is_array($lowStock) && count($lowStock) > 0): ?>
            <div class="alert-low-stock">
                <strong>Productos con stock bajo:</strong>
                <ul>
                    <?php foreach ($lowStock as $p): ?>
                        <li>
                            <?php echo htmlspecialchars($p['nombre']); ?> — stock: <strong><?php echo (int)$p['stock']; ?></strong>
                            <a class="btn-restock" href="<?php echo BASE_URL; ?>admin/productos?id=<?php echo (int)$p['id_producto']; ?>">Ver</a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <div class="admin-panel-grid">
            <a class="admin-panel-item" href="<?php echo BASE_URL; ?>admin/productos">
                <div class="admin-panel-icon">📦</div>
                <div class="admin-panel-title">Gestión de Productos</div>
                <div class="admin-panel-desc">Agregar, editar o eliminar productos del catálogo y gestionar promociones.</div>
            </a>
            <a class="admin-panel-item" href="<?php echo BASE_URL; ?>admin/compras">
                <div class="admin-panel-icon">🧾</div>
                <div class="admin-panel-title">Compras y Pedidos</div>
                <div class="admin-panel-desc">Revisa todas las compras y pedidos especiales realizados por los clientes.</div>
            </a>
            <a class="admin-panel-item" href="<?php echo BASE_URL; ?>admin/restock">
                <div class="admin-panel-icon">📋</div>
                <div class="admin-panel-title">Restock de Productos</div>
                <div class="admin-panel-desc">Consulta la estimación de productos que necesitan reposición.</div>
            </a>
            <a class="admin-panel-item" href="<?php echo BASE_URL; ?>admin/graficas">
                <div class="admin-panel-icon">📊</div>
                <div class="admin-panel-title">Gráficas de Ventas</div>
                <div class="admin-panel-desc">Visualiza el desempeño de ventas semanales en gráficos.</div>
            </a>
        </div>
    </div>
</main>
<style>
.admin-panel-main { background: #f7f8fa; min-height: 80vh; padding: 40px 0; }
.admin-panel-card { background: #fff; border-radius: 18px; box-shadow: 0 4px 24px rgba(0,0,0,0.08); max-width: 900px; margin: 0 auto; padding: 36px 48px; }
.admin-panel-card h1 { font-size: 2.2em; margin-bottom: 32px; font-family: 'Comfortaa', sans-serif; }
.alert-low-stock { background: #fff7ed; border: 1px solid #fdba74; color: #7c2d12; padding: 12px 16px; border-radius: 10px; margin-bottom: 18px; }
.alert-low-stock ul { margin: 8px 0 0 0; padding-left: 18px; }
.btn-restock { display: inline-block; margin-left: 8px; padding: 4px 10px; border-radius: 8px; background: #2563eb; color: #fff; text-decoration: none; font-weight: 700; }
.admin-panel-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 24px; }
.admin-panel-item { background: #f4f6fb; border-radius: 12px; padding: 28px 22px; text-decoration: none; color: #222; box-shadow: 0 2px 8px rgba(0,0,0,0.04); transition: box-shadow 0.2s, transform 0.2s; display: flex; flex-direction: column; align-items: flex-start; }
.admin-panel-item:hover { box-shadow: 0 6px 24px rgba(0,0,0,0.10); transform: translateY(-2px) scale(1.03); }
.admin-panel-icon { font-size: 2.8em; margin-bottom: 12px; }
.admin-panel-title { font-size: 1.25em; font-weight: 700; margin-bottom: 8px; }
.admin-panel-desc { font-size: 1em; color: #555; }
@media (max-width: 900px) { .admin-panel-grid { grid-template-columns: 1fr; gap: 20px; } .admin-panel-card { padding: 24px 10px; } }
</style>
