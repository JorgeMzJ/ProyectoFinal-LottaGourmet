// Sistema de modales personalizados para reemplazar alert() y confirm()
function initModalContainer() {
    if (document.getElementById('customModalOverlay')) return;
    
    const overlay = document.createElement('div');
    overlay.id = 'customModalOverlay';
    overlay.className = 'custom-modal-overlay';
    
    const modal = document.createElement('div');
    modal.id = 'customModal';
    modal.className = 'custom-modal';
    modal.innerHTML = `
        <div class="custom-modal-header">
            <h3 id="customModalTitle"></h3>
        </div>
        <div class="custom-modal-body">
            <p id="customModalMessage">No hay paquete seleccionado</p>
        </div>
        <div class="custom-modal-footer" id="customModalFooter">
            <!-- Botones se agregan dinámicamente -->
        </div>
    `;
    
    overlay.appendChild(modal);
    document.body.appendChild(overlay);
}

// Alert personalizado
window.customAlert = function(message, title = 'Aviso') {
    initModalContainer();
    
    const overlay = document.getElementById('customModalOverlay');
    const titleEl = document.getElementById('customModalTitle');
    const messageEl = document.getElementById('customModalMessage');
    const footer = document.getElementById('customModalFooter');
    
    titleEl.textContent = title;
    messageEl.innerHTML = message.replace(/\n/g, '<br>');
    
    footer.innerHTML = `
        <button class="custom-modal-btn custom-modal-btn-primary" onclick="closeCustomModal()">
            Aceptar
        </button>
    `;
    
    overlay.style.display = 'flex';
    
    // Focus en el botón
    setTimeout(() => {
        footer.querySelector('button').focus();
    }, 100);
};

// Confirm personalizado (retorna Promise)
window.customConfirm = function(message, title = 'Confirmar') {
    return new Promise((resolve) => {
        initModalContainer();
        
        const overlay = document.getElementById('customModalOverlay');
        const titleEl = document.getElementById('customModalTitle');
        const messageEl = document.getElementById('customModalMessage');
        const footer = document.getElementById('customModalFooter');
        
        titleEl.textContent = title;
        messageEl.innerHTML = message.replace(/\n/g, '<br>');
        
        footer.innerHTML = `
            <button class="custom-modal-btn custom-modal-btn-secondary" id="modalCancelBtn">
                Cancelar
            </button>
            <button class="custom-modal-btn custom-modal-btn-primary" id="modalConfirmBtn">
                Confirmar
            </button>
        `;
        
        overlay.style.display = 'flex';
        
        const confirmBtn = document.getElementById('modalConfirmBtn');
        const cancelBtn = document.getElementById('modalCancelBtn');
        
        confirmBtn.onclick = () => {
            closeCustomModal();
            resolve(true);
        };
        
        cancelBtn.onclick = () => {
            closeCustomModal();
            resolve(false);
        };
        
        // Focus en confirmar
        setTimeout(() => confirmBtn.focus(), 100);
        
        // ESC para cancelar
        const escHandler = (e) => {
            if (e.key === 'Escape') {
                closeCustomModal();
                resolve(false);
                document.removeEventListener('keydown', escHandler);
            }
        };
        document.addEventListener('keydown', escHandler);
    });
};

// Cerrar modal
window.closeCustomModal = function() {
    const overlay = document.getElementById('customModalOverlay');
    if (overlay) {
        overlay.style.display = 'none';
    }
};

// Cerrar al hacer clic fuera del modal
document.addEventListener('click', function(e) {
    const overlay = document.getElementById('customModalOverlay');
    if (overlay && e.target === overlay) {
        closeCustomModal();
    }
});

// Inicializar al cargar la página
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initModalContainer);
} else {
    initModalContainer();
}
