<?php
$paquete = $_SESSION['paquete_seleccionado'] ?? null;
if (!$paquete) {
    header('Location: ' . BASE_URL . 'citas');
    exit;
}
?>

<main class="main-content">
    <div class="confirmacion-container">
        <div class="confirmacion-card">
            <div class="recibo-header">
                <h1>Resumen de Pedido</h1>
                <p class="fecha-hora"><?php echo date('d/m/Y H:i'); ?></p>
            </div>
            
            <div class="recibo-divider"></div>
            <div class="recibo-divider"></div>
            
            <div class="paquete-info-box">
                <div class="info-header">
                    <h2><?php echo htmlspecialchars($paquete['nombre']); ?></h2>
                    <span class="badge-tipo"><?php echo htmlspecialchars($paquete['tipo_evento']); ?></span>
                </div>
                
                <p class="paquete-descripcion"><?php echo htmlspecialchars($paquete['descripcion']); ?></p>
                
                <div class="recibo-divider"></div>
                
                <div class="paquete-detalles">
                    <div class="detalle-item">
                        <span class="detalle-label">Cantidad de postres:</span>
                        <span class="detalle-valor"><?php echo $paquete['cantidad_postres']; ?> piezas</span>
                    </div>
                    <div class="detalle-item precio-total">
                        <span class="detalle-label">Precio total:</span>
                        <span class="detalle-valor precio">$<?php echo number_format($paquete['precio'], 2); ?></span>
                    </div>
                </div>
                
                <?php if (!empty($paquete['productos'])): ?>
                <div class="productos-incluidos">
                    <h3>Productos incluidos</h3>
                    <table class="tabla-productos">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Cant.</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($paquete['productos'] as $prod): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($prod['nombre']); ?></td>
                                    <td class="text-center"><?php echo $prod['cantidad']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="recibo-divider"></div>
                <?php endif; ?>
            </div>
            
            <div class="confirmacion-acciones">
                <a href="<?php echo BASE_URL; ?>citas" class="btn-volver">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                        <path d="M19 12H5M5 12l7 7M5 12l7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    Seleccionar otro paquete
                </a>
                <button onclick="confirmarPedido()" class="btn-proceder">
                    Confirmar y pagar
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                        <path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</main>

<!-- Modal de notificación -->
<div id="notificacionModal" class="modal-overlay" style="display: none;">
    <div class="modal-content-custom">
        <h3 id="modalTitulo"></h3>
        <p id="modalMensaje"></p>
        <button onclick="cerrarModal()" class="btn-modal-ok">Aceptar</button>
    </div>
</div>

<style>
.main-content {
    margin: 0 !important;
    padding: 0 !important;
    max-width: 100% !important;
    width: 100% !important;
}

.confirmacion-container {
    min-height: calc(100vh - 80px);
    display: flex;
    align-items: stretch;
    justify-content: center;
    padding: 0;
    background: #fff;
    font-family: 'Comfortaa', sans-serif;
    margin: 0 auto;
    width: 100%;
}

.confirmacion-card {
    background: #fff;
    width: 100%;
    max-width: 1000px;
    padding: 40px 60px;
    display: flex;
    flex-direction: column;
    margin: 0 auto;
}

.recibo-header {
    text-align: center;
    padding-bottom: 25px;
    border-bottom: 1px solid #e2e8f0;
    margin-bottom: 30px;
}

.recibo-header h1 {
    font-size: 1.8em;
    color: #1e293b;
    margin: 0 0 5px 0;
    font-weight: 600;
    font-family: 'Comfortaa', sans-serif;
}

.fecha-hora {
    color: #94a3b8;
    font-size: 0.9em;
    margin: 0;
    font-family: 'Comfortaa', sans-serif;
}

.cliente-nombre {
    color: #1e293b;
    font-size: 0.95em;
    margin: 10px 0 0 0;
    font-family: 'Comfortaa', sans-serif;
}

.cliente-nombre strong {
    color: #3b82f6;
    font-weight: 600;
}

.recibo-divider {
    height: 1px;
    background: #e2e8f0;
    margin: 25px 0;
}

.paquete-info-box {
    max-width: 900px;
    margin: 0 auto;
    width: 100%;
}

.info-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 15px;
}

.info-header h2 {
    font-size: 1.5em;
    color: #1e293b;
    margin: 0;
    font-weight: 600;
    font-family: 'Comfortaa', sans-serif;
}

.badge-tipo {
    background: #3b82f6;
    color: #fff;
    padding: 6px 16px;
    border-radius: 4px;
    font-size: 0.85em;
    font-weight: 500;
    font-family: 'Comfortaa', sans-serif;
}

.paquete-descripcion {
    color: #64748b;
    line-height: 1.7;
    margin: 0;
    font-size: 1em;
    font-family: 'Comfortaa', sans-serif;
}

