<main class="contenido-principal">
    <div class="admin-panel-card">
        <h1>Compras y Pedidos Especiales</h1>
        <p>Historial completo de todas las compras realizadas por los clientes.</p>
        
        <?php if (empty($compras)): ?>
            <div class="empty-state">
                <div class="empty-icon">📭</div>
                <h3>No hay compras registradas</h3>
                <p>Las compras realizadas por los clientes aparecerán aquí.</p>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table class="compras-table">
                    <thead>
                        <tr>
                            <th width="50"></th>
                            <th>Tipo</th>
                            <th>Cliente</th>
                            <th>Correo</th>
                            <th>Fecha Pedido</th>
                            <th>Fecha Evento</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($compras as $index => $compra): ?>
                            <?php 
                            $prioridadClass = '';
                            $prioridadLabel = '';
                            if ($compra['prioridad'] == 3) {
                                $prioridadClass = 'prioridad-alta';
                                $prioridadLabel = '🔴 URGENTE';
                            } elseif ($compra['prioridad'] == 2) {
                                $prioridadClass = 'prioridad-media';
                                $prioridadLabel = '🟠 Próximo';
                            } elseif ($compra['prioridad'] == 1) {
                                $prioridadClass = 'prioridad-baja';
                                $prioridadLabel = '🟢 Normal';
                            }
                            ?>
                            <tr class="compra-row <?php echo $prioridadClass; ?>" onclick="toggleDetails(<?php echo $index; ?>)">
                                <td class="toggle-cell">
                                    <span class="toggle-arrow" id="arrow-<?php echo $index; ?>">▶</span>
                                </td>
                                <td class="tipo-cell">
                                    <?php if ($compra['tipo_venta'] === 'compra'): ?>
                                        <span class="badge-tipo badge-compra">🛒 Compra</span>
                                    <?php else: ?>
                                        <span class="badge-tipo badge-evento"><?php echo htmlspecialchars($compra['tipoEvento']); ?></span>
                                        <?php if ($prioridadLabel): ?>
                                            <br><small class="prioridad-label"><?php echo $prioridadLabel; ?></small>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <td class="cliente-cell"><?php echo htmlspecialchars($compra['nombre_cliente']); ?></td>
                                <td class="email-cell"><?php echo htmlspecialchars($compra['email_cliente']); ?></td>
                                <td class="fecha-cell"><?php echo date('d/m/Y H:i', strtotime($compra['fecha'])); ?></td>
                                <td class="fecha-evento-cell">
                                    <?php if ($compra['fechaEvento']): ?>
                                        <?php echo date('d/m/Y', strtotime($compra['fechaEvento'])); ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="total-cell">$<?php echo number_format($compra['total'], 2); ?></td>
                            </tr>
                            <tr class="details-row" id="details-<?php echo $index; ?>" style="display: none;">
                                <td colspan="7">
                                    <div class="details-content">
                                        <div class="details-header">
                                            <h4>Detalles del Pedido #<?php echo htmlspecialchars($compra['id']); ?></h4>
                                            <?php if ($compra['tipo_venta'] === 'evento' && $compra['notas']): ?>
                                                <div class="notas-section">
                                                    <strong>📝 Notas:</strong>
                                                    <p><?php echo nl2br(htmlspecialchars($compra['notas'])); ?></p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!empty($compra['detalles'])): ?>
                                            <table class="productos-table">
                                                <thead>
                                                    <tr>
                                                        <th>Producto</th>
                                                        <th>Cantidad</th>
                                                        <th>Precio Unitario</th>
                                                        <th>Subtotal</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($compra['detalles'] as $detalle): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($detalle['nombre']); ?></td>
                                                            <td><?php echo htmlspecialchars($detalle['cantidad']); ?></td>
                                                            <td>$<?php echo number_format($detalle['precio_unitario'], 2); ?></td>
                                                            <td class="subtotal">$<?php echo number_format($detalle['cantidad'] * $detalle['precio_unitario'], 2); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        <?php else: ?>
                                            <p class="no-products">No hay productos en este pedido.</p>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</main>

<script>
function toggleDetails(index) {
    const detailsRow = document.getElementById('details-' + index);
    const arrow = document.getElementById('arrow-' + index);
    
    if (detailsRow.style.display === 'none') {
        detailsRow.style.display = 'table-row';
        arrow.textContent = '▼';
        arrow.classList.add('expanded');
    } else {
        detailsRow.style.display = 'none';
        arrow.textContent = '▶';
        arrow.classList.remove('expanded');
    }
}
</script>

