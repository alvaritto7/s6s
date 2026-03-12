/**
 * nav.js — Menú móvil: toggle del nav al pulsar "Menú".
 */
(function () {
    'use strict';
    var botonMenu = document.getElementById('btn-menu-mobile');
    var nav = document.getElementById('nav-principal');
    if (!botonMenu || !nav) return;
    botonMenu.addEventListener('click', function () {
        var abierto = nav.classList.toggle('nav-abierto');
        botonMenu.setAttribute('aria-expanded', abierto ? 'true' : 'false');
        botonMenu.textContent = abierto ? 'Cerrar' : 'Menú';
    });
})();