.paquete-detalles {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.detalle-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    font-size: 1em;
}

.detalle-label {
    color: #64748b;
    font-weight: 500;
}

.detalle-valor {
    color: #1e293b;
    font-weight: 600;
}

.detalle-item.precio-total {
    background: #f8fafc;
    padding: 18px 25px;
    border-radius: 4px;
    margin-top: 15px;
    border: 1px solid #e2e8f0;
}

.detalle-item.precio-total .precio {
    font-size: 1.5em;
    color: #1e293b;
}

.productos-incluidos h3 {
    color: #1e293b;
    font-size: 1em;
    margin: 0 0 15px 0;
    font-weight: 600;
    font-family: 'Comfortaa', sans-serif;
}

.tabla-productos {
    width: 100%;
    border-collapse: collapse;
    font-family: 'Comfortaa', sans-serif;
}

.tabla-productos th {
    text-align: left;
    padding: 12px 15px;
    background: #f8fafc;
    color: #64748b;
    font-size: 0.9em;
    font-weight: 500;
    border-bottom: 1px solid #e2e8f0;
}

.tabla-productos td {
    padding: 15px;
    color: #475569;
    border-bottom: 1px solid #f1f5f9;
    font-size: 0.95em;
}

.tabla-productos tbody tr:last-child td {
    border-bottom: none;
}

.text-center {
    text-align: center;
    font-weight: 600;
    color: #3b82f6;
}

.confirmacion-acciones {
    display: flex;
    gap: 15px;
    padding-top: 30px;
    margin-top: 30px;
    border-top: 1px solid #e2e8f0;
    max-width: 900px;
    margin-left: auto;
    margin-right: auto;
    width: 100%;
}

.btn-volver,
.btn-proceder {
    padding: 14px 28px;
    border-radius: 4px;
    font-weight: 500;
    font-size: 1em;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    border: none;
    flex: 1;
    justify-content: center;
    font-family: 'Comfortaa', sans-serif;
}

.btn-volver {
    background: #fff;
    color: #64748b;
    border: 1px solid #cbd5e1;
}

.btn-volver:hover {
    background: #f8fafc;
}

.btn-proceder {
    background: #3b82f6;
    color: #fff;
}

.btn-proceder:hover {
    background: #2563eb;
}

/* Modal personalizado */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10000;
    font-family: 'Comfortaa', sans-serif;
}

.modal-content-custom {
    background: #fff;
    padding: 30px 40px;
    border-radius: 12px;
    max-width: 450px;
    width: 90%;
    text-align: center;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.modal-content-custom h3 {
    margin: 0 0 15px 0;
    font-size: 1.4em;
    color: #1e293b;
}

.modal-content-custom p {
    margin: 0 0 25px 0;
    color: #64748b;
    font-size: 0.95em;
    line-height: 1.5;
}

.btn-modal-ok {
    background: #3b82f6;
    color: #fff;
    border: none;
    padding: 12px 32px;
    border-radius: 8px;
    font-size: 0.95em;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    font-family: 'Comfortaa', sans-serif;
}

.btn-modal-ok:hover {
    background: #2563eb;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

@media (max-width: 768px) {
    .confirmacion-card {
        padding: 30px 20px;
    }
    
    .recibo-header h1 {
        font-size: 1.5em;
    }
    
    .info-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .confirmacion-acciones {
        flex-direction: column;
    }
}
</style>

<script>
let redirigirAMenu = false;

function mostrarModal(titulo, mensaje, exito = false) {
    document.getElementById('modalTitulo').textContent = titulo;
    document.getElementById('modalMensaje').textContent = mensaje;
    document.getElementById('notificacionModal').style.display = 'flex';
    redirigirAMenu = exito;
}

function cerrarModal() {
    document.getElementById('notificacionModal').style.display = 'none';
    if (redirigirAMenu) {
        window.location.href = '<?php echo BASE_URL; ?>menu';
    }
}

function confirmarPedido() {
    console.log('Iniciando confirmación de pedido...');
    // Enviar solicitud para registrar el pedido directamente
    fetch('<?php echo BASE_URL; ?>citas/confirmar-paquete', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(resp => {
        console.log('Respuesta recibida:', resp.status);
        if (!resp.ok) {
            throw new Error('Error en la respuesta del servidor: ' + resp.status);
        }
        return resp.json();
    })
    .then(data => {
        console.log('Datos recibidos:', data);
        if (data.success) {
            mostrarModal('¡Éxito!', '¡Pedido realizado con éxito! Nos pondremos en contacto contigo.', true);
        } else {
            mostrarModal('Error', 'Error al procesar el pedido: ' + (data.error || 'Intenta nuevamente'), false);
        }
    })
    .catch(err => {
        console.error('Error completo:', err);
        mostrarModal('Error de conexión', err.message + '. Por favor intenta nuevamente.', false);
    });
}
</script>
