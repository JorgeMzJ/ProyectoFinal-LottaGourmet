<?php
$carrito = $_SESSION['carrito_compra'] ?? [];
if (empty($carrito)) {
    header('Location: ' . BASE_URL . 'menu');
    exit;
}

// Calcular total
$total = 0;
foreach ($carrito as $item) {
    $total += ($item['precio'] * $item['cantidad']);
}
?>

<main class="main-content">
    <div class="confirmacion-container">
        <div class="confirmacion-card">
            <div class="recibo-header">
                <h1>Resumen de Compra</h1>
            </div>
            
            <div class="recibo-divider"></div>
            <div class="recibo-divider"></div>
            
            <div class="paquete-info-box">
                <div class="productos-incluidos">
                    <h3>Productos en tu carrito</h3>
                    <table class="tabla-productos">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th class="text-center">Cant.</th>
                                <th class="text-right">Precio Unit.</th>
                                <th class="text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($carrito as $item): 
                                $subtotal = $item['precio'] * $item['cantidad'];
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['nombre']); ?></td>
                                    <td class="text-center"><?php echo $item['cantidad']; ?></td>
                                    <td class="text-right">$<?php echo number_format($item['precio'], 2); ?></td>
                                    <td class="text-right">$<?php echo number_format($subtotal, 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="recibo-divider"></div>
                
                <div class="paquete-detalles">
                    <div class="detalle-item precio-total">
                        <span class="detalle-label">Total a pagar:</span>
                        <span class="detalle-valor precio">$<?php echo number_format($total, 2); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="confirmacion-acciones">
                <a href="<?php echo BASE_URL; ?>menu" class="btn-volver">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                        <path d="M19 12H5M5 12l7 7M5 12l7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    Volver al menú
                </a>
                <button onclick="confirmarCompra()" class="btn-proceder">
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
    padding: 40px 50px;
    width: 100%;
    max-width: 1000px;
    margin: 0 auto;
}

.recibo-header {
    text-align: center;
    margin-bottom: 20px;
}

.recibo-header h1 {
    font-size: 2em;
    color: #1e293b;
    margin: 0 0 8px 0;
    font-weight: 700;
}

.fecha-hora {
    color: #64748b;
    font-size: 0.9em;
    margin: 0;
}

.recibo-divider {
    height: 1px;
    background: #e2e8f0;
    margin: 15px 0;
}

.paquete-info-box {
    margin: 25px 0;
}

.productos-incluidos h3 {
    font-size: 1.1em;
    color: #1e293b;
    margin: 0 0 15px 0;
    font-weight: 600;
}

.tabla-productos {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 15px;
}

.tabla-productos thead {
    background: #f8fafc;
}

.tabla-productos th {
    text-align: left;
    padding: 12px 10px;
    color: #64748b;
    font-size: 0.85em;
    font-weight: 600;
    border-bottom: 2px solid #e2e8f0;
}

.tabla-productos td {
    padding: 12px 10px;
    color: #475569;
    border-bottom: 1px solid #f1f5f9;
}

.tabla-productos tbody tr:last-child td {
    border-bottom: none;
}

.text-center {
    text-align: center;
}

.text-right {
    text-align: right;
}

.paquete-detalles {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.detalle-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
}

.detalle-item.precio-total {
    border-top: 2px solid #e2e8f0;
    padding-top: 15px;
    margin-top: 10px;
}

.detalle-label {
    color: #64748b;
    font-size: 0.95em;
    font-weight: 500;
}

.detalle-valor {
    color: #1e293b;
    font-weight: 600;
}

.detalle-valor.precio {
    color: #10b981;
    font-size: 1.5em;
    font-weight: 700;
}

.confirmacion-acciones {
    display: flex;
    gap: 15px;
    justify-content: center;
    margin-top: 30px;
}

.btn-volver,
.btn-proceder {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-size: 0.95em;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
    font-family: 'Comfortaa', sans-serif;
}

.btn-volver {
    background: #f1f5f9;
    color: #475569;
}

.btn-volver:hover {
    background: #e2e8f0;
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
    
    .tabla-productos {
        font-size: 0.85em;
    }
    
    .tabla-productos th,
    .tabla-productos td {
        padding: 8px 5px;
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
        // Limpiar localStorage del carrito (usar la MISMA clave que carrito.js)
        try {
            localStorage.removeItem('pastelesupbc_carrito');
            console.log('Carrito eliminado del localStorage');
        } catch (e) {
            console.error('Error al limpiar carrito:', e);
        }
        
        // Asegurar que el contador del carrito se actualice
        window.location.href = '<?php echo BASE_URL; ?>menu';
    }
}

function confirmarCompra() {
    console.log('Iniciando confirmación de compra...');
    
    // Deshabilitar botón para evitar doble clic
    const btnConfirmar = document.querySelector('.btn-proceder');
    if (btnConfirmar) {
        btnConfirmar.disabled = true;
        btnConfirmar.textContent = 'Procesando...';
    }
    
    fetch('<?php echo BASE_URL; ?>compras/procesar', {
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
            // Limpiar carrito INMEDIATAMENTE antes de mostrar modal (MISMA clave que carrito.js)
            try {
                localStorage.removeItem('pastelesupbc_carrito');
                console.log('Carrito limpiado exitosamente');
            } catch (e) {
                console.error('Error limpiando carrito:', e);
            }
            mostrarModal('¡Éxito!', '¡Compra realizada con éxito! Gracias por tu preferencia.', true);
        } else {
            // Re-habilitar botón en caso de error
            if (btnConfirmar) {
                btnConfirmar.disabled = false;
                btnConfirmar.innerHTML = '<span>✓</span> Confirmar y pagar';
            }
            mostrarModal('Error', 'Error al procesar la compra: ' + (data.error || 'Intenta nuevamente'), false);
        }
    })
    .catch(err => {
        console.error('Error completo:', err);
        // Re-habilitar botón en caso de error
        if (btnConfirmar) {
            btnConfirmar.disabled = false;
            btnConfirmar.innerHTML = '<span>✓</span> Confirmar y pagar';
        }
        mostrarModal('Error de conexión', err.message + '. Por favor intenta nuevamente.', false);
    });
}
</script>
