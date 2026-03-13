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

    function renderizarPropuesta(propuesta, yaVotado) {
        var id = propuesta.id || '';
        var titulo = propuesta.titulo || propuesta.nombre || 'Sin título';
        var descripcion = propuesta.descripcion || '';
        var votos = typeof propuesta.votos === 'number' ? propuesta.votos : 0;
        var autor = propuesta.autor_nombre || '';
        var fecha = propuesta.fecha_creacion || '';

        var item = document.createElement('article');
        item.className = 'item-propuesta';
        item.setAttribute('role', 'listitem');
        item.setAttribute('data-id', String(id));

        var html = '<h3 class="titulo-propuesta">' + escapeHtml(titulo) + '</h3>';
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
            html += '<button type="button" class="boton boton-primario boton-votar" data-propuesta-id="' + escapeHtml(String(id)) + '">Votar</button>';
        } else {
            html += '<button type="button" class="boton boton-votado" disabled>Votado</button>';
        }
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
                    // Actualizar tarjeta en caliente: votos + estado del botón
                    var card = boton && boton.closest ? boton.closest('.item-propuesta') : null;
                    if (card) {
                        var votosEl = card.querySelector('.votos-numero');
                        var actual = votosEl ? parseInt(votosEl.textContent || '0', 10) : 0;
                        if (votosEl) votosEl.textContent = String(actual + 1);
                        boton.textContent = 'Votado';
                        boton.classList.remove('boton-primario');
                        boton.classList.add('boton-votado');
                        boton.disabled = true;
                        boton.removeAttribute('data-propuesta-id');
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
