const inventarioModulo = document.getElementById('inventarioModulo');
const inventarioContenedor = document.getElementById('inventarioContenedor');
const buscador = document.getElementById('buscador');
const filtroEstado = document.getElementById('filtroEstado');
const filtrosArticulo = document.querySelectorAll('.filtro-articulo');
const formBuscar = document.getElementById('form_buscar');
const formEstado = document.getElementById('form_estado_filtro');
const formArticulos = document.getElementById('form_articulos_filtro');
const formPiso = document.getElementById('form_piso');
const noResultsInventario = document.getElementById('noResultsInventario');
const paginacionPisos = document.querySelector('.paginacion-pisos');
const btnFiltros = document.getElementById('btnFiltros');
const menuFiltros = document.getElementById('menuFiltros');
const btnArticulos = document.getElementById('btnArticulos');
const listaArticulos = document.getElementById('listaArticulos');
const btnTodos = document.getElementById('seleccionarTodos');
const btnLimpiar = document.getElementById('limpiarArticulos');
const btnLimpiarBusqueda = document.getElementById('btnLimpiarBusqueda');
const modalEliminar = document.getElementById('modalEliminar');
const mensajeEliminar = document.getElementById('mensajeEliminar');
const btnConfirmarEliminar = document.getElementById('btnConfirmarEliminar');
const selectArticulo = document.querySelector('select[name="articulo_id"]');
const contenedorCodigo = document.getElementById('contenedorCodigo');

let requestActual = null;
let timerBusqueda = null;

if (inventarioModulo) {
    window.inventarioPisoActual = Number(inventarioModulo.dataset.pisoActual) || 1;
    window.inventarioPuedeGestionar = inventarioModulo.dataset.puedeGestionar === '1';
    window.inventarioAbrirModalInicial = inventarioModulo.dataset.abrirModalInicial === '1';
    window.inventarioEditando = inventarioModulo.dataset.editando === '1';
}

