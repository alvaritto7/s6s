/**
 * dashboard.js — Alertas de stock: popup solo una vez por sesión (la lista en página siempre visible).
 */

(function () {
    'use strict';

    var cuerpo = document.body;
    if (!cuerpo) return;

    var cantidad = parseInt(cuerpo.getAttribute('data-alertas-cantidad') || '0', 10);
    var puedeAdmin = cuerpo.getAttribute('data-puede-admin') === '1';
    if (cantidad < 1 || !puedeAdmin || typeof window.Swal === 'undefined') return;

    var claveSesion = 's6s_alerta_stock_visto';
    if (sessionStorage.getItem(claveSesion)) return;
    sessionStorage.setItem(claveSesion, '1');

    var isotipo = cuerpo.getAttribute('data-isotipo') || '';
    var opciones = {
        icon: 'warning',
        title: 'Stock bajo umbral',
        text: 'Hay ' + cantidad + ' producto(s) con stock en o por debajo del umbral crítico. Revisa la sección de alertas debajo.',
        imageUrl: isotipo || undefined
    };
    window.Swal.fire(opciones);
})();
