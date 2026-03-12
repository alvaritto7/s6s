/**
 * registro.js — Validación básica del formulario de registro (coincidencia de contraseñas, etc.).
 * Sin código JS dentro de PHP/HTML; toda la lógica aquí.
 */
(function () {
    'use strict';

    var formulario = document.getElementById('formulario-registro');
    if (!formulario) return;

    formulario.addEventListener('submit', function (evento) {
        var password = document.getElementById('password');
        var password2 = document.getElementById('password2');
        if (!password || !password2) return;

        if (password.value !== password2.value) {
            evento.preventDefault();
            password2.setCustomValidity('Las contraseñas no coinciden');
            return;
        }
        password2.setCustomValidity('');
    });

    document.getElementById('password2') && document.getElementById('password2').addEventListener('input', function () {
        this.setCustomValidity('');
    });
})();
