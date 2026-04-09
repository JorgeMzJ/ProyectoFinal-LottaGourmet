document.addEventListener('DOMContentLoaded', () => {
    // LÓGICA DE LA COTIZACIÓN 
    const selectPan = document.getElementById('api-pan');
    const selectRelleno = document.getElementById('api-relleno');
    const selectCobertura = document.getElementById('api-cobertura');

    const capaPan = document.getElementById('capa-pan');
    const capaRelleno = document.getElementById('capa-relleno');
    const capaCobertura = document.getElementById('capa-cobertura');

    function actualizarPastel() {
        capaPan.className = 'pastel-capa pan ' + selectPan.value.toLowerCase();
        capaRelleno.className = 'pastel-capa relleno ' + selectRelleno.value.toLowerCase();
        capaCobertura.className = 'pastel-capa cobertura ' + selectCobertura.value.toLowerCase();
    }

    selectPan.addEventListener('change', actualizarPastel);
    selectRelleno.addEventListener('change', actualizarPastel);
    selectCobertura.addEventListener('change', actualizarPastel);


    // LÓGICA DE LA API (POST /api/cotizar)
    const btnCalcular = document.getElementById('btn-calcular-api');
    const btnRealizarPedido = document.getElementById('btn-realizar-pedido');
    const ticketDiv = document.getElementById('ticket-cotizacion');
    const fechaContainer = document.getElementById('fecha-entrega-api-container');

    let totalCalculado = 0;

    // ACCIÓN 1: Calcular la Cotización
    btnCalcular.addEventListener('click', async () => {
        const personas = document.getElementById('api-personas').value;
        const pan = selectPan.value;
        const relleno = selectRelleno.value;
        const cobertura = selectCobertura.value;

        const checkboxes = document.querySelectorAll('input[name="eliminar_ingrediente"]:checked');
        const ingredientesEliminados = Array.from(checkboxes).map(cb => cb.value);

        const datos = {
            personas: parseInt(personas),
            pan: pan,
            relleno: relleno,
            cobertura: cobertura,
            eliminar_ingrediente: ingredientesEliminados
        };

        try {
            const response = await fetch('api/cotizar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(datos)
            });

            const resultado = await response.json();

            if (resultado.exito) {
                totalCalculado = resultado.desglose.costo_calculado;

                let descuentoHTML = '';
                if (resultado.desglose.descuento_aplicado > 0) {
                    descuentoHTML = `
                        <div class="ticket-fila" style="color: #28a745; font-weight: bold;">
                            <span>Descuento:</span> 
                            <span>-$${resultado.desglose.descuento_aplicado}</span>
                        </div>
                    `;
                }

                ticketDiv.innerHTML = `
                    <h3 style="color: var(--accent);">🧾 Ticket de Cotización</h3>
                    <div class="ticket-fila"><span>Personas:</span> <span>${resultado.desglose.personas}</span></div>
                    <div class="ticket-fila"><span>Pan:</span> <span>${resultado.desglose.pan}</span></div>
                    <div class="ticket-fila"><span>Relleno:</span> <span>${resultado.desglose.relleno}</span></div>
                    <div class="ticket-fila"><span>Cobertura:</span> <span>${resultado.desglose.cobertura}</span></div>
                    ${descuentoHTML}
                    <div class="ticket-total">Total: $${resultado.desglose.costo_calculado} ${resultado.desglose.moneda}</div>
                `;

                // Mostrar el botón de pago y el input de fecha
                btnRealizarPedido.style.display = 'block';
                fechaContainer.style.display = 'block';

            } else {
                ticketDiv.innerHTML = `<p style="color:red; text-align:center;">Error: ${resultado.mensaje}</p>`;
                btnRealizarPedido.style.display = 'none';
                fechaContainer.style.display = 'none';
            }
        } catch (error) {
            console.error("Error al cotizar:", error);
            ticketDiv.innerHTML = `<p style="color:red; text-align:center;">Error de conexión.</p>`;
        }
    });

    // ACCIÓN 2: Realizar Pedido (Guardar en Base de Datos)
    btnRealizarPedido.addEventListener('click', async () => {
        const fechaEntrega = document.getElementById('api-fecha-entrega').value;
        const notas = document.getElementById('api-notas').value;

        if (!fechaEntrega) {
            alert('Por favor, selecciona una fecha de entrega.');
            return;
        }

        const checkboxes = document.querySelectorAll('input[name="eliminar_ingrediente"]:checked');
        const ingredientesEliminados = Array.from(checkboxes).map(cb => cb.value);

        const datosPedido = {
            personas: parseInt(document.getElementById('api-personas').value) || 10,
            pan: selectPan.value,
            relleno: selectRelleno.value,
            cobertura: selectCobertura.value,
            eliminar_ingrediente: ingredientesEliminados,
            notas: notas,
            fecha: fechaEntrega
        };

        try {
            const response = await fetch(window.BASE_URL + 'citas/crearStripeSession', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(datosPedido)
            });

            const resultado = await response.json();

            if (resultado.success && resultado.id) {
                const stripe = Stripe(window.STRIPE_PUBLIC_KEY);
                stripe.redirectToCheckout({ sessionId: resultado.id }).then(result => {
                    if (result.error) {
                        alert("Error de Stripe: " + result.error.message);
                    }
                });
            } else {
                alert("Error: " + (resultado.error || 'No se pudo iniciar el pago.'));
            }
        } catch (error) {
            console.error("Error en el pago:", error);
            alert("Ocurrió un error al intentar procesar el pago.");
        }
    });

    // LÓGICA DE LOS PAQUETES PREDEFINIDOS
    const tarjetas = document.querySelectorAll('.paquete-card');
    const panelVacio = document.getElementById('panel-reserva-vacio');
    const panelReserva = document.getElementById('form-reserva-paquete');
    const tituloPaquete = document.getElementById('paquete-seleccionado-titulo');
    const descPaquete = document.getElementById('paquete-seleccionado-desc');
    const totalEstimado = document.getElementById('paquete-total-estimado');
    const inputPersonas = document.getElementById('paquete-personas');
    const btnReservarPaquete = document.getElementById('btn-reservar-paquete');

    let precioBaseSeleccionado = 0;
    let paqueteSeleccionadoNombre = '';
    let paqueteSeleccionadoId = 0;

    tarjetas.forEach(tarjeta => {
        tarjeta.addEventListener('click', () => {
            tarjetas.forEach(t => t.classList.remove('active'));
            tarjeta.classList.add('active');

            paqueteSeleccionadoNombre = tarjeta.getAttribute('data-paquete-nombre') || tarjeta.getAttribute('data-paquete');
            paqueteSeleccionadoId = parseInt(tarjeta.getAttribute('data-paquete-id') || tarjeta.getAttribute('data-id'));
            precioBaseSeleccionado = parseInt(tarjeta.getAttribute('data-paquete-precio') || tarjeta.getAttribute('data-precio'));
            const desc = tarjeta.getAttribute('data-paquete-desc') || tarjeta.getAttribute('data-desc');

            panelVacio.style.display = 'none';
            panelReserva.style.display = 'block';

            tituloPaquete.innerText = `Paquete: ${paqueteSeleccionadoNombre}`;
            descPaquete.innerText = desc;
            calcularTotalPaquete();
        });
    });

    inputPersonas.addEventListener('input', calcularTotalPaquete);

    function calcularTotalPaquete() {
        let personas = parseInt(inputPersonas.value) || 0;
        let total = precioBaseSeleccionado;
        if (personas > 20) {
            total += (personas - 20) * 20;
        }
        totalEstimado.innerText = `$${total}`;
    }

    btnReservarPaquete.addEventListener('click', async () => {
        const fecha = document.getElementById('paquete-fecha').value;
        const personas = parseInt(inputPersonas.value) || 20;

        if (!fecha) {
            alert('Por favor, selecciona una fecha para tu evento.');
            return;
        }

        const totalNeto = parseInt(totalEstimado.innerText.replace(/[^0-9.-]+/g, ""));

        const datosPaquete = {
            paquete_id: paqueteSeleccionadoId,
            paquete_nombre: paqueteSeleccionadoNombre,
            paquete_precio: precioBaseSeleccionado,
            paquete_desc: document.getElementById('paquete-seleccionado-desc').innerText,
            fecha: fecha,
            personas: personas
        };

        try {
            const response = await fetch(window.BASE_URL + 'citas/crearStripeSessionPaquete', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(datosPaquete)
            });

            const resultado = await response.json();

            if (resultado.success && resultado.id) {
                const stripe = Stripe(window.STRIPE_PUBLIC_KEY);
                stripe.redirectToCheckout({ sessionId: resultado.id }).then(result => {
                    if (result.error) {
                        alert("Error de Stripe: " + result.error.message);
                    }
                });
            } else {
                alert("Error: " + (resultado.error || 'No se pudo iniciar el pago del paquete.'));
            }
        } catch (error) {
            console.error("Error al procesar el paquete:", error);
            alert("Hubo un error de conexión con el servidor.");
        }
    });
});