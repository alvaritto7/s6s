// Aviso opcional de stock (SweetAlert, una vez por sesión si aplica).

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
    window.Swal.fire({
        title: 'Stock bajo umbral',
        text: 'Hay ' + cantidad + ' producto(s) con stock en o por debajo del umbral crítico. Revisa la sección de alertas en el panel.',
        imageUrl: isotipo || undefined,
        confirmButtonColor: '#00A3FF',
        background: '#11141d',
        color: '#f5f5f5'
    });
})();
