/**
 * wishlist.js — Tablón de propuestas con votos vía Fetch API.
 * Carga propuestas desde el API y permite votar (POST) sin recargar la página.
 */
(function () {
    'use strict';

    var contenedor = document.getElementById('contenedor-propuestas');
    var estadoCarga = document.getElementById('estado-carga-wishlist');

    if (!contenedor) return;

    var urlBase = 'index.php?accion=api';

    function getIsotipoUrl() {
        return document.body && document.body.getAttribute('data-isotipo') ? document.body.getAttribute('data-isotipo') : '';
    }

    function opcionesSwal(parametros) {
        var opciones = { title: parametros.title || '', text: parametros.text || '' };
        var icon = parametros.icon || '';
        // Para errores/avisos mantenemos el icono; para éxitos usamos solo el isotipo
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

    function escapeHtml(texto) {
        if (!texto) return '';
        var div = document.createElement('div');
        div.textContent = texto;
        return div.innerHTML;
    }

    function getIsotipoUrlForSpinner() {
        return document.body && document.body.getAttribute('data-isotipo') ? document.body.getAttribute('data-isotipo') : '';
    }

    function mostrarEstado(mensaje, esError) {
        if (!estadoCarga) return;
        estadoCarga.className = 'estado-carga' + (esError ? ' estado-carga-error' : '');
        estadoCarga.hidden = false;
        if (!esError && mensaje && mensaje.indexOf('Cargando') !== -1) {
            var isotipo = getIsotipoUrlForSpinner();
            estadoCarga.innerHTML = '<span class="spinner-isotipo-wrap" aria-hidden="true"><img src="' + (isotipo || '') + '" alt="" class="spinner-isotipo"></span><span class="estado-carga-texto">' + (mensaje || '') + '</span>';
        } else {
            estadoCarga.textContent = mensaje;
        }
    }

    var puedeStaff = document.body && document.body.getAttribute('data-puede-staff') === '1';

    function estadoPropuestaLabel(estado) {
        var map = { en_estudio: 'En estudio', aceptada: 'Aceptada', descartada: 'Descartada', archivada: 'Archivada' };
        return map[estado] || 'En estudio';
    }

    function renderizarPropuesta(propuesta, yaVotado) {
        var id = propuesta.id || '';
        var titulo = propuesta.titulo || propuesta.nombre || 'Sin título';
        var descripcion = propuesta.descripcion || '';
        var votos = typeof propuesta.votos === 'number' ? propuesta.votos : 0;
        var autor = propuesta.autor_nombre || '';
        var fecha = propuesta.fecha_creacion || '';
        var esMia = !!propuesta.es_mia;
        var estado = (propuesta.estado || 'en_estudio').replace(/[^a-z_]/g, '');
        if (['en_estudio', 'aceptada', 'descartada', 'archivada'].indexOf(estado) === -1) estado = 'en_estudio';

        var item = document.createElement('article');
        item.className = 'item-propuesta' + (esMia ? ' item-propuesta-mia' : '');
        item.setAttribute('role', 'listitem');
        item.setAttribute('data-id', String(id));
        item.setAttribute('data-estado', estado);

        var html = '';
        if (esMia) html += '<span class="badge-mi-propuesta">Tu propuesta</span>';
        html += '<span class="badge-estado-propuesta badge-estado-' + escapeHtml(estado) + '">' + escapeHtml(estadoPropuestaLabel(estado)) + '</span>';
        html += '<h3 class="titulo-propuesta">' + escapeHtml(titulo) + '</h3>';
        if (descripcion) {
            html += '<p class="descripcion-propuesta">' + escapeHtml(descripcion) + '</p>';
        }
        if (autor || fecha) {
            html += '<p class="meta-propuesta">';
            if (autor) html += 'Propuesto por ' + escapeHtml(autor);
            if (autor && fecha) html += ' · ';
            if (fecha) html += escapeHtml(fecha);
            html += '</p>';
        }
        html += '<p class="votos"><span class="votos-numero">' + votos + '</span> voto' + (votos !== 1 ? 's' : '') + '</p>';
        if (!yaVotado) {
            html += '<div class="propuesta-acciones"><button type="button" class="boton boton-primario boton-votar" data-propuesta-id="' + escapeHtml(String(id)) + '">Votar</button></div>';
        } else {
            html += '<div class="propuesta-acciones"><span class="boton boton-votado" aria-hidden="true">Votado</span><button type="button" class="boton boton-secundario boton-quitar-voto" data-propuesta-id="' + escapeHtml(String(id)) + '">Quitar voto</button></div>';
        }
        if (puedeStaff) {
            html += '<div class="propuesta-cambiar-estado"><label for="estado-' + id + '">Estado:</label><select id="estado-' + id + '" class="select-estado-propuesta" data-propuesta-id="' + escapeHtml(String(id)) + '"><option value="en_estudio"' + (estado === 'en_estudio' ? ' selected' : '') + '>En estudio</option><option value="aceptada"' + (estado === 'aceptada' ? ' selected' : '') + '>Aceptada</option><option value="descartada"' + (estado === 'descartada' ? ' selected' : '') + '>Descartada</option><option value="archivada"' + (estado === 'archivada' ? ' selected' : '') + '>Archivada</option></select><button type="button" class="boton boton-secundario boton-aplicar-estado" data-propuesta-id="' + escapeHtml(String(id)) + '">Actualizar</button></div>';
        }
        html += '<div class="propuesta-comentarios" data-propuesta-id="' + escapeHtml(String(id)) + '"><h4>Comentarios</h4><ul class="comentarios-lista"></ul><form class="form-nuevo-comentario" data-propuesta-id="' + escapeHtml(String(id)) + '"><textarea name="texto" placeholder="Escribe un comentario..." maxlength="500"></textarea><button type="submit" class="boton boton-primario">Enviar comentario</button></form></div>';
        item.innerHTML = html;
        return item;
    }

    function renderizarSkeletonsWishlist(cantidad) {
        var fragment = document.createDocumentFragment();
        for (var i = 0; i < cantidad; i++) {
            var card = document.createElement('article');
            card.className = 'item-propuesta tarjeta-skeleton';
            card.innerHTML = '<div class="skeleton skeleton-linea" style="height:1.2rem; margin-bottom:0.5rem"></div><div class="skeleton skeleton-linea skeleton-linea-corta" style="height:0.9rem; width:70%"></div><div class="skeleton skeleton-linea" style="height:0.8rem; width:40%; margin-top:0.75rem"></div>';
            fragment.appendChild(card);
        }
        return fragment;
    }

    function cargarPropuestas() {
        estadoCarga.hidden = true;
        while (contenedor.lastChild && contenedor.lastChild !== estadoCarga) {
            contenedor.removeChild(contenedor.lastChild);
        }
        contenedor.appendChild(renderizarSkeletonsWishlist(4));

        fetch(urlBase + '&recurso=propuestas', { credentials: 'same-origin' })
            .then(function (respuesta) {
                if (!respuesta.ok) throw new Error('Error al cargar');
                return respuesta.json();
            })
            .then(function (datos) {
                var lista = datos.propuestas || [];
                while (contenedor.lastChild && contenedor.lastChild !== estadoCarga) {
                    contenedor.removeChild(contenedor.lastChild);
                }
                estadoCarga.hidden = true;
                if (lista.length === 0) {
                    estadoCarga.hidden = false;
                    estadoCarga.textContent = 'No hay propuestas aún.';
                    estadoCarga.className = 'estado-carga';
                } else {
                    lista.forEach(function (p) {
                        var yaVotado = false;
                        if (p.ya_votado !== undefined) {
                            yaVotado = !!p.ya_votado;
                        }
                        contenedor.appendChild(renderizarPropuesta(p, yaVotado));
                    });
                    enlazarBotonesVotar();
                    enlazarBotonesQuitarVoto();
                    enlazarComentariosYEstado();
                }
            })
            .catch(function () {
                while (contenedor.lastChild && contenedor.lastChild !== estadoCarga) {
                    contenedor.removeChild(contenedor.lastChild);
                }
                mostrarEstado('No se pudieron cargar las propuestas.', true);
                if (window.Swal) window.Swal.fire(opcionesSwal({ icon: 'error', title: 'Error de conexión', text: 'No se pudo conectar con el servidor. Comprueba tu conexión e intenta de nuevo.' }));
            });
    }

    function enlazarBotonesVotar() {
        contenedor.querySelectorAll('.boton-votar').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var id = this.getAttribute('data-propuesta-id');
                if (!id) return;
                votar(parseInt(id, 10), this);
            });
        });
    }

    function enlazarBotonesQuitarVoto() {
        contenedor.querySelectorAll('.boton-quitar-voto').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var id = this.getAttribute('data-propuesta-id');
                if (!id) return;
                quitarVoto(parseInt(id, 10), this);
            });
        });
    }

    function quitarVoto(propuestaId, boton) {
        fetch(urlBase + '&recurso=quitar_voto', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ propuesta_id: propuestaId })
        })
            .then(function (respuesta) { return respuesta.json(); })
            .then(function (datos) {
                if (datos.voto_quitado) {
                    var card = boton && boton.closest ? boton.closest('.item-propuesta') : null;
                    if (card) {
                        var votosEl = card.querySelector('.votos-numero');
                        var actual = votosEl ? parseInt(votosEl.textContent || '0', 10) : 0;
                        if (votosEl) votosEl.textContent = String(Math.max(0, actual - 1));
                        var acciones = card.querySelector('.propuesta-acciones');
                        if (acciones) {
                            acciones.innerHTML = '<button type="button" class="boton boton-primario boton-votar" data-propuesta-id="' + propuestaId + '">Votar</button>';
                            enlazarBotonesVotar();
                        }
                    }
                    if (window.Swal) window.Swal.fire(opcionesSwal({ title: 'Voto retirado', text: datos.mensaje || 'Ya puedes volver a votar si quieres.' }));
                } else {
                    if (window.Swal) window.Swal.fire(opcionesSwal({ icon: 'info', title: 'Wishlist', text: datos.mensaje || datos.error || 'No se pudo quitar el voto.' }));
                }
            })
            .catch(function () {
                if (window.Swal) window.Swal.fire(opcionesSwal({ icon: 'error', title: 'Error de conexión', text: 'No se pudo conectar. Comprueba tu conexión.' }));
            });
    }

    function votar(propuestaId, boton) {
        fetch(urlBase + '&recurso=votar', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ propuesta_id: propuestaId })
        })
            .then(function (respuesta) {
                return respuesta.json();
            })
            .then(function (datos) {
                if (datos.voto) {
                    var card = boton && boton.closest ? boton.closest('.item-propuesta') : null;
                    if (card) {
                        var votosEl = card.querySelector('.votos-numero');
                        var actual = votosEl ? parseInt(votosEl.textContent || '0', 10) : 0;
                        if (votosEl) votosEl.textContent = String(actual + 1);
                        var propuestaId = boton.getAttribute('data-propuesta-id') || '';
                        var acciones = card.querySelector('.propuesta-acciones');
                        if (acciones) {
                            acciones.innerHTML = '<span class="boton boton-votado" aria-hidden="true">Votado</span><button type="button" class="boton boton-secundario boton-quitar-voto" data-propuesta-id="' + propuestaId + '">Quitar voto</button>';
                            enlazarBotonesQuitarVoto();
                        }
                    }
                } else {
                    if (typeof window.Swal !== 'undefined') {
                        window.Swal.fire(opcionesSwal({ icon: 'info', title: 'Wishlist', text: datos.mensaje || 'No se pudo registrar el voto.' }));
                    } else {
                        alert(data.mensaje || 'No se pudo registrar el voto.');
                    }
                }
            })
            .catch(function () {
                if (typeof window.Swal !== 'undefined') {
                    window.Swal.fire(opcionesSwal({ icon: 'error', title: 'Error de conexión', text: 'No se pudo registrar el voto. Comprueba tu conexión e intenta de nuevo.' }));
                } else {
                    alert('No se pudo registrar el voto. Comprueba tu conexión.');
                }
            });
    }

    function cargarComentariosPropuesta(propuestaId, callback) {
        fetch(urlBase + '&recurso=comentarios_propuesta&propuesta_id=' + encodeURIComponent(propuestaId), { credentials: 'same-origin' })
            .then(function (r) { return r.ok ? r.json() : Promise.reject(); })
            .then(function (datos) { callback(null, datos.comentarios || []); })
            .catch(function () { callback(true, []); });
    }

    function pintarComentarios(propuestaId, comentarios) {
        var bloque = contenedor.querySelector('.propuesta-comentarios[data-propuesta-id="' + propuestaId + '"]');
        if (!bloque) return;
        var ul = bloque.querySelector('.comentarios-lista');
        if (!ul) return;
        ul.innerHTML = '';
        (comentarios || []).forEach(function (c) {
            var li = document.createElement('li');
            li.className = 'comentario-item';
            li.setAttribute('data-comentario-id', String(c.id || ''));
            var autor = c.autor_nombre || 'Anónimo';
            var fecha = c.fecha_creacion || '';
            var texto = c.texto || '';
            var esMio = !!c.es_mio;
            li.innerHTML = '<span class="comentario-autor">' + escapeHtml(autor) + '</span>' +
                (esMio ? '<button type="button" class="boton-editar-comentario" data-comentario-id="' + (c.id || '') + '" data-texto="' + escapeHtml(texto).replace(/"/g, '&quot;') + '">Editar</button>' : '') +
                '<p class="comentario-texto">' + escapeHtml(texto) + '</p>' +
                (fecha ? '<p class="comentario-fecha">' + escapeHtml(fecha) + '</p>' : '');
            ul.appendChild(li);
        });
        bloque.querySelectorAll('.boton-editar-comentario').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var id = this.getAttribute('data-comentario-id');
                var textoActual = (this.getAttribute('data-texto') || '').replace(/&quot;/g, '"');
                var li = this.closest('.comentario-item');
                if (!li) return;
                var textoEl = li.querySelector('.comentario-texto');
                if (!textoEl) return;
                var area = document.createElement('textarea');
                area.value = textoActual;
                area.rows = 2;
                area.className = 'comentario-edit-textarea';
                area.style.width = '100%';
                var guardar = document.createElement('button');
                guardar.type = 'button';
                guardar.className = 'boton boton-primario boton-guardar-comentario';
                guardar.textContent = 'Guardar';
                guardar.setAttribute('data-comentario-id', id);
                var cancelar = document.createElement('button');
                cancelar.type = 'button';
                cancelar.className = 'boton boton-secundario';
                cancelar.textContent = 'Cancelar';
                textoEl.replaceWith(area);
                this.replaceWith(guardar);
                li.appendChild(cancelar);
                cancelar.addEventListener('click', function () {
                    var propId = li.closest('.propuesta-comentarios') && li.closest('.propuesta-comentarios').getAttribute('data-propuesta-id');
                    if (propId) {
                        cargarComentariosPropuesta(propId, function (err, list) {
                            if (!err) pintarComentarios(propId, list);
                        });
                    }
                });
                guardar.addEventListener('click', function () {
                    var nuevoTexto = area.value.trim();
                    if (!nuevoTexto) return;
                    fetch(urlBase + '&recurso=editar_comentario', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ comentario_id: parseInt(id, 10), texto: nuevoTexto })
                    })
                        .then(function (r) { return r.json(); })
                        .then(function (datos) {
                            if (datos.actualizado) {
                                cargarComentariosPropuesta(propuestaId, function (err, list) {
                                    if (!err) pintarComentarios(propuestaId, list);
                                });
                            } else if (window.Swal) window.Swal.fire(opcionesSwal({ icon: 'error', title: 'Error', text: datos.error || 'No se pudo editar.' }));
                        })
                        .catch(function () {
                            if (window.Swal) window.Swal.fire(opcionesSwal({ icon: 'error', title: 'Error', text: 'No se pudo conectar.' }));
                        });
                });
            });
        });
    }

    function enlazarComentariosYEstado() {
        contenedor.querySelectorAll('.propuesta-comentarios').forEach(function (bloque) {
            var propuestaId = bloque.getAttribute('data-propuesta-id');
            if (!propuestaId) return;
            cargarComentariosPropuesta(propuestaId, function (err, list) {
                if (!err) pintarComentarios(propuestaId, list);
            });
        });
        contenedor.querySelectorAll('.form-nuevo-comentario').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                var propuestaId = form.getAttribute('data-propuesta-id');
                var textarea = form.querySelector('textarea[name="texto"]');
                var texto = textarea ? textarea.value.trim() : '';
                if (!texto) return;
                fetch(urlBase + '&recurso=añadir_comentario', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ propuesta_id: parseInt(propuestaId, 10), texto: texto })
                })
                    .then(function (r) { return r.json(); })
                    .then(function (datos) {
                        if (datos.creado && datos.comentario) {
                            textarea.value = '';
                            cargarComentariosPropuesta(propuestaId, function (err, list) {
                                if (!err) pintarComentarios(propuestaId, list);
                            });
                            if (window.Swal) window.Swal.fire(opcionesSwal({ title: 'Comentario añadido', text: '' }));
                        } else if (window.Swal) window.Swal.fire(opcionesSwal({ icon: 'error', title: 'Error', text: datos.error || 'No se pudo publicar.' }));
                    })
                    .catch(function () {
                        if (window.Swal) window.Swal.fire(opcionesSwal({ icon: 'error', title: 'Error', text: 'No se pudo conectar.' }));
                    });
            });
        });
        contenedor.querySelectorAll('.boton-aplicar-estado').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var propuestaId = this.getAttribute('data-propuesta-id');
                var card = this.closest('.item-propuesta');
                var select = card ? card.querySelector('.select-estado-propuesta') : null;
                var estado = select ? select.value : 'en_estudio';
                if (!propuestaId) return;
                fetch(urlBase + '&recurso=cambiar_estado_propuesta', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ propuesta_id: parseInt(propuestaId, 10), estado: estado })
                })
                    .then(function (r) { return r.json(); })
                    .then(function (datos) {
                        if (datos.actualizado && card) {
                            if (estado === 'archivada') {
                                // Archivar = quitar de la lista visible
                                card.remove();
                            } else {
                                var badge = card.querySelector('.badge-estado-propuesta');
                                if (badge) {
                                    badge.className = 'badge-estado-propuesta badge-estado-' + estado;
                                    badge.textContent = estadoPropuestaLabel(estado);
                                }
                                card.setAttribute('data-estado', estado);
                            }
                            if (window.Swal) window.Swal.fire(opcionesSwal({ title: 'Estado actualizado', text: '' }));
                        } else if (window.Swal) window.Swal.fire(opcionesSwal({ icon: 'error', title: 'Error', text: datos.error || 'No se pudo actualizar.' }));
                    })
                    .catch(function () {
                        if (window.Swal) window.Swal.fire(opcionesSwal({ icon: 'error', title: 'Error', text: 'No se pudo conectar.' }));
                    });
            });
        });
    }

    var formNueva = document.getElementById('form-nueva-propuesta');
    if (formNueva) {
        formNueva.addEventListener('submit', function (evento) {
            evento.preventDefault();
            var inputTitulo = document.getElementById('propuesta-titulo');
            var inputDesc = document.getElementById('propuesta-descripcion');
            var titulo = inputTitulo ? inputTitulo.value.trim() : '';
            var descripcion = inputDesc ? inputDesc.value.trim() : '';
            if (!titulo) {
                if (window.Swal) window.Swal.fire(opcionesSwal({ icon: 'warning', title: 'Título obligatorio', text: 'Escribe un título para la propuesta.' }));
                if (inputTitulo) inputTitulo.focus();
                return;
            }
            fetch(urlBase + '&recurso=crear_propuesta', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ titulo: titulo, descripcion: descripcion })
            })
                .then(function (respuesta) { return respuesta.json(); })
                .then(function (datos) {
                    if (datos.creado && window.Swal) {
                        window.Swal.fire(opcionesSwal({ title: 'Propuesta creada', text: datos.mensaje || '' }));
                        formNueva.reset();
                        cargarPropuestas();
                    } else if (datos.error && window.Swal) {
                        window.Swal.fire(opcionesSwal({ icon: 'error', title: 'Error', text: datos.error }));
                    }
                })
                .catch(function () {
                    if (window.Swal) window.Swal.fire(opcionesSwal({ icon: 'error', title: 'Error de conexión', text: 'No se pudo crear la propuesta. Comprueba tu conexión e intenta de nuevo.' }));
                });
        });
    }

    cargarPropuestas();
})();