<style>
.admin-panel-card h1 { margin-bottom: 6px; }
.admin-panel-card > p { color: #6b7280; margin-bottom: 24px; }

/* Estado vacío */
.empty-state { text-align: center; padding: 60px 20px; background: #f9fafb; border-radius: 12px; margin-top: 20px; }
.empty-icon { font-size: 4em; margin-bottom: 16px; }
.empty-state h3 { color: #374151; margin: 0 0 8px 0; }
.empty-state p { color: #6b7280; margin: 0; }

/* Contenedor de tabla */
.table-container { margin-top: 20px; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.06); border: 2px solid #e5e7eb; }

/* Tabla principal */
.compras-table { width: 100%; border-collapse: collapse; }
.compras-table thead { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: #fff; }
.compras-table thead th { padding: 16px 14px; font-weight: 600; text-align: left; font-size: 0.95em; letter-spacing: 0.3px; }

/* Filas principales */
.compra-row { cursor: pointer; transition: all 0.2s; border-bottom: 1px solid #f3f4f6; }
.compra-row:hover { background: #f9fafb; }
.compra-row td { padding: 16px 14px; color: #374151; font-size: 0.95em; }

/* Prioridades de eventos */
.prioridad-alta { background: #fff5f5; border-left: 4px solid #ef4444; }
.prioridad-alta:hover { background: #fee2e2; }
.prioridad-media { background: #fffbeb; border-left: 4px solid #f59e0b; }
.prioridad-media:hover { background: #fef3c7; }
.prioridad-baja { background: #f0fdf4; border-left: 4px solid #10b981; }
.prioridad-baja:hover { background: #dcfce7; }

/* Badges de tipo */
.badge-tipo { display: inline-block; padding: 4px 10px; border-radius: 6px; font-size: 0.85em; font-weight: 600; }
.badge-compra { background: #dbeafe; color: #1e40af; }
.badge-evento { background: #fae8ff; color: #86198f; }
.prioridad-label { font-size: 0.75em; font-weight: 600; margin-top: 2px; display: inline-block; }

/* Celdas específicas */
.toggle-cell { text-align: center; }
.toggle-arrow { display: inline-block; transition: transform 0.3s; color: #3b82f6; font-size: 0.9em; font-weight: bold; }
.toggle-arrow.expanded { transform: rotate(90deg); }
.tipo-cell { min-width: 120px; }
.cliente-cell { font-weight: 600; color: #1f2937; }
.email-cell { color: #6b7280; }
.fecha-cell { color: #6b7280; font-size: 0.9em; white-space: nowrap; }
.fecha-evento-cell { color: #6b7280; font-size: 0.9em; white-space: nowrap; }
.total-cell { font-weight: 700; color: #10b981; font-size: 1.1em; }
.text-muted { color: #d1d5db; }

/* Fila de detalles */
.details-row { background: #fafbfc; }
.details-content { padding: 20px 30px; }
.details-header { margin-bottom: 16px; }
.details-content h4 { margin: 0 0 12px 0; color: #1f2937; font-size: 1em; font-weight: 600; }

/* Sección de notas */
.notas-section { background: #fffbeb; border-left: 3px solid #f59e0b; padding: 12px 16px; border-radius: 6px; margin-bottom: 16px; }
.notas-section strong { color: #92400e; display: block; margin-bottom: 6px; font-size: 0.9em; }
.notas-section p { margin: 0; color: #78350f; font-size: 0.9em; line-height: 1.5; }

/* Tabla de productos */
.productos-table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; border: 1px solid #e5e7eb; }
.productos-table thead { background: #f3f4f6; }
.productos-table th { padding: 10px 12px; text-align: left; font-weight: 600; color: #6b7280; font-size: 0.85em; text-transform: uppercase; letter-spacing: 0.5px; }
.productos-table td { padding: 10px 12px; color: #374151; border-top: 1px solid #f3f4f6; font-size: 0.9em; }
.productos-table tbody tr:hover { background: #f9fafb; }
.productos-table .subtotal { font-weight: 600; color: #059669; }

.no-products { color: #6b7280; font-style: italic; margin: 0; }

/* Responsive */
@media (max-width: 900px) {
    .compras-table thead th, .compra-row td { padding: 12px 10px; font-size: 0.85em; }
    .details-content { padding: 16px 20px; }
    .productos-table th, .productos-table td { padding: 8px 10px; font-size: 0.8em; }
}

@media (max-width: 640px) {
    .email-cell, .fecha-cell { display: none; }
    .compras-table thead th:nth-child(3), .compras-table thead th:nth-child(4) { display: none; }
}
</style>
