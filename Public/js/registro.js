document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('registroForm');
    if (!form) return;

    const telefonoInput = form.telefono;
    if (telefonoInput) {
        let alertaPendiente = false;

        telefonoInput.addEventListener('input', (e) => {
            const original = e.target.value;
            // Permitir solo dígitos y un signo '+' al inicio
            let filtrado = original.replace(/[^\d+]/g, '');
            filtrado = filtrado.replace(/(?!^)\+/g, '');
            const limitado = filtrado.slice(0, 16);

            if (original !== original.replace(/(?!^\+)[^\d]/g, '') && !alertaPendiente) {
                alertaPendiente = true;
                customAlert('Solo se permite el signo + al inicio y números en el teléfono.', 'Entrada inválida');
                setTimeout(() => { alertaPendiente = false; }, 800); // Evitar spam de alertas
            }
            if (e.target.value !== limitado) {
                e.target.value = limitado;
            }
            // Marcar visualmente si hay problema (menos de 10 mientras escribe)
            const lengthSinPlus = limitado.startsWith('+') ? limitado.length - 1 : limitado.length;
            if (e.target.value.length > 0 && (lengthSinPlus < 10 || lengthSinPlus > 15)) {
                telefonoInput.classList.add('input-error');
            } else {
                telefonoInput.classList.remove('input-error');
            }
        });

        // Bloquear teclas no numéricas (excepto control y +) antes de que aparezcan
        telefonoInput.addEventListener('keypress', (e) => {
            const char = e.key;
            if (!/^[0-9+]$/.test(char)) {
                e.preventDefault();
                if (!alertaPendiente) {
                    alertaPendiente = true;
                    customAlert('Solo números (0-9) y signo +.', 'Entrada inválida');
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
        if (!/^\+?\d{10,15}$/.test(telefono)) {
            customAlert('El teléfono debe tener entre 10 y 15 dígitos (opcionalmente puede iniciar con +).', 'Error de validación');
            e.preventDefault();
            return;
        }
    });
});
