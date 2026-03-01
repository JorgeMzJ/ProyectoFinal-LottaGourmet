<main class="contenido-principal">
    <div class="admin-panel">
        <h1>Estimación de Restock</h1>
        <p>Análisis de ventas de los últimos 30 días y sugerencias de reabastecimiento.</p>
        
        <div style="margin-bottom: 20px; padding: 15px; background: #f0f8ff; border-left: 4px solid #2196F3; border-radius: 4px;">
            <strong>📊 Criterios de restock:</strong>
            <ul style="margin: 10px 0 0 20px;">
                <li><span style="color: #f44336;">●</span> <strong>Urgencia Alta:</strong> Stock para menos de 7 días</li>
                <li><span style="color: #ff9800;">●</span> <strong>Urgencia Media:</strong> Stock para 7-14 días</li>
                <li><span style="color: #4caf50;">●</span> <strong>Stock Suficiente:</strong> Más de 14 días</li>
            </ul>
            <p style="margin-top: 10px; font-size: 0.9em;">
                <em>Restock sugerido = Ventas promedio semanal × 2 semanas</em>
            </p>
        </div>

        <?php if (empty($productos)): ?>
            <p style="text-align: center; padding: 40px; color: #666;">
                No hay datos de ventas disponibles.
            </p>
        <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Estado</th>
                        <th>Producto</th>
                        <th>Ventas/Semana</th>
                        <th>Stock Actual</th>
                        <th>Días Restantes</th>
                        <th>Restock Sugerido</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($productos as $prod): ?>
                        <tr style="background-color: <?= $prod['nivel_urgencia'] === 'alto' ? '#ffebee' : ($prod['nivel_urgencia'] === 'medio' ? '#fff3e0' : '#f1f8e9') ?>;">
                            <td style="text-align: center; font-size: 1.5em;">
                                <?php if ($prod['nivel_urgencia'] === 'alto'): ?>
                                    <span style="color: #f44336;" title="Urgencia Alta">⚠️</span>
                                <?php elseif ($prod['nivel_urgencia'] === 'medio'): ?>
                                    <span style="color: #ff9800;" title="Urgencia Media">⚡</span>
                                <?php else: ?>
                                    <span style="color: #4caf50;" title="Stock Suficiente">✓</span>
                                <?php endif; ?>
                            </td>
                            <td><strong><?= htmlspecialchars($prod['nombre']) ?></strong></td>
                            <td style="text-align: center;"><?= $prod['ventas_semana'] ?> uds</td>
                            <td style="text-align: center;">
                                <strong><?= $prod['stock'] ?></strong> uds
                            </td>
                            <td style="text-align: center;">
                                <?php if ($prod['dias_restantes'] > 99): ?>
                                    <span style="color: #4caf50;">+99 días</span>
                                <?php else: ?>
                                    <span style="color: <?= $prod['nivel_urgencia'] === 'alto' ? '#f44336' : ($prod['nivel_urgencia'] === 'medio' ? '#ff9800' : '#4caf50') ?>;">
                                        <?= $prod['dias_restantes'] ?> días
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <?php if ($prod['restock_sugerido'] > 0): ?>
                                    <strong style="color: #2196F3; font-size: 1.1em;">
                                        +<?= $prod['restock_sugerido'] ?> uds
                                    </strong>
                                <?php else: ?>
                                    <span style="color: #999;">No necesario</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</main>
