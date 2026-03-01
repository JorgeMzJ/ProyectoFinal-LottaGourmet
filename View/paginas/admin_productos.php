<main class="contenido-principal">
    <div class="admin-panel-card">
        <h1>Gestión de Productos</h1>
        <p>Desde aquí puedes agregar, editar o quitar productos, y administrar promociones.</p>

        <?php
        // Mostrar errores o success si existen en sesión
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!empty($_SESSION['producto_errors'])) {
            echo '<div class="alert alert-error">';
            foreach ($_SESSION['producto_errors'] as $err) echo '<div>• ' . htmlspecialchars($err) . '</div>';
            echo '</div>';
            unset($_SESSION['producto_errors']);
        }
        if (!empty($_SESSION['producto_success'])) {
            echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['producto_success']) . '</div>';
            unset($_SESSION['producto_success']);
        }

        // Prefill from session 'producto_old' if exists (solo cuando se retornó por error)
        $old = $_SESSION['producto_old'] ?? null;
        if (isset($_SESSION['producto_old'])) unset($_SESSION['producto_old']);
        ?>

        <!-- Mini panel de restock masivo -->
        <div class="bulk-restock-panel">
            <div class="bulk-restock-header">
                <h3>Restock Masivo</h3>
                <p>Agrega stock a todos los productos simultáneamente</p>
            </div>
            <form id="bulkRestockForm" method="post" action="<?php echo BASE_URL; ?>admin/bulkRestock">
                <div class="bulk-restock-controls">
                    <input type="number" name="cantidad" id="bulkRestockQty" class="bulk-input" min="1" placeholder="Cantidad a agregar" required>
                    <button type="submit" class="btn-bulk-restock">Aplicar a Todos</button>
                </div>
            </form>
        </div>

        <div class="producto-form-grid">
            <!-- Formulario de edición/creación -->
            <div class="form-card">
                <div class="form-card-header">
                    <h3><?php echo isset($edit) && $edit ? 'Editar Producto' : 'Agregar Producto Nuevo'; ?></h3>
                </div>
                <form id="productoForm" method="post" action="<?php echo BASE_URL; ?>admin/<?php echo isset($edit) && $edit ? 'actualizarProducto' : 'guardarProducto'; ?>" enctype="multipart/form-data">
                    <?php if (isset($edit) && $edit): ?>
                        <input type="hidden" name="id_producto" value="<?php echo htmlspecialchars($edit['id_producto']); ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label class="form-label">Nombre del Producto</label>
                        <input type="text" name="nombre" class="form-input" value="<?php echo htmlspecialchars($old['nombre'] ?? ($edit['nombre'] ?? '')); ?>" required placeholder="Ej: Carlota de Fresa">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" class="form-textarea" rows="4" placeholder="Describe las características del producto..."><?php echo htmlspecialchars($old['descripcion'] ?? ($edit['descripcion'] ?? '')); ?></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Precio</label>
                            <input type="number" step="0.01" name="precio" class="form-input" value="<?php echo htmlspecialchars($old['precio'] ?? ($edit['precio'] ?? '')); ?>" required placeholder="0.00">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Stock</label>
                            <input type="number" name="stock" class="form-input" value="<?php echo htmlspecialchars($old['stock'] ?? ($edit['stock'] ?? '0')); ?>" required placeholder="0">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Imagen del Producto</label>
                        <div class="image-upload-wrapper">
                            <input type="file" name="imagen" id="imagenInput" accept="image/*" class="form-file">
                            <?php if (isset($edit) && !empty($edit['imagen'])): ?>
                                <div class="image-preview">
                                    <img src="<?php echo BASE_URL; ?>Public/img/<?php echo htmlspecialchars($edit['imagen']); ?>" alt="Preview">
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-group-promo">
                        <div class="promo-header">
                            <label class="form-label">Promoción</label>
                            <label class="switch">
                                <input type="hidden" name="en_promocion" value="0">
                                <input type="checkbox" name="en_promocion" value="1" <?php echo (!empty($old['en_promocion']) || (isset($edit) && !empty($edit['en_promocion']))) ? 'checked' : ''; ?>>
                                <span class="slider"></span>
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Precio en Oferta</label>
                            <input type="number" step="0.01" name="precio_oferta" class="form-input" value="<?php echo htmlspecialchars($old['precio_oferta'] ?? ($edit['precio_oferta'] ?? '')); ?>" placeholder="Dejar vacío si no aplica">
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary">
                            <?php echo isset($edit) ? 'Guardar Cambios' : 'Agregar Producto'; ?>
                        </button>
                        <?php if (isset($edit)): ?>
                            <a href="<?php echo BASE_URL; ?>admin/productos" class="btn-secondary">Cancelar</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Lista de productos -->
            <div class="products-list-card">
                <div class="form-card-header">
                    <h3>Lista de Productos</h3>
                </div>
                <div class="products-grid">
                    <?php foreach ($productos as $p): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <img src="<?php echo BASE_URL; ?>Public/img/<?php echo htmlspecialchars($p['imagen']); ?>" alt="<?php echo htmlspecialchars($p['nombre']); ?>">
                            <?php if (!empty($p['en_promocion'])): ?>
                                <span class="promo-badge">Promoción</span>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <h4><?php echo htmlspecialchars($p['nombre']); ?></h4>
                            <div class="product-meta">
                                <span class="product-id">ID: <?php echo htmlspecialchars($p['id_producto']); ?></span>
                                <span class="product-stock">Stock: <?php echo htmlspecialchars($p['stock']); ?></span>
                            </div>
                            <div class="product-price">
                                <?php if (!empty($p['precio_oferta'])): ?>
                                    <span class="price-original">$<?php echo number_format($p['precio'],2); ?></span>
                                    <span class="price-offer">$<?php echo number_format($p['precio_oferta'],2); ?></span>
                                <?php else: ?>
                                    <span class="price-current">$<?php echo number_format($p['precio'],2); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="product-actions">
                                <a class="btn-edit" href="<?php echo BASE_URL; ?>admin/productos?edit_id=<?php echo htmlspecialchars($p['id_producto']); ?>">Editar</a>
                                <form method="post" action="<?php echo BASE_URL; ?>admin/eliminarProducto" style="display:inline;" id="form-eliminar-<?php echo $p['id_producto']; ?>">
                                    <input type="hidden" name="id_producto" value="<?php echo htmlspecialchars($p['id_producto']); ?>">
                                    <button type="button" class="btn-delete" onclick="confirmarEliminar('<?php echo htmlspecialchars($p['nombre']); ?>', <?php echo $p['id_producto']; ?>)">Eliminar</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
                <script>
                // Cliente: validación básica antes de enviar
                (function(){
                    var form = document.getElementById('productoForm');
                    if (!form) return;
                    form.addEventListener('submit', function(e){
                        var nombre = form.querySelector('[name="nombre"]').value.trim();
                        var precio = form.querySelector('[name="precio"]').value.trim();
                        var stock = form.querySelector('[name="stock"]').value.trim();
                        var imagen = document.getElementById('imagenInput');
                        var errors = [];
                        if (!nombre) errors.push('Nombre requerido');
                        if (!precio || isNaN(precio) || Number(precio) <= 0) errors.push('Precio inválido');
                        if (stock === '' || isNaN(stock) || Number(stock) < 0) errors.push('Stock inválido');
                        if (imagen && imagen.files && imagen.files[0]) {
                            var file = imagen.files[0];
                            var allowed = ['image/jpeg','image/png','image/gif'];
                            if (allowed.indexOf(file.type) === -1) errors.push('Tipo de imagen no permitido');
                            if (file.size > 2*1024*1024) errors.push('Imagen demasiado grande (max 2MB)');
                        }
                        if (errors.length) {
                            e.preventDefault();
                            customAlert('Errores:\\n' + errors.join('\\n'), 'Error de validación');
                        }
                    });
                })();
                
                // Confirmar eliminación de producto
                async function confirmarEliminar(nombre, id) {
                    const confirmado = await customConfirm(`¿Eliminar el producto "${nombre}"?`, 'Confirmar eliminación');
                    if (confirmado) {
                        document.getElementById('form-eliminar-' + id).submit();
                    }
                }

                // Restock masivo
                (function(){
                    const bulkForm = document.getElementById('bulkRestockForm');
                    if (!bulkForm) return;
                    bulkForm.addEventListener('submit', async function(e){
                        e.preventDefault();
                        const qty = document.getElementById('bulkRestockQty').value;
                        const confirmado = await customConfirm(
                            `¿Agregar ${qty} unidades al stock de TODOS los productos?`,
                            'Confirmar Restock Masivo'
                        );
                        if (confirmado) {
                            bulkForm.submit();
                        }
                    });
                })();
                </script>
    </div>
