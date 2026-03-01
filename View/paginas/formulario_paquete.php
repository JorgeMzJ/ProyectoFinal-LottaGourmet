<?php
$paquete = $_SESSION['paquete_seleccionado'] ?? null;
$errors = $_SESSION['citas_errors'] ?? [];
$old = $_SESSION['citas_old'] ?? [];
$fechaEvento = $old['fechaEvento'] ?? '';

// Obtener datos del usuario de la sesión
$nombreUsuario = $_SESSION['usuario_nombre'] ?? '';
$emailUsuario = $_SESSION['usuario_email'] ?? '';
$telefonoUsuario = $_SESSION['usuario_telefono'] ?? '';

unset($_SESSION['citas_errors'], $_SESSION['citas_old']);
?>

<!-- Modal de confirmación de paquete -->
<div id="modalConfirmacionPaquete" class="modal-overlay" style="display: flex;">
    <div class="modal-confirmacion-content">
        <h2>Confirmar pedido de paquete</h2>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <div class="paquete-resumen-modal">
            <div class="resumen-header">
                <h3><?php echo htmlspecialchars($paquete['nombre']); ?></h3>
                <span class="badge-tipo"><?php echo htmlspecialchars($paquete['tipo_evento']); ?></span>
            </div>
            
            <div class="resumen-detalles">
                <div class="detalle-item">
                    <span class="label">Postres incluidos:</span>
                    <strong><?php echo $paquete['cantidad_postres']; ?> piezas</strong>
                </div>
                <div class="detalle-item">
                    <span class="label">Fecha del evento:</span>
                    <strong><?php echo $fechaEvento ? date('d/m/Y', strtotime($fechaEvento)) : 'No especificada'; ?></strong>
                </div>
                <div class="detalle-item">
                    <span class="label">Cliente:</span>
                    <strong><?php echo htmlspecialchars($nombreUsuario); ?></strong>
                </div>
                <div class="detalle-item">
                    <span class="label">Email:</span>
                    <strong><?php echo htmlspecialchars($emailUsuario); ?></strong>
                </div>
                <?php if ($telefonoUsuario): ?>
                <div class="detalle-item">
                    <span class="label">Teléfono:</span>
                    <strong><?php echo htmlspecialchars($telefonoUsuario); ?></strong>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="precio-total-modal">
                <span>Total a pagar:</span>
                <strong>$<?php echo number_format($paquete['precio'], 2); ?></strong>
            </div>
        </div>
        
        <form method="post" action="<?php echo BASE_URL; ?>citas/guardar-paquete" id="formConfirmarPaquete">
            <input type="hidden" name="id_paquete" value="<?php echo htmlspecialchars($paquete['id_paquete']); ?>">
            <input type="hidden" name="fechaEvento" value="<?php echo htmlspecialchars($fechaEvento); ?>">
            
            <div class="form-group">
                <label for="notas">Notas adicionales (opcional)</label>
                <textarea id="notas" name="notas" rows="3" 
                          placeholder="¿Alguna indicación especial para tu pedido?"><?php echo htmlspecialchars($old['notas'] ?? ''); ?></textarea>
            </div>
            
            <div class="modal-actions">
                <a href="<?php echo BASE_URL; ?>citas" class="btn-cancelar">Cancelar</a>
                <button type="submit" class="btn-confirmar">Confirmar pedido</button>
            </div>
        </form>
    </div>
</div>

<style>
/* Modal de confirmación de paquete */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 10000;
    backdrop-filter: blur(4px);
}

.modal-confirmacion-content {
    background: #fff;
    border-radius: 20px;
    padding: 40px;
    max-width: 600px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.modal-confirmacion-content h2 {
    font-size: 1.8em;
    color: #1e293b;
    margin-bottom: 24px;
    font-weight: 700;
}

.paquete-resumen-modal {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-radius: 16px;
    padding: 24px;
    margin-bottom: 24px;
    border: 2px solid #e2e8f0;
}

.resumen-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 2px solid #e2e8f0;
}

.resumen-header h3 {
    margin: 0;
    font-size: 1.4em;
    color: #1e293b;
    font-weight: 700;
}

.badge-tipo {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: #fff;
    padding: 6px 14px;
    border-radius: 12px;
    font-size: 0.85em;
    font-weight: 600;
}

.resumen-detalles {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-bottom: 20px;
}

.detalle-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
}

.detalle-item .label {
    color: #64748b;
    font-size: 0.95em;
}

.detalle-item strong {
    color: #1e293b;
    font-weight: 600;
}

.precio-total-modal {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    padding: 18px 20px;
    border-radius: 12px;
    margin-top: 16px;
}

.precio-total-modal span {
    color: #fff;
    font-size: 1.1em;
    font-weight: 600;
}

.precio-total-modal strong {
    color: #fff;
    font-size: 1.8em;
    font-weight: 700;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    color: #1e293b;
    font-weight: 600;
    font-size: 0.95em;
    margin-bottom: 8px;
}

.form-group textarea {
    width: 100%;
    padding: 14px 18px;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    font-size: 1em;
    font-family: inherit;
    resize: vertical;
    min-height: 80px;
    transition: all 0.3s;
}

.form-group textarea:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.alert {
    padding: 16px 20px;
    border-radius: 12px;
    margin-bottom: 20px;
}

.alert-error {
    background: #fff3f3;
    border: 2px solid #ffd6d6;
    color: #c33;
}

.alert p {
    margin: 5px 0;
}

.modal-actions {
    display: flex;
    gap: 12px;
    margin-top: 24px;
}

.btn-cancelar,
.btn-confirmar {
    padding: 14px 28px;
    border-radius: 12px;
    font-weight: 700;
    font-size: 1em;
    cursor: pointer;
    transition: all 0.3s;
    text-decoration: none;
    border: none;
    display: inline-block;
    text-align: center;
}

.btn-cancelar {
    background: #fff;
    color: #64748b;
    border: 2px solid #e2e8f0;
}

.btn-cancelar:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
}

.btn-confirmar {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: #fff;
    flex: 1;
}

.btn-confirmar:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
}

@media (max-width: 640px) {
    .modal-confirmacion-content {
        padding: 30px 20px;
    }
    
    .modal-confirmacion-content h2 {
        font-size: 1.5em;
    }
    
    .modal-actions {
        flex-direction: column;
    }
    
    .btn-cancelar,
    .btn-confirmar {
        width: 100%;
    }
}
</style>
