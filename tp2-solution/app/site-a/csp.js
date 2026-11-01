// TP 02 — Partie E, étape 16 : script externe LOCAL (~site-a/csp.js).
// Servi depuis la même origine que csp.php : il reste autorisé par
// script-src 'self'. En mode enforce, c'est lui qui doit s'exécuter, tandis
// que le script inline est bloqué.
document.addEventListener('DOMContentLoaded', function () {
    var cible = document.getElementById('externe');
    if (cible) {
        cible.textContent = 'Script externe /csp.js exécuté.';
    } else {
        console.log('Script externe /csp.js exécuté.');
    }
    console.log('csp.js chargé');
});
