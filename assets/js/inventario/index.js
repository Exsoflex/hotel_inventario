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
const historialLink = document.getElementById('historialLink');
const modalHistorial = document.getElementById('modalHistorial');
const historialBody = document.getElementById('historialBody');
const historialFecha = document.getElementById('historialFecha');
const historialNota = document.getElementById('historialNota');
const btnAgregarHistorial = document.getElementById('btnAgregarHistorial');
const btnCancelarHistorial = document.getElementById('btnCancelarHistorial');
let historialInventarioId = null;
let historialEditandoId = null;
let requestActual = null;
let timerBusqueda = null;

if (inventarioModulo) {
    window.inventarioPisoActual = Number(inventarioModulo.dataset.pisoActual) || 1;
    window.inventarioPuedeGestionar = inventarioModulo.dataset.puedeGestionar === '1';
    window.inventarioRol = inventarioModulo.dataset.rol || 'operador';
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

    const historialHtml = `
        <div class="inventario-historial">
            <button
                type="button"
                class="btn-historial"
                data-inventario-id="${escaparHtml(item.id)}"
            >
                Historial
            </button>
        </div>
    `;

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
            ${historialHtml}
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
    btnConfirmarEliminar.onclick = null;
}

function exportarExcel() {
    window.location.href = crearUrlConFiltros(
        'index.php?modulo=inventario&accion=exportar',
        false
    );
}

function abrirModalHistorial(inventarioId) {
    historialInventarioId = inventarioId;
    historialEditandoId = null;
    historialFecha.value = '';
    historialNota.value = '';
    btnAgregarHistorial.textContent = 'Agregar';
    cargarHistorial();
    modalHistorial?.classList.add('active');
    document.body.style.overflow = 'hidden';
    actualizarFormularioHistorial();
}

function actualizarFormularioHistorial() {
    const formulario = document.querySelector('.historial-form');
    if (!formulario) {
        return;
    }

    if (window.inventarioRol === 'operador') {
        formulario.style.display = 'none';
    } else {
        formulario.style.display = 'block';
    }
}

function cerrarModalHistorial() {
    modalHistorial?.classList.remove('active');
    document.body.style.overflow = 'auto';
    historialEditandoId = null;
    historialFecha.value = '';
    historialNota.value = '';
    btnAgregarHistorial.textContent = 'Agregar';
}

async function cargarHistorial() {
    if (!historialBody || !historialInventarioId) {
        return;
    }

    try {
        const res = await fetch(
            `index.php?modulo=inventario&accion=historial&inventario_id=${historialInventarioId}`,
            { headers: { 'Accept': 'application/json' } }
        );

        if (!res.ok) {
            throw new Error('No se pudo cargar el historial.');
        }

        const entries = await res.json();
        renderHistorial(entries);
    } catch (error) {
        console.error(error);
    }
}

function renderHistorial(entries) {
    if (!historialBody) {
        return;
    }

    if (!Array.isArray(entries) || entries.length === 0) {
        historialBody.innerHTML = `
            <tr>
                <td colspan="3" style="text-align: center; padding: 20px;">
                    Sin registros de historial
                </td>
            </tr>
        `;
        return;
    }

    const esAdmin = window.inventarioRol === 'admin';
    const esSupervisor = window.inventarioRol === 'supervisor';
    const puedeEditar = esAdmin || esSupervisor;
    const puedeEliminar = esAdmin;
    const muestraAcciones = puedeEditar || puedeEliminar;

    const thead = document.querySelector('.historial-tabla thead');
    if (thead) {
        const thAcciones = thead.querySelector('th:last-child');
        if (thAcciones) {
            thAcciones.style.display = muestraAcciones ? '' : 'none';
        }
    }

    historialBody.innerHTML = entries.map(entry => {
        let accionesHtml = '';

        if (muestraAcciones) {
            accionesHtml = '<td>';

            if (puedeEditar) {
                accionesHtml += `
                    <button
                        type="button"
                        class="btn-editar-historial"
                        data-id="${escaparHtml(entry.id)}"
                        data-fecha="${escaparHtml(entry.fecha)}"
                        data-nota="${escaparHtml(entry.nota)}"
                    >
                        Editar
                    </button>
                `;
            }

            if (puedeEliminar) {
                accionesHtml += `
                    <button
                        type="button"
                        class="btn-eliminar-historial"
                        data-id="${escaparHtml(entry.id)}"
                    >
                        Eliminar
                    </button>
                `;
            }

            accionesHtml += '</td>';
        } else {
            accionesHtml = '<td></td>';
        }

        return `
            <tr>
                <td>${escaparHtml(entry.fecha)}</td>
                <td>${escaparHtml(entry.nota)}</td>
                ${accionesHtml}
            </tr>
        `;
    }).join('');
}