</main>

<style>
/* Alertas */
.alert { padding: 14px 18px; border-radius: 12px; margin-bottom: 20px; font-weight: 500; }
.alert-error { background: #fff3f3; border: 2px solid #ffd6d6; color: #c33; }
.alert-success { background: #ebffef; border: 2px solid #c7f0d0; color: #2a7; }

/* Panel de restock masivo */
.bulk-restock-panel {
    background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
    border: 3px solid #3b82f6;
    border-radius: 16px;
    padding: 24px;
    margin-bottom: 24px;
    box-shadow: 0 8px 24px rgba(59, 130, 246, 0.15);
}
.bulk-restock-header h3 {
    margin: 0 0 8px 0;
    color: #1e40af;
    font-size: 1.4em;
    font-weight: 700;
}
.bulk-restock-header p {
    margin: 0 0 16px 0;
    color: #475569;
    font-size: 0.95em;
}
.bulk-restock-controls {
    display: flex;
    gap: 12px;
    align-items: center;
}
.bulk-input {
    flex: 1;
    padding: 14px 16px;
    border: 2px solid #93c5fd;
    border-radius: 10px;
    font-size: 1.05em;
    font-weight: 600;
    transition: all 0.2s;
}
.bulk-input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
}
.btn-bulk-restock {
    padding: 14px 28px;
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: #fff;
    border: none;
    border-radius: 10px;
    font-weight: 700;
    font-size: 1em;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
}
.btn-bulk-restock:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(59, 130, 246, 0.4);
}

