const revisionGrid = document.getElementById('revisionGrid');
const buscador = document.getElementById('buscador');
const filtroEstado = document.getElementById('filtroEstado');
const filtroTipo = document.getElementById('filtroTipo');
const noResultsRevision = document.getElementById('noResultsRevision');
const paginacionPisos = document.querySelector('.paginacion-pisos');
const btnFiltros = document.getElementById('btnFiltros');
const menuFiltros = document.getElementById('menuFiltros');
const btnLimpiarFiltros = document.getElementById('btnLimpiarFiltros');

if (revisionGrid && buscador && filtroEstado && filtroTipo) {
    let requestActual = null;
    let timerBusqueda = null;

    function escaparHtml(valor) {
        return String(valor ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function obtenerEstadoHabitacion(items) {
        const tieneFaltantes = items.some(item => Number(item.faltantes) > 0);
        const tieneSobrantes = items.some(item => Number(item.sobrantes) > 0);

        if (tieneFaltantes) {
            return 'faltante';
        }

        if (tieneSobrantes) {
            return 'sobrante';
        }

        return 'completa';
    }

    function obtenerTextoEstadoArticulo(item) {
        const faltantes = Number(item.faltantes);
        const sobrantes = Number(item.sobrantes);

        if (faltantes > 0) {
            return `Faltan ${faltantes}`;
        }

        if (sobrantes > 0) {
            return `Sobran ${sobrantes}`;
        }

        return 'Completo';
    }

    function obtenerTextoEstadoHabitacion(estado) {
        if (estado === 'faltante') {
            return 'Con faltantes';
        }

        if (estado === 'sobrante') {
            return 'Con sobrantes';
        }

        return 'Completa';
    }

    function obtenerClaseEstado(estado) {
        if (estado === 'faltante') {
            return 'faltante';
        }

        if (estado === 'sobrante') {
            return 'sobrante';
        }

        return 'ok';
    }

    function actualizarPaginacion() {
        const hayBusqueda = buscador.value.trim() !== '';

        if (paginacionPisos) {
            paginacionPisos.classList.toggle('hidden', hayBusqueda);
        }

        document.querySelectorAll('.paginacion-pisos a').forEach(link => {
            const url = new URL(link.href, window.location.href);
            const pisoLink = Number(url.searchParams.get('piso'));
            link.classList.toggle('activo', pisoLink === Number(window.pisoActual));
        });
    }

    function actualizarUrl() {
        const params = new URLSearchParams();
        const buscar = buscador.value.trim();
        const estado = filtroEstado.value;
        const tipo = filtroTipo.value;

        params.set('modulo', 'revision');

        if (buscar === '') {
            params.set('piso', window.pisoActual || 1);
        } else {
            params.set('buscar', buscar);
        }

        if (estado !== '') {
            params.set('estado', estado);
        }

        if (tipo !== '') {
            params.set('tipo', tipo);
        }

        window.history.replaceState({}, '', `index.php?${params.toString()}`);
    }

    async function cargarRevision() {
        const params = new URLSearchParams();
        const buscar = buscador.value.trim();

        params.set('modulo', 'revision');
        params.set('accion', 'ajax');
        params.set('piso', window.pisoActual || 1);
        params.set('buscar', buscar);
        params.set('estado', filtroEstado.value);
        params.set('tipo', filtroTipo.value);

        if (requestActual) {
            requestActual.abort();
        }

        requestActual = new AbortController();
        revisionGrid.classList.add('is-loading');

        try {
            const res = await fetch(`index.php?${params.toString()}`, {
                headers: {
                    'Accept': 'application/json'
                },
                signal: requestActual.signal
            });

            if (!res.ok) {
                throw new Error('No se pudo cargar la revision.');
            }

            const habitaciones = await res.json();
            renderHabitaciones(habitaciones);
            actualizarUrl();
            actualizarPaginacion();
        } catch (error) {
            if (error.name !== 'AbortError') {
                revisionGrid.innerHTML = '';
                noResultsRevision?.classList.remove('hidden');
                console.error(error);
            }
        } finally {
            revisionGrid.classList.remove('is-loading');
        }
    }

    function cargarRevisionConEspera() {
        window.clearTimeout(timerBusqueda);
        timerBusqueda = window.setTimeout(cargarRevision, 250);
    }

    function renderHabitaciones(habitaciones) {
        revisionGrid.innerHTML = '';

        if (!Array.isArray(habitaciones) || habitaciones.length === 0) {
            noResultsRevision?.classList.remove('hidden');
            return;
        }

        noResultsRevision?.classList.add('hidden');

        habitaciones.forEach(hab => {
            const estadoHabitacion = obtenerEstadoHabitacion(hab.items || []);
            const claseEstado = obtenerClaseEstado(estadoHabitacion);
            const card = document.createElement('article');
            const inventarioUrl = `index.php?modulo=inventario&buscar=${encodeURIComponent(hab.numero)}`;

            card.className = 'habitacion-card';
            card.dataset.estado = estadoHabitacion;
            card.dataset.tipo = String(hab.tipo ?? '').toLowerCase();

            const itemsHtml = (hab.items || []).map(item => {
                const estado = obtenerTextoEstadoArticulo(item);
                const claseItem = obtenerClaseEstado(
                    Number(item.faltantes) > 0
                        ? 'faltante'
                        : Number(item.sobrantes) > 0
                            ? 'sobrante'
                            : 'completa'
                );

                return `
                    <div class="item-row">
                        <div class="item-info">
                            <strong>${escaparHtml(item.articulo)}</strong>
                            <span>Actual ${escaparHtml(item.cantidad_actual)} / Base ${escaparHtml(item.cantidad_base)}</span>
                        </div>
                        <span class="badge-${claseItem}">${escaparHtml(estado)}</span>
                    </div>
                `;
            }).join('');

            card.innerHTML = `
                <div class="habitacion-card-header">
                    <div>
                        <h2>Habitacion ${escaparHtml(hab.numero)}</h2>
                        <p>${escaparHtml(hab.tipo)} - Piso ${escaparHtml(hab.piso)}</p>
                    <a href="${inventarioUrl}" class="btn-ver-inventario">Ver inventario</a>
                    </div>
                    <span class="estado-${claseEstado}">
                        ${escaparHtml(obtenerTextoEstadoHabitacion(estadoHabitacion))}
                    </span>
                </div>
                <div class="habitacion-items">
                    ${itemsHtml}
                </div>
            `;

            revisionGrid.appendChild(card);
        });
    }

    buscador.addEventListener('input', cargarRevisionConEspera);
    filtroEstado.addEventListener('change', cargarRevision);
    filtroTipo.addEventListener('change', cargarRevision);

    document.querySelectorAll('.paginacion-pisos a').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();

            const url = new URL(this.href, window.location.href);
            window.pisoActual = Number(url.searchParams.get('piso')) || 1;

            cargarRevision();
        });
    });

    if (btnFiltros && menuFiltros) {
        btnFiltros.addEventListener('click', function(e) {
            e.stopPropagation();
            menuFiltros.classList.toggle('active');
        });

        document.addEventListener('click', function(e) {
            if (
                !menuFiltros.contains(e.target) &&
                !btnFiltros.contains(e.target)
            ) {
                menuFiltros.classList.remove('active');
            }
        });
    }

    if (btnLimpiarFiltros) {
        btnLimpiarFiltros.addEventListener('click', function() {
            buscador.value = '';
            filtroEstado.value = '';
            filtroTipo.value = '';
            menuFiltros?.classList.remove('active');
            cargarRevision();
        });
    }

    cargarRevision();
}

function exportarExcelRevision() {
    const params = new URLSearchParams();

    params.set('modulo', 'revision');
    params.set('accion', 'exportar');
    params.set('buscar', buscador?.value.trim() || '');
    params.set('estado', filtroEstado?.value || '');
    params.set('tipo', filtroTipo?.value || '');

    window.location.href = `index.php?${params.toString()}`;
}
