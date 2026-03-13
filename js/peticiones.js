/**
 * peticiones.js — Cambio de estado (staff), nueva solicitud desde formulario, carga de productos.
 */
(function () {
    'use strict';

    var productosConStock = [];

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

    function cargarProductosEnSelect() {
        var sel = document.getElementById('nueva-solicitud-producto');
        if (!sel) return;
        sel.innerHTML = '<option value="">Cargando…</option>';
        fetch('index.php?accion=api&recurso=productos', { credentials: 'same-origin' })
            .then(function (respuesta) { return respuesta.json(); })
            .then(function (datos) {
                productosConStock = (datos.productos || []).filter(function (p) {
                    return (p.stock_disponible || p.stock || 0) > 0;
                });
                sel.innerHTML = '<option value="">— Elige producto —</option>';
                productosConStock.forEach(function (p) {
                    var opt = document.createElement('option');
                    opt.value = p.id;
                    opt.textContent = (p.nombre || '') + ' (disp. ' + (p.stock_disponible != null ? p.stock_disponible : p.stock) + ')';
                    opt.setAttribute('data-max', String(p.stock_disponible != null ? p.stock_disponible : p.stock));
                    sel.appendChild(opt);
                });
                actualizarMaxUnidades();
            })
            .catch(function () {
                sel.innerHTML = '<option value="">Error al cargar</option>';
                if (window.Swal) window.Swal.fire(opcionesSwal({ icon: 'error', title: 'Error de conexión', text: 'No se pudo cargar el listado de productos. Comprueba tu conexión e intenta de nuevo.' }));
            });
    }

    function actualizarMaxUnidades() {
        var sel = document.getElementById('nueva-solicitud-producto');
        var input = document.getElementById('nueva-solicitud-unidades');
        if (!sel || !input) return;
        var opt = sel.options[sel.selectedIndex];
        var max = opt && opt.getAttribute('data-max') ? parseInt(opt.getAttribute('data-max'), 10) : 9999;
        input.max = max;
        if (parseInt(input.value, 10) > max) input.value = max;
    }

    document.getElementById('nueva-solicitud-producto') && document.getElementById('nueva-solicitud-producto').addEventListener('change', actualizarMaxUnidades);

    var formNueva = document.getElementById('form-nueva-solicitud');
    if (formNueva) {
        formNueva.addEventListener('submit', function (evento) {
            evento.preventDefault();
            var selProducto = document.getElementById('nueva-solicitud-producto');
            var inputUnidades = document.getElementById('nueva-solicitud-unidades');
            var productoId = selProducto ? selProducto.value : '';
            var unidades = inputUnidades ? parseInt(inputUnidades.value, 10) : 0;
            var prioridad = (document.getElementById('nueva-solicitud-prioridad') || {}).value || 'normal';
            var motivo = ((document.getElementById('nueva-solicitud-motivo') || {}).value || '').trim();
            if (!productoId || productoId === '') {
                if (window.Swal) window.Swal.fire(opcionesSwal({ icon: 'warning', title: 'Elige un producto', text: 'Selecciona un producto del listado antes de enviar.' }));
                return;
            }
            if (unidades < 1) {
                if (window.Swal) window.Swal.fire(opcionesSwal({ icon: 'warning', title: 'Unidades', text: 'Indica al menos 1 unidad.' }));
                return;
            }
            fetch('index.php?accion=api&recurso=crear_pedido', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    producto_id: parseInt(productoId, 10),
                    unidades: unidades,
                    prioridad: prioridad || 'normal',
                    motivo: motivo
                })
            })
                .then(function (respuesta) { return respuesta.json(); })
                .then(function (datos) {
                    if (datos.creado && window.Swal) {
                        window.Swal.fire(opcionesSwal({ title: 'Solicitud enviada', text: datos.mensaje || '' }));
                        formNueva.reset();
                        document.getElementById('nueva-solicitud-unidades').value = 1;
                        cargarProductosEnSelect();
                        setTimeout(function () { window.location.reload(); }, 1200);
                    } else if (datos.error && window.Swal) {
                        window.Swal.fire(opcionesSwal({ icon: 'error', title: 'Error', text: datos.error }));
                    }
                })
                .catch(function () {
                    if (window.Swal) window.Swal.fire(opcionesSwal({ icon: 'error', title: 'Error de conexión', text: 'No se pudo enviar la solicitud. Comprueba tu conexión e intenta de nuevo.' }));
                });
        });
        cargarProductosEnSelect();
    }

    function cambiarEstado(pedidoId, estado) {
        fetch('index.php?accion=api&recurso=cambiar_estado_pedido', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ pedido_id: parseInt(pedidoId, 10), nuevo_estado: estado })
        })
            .then(function (respuesta) { return respuesta.json(); })
            .then(function (datos) {
                if (datos.actualizado && window.Swal) {
                    window.Swal.fire(opcionesSwal({ title: 'Estado actualizado', text: datos.mensaje || '' }));
                    var fila = document.querySelector('.item-peticion[data-pedido-id="' + pedidoId + '"]');
                    if (fila) fila.remove();
                } else if (datos.error && window.Swal) {
                    window.Swal.fire(opcionesSwal({ icon: 'error', title: 'Error', text: datos.error }));
                }
            })
            .catch(function () {
                if (window.Swal) window.Swal.fire(opcionesSwal({ icon: 'error', title: 'Error de conexión', text: 'No se pudo actualizar el estado. Comprueba tu conexión e intenta de nuevo.' }));
            });
    }

    function confirmarYCambiarEstado(pedidoId, estado, textoBoton) {
        var mensajes = {
            aprobado: '¿Aprobar esta solicitud?',
            denegado: '¿Denegar esta solicitud? El solicitante verá el estado actualizado.',
            entregado: '¿Marcar como entregado?'
        };
        var texto = mensajes[estado] || '¿Continuar?';
        if (!window.Swal) {
            cambiarEstado(pedidoId, estado);
            return;
        }
        window.Swal.fire({
            icon: 'question',
            title: textoBoton || 'Confirmar',
            text: texto,
            showCancelButton: true,
            confirmButtonText: 'Sí',
            cancelButtonText: 'Cancelar',
            imageUrl: getIsotipoUrl() || undefined
        }).then(function (resultado) {
            if (resultado.isConfirmed) cambiarEstado(pedidoId, estado);
        });
    }

    function onBotonEstadoClick(evento) {
        var boton = evento.target && evento.target.closest('.boton-estado');
        if (!boton) return;
        var fila = boton.closest('.item-peticion');
        var pedidoId = fila && fila.getAttribute('data-pedido-id');
        var estado = boton.getAttribute('data-estado');
        if (!pedidoId || !estado) return;
        evento.preventDefault();
        var textoBoton = (boton.textContent || '').trim();
        var requiereConfirmacion = estado === 'aprobado' || estado === 'denegado' || estado === 'entregado';
        if (requiereConfirmacion) {
            confirmarYCambiarEstado(pedidoId, estado, textoBoton);
        } else {
            cambiarEstado(pedidoId, estado);
        }
    }

    document.getElementById('lista-revision') && document.getElementById('lista-revision').addEventListener('click', onBotonEstadoClick);
    document.getElementById('lista-pendientes') && document.getElementById('lista-pendientes').addEventListener('click', onBotonEstadoClick);

    /* Filtro por estado en Mis solicitudes */
    var contenedorMis = document.getElementById('contenedor-mis-peticiones');
    var filtrosEstado = document.querySelectorAll('.boton-filtro-estado');
    if (contenedorMis && filtrosEstado.length) {
        filtrosEstado.forEach(function (boton) {
            boton.addEventListener('click', function () {
                var estadoFiltro = (boton.getAttribute('data-estado') || '').trim();
                filtrosEstado.forEach(function (b) { b.classList.toggle('boton-filtro-activo', b === boton); });
                contenedorMis.querySelectorAll('.item-peticion').forEach(function (li) {
                    var est = li.getAttribute('data-estado') || '';
                    li.hidden = estadoFiltro !== '' && est !== estadoFiltro;
                });
            });
        });
    }
})();
