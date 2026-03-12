/**
 * admin.js — Gráficos con Chart.js (productos por categoría, pedidos por estado).
 * Lee los datos desde un bloque JSON en la página; sin datos embebidos en el script.
 */
(function () {
    'use strict';

    var bloque = document.getElementById('datos-graficos');
    if (!bloque || !bloque.textContent) return;

    var datos;
    try {
        datos = JSON.parse(bloque.textContent);
    } catch (error) {
        return;
    }

    var coloresAcento = ['#00A3FF', '#0066aa', '#0088cc', '#004466', '#00c4ff'];
    var coloresEstados = ['#00A3FF', '#B0B0B0', '#22aa22', '#aa2222', '#ffaa00'];

    // Gráfico: productos por categoría
    var canvasCat = document.getElementById('grafico-categorias');
    if (canvasCat && datos.categorias) {
        var labelsCat = Object.keys(datos.categorias);
        var valuesCat = Object.values(datos.categorias);
        new Chart(canvasCat.getContext('2d'), {
            type: 'bar',
            data: {
                labels: labelsCat,
                datasets: [{
                    label: 'Productos por categoría',
                    data: valuesCat,
                    backgroundColor: coloresAcento.slice(0, labelsCat.length)
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }

    // Gráfico: pedidos por estado
    var canvasEst = document.getElementById('grafico-estados');
    if (canvasEst && datos.estados) {
        var labelsEst = Object.keys(datos.estados);
        var valuesEst = Object.values(datos.estados);
        new Chart(canvasEst.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: labelsEst,
                datasets: [{
                    data: valuesEst,
                    backgroundColor: coloresEstados.slice(0, labelsEst.length)
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }
})();
