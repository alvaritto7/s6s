/**
 * mi-cuenta.js — Guardar nombre y cambiar contraseña vía API.
 */
(function () {
    'use strict';

    var urlApi = 'index.php?accion=api&recurso=';
    var inputNombre = document.getElementById('mi-cuenta-nombre');
    var btnGuardarNombre = document.getElementById('btn-guardar-nombre');
    var formClave = document.getElementById('form-cambiar-clave');

    function getIsotipoUrl() {
        return document.body && document.body.getAttribute('data-isotipo') ? document.body.getAttribute('data-isotipo') : '';
    }

    function opcionesSwal(params) {
        var opciones = { title: params.title || '', text: params.text || '' };
        if (params.icon === 'error' || params.icon === 'warning') opciones.icon = params.icon;
        var iso = getIsotipoUrl();
        if (iso) opciones.imageUrl = iso;
        opciones.confirmButtonColor = '#00A3FF';
        return opciones;
    }

    function guardarNombre() {
        var nombre = inputNombre && inputNombre.value ? inputNombre.value.trim() : '';
        if (!nombre) {
            if (window.Swal) window.Swal.fire(opcionesSwal({ icon: 'warning', title: 'Nombre vacío', text: 'Escribe un nombre para guardar.' }));
            return;
        }
        if (!btnGuardarNombre) return;
        btnGuardarNombre.disabled = true;

        fetch(urlApi + 'actualizar_perfil', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ nombre: nombre })
        })
            .then(function (r) { return r.json(); })
            .then(function (datos) {
                if (datos.ok && window.Swal) {
                    window.Swal.fire(opcionesSwal({ title: 'Nombre actualizado', text: datos.mensaje || 'Se ha guardado correctamente.' }));
                } else if (datos.error && window.Swal) {
                    window.Swal.fire(opcionesSwal({ icon: 'error', title: 'Error', text: datos.error }));
                }
            })
            .catch(function () {
                if (window.Swal) window.Swal.fire(opcionesSwal({ icon: 'error', title: 'Error de conexión', text: 'No se pudo guardar. Comprueba tu conexión.' }));
            })
            .finally(function () {
                if (btnGuardarNombre) btnGuardarNombre.disabled = false;
            });
    }

    function cambiarClave(e) {
        e.preventDefault();
        var actual = document.getElementById('clave-actual');
        var nueva = document.getElementById('clave-nueva');
        var nueva2 = document.getElementById('clave-nueva-2');
        if (!actual || !nueva || !nueva2) return;

        var passActual = actual.value;
        var passNueva = nueva.value;
        var passNueva2 = nueva2.value;

        if (!passActual) {
            if (window.Swal) window.Swal.fire(opcionesSwal({ icon: 'warning', title: 'Contraseña actual', text: 'Indica tu contraseña actual.' }));
            return;
        }
        if (passNueva.length < 6) {
            if (window.Swal) window.Swal.fire(opcionesSwal({ icon: 'warning', title: 'Contraseña corta', text: 'La nueva contraseña debe tener al menos 6 caracteres.' }));
            return;
        }
        if (passNueva !== passNueva2) {
            if (window.Swal) window.Swal.fire(opcionesSwal({ icon: 'error', title: 'No coinciden', text: 'La nueva contraseña y la repetición no coinciden.' }));
            return;
        }

        fetch(urlApi + 'actualizar_perfil', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ password_actual: passActual, password_nueva: passNueva })
        })
            .then(function (r) { return r.json(); })
            .then(function (datos) {
                if (datos.ok && window.Swal) {
                    window.Swal.fire(opcionesSwal({ title: 'Contraseña actualizada', text: datos.mensaje || 'Ya puedes usar tu nueva contraseña.' }));
                    formClave.reset();
                } else if (datos.error && window.Swal) {
                    window.Swal.fire(opcionesSwal({ icon: 'error', title: 'Error', text: datos.error }));
                }
            })
            .catch(function () {
                if (window.Swal) window.Swal.fire(opcionesSwal({ icon: 'error', title: 'Error de conexión', text: 'No se pudo cambiar la contraseña.' }));
            });
    }

    function escapeHtml(texto) {
        if (!texto) return '';
        var div = document.createElement('div');
        div.textContent = texto;
        return div.innerHTML;
    }

    function estadoPedidoLabel(estado) {
        var map = { pendiente: 'Pendiente', en_revision: 'En revisión', aprobado: 'Aprobado', denegado: 'Denegado', entregado: 'Entregado' };
        return map[estado] || estado || '—';
    }

    function estadoPropuestaLabel(estado) {
        var map = { en_estudio: 'En estudio', aceptada: 'Aceptada', descartada: 'Descartada' };
        return map[estado] || 'En estudio';
    }

    function cargarHistorial() {
        var contenedor = document.getElementById('mi-cuenta-historial-contenido');
        var estadoEl = document.getElementById('mi-cuenta-estado-historial');
        if (!contenedor) return;

        fetch(urlApi + 'mi_historial', { credentials: 'same-origin' })
            .then(function (r) { return r.ok ? r.json() : Promise.reject(); })
            .then(function (datos) {
                var pedidos = datos.pedidos_recientes || [];
                var propuestas = datos.propuestas_recientes || [];
                var html = '';
                if (pedidos.length > 0) {
                    html += '<h3 class="mi-cuenta-historial-subtitulo">Peticiones recientes</h3><ul class="mi-cuenta-lista-historial">';
                    pedidos.forEach(function (p) {
                        var prod = p.producto_nombre || 'Producto';
                        var est = p.estado || 'pendiente';
                        var fecha = p.fecha_creacion || '';
                        html += '<li class="mi-cuenta-item-historial"><a href="index.php?accion=peticiones">' + escapeHtml(prod) + '</a> <span class="mi-cuenta-badge-estado">' + escapeHtml(estadoPedidoLabel(est)) + '</span>' + (fecha ? ' <span class="mi-cuenta-fecha">' + escapeHtml(fecha) + '</span>' : '') + '</li>';
                    });
                    html += '</ul>';
                } else {
                    html += '<h3 class="mi-cuenta-historial-subtitulo">Peticiones recientes</h3><p class="mi-cuenta-sin-datos">No tienes peticiones recientes.</p>';
                }
                if (propuestas.length > 0) {
                    html += '<h3 class="mi-cuenta-historial-subtitulo">Propuestas (wishlist)</h3><ul class="mi-cuenta-lista-historial">';
                    propuestas.forEach(function (p) {
                        var titulo = p.titulo || 'Propuesta';
                        var est = p.estado || 'en_estudio';
                        var part = p.participacion === 'autor' ? 'Creaste esta propuesta' : 'Votaste en esta propuesta';
                        var fecha = p.fecha_creacion || '';
                        html += '<li class="mi-cuenta-item-historial"><a href="index.php?accion=wishlist">' + escapeHtml(titulo) + '</a> <span class="mi-cuenta-badge-estado">' + escapeHtml(estadoPropuestaLabel(est)) + '</span> <span class="mi-cuenta-participacion">' + escapeHtml(part) + '</span>' + (fecha ? ' <span class="mi-cuenta-fecha">' + escapeHtml(fecha) + '</span>' : '') + '</li>';
                    });
                    html += '</ul>';
                } else {
                    html += '<h3 class="mi-cuenta-historial-subtitulo">Propuestas (wishlist)</h3><p class="mi-cuenta-sin-datos">No has participado en ninguna propuesta reciente.</p>';
                }
                if (estadoEl) estadoEl.remove();
                contenedor.innerHTML = html;
            })
            .catch(function () {
                if (estadoEl) estadoEl.textContent = 'No se pudo cargar el historial.';
            });
    }

    if (btnGuardarNombre) btnGuardarNombre.addEventListener('click', guardarNombre);
    if (formClave) formClave.addEventListener('submit', cambiarClave);

    cargarHistorial();
})();