async function agregarHistorial() {
    if (!historialInventarioId) {
        return;
    }

    const fecha = historialFecha.value;
    const nota = historialNota.value.trim();

    if (!fecha || !nota) {
        return;
    }

    try {
        const res = await fetch('index.php?modulo=inventario&accion=agregarHistorial', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `inventario_id=${historialInventarioId}&fecha=${encodeURIComponent(fecha)}&nota=${encodeURIComponent(nota)}`
        });

        const data = await res.json();

        if (data.error) {
            console.error(data.error);
            return;
        }

        cargarHistorial();
        historialFecha.value = '';
        historialNota.value = '';
        btnAgregarHistorial.textContent = 'Agregar';
        historialEditandoId = null;

        const mov = new Movimientos;
        mov.registrar(
            'historial_articulos',
            'crear',
            `Agregó un registro al historial del inventario ID ${historialInventarioId}`,
            data.id
        );
    } catch (error) {
        console.error(error);
    }
}

async function editarHistorialEntry(id) {
    if (!id) {
        return;
    }

    historialEditandoId = id;
    btnAgregarHistorial.textContent = 'Guardar';
}

async function guardarHistorialEditado() {
    if (!historialEditandoId || !historialInventarioId) {
        return;
    }

    const fecha = historialFecha.value;
    const nota = historialNota.value.trim();

    if (!fecha || !nota) {
        return;
    }

    try {
        const res = await fetch('index.php?modulo=inventario&accion=editarHistorial', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${historialEditandoId}&fecha=${encodeURIComponent(fecha)}&nota=${encodeURIComponent(nota)}`
        });

        const data = await res.json();

        if (data.error) {
            console.error(data.error);
            return;
        }

        cargarHistorial();
        historialFecha.value = '';
        historialNota.value = '';
        btnAgregarHistorial.textContent = 'Agregar';
        historialEditandoId = null;

        const mov = new Movimientos;
        mov.registrar(
            'historial_articulos',
            'editar',
            `Editó el registro de historial ID ${historialEditandoId}`,
            historialEditandoId
        );
    } catch (error) {
        console.error(error);
    }
}

async function eliminarHistorialEntry(id) {
    if (!id) {
        return;
    }

    if (!modalEliminar || !mensajeEliminar || !btnConfirmarEliminar) {
        return;
    }

    mensajeEliminar.textContent =
        `¿Seguro que deseas eliminar este registro de historial?`;

    modalEliminar.classList.add('active');

    btnConfirmarEliminar.onclick = async function(e) {
        e.preventDefault();
        modalEliminar.classList.remove('active');
        btnConfirmarEliminar.onclick = null;

        try {
            const res = await fetch('index.php?modulo=inventario&accion=eliminarHistorial', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${id}`
            });

            const data = await res.json();

            if (data.error) {
                console.error(data.error);
                return;
            }

            cargarHistorial();
        } catch (error) {
            console.error(error);
        }
    };
}

window.abrirModal = abrirModal;
window.cerrarModal = cerrarModal;
window.cerrarModalEliminar = cerrarModalEliminar;
window.exportarExcel = exportarExcel;
window.abrirModalHistorial = abrirModalHistorial;
window.cerrarModalHistorial = cerrarModalHistorial;
window.agregarHistorial = agregarHistorial;
window.editarHistorialEntry = editarHistorialEntry;
window.guardarHistorialEditado = guardarHistorialEditado;
window.eliminarHistorialEntry = eliminarHistorialEntry;

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

    if (historialLink && modalHistorial) {
        historialLink.addEventListener('click', function(e) {
            e.preventDefault();
            const inventarioId = parseInt(historialLink.dataset.inventarioId);
            if (inventarioId > 0) {
                abrirModalHistorial(inventarioId);
            }
        });
    }

    if (inventarioContenedor) {
        inventarioContenedor.addEventListener('click', function(e) {
            const btnHistorial = e.target.closest('.btn-historial');
            if (!btnHistorial) {
                return;
            }
            e.preventDefault();
            const inventarioId = parseInt(btnHistorial.dataset.inventarioId);
            if (inventarioId > 0) {
                abrirModalHistorial(inventarioId);
            }
        });
    }

    if (btnAgregarHistorial) {
        btnAgregarHistorial.addEventListener('click', function() {
            if (historialEditandoId) {
                guardarHistorialEditado();
            } else {
                agregarHistorial();
            }
        });
    }

    if (btnCancelarHistorial) {
        btnCancelarHistorial.addEventListener('click', cerrarModalHistorial);
    }

    if (historialBody) {
        historialBody.addEventListener('click', function(e) {
            const btnEditar = e.target.closest('.btn-editar-historial');
            const btnEliminar = e.target.closest('.btn-eliminar-historial');

            if (btnEditar) {
                const id = parseInt(btnEditar.dataset.id);
                const fecha = btnEditar.dataset.fecha;
                const nota = btnEditar.dataset.nota;

                historialEditandoId = id;
                historialFecha.value = fecha;
                historialNota.value = nota;
                btnAgregarHistorial.textContent = 'Guardar';
                return;
            }

            if (btnEliminar) {
                const id = parseInt(btnEliminar.dataset.id);
                eliminarHistorialEntry(id);
                return;
            }
        });
    }

    if (window.inventarioAbrirModalInicial) {
        abrirModal();
    }

    sincronizarFiltros();
    cargarInventario();
    actualizarVisibilidadBotonLimpiar();
}