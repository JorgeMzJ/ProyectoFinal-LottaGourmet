(function(){
    const input = document.querySelector('.menu-search input[name="q"]');
    if (!input) return;

    let timeout = null;
    const delay = 350;

    input.addEventListener('input', function(e){
        clearTimeout(timeout);
        timeout = setTimeout(() => doSearch(input.value.trim()), delay);
    });

    function doSearch(q) {
        const url = new URL(window.location.href);
        url.searchParams.set('q', q);

        // Update the URL in the address bar without reloading
        window.history.replaceState({}, '', url);

        fetch(url.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(resp => resp.json())
        .then(data => renderResults(data))
        .catch(err => console.error('Search error', err));
    }

    function renderResults(items) {
        const container = document.querySelector('.menu-contenedor');
        if (!container) return;

        if (!items || items.length === 0) {
            container.innerHTML = '<p class="no-productos">No se encontraron productos.</p>';
            return;
        }

        let html = '';
        items.forEach(item => {
            html += `
                <div class="producto-tarjeta">
                    <img src="${escapeHtml(window.BASE_URL || '')}Public/img/${escapeHtml(item.imagen || 'placeholder.jpg')}" alt="${escapeHtml(item.nombre)}">
                    <div class="producto-info">
                        <h3>${escapeHtml(item.nombre)}</h3>
                        <p>${escapeHtml(item.descripcion)}</p>
                    </div>
                    <div class="producto-footer">
                        <span class="producto-precio">$${Number(item.precio).toFixed(2)}</span>
                        <a href="#" class="btn-pedir">Pedir</a>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
    }

    function escapeHtml(text) {
        if (!text) return '';
        return String(text).replace(/[&<>\"']/g, function(m) { return {'&':'&amp;','<':'&lt;','>':'&gt;','\"':'&quot;',"'":'&#039;'}[m]; });
    }
})();
