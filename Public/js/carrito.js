document.addEventListener('DOMContentLoaded', function() {
    const CART_KEY = 'pastelesupbc_carrito';
    const cargarCarrito = () => {
        try {
            const raw = localStorage.getItem(CART_KEY);
            if (!raw) return [];
            const parsed = JSON.parse(raw);
            if (!Array.isArray(parsed)) return [];
            return parsed;
        } catch (e) { console.warn('Error leyendo carrito desde localStorage', e); return []; }
    };

    const guardarCarrito = (lista) => {
        try {
            localStorage.setItem(CART_KEY, JSON.stringify(lista));
        } catch (e) { console.warn('Error guardando carrito en localStorage', e); }
    };

    let carrito = cargarCarrito();
    const modal = document.getElementById('carritoModal');
    const btnVerCarrito = document.getElementById('verCarritoBtn');
    const btnHeaderCarrito = document.getElementById('headerCarritoBtn');
    const spanCerrar = document.getElementsByClassName('cerrar')[0];
    const btnComprar = document.getElementById('comprarBtn');
    const carritoContenedor = document.getElementById('carritoContenedor');
    const carritoTotal = document.getElementById('carritoTotal');

    // Actualizar contador del header
    const actualizarContadorHeader = () => {
        const headerCount = document.getElementById('headerCarritoCount');
        if (headerCount) {
            const totalItems = carrito.reduce((sum, item) => sum + (item.cantidad || 0), 0);
            headerCount.textContent = totalItems;
        }
    };
    actualizarContadorHeader();

    // Usar delegación de eventos para manejar clicks en botones dinámicos
    document.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('btn-pedir')) {
            e.preventDefault();
            // Verificar si el usuario está logueado (simple: buscar variable global o cookie)
            let isLogged = false;
            try {
                isLogged = window.sessionStorage.getItem('usuario_logueado') === '1';
            } catch (err) { isLogged = false; }
            // Si no está logueado, mostrar modal/login
            if (!isLogged) {
                mostrarLoginModal();
                return;
            }
            const tarjeta = e.target.closest('.producto-tarjeta');
            if (!tarjeta) return;
            const stock = parseInt(tarjeta.dataset.stock || '0', 10);
            const nombreProducto = tarjeta.dataset.nombre || 'Este producto';
            if (stock <= 0) {
                customAlert(`${nombreProducto} está agotado.`, 'Producto agotado');
                return;
            }
            const producto = {
                id: tarjeta.dataset.id,
                nombre: tarjeta.dataset.nombre,
                precio: parseFloat(tarjeta.dataset.precio),
                cantidad: 1,
                originalPrecio: tarjeta.dataset.precioOriginal ? parseFloat(tarjeta.dataset.precioOriginal) : null,
                stock: stock
            };
            agregarAlCarrito(producto);
        }
    });

    // Modal de login/registro
    function mostrarLoginModal() {
        let modal = document.getElementById('loginModal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'loginModal';
            modal.innerHTML = `
                <div class="login-modal-overlay" style="position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.5);z-index:2000;display:flex;align-items:center;justify-content:center;">
                    <div class="login-modal-content" style="background:#fff;padding:32px 28px;border-radius:16px;max-width:340px;width:100%;box-shadow:0 8px 32px rgba(0,0,0,0.18);position:relative;">
                        <button id="cerrarLoginModal" style="position:absolute;top:12px;right:12px;background:none;border:none;font-size:1.5em;cursor:pointer;">&times;</button>
                        <h2>Inicia sesión para continuar</h2>
                        <p>Debes iniciar sesión o registrarte para agregar productos al carrito.</p>
                        <div style="display:flex;gap:12px;margin-top:18px;">
                            <a href="` + (window.BASE_URL || '/') + `login" class="btn-primary" style="flex:1;text-align:center;padding:10px 0;border-radius:8px;background:#E91E63;color:#fff;font-weight:700;text-decoration:none;">Iniciar sesión</a>
                            <a href="` + (window.BASE_URL || '/') + `registro" class="btn-primary" style="flex:1;text-align:center;padding:10px 0;border-radius:8px;background:#7bd389;color:#083108;font-weight:700;text-decoration:none;">Registrarse</a>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
            document.getElementById('cerrarLoginModal').onclick = function() {
                modal.remove();
            };
            modal.querySelector('.login-modal-overlay').onclick = function(e) {
                if (e.target === modal.querySelector('.login-modal-overlay')) modal.remove();
            };
        }
    }

    if (btnVerCarrito && modal) {
        btnVerCarrito.addEventListener('click', function() {
            modal.style.display = "block";
            actualizarCarrito();
        });
    }
    if (btnHeaderCarrito && modal) {
        btnHeaderCarrito.addEventListener('click', function() {
            modal.style.display = "block";
            actualizarCarrito();
        });
    }
    if (spanCerrar && modal) {
        spanCerrar.addEventListener('click', function() {
            modal.style.display = "none";
        });
    }
    if (modal) {
        window.addEventListener('click', function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        });
    }

    if (btnComprar) {
        btnComprar.addEventListener('click', () => {
            // Verificar si el usuario está logueado
            let isLogged = false;
            try {
                isLogged = window.sessionStorage.getItem('usuario_logueado') === '1';
            } catch (e) {}
            
            if (!isLogged) {
                customAlert('Debes iniciar sesión para realizar una compra', 'Aviso');
                return;
            }
            
            if (carrito.length === 0) {
                customAlert('El carrito está vacío', 'Aviso');
                return;
            }
            
            // Crear formulario temporal para enviar el carrito a la página de confirmación
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = window.BASE_URL + 'compras/confirmar';
            
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'carrito';
            input.value = JSON.stringify(carrito);
            
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        });
    }

function agregarAlCarrito(producto) {
    const totalItemsActuales = carrito.reduce((sum, item) => sum + (item.cantidad || 0), 0);
    const productoExistente = carrito.find(p => p.id === producto.id);

    if (totalItemsActuales >= 7) {
        customAlert('Has alcanzado el límite máximo de 7 productos en el carrito.', 'Carrito lleno');
        return; 
    }

    if (productoExistente) {
        // Limitar por stock
        const maxStock = productoExistente.stock || producto.stock || Infinity;
        
        if ((productoExistente.cantidad + 1) > maxStock) {
            customAlert('No puedes agregar más de ' + maxStock + ' unidades por stock disponible.', 'Stock limitado');
            return;
        }
        
        productoExistente.cantidad++;
        try {
            const precioExistente = parseFloat(productoExistente.precio) || 0;
            const precioNuevo = parseFloat(producto.precio) || 0;
            const nuevoPrecioUnitario = Math.min(precioExistente, precioNuevo);
            
            if ((!productoExistente.originalPrecio || productoExistente.originalPrecio <= nuevoPrecioUnitario) && precioExistente > nuevoPrecioUnitario) {
                productoExistente.originalPrecio = precioExistente;
            }
            productoExistente.precio = nuevoPrecioUnitario;
            
            if (producto.originalPrecio) {
                const op = parseFloat(producto.originalPrecio);
                if (!productoExistente.originalPrecio || op > productoExistente.originalPrecio) {
                    productoExistente.originalPrecio = op;
                }
            }
        } catch (e) { /* ignore parse errors */ }
    } else {
        carrito.push({ ...producto, cantidad: 1 });
    }

    actualizarCarrito();
    guardarCarrito(carrito);
    actualizarContadorHeader();

    if (typeof showToast === 'function') {
        try { showToast(producto.nombre + ' agregado al carrito'); } catch (e) { }
    } else {
        inlineToast(producto.nombre + ' agregado al carrito');
    }
}

    function inlineToast(message, timeout = 2600) {
        try {
            let container = document.getElementById('toastContainer');
            if (!container) {
                container = document.createElement('div');
                container.id = 'toastContainer';
                container.style.position = 'fixed';
                container.style.right = '18px';
                container.style.bottom = '18px';
                container.style.zIndex = 1200;
                document.body.appendChild(container);
            }
            const toast = document.createElement('div');
            toast.className = 'toast-notification';
            toast.textContent = message;
            container.appendChild(toast);
            toast.offsetHeight;
            toast.classList.add('visible');
            setTimeout(() => {
                toast.classList.remove('visible');
                setTimeout(() => toast.remove(), 350);
            }, timeout);
        } catch (e) { console.warn('inlineToast error', e); }
    }

    function restarDelCarrito(productoId) {
        const productoExistente = carrito.find(p => p.id === productoId);
        if (productoExistente) {
            productoExistente.cantidad--;
            if (productoExistente.cantidad <= 0) {
                eliminarProductoDelCarrito(productoId);
            }
        }
        actualizarCarrito();
        guardarCarrito(carrito);
        actualizarContadorHeader();
    }

    function eliminarProductoDelCarrito(productoId) {
        const productoIndex = carrito.findIndex(p => p.id === productoId);
        if (productoIndex > -1) {
            carrito.splice(productoIndex, 1);
        }
        actualizarCarrito();
        guardarCarrito(carrito);
        actualizarContadorHeader();
    }

    function actualizarCarrito() {
        if (!carritoContenedor) return;
        carritoContenedor.innerHTML = '';
        let total = 0;

        if (carrito.length === 0) {
            carritoContenedor.innerHTML = '<p>El carrito está vacío.</p>';
        } else {
            carrito.forEach(producto => {
                const productoDiv = document.createElement('div');
                productoDiv.classList.add('carrito-producto');
                let precioLinea = '';
                if (producto.originalPrecio && parseFloat(producto.originalPrecio) > parseFloat(producto.precio)) {
                    precioLinea = `
                        <div class="precio-item">
                            <span class="precio-actual">$${(producto.precio * producto.cantidad).toFixed(2)}</span>
                            <span class="precio-original">$${(producto.originalPrecio * producto.cantidad).toFixed(2)}</span>
                            <span class="badge-promo">Promo</span>
                        </div>
                    `;
                } else {
                    precioLinea = `<span>$${(producto.precio * producto.cantidad).toFixed(2)}</span>`;
                }

                productoDiv.innerHTML = `
                    <div class="carrito-producto-info">
                        <span>${producto.nombre}</span>
                        ${precioLinea}
                    </div>
                    <div class="carrito-producto-controles">
                        <button class="btn-restar" data-id="${producto.id}" ${producto.cantidad <= 1 ? '' : ''}>-</button>
                        <span class="cantidad">${producto.cantidad}</span>
                        <button class="btn-sumar" data-id="${producto.id}" ${ (producto.stock && producto.cantidad >= parseInt(producto.stock,10)) ? 'disabled' : '' }>+</button>
                        <button class="btn-eliminar" data-id="${producto.id}">Eliminar</button>
                    </div>
                `;
                carritoContenedor.appendChild(productoDiv);
                total += producto.precio * producto.cantidad;
            });
        }
        if (carritoTotal) carritoTotal.textContent = total.toFixed(2);
    }

    if (carritoContenedor) {
        carritoContenedor.addEventListener('click', (e) => {
            const id = e.target.dataset.id;
            if (e.target.classList.contains('btn-sumar')) {
                const producto = carrito.find(p => p.id === id);
                if (producto) {
                    const maxStock = producto.stock || Infinity;
                    if ((producto.cantidad + 1) > maxStock) {
                        customAlert('No puedes superar el stock disponible (' + maxStock + ').', 'Stock limitado');
                        return;
                    }
                    agregarAlCarrito(producto);
                }
            } else if (e.target.classList.contains('btn-restar')) {
                restarDelCarrito(id);
            } else if (e.target.classList.contains('btn-eliminar')) {
                eliminarProductoDelCarrito(id);
            }
        });
    }
    window.addEventListener('carritoStorageUpdated', function(e) {
        try {
            const nueva = e.detail || [];
            carrito = Array.isArray(nueva) ? nueva : [];
            actualizarCarrito();
        } catch (err) { console.warn('carritoStorageUpdated error', err); }
    });
    actualizarCarrito();

    function vaciarCarrito() {
        carrito.length = 0;
        actualizarCarrito();
        try { localStorage.removeItem(CART_KEY); } catch (e) { }
    }
});
window.addEventListener('storage', function(e) {
    if (e.key !== 'pastelesupbc_carrito') return;
    try {
        const nueva = e.newValue ? JSON.parse(e.newValue) : [];
        if (window.document && typeof window.document.getElementById === 'function') {
            const ev = new CustomEvent('carritoStorageUpdated', { detail: nueva });
            window.dispatchEvent(ev);
        }
    } catch (err) { console.warn('Error procesando evento storage carrito', err); }
});