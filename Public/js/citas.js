document.addEventListener('DOMContentLoaded', function(){
    const tipoSelect = document.getElementById('tipoEvento');
    const otroContainer = document.getElementById('otroContainer');
    const otroInput = document.getElementById('otroEvento');
    const form = document.getElementById('citasForm');
    const btnAbrirModal = document.getElementById('btnAbrirModal');
    const productosModal = document.getElementById('productosModal');
    const modalOverlay = document.getElementById('modalOverlay');
    const btnCerrarModal = document.getElementById('btnCerrarModal');
    const modalProductosGrid = document.querySelector('.modal-productos-grid');
    const carritoResumen = document.getElementById('carritoResumen');
    const notificationBar = document.getElementById('notificationBar');

    const carritoLocal = [];

    if (notificationBar && window.location.search.includes('success=1')) {
        notificationBar.classList.add('active', 'auto-close');
        setTimeout(() => {
            notificationBar.classList.remove('active');
        }, 3000);
    }

    if (tipoSelect) {
        tipoSelect.addEventListener('change', function(){
            if (tipoSelect.value === 'Otro') {
                if (otroContainer) otroContainer.style.display = 'block';
                if (otroInput) otroInput.setAttribute('required','required');
            } else {
                if (otroContainer) otroContainer.style.display = 'none';
                if (otroInput) otroInput.removeAttribute('required');
            }
        });
    }

    if (form) {
        form.addEventListener('submit', function(e){
            // Si el evento requiere paquete, exigir selección de paquete primero
            if (tipoSelect && tipoSelect.value !== 'Pedido') {
                const paqueteSeleccionado = window.paqueteSeleccionadoTemp || document.querySelector('input[name="paquete_id"]');
                if (!paqueteSeleccionado) {
                    customAlert('Selecciona un paquete primero', 'Aviso');
                    e.preventDefault(); return;
                }
            }

            if (tipoSelect && tipoSelect.value === 'Otro') {
                if (!otroInput || otroInput.value.trim() === '') { customAlert('Especifica el tipo de evento.', 'Error de validación'); e.preventDefault(); return; }
            }

            // Verificar que haya al menos un producto en el carrito
            const hiddenIds = form.querySelectorAll('input[name="product_id[]"]');
            if (hiddenIds.length === 0) {
                customAlert('Selecciona al menos un producto del menú.', 'Error de validación');
                e.preventDefault(); return;
            }
        });
    }

    function openModal() {
        if (productosModal) productosModal.style.display = 'block';
        if (modalOverlay) modalOverlay.style.display = 'block';
    }

    function closeModal() {
        if (productosModal) productosModal.style.display = 'none';
        if (modalOverlay) modalOverlay.style.display = 'none';
    }

    if (btnAbrirModal) btnAbrirModal.addEventListener('click', function(){ openModal(); });
    if (btnCerrarModal) btnCerrarModal.addEventListener('click', function(){ closeModal(); });
    if (modalOverlay) modalOverlay.addEventListener('click', function(){ closeModal(); });

    // Manejar click en botones "Agregar al carrito" dentro del modal
    if (modalProductosGrid) {
        modalProductosGrid.addEventListener('click', function(e){
            if (e.target && e.target.classList.contains('btn-add-carrito')) {
                const productoEl = e.target.closest('.modal-producto');
                if (!productoEl) return;
                const id = productoEl.getAttribute('data-product-id');
                const precio = parseFloat(productoEl.getAttribute('data-price')) || 0;
                const nombreEl = productoEl.querySelector('.modal-producto-name');
                const nombre = nombreEl ? nombreEl.textContent.trim() : ('Prod ' + id);
                const qtyInput = productoEl.querySelector('input[type="number"]');
                let qty = 1;
                if (qtyInput) qty = Math.max(1, parseInt(qtyInput.value) || 1);

                // Añadir o actualizar en carritoLocal
                const existing = carritoLocal.find(p => p.id === id);
                if (existing) {
                    existing.cantidad = existing.cantidad + qty;
                } else {
                    carritoLocal.push({ id: id, nombre: nombre, precio: precio, cantidad: qty });
                }

                // Actualizar UI y campos ocultos
                renderCarritoResumen();
                syncHiddenInputs();
                // Mostrar notificación breve
                showToast(nomeOrSafe(nombre) + ' agregado al carrito');
            }
        });
    }

    function renderCarritoResumen() {
        if (!carritoResumen) return;
        if (carritoLocal.length === 0) {
            carritoResumen.innerHTML = '<p style="text-align: center; color: #999;">No hay productos en el carrito. Abre el menú para agregar.</p>';
            return;
        }
        let html = '<ul class="resumen-list">';
        carritoLocal.forEach(item => {
            html += '<li>' + escapeHtml(item.nombre) + ' x ' + item.cantidad + ' — $' + (item.precio * item.cantidad).toFixed(2) + ' <button type="button" class="btn-eliminar-item" data-id="'+item.id+'">Eliminar</button></li>';
        });
        html += '</ul>';
        carritoResumen.innerHTML = html;
    }

    // Delegation para eliminar items desde el resumen
    if (carritoResumen) {
        carritoResumen.addEventListener('click', function(e){
            if (e.target && e.target.classList.contains('btn-eliminar-item')) {
                const id = e.target.getAttribute('data-id');
                const idx = carritoLocal.findIndex(p => p.id === id);
                if (idx > -1) carritoLocal.splice(idx,1);
                renderCarritoResumen();
                syncHiddenInputs();
            }
        });
    }

    function syncHiddenInputs() {
        if (!form) return;
        // Eliminar inputs previos
        form.querySelectorAll('input[name="product_id[]"]').forEach(n => n.remove());
        form.querySelectorAll('input[name="cantidad[]"]').forEach(n => n.remove());

        // Añadir inputs según carritoLocal (mantener orden)
        carritoLocal.forEach(item => {
            const inpId = document.createElement('input');
            inpId.type = 'hidden';
            inpId.name = 'product_id[]';
            inpId.value = item.id;
            form.appendChild(inpId);

            const inpQty = document.createElement('input');
            inpQty.type = 'hidden';
            inpQty.name = 'cantidad[]';
            inpQty.value = item.cantidad;
            form.appendChild(inpQty);
        });
    }

    function escapeHtml(text) {
        return text.replace(/["&'<>]/g, function (a) { return {'"':'&quot;','&':'&amp;',"'":"&#39;","<":"&lt;",">":"&gt;"}[a]; });
    }

    // --- Toast notification ---
    function createToastContainer() {
        let container = document.getElementById('toastContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toastContainer';
            document.body.appendChild(container);
        }
        return container;
    }

    function showToast(message, timeout = 2800) {
        try {
            const container = createToastContainer();
            const toast = document.createElement('div');
            toast.className = 'toast-notification';
            toast.textContent = message;
            container.appendChild(toast);
            // Force reflow for animation
            // eslint-disable-next-line no-unused-expressions
            toast.offsetHeight;
            toast.classList.add('visible');
            setTimeout(() => {
                toast.classList.remove('visible');
                // remove after transition
                setTimeout(() => { toast.remove(); }, 350);
            }, timeout);
        } catch (e) {
            // Fail silently (do not block UI)
            console.warn('Toast error', e);
        }
    }

    function nomeOrSafe(n) { return (typeof n === 'string' && n.trim() !== '') ? n : 'Producto'; }
});