function escaparHtml(valor) {
    return String(valor ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function obtenerFiltrosActuales() {
    const articulosSeleccionados = Array.from(filtrosArticulo)
        .filter(check => check.checked)
        .map(check => check.value);

    return {
        buscar: buscador?.value.trim() || '',
        estado: filtroEstado?.value || '',
        articulos: articulosSeleccionados.join(','),
        piso: window.inventarioPisoActual || 1
    };
}

function crearUrlConFiltros(baseUrl, incluirPiso = true) {
    const filtros = obtenerFiltrosActuales();
    const url = new URL(baseUrl, window.location.href);

    ['buscar', 'estado', 'articulos'].forEach(nombre => {
        if (filtros[nombre]) {
            url.searchParams.set(nombre, filtros[nombre]);
        } else {
            url.searchParams.delete(nombre);
        }
    });

    if (incluirPiso && filtros.buscar === '') {
        url.searchParams.set('piso', filtros.piso);
    } else {
        url.searchParams.delete('piso');
    }

    return `${url.pathname}?${url.searchParams.toString()}`;
}

function sincronizarFiltros() {
    const filtros = obtenerFiltrosActuales();

    if (formBuscar) {
        formBuscar.value = filtros.buscar;
    }

    if (formEstado) {
        formEstado.value = filtros.estado;
    }

    if (formArticulos) {
        formArticulos.value = filtros.articulos;
    }

    if (formPiso) {
        formPiso.value = filtros.piso;
    }
}

function actualizarPaginacion() {
    const hayBusqueda = obtenerFiltrosActuales().buscar !== '';

    if (paginacionPisos) {
        paginacionPisos.classList.toggle('hidden', hayBusqueda);
    }

    document.querySelectorAll('.paginacion-pisos a').forEach(link => {
        const url = new URL(link.href, window.location.href);
        const pisoLink = Number(url.searchParams.get('piso'));
        link.classList.toggle('activo', pisoLink === Number(window.inventarioPisoActual));
    });
}

function actualizarVisibilidadBotonLimpiar() {
    if (btnLimpiarBusqueda) {
        btnLimpiarBusqueda.classList.toggle('hidden', !buscador?.value);
    }
}

function actualizarUrl() {
    window.history.replaceState(
        {},
        '',
        crearUrlConFiltros('index.php?modulo=inventario')
    );
}

async function cargarInventario() {
    if (!inventarioContenedor) {
        return;
    }

    const filtros = obtenerFiltrosActuales();
    const params = new URLSearchParams();

    params.set('modulo', 'inventario');
    params.set('accion', 'ajax');
    params.set('piso', filtros.piso);
    params.set('buscar', filtros.buscar);
    params.set('estado', filtros.estado);
    params.set('articulos', filtros.articulos);

    if (requestActual) {
        requestActual.abort();
    }

    requestActual = new AbortController();
    inventarioContenedor.classList.add('is-loading');

    try {
        const res = await fetch(`index.php?${params.toString()}`, {
            headers: {
                'Accept': 'application/json'
            },
            signal: requestActual.signal
        });

        if (!res.ok) {
            throw new Error('No se pudo cargar el inventario.');
        }

        const habitaciones = await res.json();

        renderInventario(habitaciones);
        sincronizarFiltros();
        actualizarPaginacion();
        actualizarUrl();
    } catch (error) {
        if (error.name !== 'AbortError') {
            inventarioContenedor.innerHTML = '';
            noResultsInventario?.classList.remove('hidden');
            console.error(error);
        }
    } finally {
        inventarioContenedor.classList.remove('is-loading');
    }
}

function cargarInventarioConEspera() {
    window.clearTimeout(timerBusqueda);
    timerBusqueda = window.setTimeout(cargarInventario, 250);
}

function renderInventario(habitaciones) {
    inventarioContenedor.innerHTML = '';

    if (!Array.isArray(habitaciones) || habitaciones.length === 0) {
        noResultsInventario?.classList.remove('hidden');
        return;
    }

    noResultsInventario?.classList.add('hidden');

    habitaciones.forEach(habitacion => {
        const section = document.createElement('section');
        const revisionUrl = `index.php?modulo=revision&buscar=${encodeURIComponent(habitacion.numero)}`;
        const itemsHtml = (habitacion.items || []).map(renderCardInventario).join('');

        section.className = 'habitacion-section';
        section.innerHTML = `
            <div class="habitacion-section-header">
                <h2>Habitacion ${escaparHtml(habitacion.numero)}</h2>
                <a href="${revisionUrl}" class="btn-ver-revision">Ver revision</a> 
            </div>
            <div class="inventario-grid">
                ${itemsHtml}
            </div>
        `;

        inventarioContenedor.appendChild(section);
    });
}

function renderCardInventario(item) {
    const codigoHtml = Number(item.usa_codigo_barras) === 1
        ? `
            <p>
                <strong>Codigo:</strong>
                ${escaparHtml(item.codigo_barras || 'Sin asignar')}
            </p>
        `
        : '';

    const accionesHtml = window.inventarioPuedeGestionar
        ? `
            <div class="inventario-actions">
                <a
                    class="btn-editar"
                    data-base-url="index.php?modulo=inventario&accion=editar&id=${encodeURIComponent(item.id)}"
                    href="${crearUrlConFiltros(`index.php?modulo=inventario&accion=editar&id=${encodeURIComponent(item.id)}`)}"
                >
                    Editar
                </a>
                <a
                    href="#"
                    class="btn-eliminar"
                    data-base-url="index.php?modulo=inventario&accion=eliminar&id=${encodeURIComponent(item.id)}"
                    data-inventario="${escaparHtml(item.nombre)}"
                >
                    Eliminar
                </a>
            </div>
        `
        : '';

    return `
        <div
            class="inventario-card"
            id="inventario-${escaparHtml(item.id)}"
            data-estado="${escaparHtml(item.estado)}"
            data-articulo="${escaparHtml(String(item.nombre ?? '').toLowerCase())}"
        >
            <div class="inventario-card-header">
                <div>
                    <h3>${escaparHtml(item.nombre)}</h3>
                </div>
                <div class="estado-badge estado-${escaparHtml(item.estado)}">
                    ${escaparHtml(String(item.estado ?? '').replace('_', ' '))}
                </div>
            </div>
            <div class="inventario-info">
                ${codigoHtml}
                <p>
                    <strong>Cantidad:</strong>
                    ${escaparHtml(item.cantidad)}
                </p>
                <p>
                    <strong>Comentarios:</strong>
                    ${escaparHtml(item.comentarios || 'Sin comentarios')}
                </p>
            </div>
            ${accionesHtml}
        </div>
    `;
}

function abrirModal() {
    document.getElementById('modalInventario')?.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function cerrarModal() {
    if (window.inventarioEditando) {
        window.location.href = crearUrlConFiltros('index.php?modulo=inventario');
        return;
    }

    document.getElementById('modalInventario')?.classList.remove('active');
    document.body.style.overflow = 'auto';
}

function cerrarModalEliminar() {
    modalEliminar?.classList.remove('active');
}

function exportarExcel() {
    window.location.href = crearUrlConFiltros(
        'index.php?modulo=inventario&accion=exportar'
    );
}

window.abrirModal = abrirModal;
window.cerrarModal = cerrarModal;
window.cerrarModalEliminar = cerrarModalEliminar;
window.exportarExcel = exportarExcel;

if (buscador && filtroEstado && inventarioContenedor) {
    buscador.addEventListener('input', function() {
        cargarInventarioConEspera();
        actualizarVisibilidadBotonLimpiar();
    });
    filtroEstado.addEventListener('change', cargarInventario);

    filtrosArticulo.forEach(check => {
        check.addEventListener('change', cargarInventario);
    });

    document.querySelectorAll('.paginacion-pisos a').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();

            const url = new URL(this.href, window.location.href);
            window.inventarioPisoActual = Number(url.searchParams.get('piso')) || 1;

            cargarInventario();
        });
    });

    if (btnFiltros && menuFiltros) {
        btnFiltros.addEventListener('click', function(e) {
            e.stopPropagation();
            menuFiltros.classList.toggle('active');
        });

        document.addEventListener('click', function(e) {
            if (!menuFiltros.contains(e.target) && !btnFiltros.contains(e.target)) {
                menuFiltros.classList.remove('active');
            }
        });
    }

    if (btnArticulos && listaArticulos) {
        btnArticulos.addEventListener('click', function(e) {
            e.stopPropagation();
            listaArticulos.classList.toggle('active');
        });
    }

    if (btnTodos) {
        btnTodos.addEventListener('click', function() {
            filtrosArticulo.forEach(check => {
                check.checked = true;
            });

            cargarInventario();
        });
    }

    if (btnLimpiar) {
        btnLimpiar.addEventListener('click', function() {
            filtrosArticulo.forEach(check => {
                check.checked = false;
            });

            if (filtroEstado) {
                filtroEstado.value = '';
            }

            cargarInventario();
        });
    }

    if (btnLimpiarBusqueda) {
        btnLimpiarBusqueda.addEventListener('click', function() {
            buscador.value = '';
            filtrosArticulo.forEach(check => {
                check.checked = false;
            });
            if (filtroEstado) {
                filtroEstado.value = '';
            }
            menuFiltros?.classList.remove('active');
            cargarInventario();
        });
    }

    if (inventarioContenedor && modalEliminar && mensajeEliminar && btnConfirmarEliminar) {
        inventarioContenedor.addEventListener('click', function(e) {
            const boton = e.target.closest('.btn-eliminar');

            if (!boton) {
                return;
            }

            e.preventDefault();

            mensajeEliminar.textContent =
                `Seguro que deseas eliminar el articulo "${boton.dataset.inventario}" del inventario?`;

            btnConfirmarEliminar.href = crearUrlConFiltros(boton.dataset.baseUrl);
            modalEliminar.classList.add('active');
        });
    }

    if (selectArticulo && contenedorCodigo) {
        const actualizarCampoCodigo = function() {
            const opcionSeleccionada = selectArticulo.options[selectArticulo.selectedIndex];
            contenedorCodigo.style.display = opcionSeleccionada?.dataset.codigo === '1'
                ? 'block'
                : 'none';
        };

        selectArticulo.addEventListener('change', actualizarCampoCodigo);
        actualizarCampoCodigo();
    }

    if (window.inventarioAbrirModalInicial) {
        abrirModal();
    }

    sincronizarFiltros();
    cargarInventario();
    actualizarVisibilidadBotonLimpiar();
}
