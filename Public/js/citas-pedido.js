document.addEventListener('DOMContentLoaded', function() {
    const carritoResumen = document.getElementById('carritoResumen');
    const form = document.getElementById('citasForm');
    let carritoCitas = [];

    // --- Lógica para agregar productos al pedido ---
    document.querySelectorAll('.modal-producto').forEach(productoDiv => {
        const id = productoDiv.dataset.productId;
        const nombre = productoDiv.querySelector('.modal-producto-name').textContent;
        const precio = parseFloat(productoDiv.dataset.price);

        const btnMinus = productoDiv.querySelector('.btn-qty-minus');
        const btnPlus = productoDiv.querySelector('.btn-qty-plus');

        btnMinus.addEventListener('click', () => {
            let itemIndex = carritoCitas.findIndex(p => p.id === id);
            if (itemIndex > -1) {
                if (carritoCitas[itemIndex].cantidad > 1) {
                    carritoCitas[itemIndex].cantidad--;
                } else {
                    carritoCitas.splice(itemIndex, 1);
                }
                actualizarResumenCarrito();
            }
        });

        btnPlus.addEventListener('click', () => {
            const maxPerProduct = 25;
            const maxTotal = 100;

            let currentTotal = carritoCitas.reduce((sum, item) => sum + item.cantidad, 0);

            if (currentTotal >= maxTotal) {
                if (typeof customAlert === 'function') {
                    customAlert(`Has alcanzado el límite máximo de ${maxTotal} productos por pedido.`, 'Límite alcanzado');
                } else {
                    alert(`Has alcanzado el límite máximo de ${maxTotal} productos por pedido.`);
                }
                return;
            }

            let itemIndex = carritoCitas.findIndex(p => p.id === id);
            if (itemIndex > -1) {
                if (carritoCitas[itemIndex].cantidad >= maxPerProduct) {
                    if (typeof customAlert === 'function') {
                        customAlert(`Puedes pedir un máximo de ${maxPerProduct} unidades de este producto.`, 'Límite alcanzado');
                    } else {
                        alert(`Puedes pedir un máximo de ${maxPerProduct} unidades de este producto.`);
                    }
                    return;
                }
                carritoCitas[itemIndex].cantidad++;
            } else {
                carritoCitas.push({ id, nombre, precio, cantidad: 1 });
            }
            actualizarResumenCarrito();
        });
    });

    function renderAllQuantities() {
        document.querySelectorAll('.modal-producto').forEach(productoDiv => {
            const id = productoDiv.dataset.productId;
            const item = carritoCitas.find(p => p.id === id);
            const display = productoDiv.querySelector('.qty-display');
            if (display) {
                display.textContent = item ? item.cantidad : '0';
            }
        });
    }

    function actualizarResumenCarrito() {
        if (carritoCitas.length === 0) {
            carritoResumen.innerHTML = '<p style="text-align: center; color: #555555; font-weight: bold;">No hay productos seleccionados.</p>';
            renderAllQuantities();
            actualizarFormularioOculto();
            return;
        }

        let total = 0;
        let totalItems = 0;
        carritoResumen.innerHTML = '<h4>Productos Seleccionados:</h4>';
        const lista = document.createElement('ul');

        carritoCitas.forEach((p, index) => {
            const item = document.createElement('li');
            item.innerHTML = `
                ${p.cantidad} x ${p.nombre} - $${(p.precio * p.cantidad).toFixed(2)}
                <button type="button" class="btn-remover-item" data-index="${index}" aria-label="Eliminar ${p.nombre} del carrito" style="margin-left: 10px; color: #d32f2f; cursor:pointer; background:none; border:none; font-size: 1.2em;">&times;</button>
            `;
            lista.appendChild(item);
            total += p.precio * p.cantidad;
            totalItems += p.cantidad;
        });

        carritoResumen.appendChild(lista);
        
        let descuentoPorcentaje = 0;
        if (totalItems >= 24) {
            descuentoPorcentaje = 0.10;
        } else if (totalItems >= 12) {
            descuentoPorcentaje = 0.05;
        }
        
        let descuentoMonto = total * descuentoPorcentaje;
        let granTotal = total - descuentoMonto;

        const totalDiv = document.createElement('div');
        totalDiv.style.marginTop = '10px';
        totalDiv.style.textAlign = 'right';
        
        if (descuentoPorcentaje > 0) {
            totalDiv.innerHTML = `
                <div style="color: #666; font-size: 0.95em;">Subtotal: $${total.toFixed(2)}</div>
                <div style="color: #E91E63; font-weight: 600; font-size: 0.95em;">Descuento (${descuentoPorcentaje * 100}%): -$${descuentoMonto.toFixed(2)}</div>
                <strong style="display:block; font-size: 1.2em; margin-top: 4px; color: #1f2937;">Total Final: $${granTotal.toFixed(2)}</strong>
            `;
        } else {
            totalDiv.innerHTML = `
                <strong style="display:block; font-size: 1.1em; color: #1f2937;">Total: $${granTotal.toFixed(2)}</strong>
            `;
        }
        
        carritoResumen.appendChild(totalDiv);

        renderAllQuantities();
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
        const oldInputs = form.querySelectorAll('input[name="product_id[]"], input[name="cantidad[]"]');
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
            qtyInput.name = 'cantidad[]'; // IMPORTANT: This matches what CitasController.php expects
            qtyInput.value = p.cantidad;
            form.appendChild(qtyInput);
        });
    }

    // --- Lógica del formulario de citas ---
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
        // Disparar change al cargar para el estado inicial
        tipoSelect.dispatchEvent(new Event('change'));
    }

    // --- Lógica de "Ver más..." en descripciones ---
    setTimeout(() => {
        document.querySelectorAll('.producto-desc-text').forEach(el => {
            // Check if text is overflowing its 2-line clamp
            if (el.scrollHeight > el.clientHeight + 2) {
                const btn = el.nextElementSibling;
                if (btn && btn.classList.contains('btn-ver-mas-desc')) {
                    btn.style.display = 'inline-block';
                }
            }
        });
    }, 100);

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-ver-mas-desc')) {
            e.preventDefault();
            const textEl = e.target.previousElementSibling;
            if (textEl.style.webkitLineClamp === '2' || textEl.style.webkitLineClamp === '') {
                textEl.style.webkitLineClamp = 'unset';
                textEl.style.maxHeight = 'none';
                e.target.textContent = 'Ver menos';
                e.target.setAttribute('aria-expanded', 'true');
            } else {
                textEl.style.webkitLineClamp = '2';
                textEl.style.maxHeight = '2.8em';
                e.target.textContent = 'Ver más...';
                e.target.setAttribute('aria-expanded', 'false');
            }
        }
    });

    if (form) {
        form.addEventListener('submit', function(e){
            // Ensure cart is not empty before submitting
            if (carritoCitas.length === 0) {
                if (typeof customAlert === 'function') {
                    customAlert('Debes seleccionar al menos un producto para tu pedido.', 'Error de validación');
                } else {
                    alert('Debes seleccionar al menos un producto para tu pedido.');
                }
                e.preventDefault();
            }
        });
    }
});
