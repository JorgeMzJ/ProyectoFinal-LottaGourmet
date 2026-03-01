<main class="contenido-principal">
    <div class="admin-panel-card">
        <h1>Gráficas de Ventas</h1>
        <p>Ventas de los últimos 7 días</p>
        
        <!-- Resumen -->
        <div class="resumen-ventas">
            <?php 
            $totalSemana = array_sum(array_column($datosGrafica, 'total'));
            $ultimoDia = end($datosGrafica);
            ?>
            
            <div class="resumen-item">
                <span class="resumen-label">Total Semanal</span>
                <span class="resumen-valor">$<?php echo number_format($totalSemana, 2); ?></span>
            </div>
            
            <div class="resumen-item">
                <span class="resumen-label">Último Día</span>
                <span class="resumen-valor">$<?php echo number_format($ultimoDia['total'], 2); ?></span>
                <?php if ($ultimoDia['porcentaje'] != 0): ?>
                    <span class="cambio <?php echo $ultimoDia['porcentaje'] >= 0 ? 'positivo' : 'negativo'; ?>">
                        <?php echo $ultimoDia['porcentaje'] >= 0 ? '+' : ''; ?><?php echo number_format($ultimoDia['porcentaje'], 1); ?>%
                    </span>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Gráfica -->
        <div class="grafica-box">
            <canvas id="ventasChart"></canvas>
        </div>
        
        <!-- Tabla -->
        <div class="tabla-ventas">
            <table>
                <thead>
                    <tr>
                        <th>Día</th>
                        <th>Ventas</th>
                        <th>Cambio</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $diasSemana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
                    foreach ($datosGrafica as $index => $dato): 
                        $nombreDia = $diasSemana[date('w', strtotime($dato['dia']))];
                    ?>
                        <tr>
                            <td><?php echo $nombreDia . ' ' . date('d/m', strtotime($dato['dia'])); ?></td>
                            <td><strong>$<?php echo number_format($dato['total'], 2); ?></strong></td>
                            <td>
                                <?php if ($index > 0): ?>
                                    <span class="cambio <?php echo $dato['porcentaje'] >= 0 ? 'positivo' : 'negativo'; ?>">
                                        <?php echo $dato['porcentaje'] >= 0 ? '↑' : '↓'; ?>
                                        <?php echo number_format(abs($dato['porcentaje']), 1); ?>%
                                    </span>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const datosVentas = <?php echo json_encode($datosGrafica); ?>;
const diasSemanaCortos = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
const labels = datosVentas.map(d => {
    const fecha = new Date(d.dia + 'T00:00:00');
    const dia = diasSemanaCortos[fecha.getDay()];
    const numDia = fecha.getDate();
    return dia + ' ' + numDia;
});
const ventas = datosVentas.map(d => d.total);
const colores = datosVentas.map(d => d.porcentaje >= 0 ? '#10b981' : '#ef4444');

const ctx = document.getElementById('ventasChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [{
            label: 'Ventas',
            data: ventas,
            backgroundColor: colores,
            borderRadius: 4,
            // Hacer las barras 50% más delgadas
            categoryPercentage: 0.8,
            barPercentage: 0.8
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return '$' + context.parsed.y.toFixed(2);
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + value;
                    }
                }
            }
        }
    }
});
</script>

<style>
.admin-panel-card h1 { 
    margin-bottom: 8px; 
    font-size: 1.5em;
}
.admin-panel-card > p { 
    color: #666; 
    margin-bottom: 20px;
    font-size: 0.95em;
}

.resumen-ventas {
    display: flex;
    gap: 16px;
    margin-bottom: 24px;
}

.resumen-item {
    background: #fff;
    padding: 16px;
    border-radius: 8px;
    border: 1px solid #ddd;
    flex: 1;
}

.resumen-label {
    display: block;
    color: #666;
    font-size: 0.85em;
    margin-bottom: 6px;
}

.resumen-valor {
    display: block;
    font-size: 1.4em;
    font-weight: bold;
    color: #333;
}

.cambio {
    display: inline-block;
    margin-top: 6px;
    font-size: 0.85em;
    font-weight: bold;
}

.cambio.positivo { color: #10b981; }
.cambio.negativo { color: #ef4444; }

.grafica-box {
    background: #fff;
    padding: 12px;
    border-radius: 8px;
    border: 1px solid #ddd;
    margin: 0 auto 24px;
    max-width: 800px;
    height: 260px;
}

.grafica-box canvas {
    width: 100% !important;
    height: 100% !important;
}

.tabla-ventas {
    background: #fff;
    border-radius: 8px;
    border: 1px solid #ddd;
    overflow: hidden;
}

.tabla-ventas table {
    width: 100%;
    border-collapse: collapse;
}

.tabla-ventas thead {
    background: #f5f5f5;
}

.tabla-ventas th {
    padding: 10px 12px;
    text-align: left;
    font-weight: 600;
    font-size: 0.9em;
    color: #333;
    border-bottom: 1px solid #ddd;
}

.tabla-ventas td {
    padding: 10px 12px;
    font-size: 0.9em;
    border-bottom: 1px solid #f5f5f5;
}

.tabla-ventas tbody tr:hover {
    background: #fafafa;
}

@media (max-width: 768px) {
    .resumen-ventas {
        flex-direction: column;
    }
    
    .grafica-box {
        height: 250px;
    }
}
</style>
