/**
 * nav.js — Menú móvil: toggle del nav. Cabecera sticky que se reduce al hacer scroll.
 */
(function () {
    'use strict';
    var cabecera = document.querySelector('.cabecera-dashboard');
    var botonMenu = document.getElementById('btn-menu-mobile');
    var nav = document.getElementById('nav-principal');

    if (botonMenu && nav) {
        botonMenu.addEventListener('click', function () {
            var abierto = nav.classList.toggle('nav-abierto');
            botonMenu.setAttribute('aria-expanded', abierto ? 'true' : 'false');
            botonMenu.textContent = abierto ? 'Cerrar' : 'Menú';
        });
    }

    if (cabecera) {
        var ticking = false;
        function umbralesScroll() {
            var h = window.innerHeight;
            return {
                entrar: Math.max(80, Math.floor(h * 0.22)),
                salir: Math.max(40, Math.floor(h * 0.10))
            };
        }
        function actualizarCabecera() {
            var umbrales = umbralesScroll();
            var y = window.scrollY;
            if (y > umbrales.entrar) {
                cabecera.classList.add('cabecera-reducida');
            } else if (y < umbrales.salir) {
                cabecera.classList.remove('cabecera-reducida');
            }
            ticking = false;
        }
        function onScroll() {
            if (!ticking) {
                requestAnimationFrame(actualizarCabecera);
                ticking = true;
            }
        }
        window.addEventListener('scroll', onScroll, { passive: true });
        window.addEventListener('resize', function () {
            actualizarCabecera();
        });
        actualizarCabecera();
    }
})();
