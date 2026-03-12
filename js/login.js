/**
 * login.js — Validación del formulario de login (sin una línea de JS en HTML/PHP).
 * Comprueba campos vacíos antes de enviar; el servidor sigue validando.
 */

(function () {
    'use strict';

    const form = document.getElementById('formulario-login');
    if (!form) return;

    form.addEventListener('submit', function (evento) {
        const email = form.querySelector('#email');
        const password = form.querySelector('#password');

        const emailVal = (email && email.value) ? email.value.trim() : '';
        const passVal = (password && password.value) ? password.value : '';

        if (!emailVal || !passVal) {
            evento.preventDefault();
            if (emailVal === '' && email) {
                email.focus();
                email.setCustomValidity('Introduce tu email');
            } else if (email) {
                email.setCustomValidity('');
            }
            if (passVal === '' && password) {
                password.setCustomValidity('Introduce tu contraseña');
                if (emailVal !== '') password.focus();
            } else if (password) {
                password.setCustomValidity('');
            }
            return;
        }

        if (email) email.setCustomValidity('');
        if (password) password.setCustomValidity('');
    });
})();
