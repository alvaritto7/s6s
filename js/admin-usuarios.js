/**
 * admin-usuarios.js — Gestión de usuarios: listado, cambiar rol, activar/desactivar, eliminar.
 * Solo visible para administrador. Las acciones se envían por POST a la API.
 */
(function () {
    'use strict';

    var lista = document.getElementById('lista-usuarios-admin');
    if (!lista) return;

    var urlBase = 'index.php?accion=api';
    var usuarioActualId = parseInt(document.body.getAttribute('data-usuario-id') || '0', 10);

    function opcionesSwal(parametros) {
        var opciones = { title: parametros.title || '', text: parametros.text || '' };
        var icon = parametros.icon || '';
        if (icon === 'error' || icon === 'warning') {
            opciones.icon = icon;
        }
        var isotipo = document.body.getAttribute('data-isotipo');
        if (isotipo) {
            opciones.imageUrl = isotipo;
            opciones.imageHeight = 60;
            opciones.imageWidth = 60;
        }
        opciones.confirmButtonColor = '#00A3FF';
        return opciones;
    }

    function escapeHtml(texto) {
        if (!texto) return '';
        var div = document.createElement('div');
        div.textContent = texto;
        return div.innerHTML;
    }

    function cargarUsuarios() {
        fetch(urlBase + '&recurso=usuarios', { credentials: 'same-origin' })
            .then(function (respuesta) {
                if (!respuesta.ok) throw new Error('No autorizado');
                return respuesta.json();
            })
            .then(function (datos) {
                var usuarios = datos.usuarios || [];
                lista.innerHTML = '';
                if (usuarios.length === 0) {
                    lista.innerHTML = '<p class="color-gris">No hay usuarios.</p>';
                    return;
                }
                var table = '<table class="tabla-productos tabla-usuarios"><thead><tr><th>ID</th><th>Email</th><th>Nombre</th><th>Rol</th><th>Activo</th><th>Acciones</th></tr></thead><tbody>';
                usuarios.forEach(function (u) {
                    var id = u.id;
                    var esYo = (id === usuarioActualId);
                    var activo = u.activo !== false;
                    var rol = u.rol || 'empleado';
                    table += '<tr data-id="' + id + '">';
                    table += '<td>' + id + '</td>';
                    table += '<td>' + escapeHtml(u.email || '') + '</td>';
                    table += '<td>' + escapeHtml(u.nombre || '') + '</td>';
                    table += '<td><select class="select-rol-usuario" data-id="' + id + '" aria-label="Rol">';
                    table += '<option value="empleado"' + (rol === 'empleado' ? ' selected' : '') + '>Empleado</option>';
                    table += '<option value="staff"' + (rol === 'staff' ? ' selected' : '') + '>Staff</option>';
                    table += '<option value="administrador"' + (rol === 'administrador' ? ' selected' : '') + '>Administrador</option>';
                    table += '</select></td>';
                    table += '<td><select class="select-activo-usuario" data-id="' + id + '" data-activo="' + (activo ? '1' : '0') + '" aria-label="Activo">';
                    if (esYo) {
                        table += '<option value="1" selected>Sí (tú)</option>';
                    } else {
                        table += '<option value="1"' + (activo ? ' selected' : '') + '>Sí</option>';
                        table += '<option value="0"' + (!activo ? ' selected' : '') + '>No</option>';
                    }
                    table += '</select></td>';
                    table += '<td>';
                    if (!esYo) {
                        table += '<button type="button" class="boton boton-secundario btn-eliminar-usuario" data-id="' + id + '" data-email="' + escapeHtml(u.email || '') + '">Eliminar</button>';
                    } else {
                        table += '<span class="color-gris">—</span>';
                    }
                    table += '</td></tr>';
                });
                table += '</tbody></table>';
                lista.innerHTML = table;
                enlazarEventos();
            })
            .catch(function () {
                lista.innerHTML = '<p class="estado-carga-error">Error al cargar usuarios. Comprueba que eres administrador.</p>';
                if (window.Swal) window.Swal.fire(opcionesSwal({ icon: 'error', title: 'Error', text: 'No se pudo cargar el listado.' }));
            });
    }

    function enlazarEventos() {
        lista.querySelectorAll('.select-rol-usuario').forEach(function (select) {
            select.addEventListener('change', function () {
                var id = parseInt(this.getAttribute('data-id'), 10);
                var rol = this.value;
                if (!id || !rol) return;
                var fd = new FormData();
                fd.append('id', id);
                fd.append('rol', rol);
                fetch(urlBase + '&recurso=usuario_actualizar_rol', {
                    method: 'POST',
                    credentials: 'same-origin',
                    body: fd
                })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (data.error && window.Swal) {
                            window.Swal.fire(opcionesSwal({ icon: 'error', title: 'Error', text: data.error }));
                            cargarUsuarios();
                            return;
                        }
                        if (window.Swal) window.Swal.fire(opcionesSwal({ icon: 'success', title: 'Rol actualizado', text: 'El usuario tiene ahora el rol "' + rol + '".' }));
                    })
                    .catch(function () {
                        if (window.Swal) window.Swal.fire(opcionesSwal({ icon: 'error', title: 'Error de conexión', text: 'No se pudo actualizar el rol.' }));
                        cargarUsuarios();
                    });
            });
        });

        lista.querySelectorAll('.select-activo-usuario').forEach(function (select) {
            select.addEventListener('change', function () {
                var id = parseInt(this.getAttribute('data-id'), 10);
                var activo = this.value === '1';
                if (!id) return;
                var fd = new FormData();
                fd.append('id', id);
                fd.append('activo', activo ? '1' : '0');
                fetch(urlBase + '&recurso=usuario_actualizar_activo', {
                    method: 'POST',
                    credentials: 'same-origin',
                    body: fd
                })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (data.error && window.Swal) {
                            window.Swal.fire(opcionesSwal({ icon: 'error', title: 'Error', text: data.error }));
                            cargarUsuarios();
                            return;
                        }
                        if (window.Swal) window.Swal.fire(opcionesSwal({ icon: 'success', title: 'Estado actualizado', text: activo ? 'Usuario activado.' : 'Usuario desactivado (no podrá iniciar sesión).' }));
                        this.setAttribute('data-activo', activo ? '1' : '0');
                        cargarUsuarios();
                    }.bind(this))
                    .catch(function () {
                        if (window.Swal) window.Swal.fire(opcionesSwal({ icon: 'error', title: 'Error de conexión', text: 'No se pudo actualizar.' }));
                        cargarUsuarios();
                    });
            });
        });

        lista.querySelectorAll('.btn-eliminar-usuario').forEach(function (boton) {
            boton.addEventListener('click', function () {
                var id = this.getAttribute('data-id');
                var email = this.getAttribute('data-email');
                if (!id || !window.Swal) return;
                window.Swal.fire({
                    title: '¿Eliminar usuario?',
                    text: 'Se eliminará de forma definitiva: ' + (email || id) + '. Esta acción no se puede deshacer.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#c00',
                    cancelButtonText: 'Cancelar',
                    confirmButtonText: 'Sí, eliminar'
                }).then(function (result) {
                    if (!result.isConfirmed) return;
                    var fd = new FormData();
                    fd.append('id', id);
                    fetch(urlBase + '&recurso=usuario_eliminar', {
                        method: 'POST',
                        credentials: 'same-origin',
                        body: fd
                    })
                        .then(function (r) { return r.json(); })
                        .then(function (data) {
                            if (data.error && window.Swal) {
                                window.Swal.fire(opcionesSwal({ icon: 'error', title: 'Error', text: data.error }));
                                return;
                            }
                            if (window.Swal) window.Swal.fire(opcionesSwal({ icon: 'success', title: 'Usuario eliminado', text: 'El usuario ha sido eliminado de la aplicación.' }));
                            cargarUsuarios();
                        })
                        .catch(function () {
                            if (window.Swal) window.Swal.fire(opcionesSwal({ icon: 'error', title: 'Error de conexión', text: 'No se pudo eliminar.' }));
                            cargarUsuarios();
                        });
                });
            });
        });
    }

    cargarUsuarios();
})();
