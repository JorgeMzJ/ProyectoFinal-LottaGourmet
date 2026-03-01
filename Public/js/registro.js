document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('registroForm');
    if (!form) return;

    const telefonoInput = form.telefono;
    if (telefonoInput) {
        let alertaPendiente = false;

        telefonoInput.addEventListener('input', (e) => {
            const original = e.target.value;
            const filtrado = original.replace(/\D+/g, '');
            const limitado = filtrado.slice(0, 15);

            if (original !== filtrado && !alertaPendiente) {
                alertaPendiente = true;
                customAlert('Solo se permiten números en el teléfono.', 'Entrada inválida');
                setTimeout(() => { alertaPendiente = false; }, 800); // Evitar spam de alertas
            }
            if (e.target.value !== limitado) {
                e.target.value = limitado;
            }
            // Marcar visualmente si hay problema (menos de 10 mientras escribe)
            if (e.target.value.length > 0 && (e.target.value.length < 10 || e.target.value.length > 15)) {
                telefonoInput.classList.add('input-error');
            } else {
                telefonoInput.classList.remove('input-error');
            }
        });

        // Bloquear teclas no numéricas (excepto control) antes de que aparezcan
        telefonoInput.addEventListener('keypress', (e) => {
            const char = e.key;
            if (!/[0-9]/.test(char)) {
                e.preventDefault();
                if (!alertaPendiente) {
                    alertaPendiente = true;
                    customAlert('Solo números (0-9).', 'Entrada inválida');
                    setTimeout(() => { alertaPendiente = false; }, 800);
                }
            }
        });
    }

    form.addEventListener('submit', (e) => {
        const password = form.password.value || '';
        const confirm = form.confirmPassword.value || '';
        const telefono = telefonoInput ? telefonoInput.value : '';

        if (password.length < 6) {
            customAlert('La contraseña debe tener al menos 6 caracteres.', 'Error de validación');
            e.preventDefault();
            return;
        }
        if (password !== confirm) {
            customAlert('Las contraseñas no coinciden.', 'Error de validación');
            e.preventDefault();
            return;
        }
        if (!/^\d{10,15}$/.test(telefono)) {
            customAlert('El teléfono debe tener entre 10 y 15 dígitos y solo números.', 'Error de validación');
            e.preventDefault();
            return;
        }
    });
});
