/**
 * inventario.js — Catálogo dinámico con filtros AJAX.
 * Pide productos al API y pinta tarjetas; sin JS inline en PHP/HTML.
 */
(function () {
    'use strict';

    var modal = document.getElementById('modal-solicitar');
    function cerrarModal() {
        if (modal) {
            modal.setAttribute('hidden', '');
        }
    }
    function abrirModalSoloCuandoUsuario() {
        if (modal) modal.removeAttribute('hidden');
    }
    if (modal) modal.setAttribute('hidden', '');

    var contenedor = document.getElementById('contenedor-tarjetas');
    var estadoCarga = document.getElementById('estado-carga');
    var filtros = document.getElementById('filtros-categoria');
    var busquedaInput = document.getElementById('busqueda-inventario');
    var ultimaListaProductos = [];

    /**
     * Obtiene la URL del API de productos (opcional: categoría).
     */
    function urlProductos(categoria) {
        var url = 'index.php?accion=api&recurso=productos';
        if (categoria) {
            url += '&categoria=' + encodeURIComponent(categoria);
        }
        return url;
    }

    /**
     * Pinta una tarjeta de producto. Incluye stock disponible y botón Solicitar si hay unidades.
     */
    function renderizarTarjeta(producto) {
        var nombre = producto.nombre || 'Sin nombre';
        var descripcion = producto.descripcion || '';
        var stock = typeof producto.stock === 'number' ? producto.stock : (producto.stock || 0);
        var disponible = typeof producto.stock_disponible === 'number' ? producto.stock_disponible : stock;
        var umbral = typeof producto.umbral_critico === 'number' ? producto.umbral_critico : (producto.umbral_critico || 0);
        var imagen = producto.imagen || '';
        var id = producto.id || '';

        var sinStock = disponible === 0;
        // Solo se considera "stock bajo" (naranja) si queda algo disponible (>0).
        // Si el disponible es 0, será "Agotado" (rojo) con su propio borde.
        var esBajo = !sinStock && umbral > 0 && disponible <= umbral;
        var claseStock = '';
        if (sinStock) {
            claseStock = 'tarjeta-agotado';
        } else if (esBajo) {
            claseStock = 'tarjeta-stock-bajo';
        }

        var tarjeta = document.createElement('article');
        tarjeta.className = 'tarjeta-producto ' + claseStock;
        tarjeta.setAttribute('role', 'listitem');
        tarjeta.setAttribute('data-id', String(id));

        var html = '';
        if (imagen) {
            html += '<div class="tarjeta-imagen"><img src="' + escapeHtml(imagen) + '" alt="" loading="lazy"></div>';
        } else {
            html += '<div class="tarjeta-imagen tarjeta-imagen-placeholder" aria-hidden="true"></div>';
        }
        html += '<div class="tarjeta-cuerpo">';
        html += '<h2 class="tarjeta-titulo">' + escapeHtml(nombre) + '</h2>';
        if (descripcion) {
            html += '<p class="tarjeta-descripcion">' + escapeHtml(descripcion) + '</p>';
        }
        html += '<p class="tarjeta-stock">Disponible: <strong>' + disponible + '</strong>';
        if (esBajo) {
            html += ' <span class="tarjeta-alerta">Stock bajo</span>';
        }
        if (sinStock) {
            html += ' <span class="texto-agotado">Agotado</span>';
        }
        html += '</p>';
        if (esBajo) {
            var porcentaje = 0;
            if (umbral > 0) {
                porcentaje = Math.round((stock / umbral) * 100);
                if (porcentaje < 0) porcentaje = 0;
                if (porcentaje > 100) porcentaje = 100;
            }
            html += '<div class="barra-stock-critico" aria-hidden="true"><div class="barra-stock-critico-inner" style="width:' + porcentaje + '%;"></div></div>';
        }
        if (disponible > 0) {
            html += '<div class="tarjeta-acciones"><button type="button" class="boton boton-primario boton-solicitar" data-id="' + escapeHtml(String(id)) + '" data-nombre="' + escapeHtml(nombre) + '" data-disponible="' + disponible + '">Solicitar</button></div>';
        }
        html += '</div>';
        tarjeta.innerHTML = html;
        return tarjeta;
    }

    function escapeHtml(texto) {
        if (!texto) return '';
        var div = document.createElement('div');
        div.textContent = texto;
        return div.innerHTML;
    }

    /**
     * Muestra mensaje en el contenedor. Si es "cargando", muestra spinner con isotipo.
     */
    function mostrarEstado(mensaje, esError) {
        estadoCarga.className = 'estado-carga' + (esError ? ' estado-carga-error' : '');
        estadoCarga.hidden = false;
        if (!esError && mensaje && mensaje.indexOf('Cargando') !== -1) {
            var isotipo = document.body.getAttribute('data-isotipo') || '';
            estadoCarga.innerHTML = '<span class="spinner-isotipo-wrap" aria-hidden="true"><img src="' + escapeHtml(isotipo) + '" alt="" class="spinner-isotipo"></span><span class="estado-carga-texto">' + escapeHtml(mensaje) + '</span>';
        } else {
            estadoCarga.textContent = mensaje;
        }
    }

    function renderizarSkeletons(cantidad) {
        var fragment = document.createDocumentFragment();
        for (var i = 0; i < cantidad; i++) {
            var card = document.createElement('article');
            card.className = 'tarjeta-skeleton';
            card.setAttribute('role', 'listitem');
            card.innerHTML = '<div class="skeleton skeleton-imagen"></div><div class="skeleton-cuerpo"><div class="skeleton skeleton-linea"></div><div class="skeleton skeleton-linea skeleton-linea-corta"></div><div class="skeleton skeleton-linea" style="width:40%"></div></div>';
            fragment.appendChild(card);
        }
        return fragment;
    }

    /**
     * Carga productos y rellena el contenedor de tarjetas.
     */
    function cargarProductos(categoria) {
        estadoCarga.hidden = true;
        while (contenedor.lastChild && contenedor.lastChild !== estadoCarga) {
            contenedor.removeChild(contenedor.lastChild);
        }
        contenedor.appendChild(renderizarSkeletons(6));

        fetch(urlProductos(categoria), { credentials: 'same-origin' })
            .then(function (respuesta) {
                if (!respuesta.ok) throw new Error('Error al cargar');
                return respuesta.json();
            })
            .then(function (datos) {
                var lista = datos.productos || [];
                ultimaListaProductos = lista;
                aplicarBusquedaYRenderizar();
                rellenarSelectProductos();
            })
            .catch(function () {
                while (contenedor.lastChild && contenedor.lastChild !== estadoCarga) {
                    contenedor.removeChild(contenedor.lastChild);
                }
                mostrarEstado('No se pudo cargar el catálogo. Revisa la conexión.', true);
                if (window.Swal) window.Swal.fire(opcionesSwal({ icon: 'error', title: 'Error de conexión', text: 'No se pudo conectar con el servidor. Comprueba tu conexión e intenta de nuevo.' }));
            });
    }

    function aplicarBusquedaYRenderizar() {
        var texto = (busquedaInput && busquedaInput.value) ? busquedaInput.value.trim().toLowerCase() : '';
        var lista = texto
            ? ultimaListaProductos.filter(function (p) {
                return (p.nombre || '').toLowerCase().indexOf(texto) !== -1 ||
                    (p.descripcion || '').toLowerCase().indexOf(texto) !== -1;
            })
            : ultimaListaProductos;
        while (contenedor.lastChild && contenedor.lastChild !== estadoCarga) {
            contenedor.removeChild(contenedor.lastChild);
        }
        if (lista.length === 0) {
            estadoCarga.hidden = false;
            estadoCarga.textContent = texto ? 'No hay productos que coincidan con «' + (busquedaInput.value || '') + '».' : 'No hay productos en esta categoría.';
            estadoCarga.className = 'estado-carga';
        } else {
            estadoCarga.hidden = true;
            lista.forEach(function (p) {
                contenedor.appendChild(renderizarTarjeta(p));
            });
            enlazarBotonesSolicitar();
        }
    }

    if (contenedor && filtros && estadoCarga) {
        if (busquedaInput) {
            busquedaInput.addEventListener('input', aplicarBusquedaYRenderizar);
            busquedaInput.addEventListener('search', aplicarBusquedaYRenderizar);
        }

        /**
         * Activa el botón de filtro pulsado y desactiva el resto.
         */
        function marcarFiltroActivo(botonActivo) {
            var botones = filtros.querySelectorAll('.boton-filtro');
            botones.forEach(function (botonFiltro) {
                botonFiltro.classList.toggle('boton-filtro-activo', botonFiltro === botonActivo);
            });
        }

        filtros.addEventListener('click', function (evento) {
            var boton = evento.target && evento.target.closest('.boton-filtro');
            if (!boton) return;
            evento.preventDefault();
            var cat = (boton.getAttribute('data-categoria') || '').trim();
            marcarFiltroActivo(boton);
            cargarProductos(cat);
        });
    }

    if (!modal) modal = document.getElementById('modal-solicitar');
    var selectProducto = document.getElementById('solicitar-producto');
    var productoIdInput = document.getElementById('solicitar-producto-id');
    var unidadesInput = document.getElementById('solicitar-unidades');

    function getIsotipoUrl() {
        return document.body && document.body.getAttribute('data-isotipo') ? document.body.getAttribute('data-isotipo') : '';
    }

    function opcionesSwal(parametros) {
        var opciones = { title: parametros.title || '', text: parametros.text || '' };
        var icon = parametros.icon || '';
        if (icon === 'error' || icon === 'warning') {
            opciones.icon = icon;
        }
        var iso = getIsotipoUrl();
        if (iso) {
            opciones.imageUrl = iso;
        }
        opciones.confirmButtonColor = '#00A3FF';
        return opciones;
    }

    function rellenarSelectProductos() {
        if (!selectProducto) return;
        var opts = []; var i; var p; var disp;
        for (i = 0; i < ultimaListaProductos.length; i++) {
            p = ultimaListaProductos[i];
            disp = typeof p.stock_disponible === 'number' ? p.stock_disponible : (p.stock || 0);
            if (disp > 0) opts.push({ id: p.id, nombre: p.nombre || 'Sin nombre', disponible: disp });
        }
        selectProducto.innerHTML = '<option value="">— Elige producto —</option>';
        opts.forEach(function (o) {
            var opt = document.createElement('option');
            opt.value = o.id;
            opt.setAttribute('data-max', String(o.disponible));
            opt.textContent = o.nombre + ' (disp. ' + o.disponible + ')';
            selectProducto.appendChild(opt);
        });
    }

    function abrirModalConProducto(id, disponible) {
        if (!modal) return;
        rellenarSelectProductos();
        if (selectProducto && id) selectProducto.value = String(id);
        if (productoIdInput) productoIdInput.value = selectProducto ? selectProducto.value : '';
        var max = disponible;
        if (!max && selectProducto) {
            var opt = selectProducto.options[selectProducto.selectedIndex];
            max = opt && opt.getAttribute('data-max') ? parseInt(opt.getAttribute('data-max'), 10) : 9999;
        }
        if (unidadesInput) {
            unidadesInput.setAttribute('max', String(max || 9999));
            unidadesInput.value = Math.min(1, max || 9999);
        }
        abrirModalSoloCuandoUsuario();
        if (unidadesInput) unidadesInput.focus();
    }

    function enlazarBotonesSolicitar() {
        if (!contenedor) return;
        contenedor.querySelectorAll('.boton-solicitar').forEach(function (boton) {
            boton.addEventListener('click', function () {
                var id = this.getAttribute('data-id');
                var disponible = parseInt(this.getAttribute('data-disponible') || '1', 10);
                if (!id) return;
                abrirModalConProducto(id, disponible);
            });
        });
    }

    if (selectProducto) {
        selectProducto.addEventListener('change', function () {
            var opt = this.options[this.selectedIndex];
            if (productoIdInput) productoIdInput.value = this.value || '';
            var max = opt && opt.getAttribute('data-max') ? parseInt(opt.getAttribute('data-max'), 10) : 9999;
            if (unidadesInput) {
                unidadesInput.setAttribute('max', String(max));
                if (parseInt(unidadesInput.value, 10) > max) unidadesInput.value = max;
            }
        });
    }

    var btnCerrar = document.getElementById('solicitar-cerrar');
    if (btnCerrar) {
        btnCerrar.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            cerrarModal();
        });
    }

    document.addEventListener('click', function (evento) {
        var target = evento.target;
        if (target.id === 'solicitar-cerrar' || (target.closest && target.closest('#solicitar-cerrar'))) {
            evento.preventDefault();
            evento.stopPropagation();
            cerrarModal();
            return;
        }
        if (target.id === 'modal-solicitar') {
            evento.preventDefault();
            cerrarModal();
        }
    }, true);

    /**
     * Envía la solicitud de material (delegación en document para asegurar que el submit se capture).
     */
    function enviarSolicitudModal(evento) {
        var form = evento.target && evento.target.id === 'form-solicitar' ? evento.target : null;
        if (!form) return;
        evento.preventDefault();
        evento.stopPropagation();
        var idInput = document.getElementById('solicitar-producto-id');
        var inputUnidades = document.getElementById('solicitar-unidades');
        var selPrioridad = document.getElementById('solicitar-prioridad');
        var areaMotivo = document.getElementById('solicitar-motivo');
        var idVal = (idInput && idInput.value) ? idInput.value.trim() : '';
        if (!idVal) {
            idVal = (selectProducto && selectProducto.value) ? selectProducto.value.trim() : '';
            if (idInput) idInput.value = idVal;
        }
        if (!idVal) {
            if (window.Swal) window.Swal.fire(opcionesSwal({ icon: 'warning', title: 'Elige un producto', text: 'Selecciona un producto en el desplegable.' }));
            return;
        }
        var maxUnidades = parseInt(inputUnidades && inputUnidades.getAttribute('max') ? inputUnidades.getAttribute('max') : '9999', 10) || 9999;
        var unidades = parseInt(inputUnidades && inputUnidades.value ? inputUnidades.value : '1', 10) || 1;
        if (unidades < 1 || unidades > maxUnidades) {
            if (window.Swal) window.Swal.fire(opcionesSwal({ icon: 'error', title: 'Unidades no válidas', text: 'Máximo disponible: ' + maxUnidades }));
            return;
        }
        var datosEnvio = {
            producto_id: parseInt(idVal, 10),
            unidades: unidades,
            prioridad: (selPrioridad && selPrioridad.value) ? selPrioridad.value : 'normal',
            motivo: (areaMotivo && areaMotivo.value) ? String(areaMotivo.value).trim() : ''
        };
        cerrarModal();
        fetch('index.php?accion=api&recurso=crear_pedido', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(datosEnvio)
        })
            .then(function (respuesta) { return respuesta.json(); })
            .then(function (datos) {
                if (datos.creado && window.Swal) {
                    window.Swal.fire(opcionesSwal({ title: 'Solicitud enviada', text: datos.mensaje || 'Recibirás notificación cuando se revise.' }));
                } else if (datos.error && window.Swal) {
                    window.Swal.fire(opcionesSwal({ icon: 'error', title: 'Error', text: datos.error }));
                }
                if (contenedor && typeof cargarProductos === 'function') {
                    var botonActivo = document.querySelector('.boton-filtro-activo');
                    var cat = (botonActivo && botonActivo.getAttribute('data-categoria')) ? botonActivo.getAttribute('data-categoria') : '';
                    cargarProductos(cat || '');
                }
            })
            .catch(function () {
                if (window.Swal) window.Swal.fire(opcionesSwal({ icon: 'error', title: 'Error de conexión', text: 'No se pudo enviar la solicitud. Comprueba tu conexión e intenta de nuevo.' }));
                if (contenedor && typeof cargarProductos === 'function') {
                    var botonActivo = document.querySelector('.boton-filtro-activo');
                    cargarProductos((botonActivo && botonActivo.getAttribute('data-categoria')) || '');
                }
            });
    }

    document.addEventListener('submit', function (evento) {
        if (evento.target && evento.target.id === 'form-solicitar') enviarSolicitudModal(evento);
    });

    // Carga inicial del catálogo y enlace de botones "Solicitar" (solo si existen contenedor y filtros)
    if (contenedor && filtros && estadoCarga) {
        cargarProductos('');
    }
})();
