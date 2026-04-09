<main class="contenido-principal">
    <div class="form-card" style="max-width: 860px; margin: 30px auto; padding: 24px;">
        <?php if (!empty($errorMessage)): ?>
            <div style="padding: 18px; border: 1px solid #f5c6cb; background: #fff4f4; border-radius: 10px; color: #a94442; margin-bottom: 20px;">
                <h2 style="margin-top:0;">No se pudo confirmar el pago</h2>
                <p><?php echo htmlspecialchars($errorMessage); ?></p>
            </div>
            <a href="<?php echo BASE_URL; ?>compras/confirmar" class="btn-primary" style="display:inline-block; margin-top: 10px;">Volver a confirmar compra</a>
        <?php else: ?>
            <div style="padding: 18px; border: 1px solid #d4edda; background: #f0fff4; border-radius: 10px; color: #155724; margin-bottom: 20px;">
                <h2 style="margin-top:0;">¡Pago completado con éxito!</h2>
                <p>Gracias por tu compra. Hemos recibido el pago correctamente y tu pedido se ha guardado.</p>
            </div>
            <div style="margin-bottom: 18px;">
                <strong>ID de sesión:</strong>
                <div style="padding: 12px; background: #fafafa; border-radius: 8px; border: 1px solid #eee; word-break: break-all;">
                    <?php echo htmlspecialchars(is_array($sessionDetails) && isset($sessionDetails['id']) ? $sessionDetails['id'] : 'N/A'); ?>
                </div>
            </div>
            <div style="margin-bottom: 18px; display:flex; gap:12px; flex-wrap:wrap;">
                <div style="flex:1; min-width:220px; padding: 14px; background:#fff; border-radius:8px; border:1px solid #ececec;">
                    <strong>Estado de pago</strong>
                    <p><?php echo htmlspecialchars(is_array($sessionDetails) && isset($sessionDetails['payment_status']) ? $sessionDetails['payment_status'] : 'N/A'); ?></p>
                </div>
                <div style="flex:1; min-width:220px; padding: 14px; background:#fff; border-radius:8px; border:1px solid #ececec;">
                    <strong>Total pagado</strong>
                    <p>$<?php echo is_array($sessionDetails) && isset($sessionDetails['amount_total']) ? number_format($sessionDetails['amount_total'] / 100, 2) : '0.00'; ?> MXN</p>
                </div>
            </div>
            <?php if (!empty($savedPurchaseId)): ?>
                <div style="margin-bottom: 18px; padding: 14px; background: #eef6ff; border-radius: 10px; border: 1px solid #cfe2ff; color: #084298;">
                    <strong>Compra guardada con éxito:</strong>
                    <p>ID registrado: #<?php echo htmlspecialchars($savedPurchaseId); ?></p>
                </div>
            <?php endif; ?>
            <a href="<?php echo BASE_URL; ?>menu" class="btn-primary" style="display:inline-block;">Volver al menú</a>
        <?php endif; ?>
    </div>
</main>

<?php if (empty($errorMessage)): ?>
<script>
try {
    localStorage.removeItem('lottagourmet_carrito');
    console.log('Carrito eliminado del localStorage después del pago.');
} catch (err) {
    console.warn('No se pudo limpiar localStorage después del pago:', err);
}
</script>
<?php endif; ?>
