document.addEventListener('DOMContentLoaded', function() {
    // ===== LÓGICA DE PAQUETES =====
    const tipoEventoSelect = document.getElementById('tipoEvento');
    const paquetesContainer = document.getElementById('paquetesContainer');
    const paquetesGrid = document.getElementById('paquetesGrid');
    const formularioPersonalizado = document.getElementById('formularioPersonalizado');
    const btnPedidoPersonalizado = document.getElementById('btnPedidoPersonalizado');
    
    // Datos de paquetes inyectados desde PHP
    const paquetesData = window.paquetesEventos || [];
    
    // Agrupar paquetes por tipo de evento
    const paquetesPorTipo = {};
    paquetesData.forEach(paq => {
        if (!paquetesPorTipo[paq.tipo_evento]) {
            paquetesPorTipo[paq.tipo_evento] = [];
        }
        paquetesPorTipo[paq.tipo_evento].push(paq);
    });
    
    // Mostrar/ocultar paquetes según tipo de evento
    function actualizarPaquetes() {
        const tipoSeleccionado = tipoEventoSelect.value;
        
        // Si es "Pedido", ocultar paquetes y mostrar formulario personalizado
        if (tipoSeleccionado === 'Pedido') {
            if (paquetesContainer) paquetesContainer.style.display = 'none';
            if (formularioPersonalizado) formularioPersonalizado.style.display = 'block';
            return;
        }
        
        // Mostrar paquetes para el tipo de evento seleccionado
        const paquetesDisponibles = paquetesPorTipo[tipoSeleccionado] || [];
        
        if (paquetesDisponibles.length > 0) {
            paquetesGrid.innerHTML = '';
            paquetesDisponibles.forEach(paquete => {
                const ahorro = calcularAhorro(paquete);
                const card = crearTarjetaPaquete(paquete, ahorro);
                paquetesGrid.appendChild(card);
            });
            if (paquetesContainer) paquetesContainer.style.display = 'block';
            if (formularioPersonalizado) formularioPersonalizado.style.display = 'none';
        } else {
            // Si no hay paquetes, mostrar formulario personalizado
            if (paquetesContainer) paquetesContainer.style.display = 'none';
            if (formularioPersonalizado) formularioPersonalizado.style.display = 'block';
        }
    }
    
    // Calcular ahorro estimado
    function calcularAhorro(paquete) {
        const precioPromedio = 180; // Precio promedio por postre
        const precioIndividual = paquete.cantidad_postres * precioPromedio;
        return precioIndividual - parseFloat(paquete.precio);
    }
    
    // Crear tarjeta HTML para un paquete
    function crearTarjetaPaquete(paquete, ahorro) {
        const div = document.createElement('div');
        div.className = 'paquete-card';
        
        const porcentajeAhorro = ahorro > 0 ? ((ahorro / (parseFloat(paquete.precio) + ahorro)) * 100).toFixed(0) : 0;
        
        div.innerHTML = `
            <div class="paquete-header">
                <h4>${paquete.nombre}</h4>
                ${ahorro > 0 ? `<span class="badge-ahorro">Ahorra $${ahorro.toFixed(2)} (${porcentajeAhorro}%)</span>` : ''}
            </div>
            <p class="paquete-desc">${paquete.descripcion}</p>
            <div class="paquete-info">
                <span class="paquete-cantidad">${paquete.cantidad_postres} postres</span>
                <span class="paquete-precio">$${parseFloat(paquete.precio).toFixed(2)}</span>
            </div>
            <button type="button" class="btn-seleccionar-paquete" data-paquete-id="${paquete.id_paquete}">
                Seleccionar este paquete
            </button>
        `;
        
        const btnSeleccionar = div.querySelector('.btn-seleccionar-paquete');
        btnSeleccionar.addEventListener('click', () => seleccionarPaquete(paquete));
        
        return div;
    }
    
    // Seleccionar un paquete
    function seleccionarPaquete(paquete) {
        // Mostrar modal de fecha
        const modalFecha = document.getElementById('modalFechaPaquete');
        const modalOverlay = document.getElementById('modalOverlay');
        const fechaInput = document.getElementById('fechaEventoPaquete');
        const paqueteNombre = document.getElementById('paqueteNombreModal');
        const paquetePrecio = document.getElementById('paquetePrecioModal');
        
        // Establecer fecha mínima (mañana) y máxima (3 meses)
        const mañana = new Date();
        mañana.setDate(mañana.getDate() + 1);
        fechaInput.min = mañana.toISOString().split('T')[0];
        
        const maxFecha = new Date();
        maxFecha.setMonth(maxFecha.getMonth() + 3);
        fechaInput.max = maxFecha.toISOString().split('T')[0];
        
        fechaInput.value = '';
        
        // Mostrar información del paquete
        paqueteNombre.textContent = paquete.nombre;
        paquetePrecio.textContent = `$${parseFloat(paquete.precio).toFixed(2)} - ${paquete.cantidad_postres} postres`;
        
        // Mostrar modal
        if (modalFecha) modalFecha.style.display = 'flex';
        if (modalOverlay) modalOverlay.style.display = 'block';
        
        // Guardar paquete temporalmente
        window.paqueteSeleccionadoTemp = paquete;
    }
    
    // Mostrar formulario personalizado
    if (btnPedidoPersonalizado) {
        btnPedidoPersonalizado.addEventListener('click', function() {
            if (paquetesContainer) paquetesContainer.style.display = 'none';
            if (formularioPersonalizado) formularioPersonalizado.style.display = 'block';
        });
    }
    
    // Evento de cambio de tipo de evento
    if (tipoEventoSelect) {
        tipoEventoSelect.addEventListener('change', actualizarPaquetes);
        actualizarPaquetes(); // Ejecutar al cargar
    }
    
    // ===== LÓGICA DEL MODAL DE FECHA PARA PAQUETES =====
    const modalFechaPaquete = document.getElementById('modalFechaPaquete');
    const btnCerrarModalFecha = document.getElementById('btnCerrarModalFecha');
    const btnCancelarFecha = document.getElementById('btnCancelarFecha');
    const btnContinuarPaquete = document.getElementById('btnContinuarPaquete');
    const fechaEventoPaquete = document.getElementById('fechaEventoPaquete');
    
    // Cerrar modal de fecha
    function cerrarModalFecha() {
        if (modalFechaPaquete) modalFechaPaquete.style.display = 'none';
        if (modalOverlay) modalOverlay.style.display = 'none';
        window.paqueteSeleccionadoTemp = null;
    }
    
    if (btnCerrarModalFecha) {
        btnCerrarModalFecha.addEventListener('click', cerrarModalFecha);
    }
    
    if (btnCancelarFecha) {
        btnCancelarFecha.addEventListener('click', cerrarModalFecha);
    }
    
    // Continuar con el paquete (validar fecha y redirigir)
    if (btnContinuarPaquete) {
        btnContinuarPaquete.addEventListener('click', function() {
            const fechaSeleccionada = fechaEventoPaquete.value;
            
            if (!fechaSeleccionada) {
                alert('Por favor selecciona una fecha para el evento');
                fechaEventoPaquete.focus();
                return;
            }
            
            // Validar que la fecha sea futura
            const fechaEvento = new Date(fechaSeleccionada + 'T00:00:00');
            const hoy = new Date();
            hoy.setHours(0, 0, 0, 0);
            
            if (fechaEvento <= hoy) {
                alert('La fecha del evento debe ser al menos 1 día en el futuro');
                fechaEventoPaquete.focus();
                return;
            }
            
            const paquete = window.paqueteSeleccionadoTemp;
            if (paquete) {
                // Redirigir con la fecha como parámetro
                window.location.href = window.BASE_URL + 'citas/paquete/' + paquete.id_paquete + '?fecha=' + fechaSeleccionada;
            }
        });
    }
    
    // ===== LÓGICA DE MODAL DE PRODUCTOS (EXISTENTE) =====
    const btnAbrirModal = document.getElementById('btnAbrirModal');
    const btnCerrarModal = document.getElementById('btnCerrarModal');
    const modalOverlay = document.getElementById('modalOverlay');
    const productosModal = document.getElementById('productosModal');
    const carritoResumen = document.getElementById('carritoResumen');
    const form = document.getElementById('citasForm');
    let carritoCitas = [];

    if (btnAbrirModal) {
        btnAbrirModal.addEventListener('click', () => {
            if (modalOverlay) modalOverlay.style.display = 'block';
            if (productosModal) productosModal.style.display = 'block';
        });
    }

    if (btnCerrarModal) {
        btnCerrarModal.addEventListener('click', () => {
            if (modalOverlay) modalOverlay.style.display = 'none';
            if (productosModal) productosModal.style.display = 'none';
        });
    }

    if (modalOverlay) {
        modalOverlay.addEventListener('click', () => {
            if (modalOverlay) modalOverlay.style.display = 'none';
            if (productosModal) productosModal.style.display = 'none';
        });
    }

    // --- Lógica para agregar productos desde el modal ---
    document.querySelectorAll('.btn-add-carrito').forEach(btn => {
        btn.addEventListener('click', () => {
            const productoDiv = btn.closest('.modal-producto');
            const id = productoDiv.dataset.productId;
            const nombre = productoDiv.querySelector('.modal-producto-name').textContent;
            const precio = parseFloat(productoDiv.dataset.price);
            const cantidadInput = productoDiv.querySelector('input[type="number"]');
            const cantidad = parseInt(cantidadInput.value, 10);

            if (cantidad > 0) {
                agregarAlCarritoCitas({ id, nombre, precio, cantidad });
                cantidadInput.value = '1'; // Reset quantity
            }
        });
    });

    function agregarAlCarritoCitas(producto) {
        const existente = carritoCitas.find(p => p.id === producto.id);
        if (existente) {
            existente.cantidad += producto.cantidad;
        } else {
            carritoCitas.push(producto);
        }
        actualizarResumenCarrito();
    }

    function actualizarResumenCarrito() {
        if (carritoCitas.length === 0) {
            carritoResumen.innerHTML = '<p style="text-align: center; color: #999;">No hay productos en el carrito. Abre el menú para agregar.</p>';
            return;
        }

        let total = 0;
        carritoResumen.innerHTML = '<h4>Productos Seleccionados:</h4>';
        const lista = document.createElement('ul');

        carritoCitas.forEach((p, index) => {
            const item = document.createElement('li');
            item.innerHTML = `
                ${p.cantidad} x ${p.nombre} - $${(p.precio * p.cantidad).toFixed(2)}
                <button type="button" class="btn-remover-item" data-index="${index}">&times;</button>
            `;
            lista.appendChild(item);
            total += p.precio * p.cantidad;
        });

        carritoResumen.appendChild(lista);
        const totalEl = document.createElement('p');
        totalEl.innerHTML = `<strong>Total: $${total.toFixed(2)}</strong>`;
        carritoResumen.appendChild(totalEl);

        // Actualizar campos ocultos en el formulario
        actualizarFormularioOculto();
    }

    carritoResumen.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-remover-item')) {
            const index = parseInt(e.target.dataset.index, 10);
            carritoCitas.splice(index, 1);
            actualizarResumenCarrito();
        }
    });

    function actualizarFormularioOculto() {
        // Limpiar campos ocultos existentes
        const oldInputs = form.querySelectorAll('input[name="product_id[]"], input[name="product_qty[]"]');
        oldInputs.forEach(input => input.remove());

        // Agregar nuevos campos
        carritoCitas.forEach(p => {
            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'product_id[]';
            idInput.value = p.id;
            form.appendChild(idInput);

            const qtyInput = document.createElement('input');
            qtyInput.type = 'hidden';
            qtyInput.name = 'product_qty[]';
            qtyInput.value = p.cantidad;
            form.appendChild(qtyInput);
        });
    }

    // --- Lógica del formulario de citas (de citas.js) ---
    const tipoSelect = document.getElementById('tipoEvento');
    const otroContainer = document.getElementById('otroContainer');
    const otroInput = document.getElementById('otroEvento');

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
            if (carritoCitas.length === 0) {
                customAlert('Debes seleccionar al menos un producto para tu pedido.', 'Error de validación');
                e.preventDefault();
            }
        });
    }
});
