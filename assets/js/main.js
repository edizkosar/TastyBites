/**
 * TastyBites — Tiny site JavaScript.
 * - Sets the min date on inputs marked [data-min-today].
 * - Fades out flash alerts after 4 seconds.
 */
(function () {
    'use strict';

    function setMinDateOnDateInputs() {
        var inputs = document.querySelectorAll('input[data-min-today]');
        if (inputs.length === 0) { return; }
        var today = new Date();
        var yyyy = today.getFullYear();
        var mm   = String(today.getMonth() + 1).padStart(2, '0');
        var dd   = String(today.getDate()).padStart(2, '0');
        var iso  = yyyy + '-' + mm + '-' + dd;
        for (var i = 0; i < inputs.length; i++) {
            inputs[i].setAttribute('min', iso);
        }
    }

    function autoFadeAlerts() {
        // Only fade alerts that are inside .container > .alert
        // and not marked .alert-dismissible (which has its own close button)
        // and not marked .tb-no-fade.
        var alerts = document.querySelectorAll(
            '.container > .alert:not(.alert-dismissible):not(.tb-no-fade)'
        );
        if (alerts.length === 0) { return; }
        alerts.forEach(function (el) {
            setTimeout(function () {
                el.classList.add('tb-fade-out');
            }, 4000);
            setTimeout(function () {
                if (el.parentNode) {
                    el.parentNode.removeChild(el);
                }
            }, 4500);
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        setMinDateOnDateInputs();
        autoFadeAlerts();
    });
})();