/* Grid principal */
.producto-form-grid { display: grid; grid-template-columns: 420px 1fr; gap: 28px; margin-top: 24px; }
@media (max-width: 1100px) { .producto-form-grid { grid-template-columns: 1fr; } }

/* Tarjeta de formulario */
.form-card { background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%); border-radius: 16px; padding: 28px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border: 1px solid #e5e7eb; }
.form-card-header { margin-bottom: 24px; }
.form-card-header h3 { font-size: 1.5em; color: #1f2937; margin: 0; font-weight: 700; }

/* Campos del formulario */
.form-group { margin-bottom: 20px; }
.form-label { display: block; font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 0.95em; }
.form-input, .form-textarea { width: 100%; padding: 12px 14px; border: 2px solid #e5e7eb; border-radius: 10px; font-size: 1em; transition: all 0.2s; font-family: inherit; }
.form-input:focus, .form-textarea:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
.form-textarea { resize: vertical; min-height: 100px; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

/* Upload de imagen */
.image-upload-wrapper { position: relative; }
.form-file { padding: 10px; border: 2px dashed #d1d5db; border-radius: 10px; width: 100%; cursor: pointer; transition: border 0.2s; }
.form-file:hover { border-color: #3b82f6; }
.image-preview { margin-top: 14px; text-align: center; }
.image-preview img { max-width: 200px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); border: 3px solid #e5e7eb; }

/* Switch de promoción */
.form-group-promo { background: #fef3c7; border: 2px solid #fbbf24; border-radius: 12px; padding: 18px; margin-bottom: 20px; }
.promo-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
.switch { position: relative; display: inline-block; width: 54px; height: 28px; }
.switch input { opacity: 0; width: 0; height: 0; }
.slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #cbd5e1; border-radius: 28px; transition: 0.3s; }
.slider:before { position: absolute; content: ""; height: 20px; width: 20px; left: 4px; bottom: 4px; background-color: white; border-radius: 50%; transition: 0.3s; }
input:checked + .slider { background-color: #10b981; }
input:checked + .slider:before { transform: translateX(26px); }

/* Botones */
.form-actions { display: flex; gap: 12px; margin-top: 24px; }
.btn-primary { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: #fff; padding: 14px 24px; border-radius: 10px; border: none; font-weight: 700; cursor: pointer; transition: all 0.2s; font-size: 1em; }
.btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4); }
.btn-secondary { background: #fff; color: #374151; padding: 14px 20px; border-radius: 10px; border: 2px solid #d1d5db; text-decoration: none; font-weight: 600; transition: all 0.2s; display: inline-block; }
.btn-secondary:hover { background: #f3f4f6; border-color: #9ca3af; }

/* Lista de productos */
.products-list-card { background: #fff; border-radius: 16px; padding: 28px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border: 1px solid #e5e7eb; }
.products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; margin-top: 20px; }

/* Tarjeta de producto */
.product-card { background: linear-gradient(135deg, #ffffff 0%, #fafbfc 100%); border-radius: 14px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.06); border: 2px solid #f3f4f6; transition: all 0.3s; }
.product-card:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0,0,0,0.12); }
.product-image { position: relative; height: 200px; overflow: hidden; }
.product-image img { width: 100%; height: 100%; object-fit: cover; }
.promo-badge { position: absolute; top: 10px; right: 10px; background: linear-gradient(135deg, #f59e0b 0%, #ef4444 100%); color: #fff; padding: 6px 12px; border-radius: 8px; font-size: 0.85em; font-weight: 700; box-shadow: 0 2px 8px rgba(0,0,0,0.2); }
.product-info { padding: 16px; }
.product-info h4 { margin: 0 0 10px 0; font-size: 1.15em; color: #1f2937; font-weight: 700; }
.product-meta { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 0.9em; color: #6b7280; }
.product-price { margin-bottom: 14px; }
.price-original { text-decoration: line-through; color: #9ca3af; font-size: 0.95em; margin-right: 8px; }
.price-offer { color: #ef4444; font-weight: 700; font-size: 1.3em; }
.price-current { color: #10b981; font-weight: 700; font-size: 1.3em; }
.product-actions { display: flex; gap: 8px; }
.btn-edit, .btn-delete { flex: 1; padding: 10px; border-radius: 8px; font-weight: 600; font-size: 0.9em; cursor: pointer; transition: all 0.2s; border: none; text-decoration: none; text-align: center; }
.btn-edit { background: #dbeafe; color: #1e40af; }
.btn-edit:hover { background: #3b82f6; color: #fff; }
.btn-delete { background: #fee2e2; color: #b91c1c; }
.btn-delete:hover { background: #ef4444; color: #fff; }

.admin-panel-card h1 { margin-bottom: 6px; }
.admin-panel-card > p { color: #6b7280; margin-bottom: 8px; }
</style>