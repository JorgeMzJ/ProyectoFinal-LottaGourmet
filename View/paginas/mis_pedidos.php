<main class="main-content">
    <div class="mis-pedidos-container">
        <div class="pedidos-header">
            <h1>Mis Pedidos y Compras</h1>
            <p>Historial completo de tus órdenes</p>
        </div>

        <?php if (empty($pedidos) && empty($compras)): ?>
            <div class="sin-pedidos">
                <svg width="80" height="80" viewBox="0 0 24 24" fill="none">
                    <path d="M9 11l3 3L22 4" stroke="#cbd5e1" stroke-width="2" stroke-linecap="round"/>
                    <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11" stroke="#cbd5e1" stroke-width="2" stroke-linecap="round"/>
                </svg>
                <h2>No hay pedidos aún</h2>
                <p>Cuando realices un pedido o compra, aparecerá aquí.</p>
                <a href="<?php echo BASE_URL; ?>menu" class="btn-menu">Ver Menú</a>
            </div>
        <?php else: ?>

            <!-- Pedidos de Eventos/Citas -->
            <?php if (!empty($pedidos)): ?>
                <section class="seccion-pedidos">
                    <h2 class="seccion-titulo">Pedidos de Eventos</h2>
                    <?php foreach ($pedidos as $pedido): ?>
                        <div class="pedido-card">
                            <div class="pedido-header">
                                <div class="pedido-info">
                                    <span class="badge-evento"><?php echo htmlspecialchars($pedido['tipoEvento']); ?></span>
                                    <?php if ($pedido['nombre_paquete']): ?>
                                        <span class="badge-paquete">📦 <?php echo htmlspecialchars($pedido['nombre_paquete']); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="pedido-fecha">
                                    <?php echo date('d/m/Y', strtotime($pedido['fecha'])); ?>
                                </div>
                            </div>

                            <?php if ($pedido['fechaEvento']): ?>
                                <div class="info-evento">
                                    <strong>Fecha del evento:</strong> <?php echo date('d/m/Y', strtotime($pedido['fechaEvento'])); ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($pedido['notas']): ?>
                                <div class="notas">
                                    <strong>Notas:</strong> <?php echo htmlspecialchars($pedido['notas']); ?>
                                </div>
                            <?php endif; ?>

                            <table class="tabla-productos">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Cantidad</th>
                                        <th>Precio Unit.</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pedido['productos'] as $prod): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($prod['nombre']); ?></td>
                                            <td class="text-center"><?php echo $prod['cantidad']; ?></td>
                                            <td>$<?php echo number_format($prod['precio_unitario'], 2); ?></td>
                                            <td class="text-right">$<?php echo number_format($prod['cantidad'] * $prod['precio_unitario'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>

                            <div class="pedido-total">
                                <strong>Total:</strong>
                                <span class="monto-total">$<?php echo number_format($pedido['total'] ?? 0, 2); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </section>
            <?php endif; ?>

            <!-- Compras Directas -->
            <?php if (!empty($compras)): ?>
                <section class="seccion-pedidos">
                    <h2 class="seccion-titulo">Compras Directas</h2>
                    <?php foreach ($compras as $compra): ?>
                        <div class="pedido-card">
                            <div class="pedido-header">
                                <div class="pedido-info">
                                    <span class="badge-compra">🛒 Compra en línea</span>
                                </div>
                                <div class="pedido-fecha">
                                    <?php echo date('d/m/Y', strtotime($compra['fecha'])); ?>
                                </div>
                            </div>

                            <table class="tabla-productos">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Cantidad</th>
                                        <th>Precio Unit.</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($compra['productos'] as $prod): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($prod['nombre']); ?></td>
                                            <td class="text-center"><?php echo $prod['cantidad']; ?></td>
                                            <td>$<?php echo number_format($prod['precio_unitario'], 2); ?></td>
                                            <td class="text-right">$<?php echo number_format($prod['cantidad'] * $prod['precio_unitario'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>

                            <div class="pedido-total">
                                <strong>Total:</strong>
                                <span class="monto-total">$<?php echo number_format($compra['total'] ?? 0, 2); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </section>
            <?php endif; ?>

        <?php endif; ?>
    </div>
</main>

<style>
.main-content {
    font-family: 'Comfortaa', sans-serif;
    margin: 0 !important;
    padding: 0 !important;
    max-width: 100% !important;
    width: 100% !important;
}

.mis-pedidos-container {
    max-width: 100%;
    width: 100%;
    margin: 0;
    padding: 40px 60px;
}

.pedidos-header {
    margin-bottom: 30px;
}

.pedidos-header h1 {
    font-size: 1.8em;
    color: #1e293b;
    margin: 0 0 5px 0;
    font-weight: 600;
}

.pedidos-header p {
    color: #64748b;
    margin: 0;
    font-size: 0.95em;
}

.sin-pedidos {
    text-align: center;
    padding: 80px 20px;
}

.sin-pedidos svg {
    margin-bottom: 20px;
    opacity: 0.5;
}

.sin-pedidos h2 {
    color: #64748b;
    font-size: 1.5em;
    margin: 0 0 10px 0;
}

.sin-pedidos p {
    color: #94a3b8;
    margin: 0 0 30px 0;
}

.btn-menu {
    display: inline-block;
    padding: 12px 28px;
    background: #3b82f6;
    color: #fff;
    text-decoration: none;
    border-radius: 6px;
    font-weight: 600;
    transition: all 0.2s;
}

.btn-menu:hover {
    background: #2563eb;
}

.seccion-pedidos {
    margin-bottom: 40px;
}

.seccion-titulo {
    font-size: 1.2em;
    color: #1e293b;
    margin: 0 0 20px 0;
    font-weight: 600;
    padding-bottom: 10px;
    border-bottom: 1px solid #e2e8f0;
}

.pedido-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 20px 25px;
    margin-bottom: 15px;
    transition: box-shadow 0.2s;
}

.pedido-card:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.pedido-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #f1f5f9;
}

.pedido-info {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.pedido-info h3 {
    font-size: 1em;
    color: #1e293b;
    margin: 0;
    font-weight: 600;
}

.badge-evento,
.badge-paquete,
.badge-compra {
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 0.8em;
    font-weight: 500;
}

.badge-evento {
    background: #dbeafe;
    color: #1e40af;
}

.badge-paquete {
    background: #fef3c7;
    color: #92400e;
}

.badge-compra {
    background: #d1fae5;
    color: #065f46;
}

.pedido-fecha {
    color: #94a3b8;
    font-size: 0.85em;
    font-weight: 500;
}

.info-evento,
.notas {
    margin-bottom: 12px;
    padding: 8px 12px;
    background: #f8fafc;
    border-radius: 4px;
    font-size: 0.85em;
    color: #475569;
}

.tabla-productos {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 12px;
    font-size: 0.9em;
}

.tabla-productos th {
    text-align: left;
    padding: 8px 10px;
    background: #f8fafc;
    color: #64748b;
    font-size: 0.85em;
    font-weight: 500;
    border-bottom: 1px solid #e2e8f0;
}

.tabla-productos td {
    padding: 10px;
    color: #475569;
    border-bottom: 1px solid #f8fafc;
}

.tabla-productos tbody tr:last-child td {
    border-bottom: none;
}

.text-center {
    text-align: center;
}

.text-right {
    text-align: right;
    font-weight: 500;
}

.pedido-total {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 12px;
    padding-top: 12px;
    border-top: 1px solid #e2e8f0;
    font-size: 1em;
}

.monto-total {
    color: #10b981;
    font-weight: 600;
    font-size: 1.2em;
}

@media (max-width: 768px) {
    .mis-pedidos-container {
        padding: 30px 20px;
    }
    
    .pedidos-header h1 {
        font-size: 1.5em;
    }
    
    .pedido-card {
        padding: 15px;
    }
    
    .pedido-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .tabla-productos {
        font-size: 0.85em;
    }
    
    .tabla-productos th,
    .tabla-productos td {
        padding: 8px 5px;
    }
}
</style>
