/**
 * admin-productos.js — CRUD productos: listado, alta con subida de imagen, edición, desactivar.
 * Solo visible para staff/admin. Fetch API y FormData para multipart.
 */
(function () {
    'use strict';

    var wrapper = document.getElementById('form-producto-wrapper');
    var form = document.getElementById('form-producto');
    var lista = document.getElementById('lista-productos-admin');
    var btnAbrir = document.getElementById('btn-abrir-form-producto');
    var btnCerrar = document.getElementById('btn-cerrar-form-producto');
    var uploadZone = document.getElementById('upload-zone-imagen');
    var uploadFilename = document.getElementById('upload-filename');
    var inputImagen = document.getElementById('producto-imagen');

    if (!lista || !form) return;

    function actualizarNombreArchivo() {
        if (!uploadFilename || !inputImagen) return;
        var file = inputImagen.files && inputImagen.files[0];
        if (file) {
            uploadFilename.textContent = file.name;
            if (uploadZone) uploadZone.classList.add('has-file');
        } else {
            uploadFilename.textContent = '';
            if (uploadZone) uploadZone.classList.remove('has-file');
        }
    }

    if (inputImagen) {
        inputImagen.addEventListener('change', actualizarNombreArchivo);
    }
    if (uploadZone && form) {
        form.addEventListener('reset', function () {
            setTimeout(actualizarNombreArchivo, 0);
        });
        uploadZone.addEventListener('dragover', function (e) {
            e.preventDefault();
            e.stopPropagation();
            uploadZone.classList.add('upload-zone-dragover');
        });
        uploadZone.addEventListener('dragleave', function (e) {
            e.preventDefault();
            e.stopPropagation();
            uploadZone.classList.remove('upload-zone-dragover');
        });
        uploadZone.addEventListener('drop', function (e) {
            e.preventDefault();
            e.stopPropagation();
            uploadZone.classList.remove('upload-zone-dragover');
            var files = e.dataTransfer && e.dataTransfer.files;
            if (inputImagen && files && files.length) {
                inputImagen.files = files;
                actualizarNombreArchivo();
            }
        });
    }

    var urlBase = 'index.php?accion=api';
    var categoriasCache = [];

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

    function cargarProductos() {
        fetch(urlBase + '&recurso=productos&todos=1', { credentials: 'same-origin' })
            .then(function (respuesta) { return respuesta.json(); })
            .then(function (datos) {
                var productos = datos.productos || [];
                lista.innerHTML = '';
                if (productos.length === 0) {
                    lista.innerHTML = '<p class="color-gris">No hay productos.</p>';
                    return;
                }
                var table = '<table class="tabla-productos"><thead><tr><th>ID</th><th>Nombre</th><th>Categoría</th><th>Stock</th><th>Acciones</th></tr></thead><tbody>';
                productos.forEach(function (p) {
                    var activo = (p.activo !== false);
                    var stock = p.stock || 0;
                    table += '<tr data-id="' + p.id + '">';
                    table += '<td>' + p.id + '</td><td>' + escapeHtml(p.nombre || '') + '</td>';
                    table += '<td>' + (p.categoria_id || '') + '</td><td>' + (stock === 0 ? '<span class="texto-agotado">0 (Agotado)</span>' : stock) + '</td>';
                    table += '<td><button type="button" class="boton boton-secundario btn-editar-producto" data-id="' + p.id + '">Editar</button> ';
                    if (activo) {
                        table += '<button type="button" class="boton boton-secundario btn-eliminar-producto" data-id="' + p.id + '" data-nombre="' + escapeHtml(p.nombre || '') + '">Desactivar</button>';
                    }
                    table += '</td></tr>';
                });
                table += '</tbody></table>';
                lista.innerHTML = table;
                enlazarBotones();
            })
            .catch(function () {
                lista.innerHTML = '<p class="estado-carga-error">Error al cargar productos.</p>';
                if (window.Swal) window.Swal.fire(opcionesSwal({ icon: 'error', title: 'Error de conexión', text: 'No se pudo cargar el listado. Comprueba tu conexión e intenta de nuevo.' }));
            });
    }

    function escapeHtml(texto) {
        if (!texto) return '';
        var div = document.createElement('div');
        div.textContent = texto;
        return div.innerHTML;
    }

    function enlazarBotones() {
        lista.querySelectorAll('.btn-editar-producto').forEach(function (boton) {
            boton.addEventListener('click', function () {
                var id = this.getAttribute('data-id');
                if (!id) return;
                abrirFormularioEdicion(parseInt(id, 10));
            });
        });
        lista.querySelectorAll('.btn-eliminar-producto').forEach(function (boton) {
            boton.addEventListener('click', function () {
                var id = this.getAttribute('data-id');
                var nombre = this.getAttribute('data-nombre');
                if (!id || !window.Swal) return;
                var opciones = opcionesSwal({
                    icon: 'warning',
                    title: 'Desactivar producto',
                    text: '¿Desactivar «' + nombre + '»? No se borrará, dejará de mostrarse en el catálogo.'
                });
                opciones.showCancelButton = true;
                opciones.confirmButtonText = 'Desactivar';
                opciones.cancelButtonText = 'Cancelar';
                window.Swal.fire(opciones).then(function (resultado) {
                    if (resultado.isConfirmed) eliminarProducto(parseInt(id, 10));
                });
            });
        });
    }

    function abrirFormularioEdicion(id) {
        var idNum = parseInt(id, 10);
        if (!idNum) return;
        fetch(urlBase + '&recurso=productos&todos=1', { credentials: 'same-origin' })
            .then(function (respuesta) { return respuesta.json(); })
            .then(function (datos) {
                var productos = datos.productos || [];
                var p = productos.filter(function (x) { return Number(x.id) === idNum; })[0];
                if (!p) return;
                document.getElementById('producto-id').value = String(p.id);
                document.getElementById('producto-nombre').value = p.nombre || '';
                document.getElementById('producto-descripcion').value = p.descripcion || '';
                document.getElementById('producto-categoria_id').value = String(p.categoria_id || 1);
                document.getElementById('producto-stock').value = p.stock || 0;
                document.getElementById('producto-umbral_critico').value = p.umbral_critico || 0;
                var imgInput = document.getElementById('producto-imagen');
                if (imgInput) imgInput.value = '';
                actualizarNombreArchivo();
                if (wrapper) {
                    wrapper.hidden = false;
                    wrapper.removeAttribute('hidden');
                    var firstInput = document.getElementById('producto-nombre');
                    if (firstInput) firstInput.focus();
                }
            })
            .catch(function () {
                if (window.Swal) window.Swal.fire(opcionesSwal({ icon: 'error', title: 'Error', text: 'No se pudo cargar el producto.' }));
            });
    }

    function eliminarProducto(id) {
        fetch(urlBase + '&recurso=producto_eliminar', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        })
            .then(function (respuesta) { return respuesta.json(); })
            .then(function (datos) {
                if (datos.eliminado && window.Swal) {
                    window.Swal.fire(opcionesSwal({ title: 'Producto desactivado', text: datos.mensaje || '' }));
                    cargarProductos();
                } else if (datos.error && window.Swal) {
                    window.Swal.fire(opcionesSwal({ icon: 'error', title: 'Error', text: datos.error }));
                }
            })
            .catch(function () {
                if (window.Swal) window.Swal.fire(opcionesSwal({ icon: 'error', title: 'Error de conexión', text: 'No se pudo desactivar. Comprueba tu conexión e intenta de nuevo.' }));
            });
    }

    if (btnAbrir && wrapper) {
        btnAbrir.addEventListener('click', function () {
            document.getElementById('producto-id').value = '';
            form.reset();
            actualizarNombreArchivo();
            wrapper.hidden = false;
        });
    }
    if (btnCerrar && wrapper) {
        btnCerrar.addEventListener('click', function () {
            wrapper.hidden = true;
        });
    }

    form.addEventListener('submit', function (evento) {
        evento.preventDefault();
        var id = document.getElementById('producto-id').value.trim();
        var url = urlBase + (id ? '&recurso=producto_actualizar' : '&recurso=producto_crear');
        var formData = new FormData(form);
        if (!id) formData.delete('id');
        fetch(url, { method: 'POST', credentials: 'same-origin', body: formData })
            .then(function (respuesta) { return respuesta.json(); })
            .then(function (datos) {
                if ((datos.creado || datos.actualizado) && window.Swal) {
                    window.Swal.fire(opcionesSwal({ title: id ? 'Producto actualizado' : 'Producto creado', text: datos.mensaje || '' }));
                    wrapper.hidden = true;
                    form.reset();
                    document.getElementById('producto-id').value = '';
                    cargarProductos();
                } else if (datos.error && window.Swal) {
                    window.Swal.fire(opcionesSwal({ icon: 'error', title: 'Error', text: datos.error }));
                }
            })
            .catch(function () {
                if (window.Swal) window.Swal.fire(opcionesSwal({ icon: 'error', title: 'Error de conexión', text: 'No se pudo guardar. Comprueba tu conexión e intenta de nuevo.' }));
            });
    });

    cargarProductos();
})();
