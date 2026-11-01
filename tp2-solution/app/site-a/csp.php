<?php
// TP 02 — Partie E, étapes 16 et 17 : page CSP.
//
// La page contient : un script inline, un script externe local /csp.js et
// une image distante. Trois modes de comparaison :
//   csp.php?csp=none     (défaut) : pas d'en-tête CSP
//   csp.php?csp=report   : Content-Security-Policy-Report-Only (signale sans bloquer)
//   csp.php?csp=enforce  : Content-Security-Policy (bloque réellement)

$politique = "default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self'";

$mode = $_GET['csp'] ?? 'none';

if ($mode === 'report') {
    // Étape 17 : Report-Only — la politique est signalée, rien n'est bloqué
    header('Content-Security-Policy-Report-Only: ' . $politique);
} elseif ($mode === 'enforce') {
    // Étape 17 : la politique s'applique réellement
    header('Content-Security-Policy: ' . $politique);
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>TP 02 — CSP</title>
</head>
<body>
    <h1>Partie E — Content Security Policy</h1>

    <p>Mode demandé : <code><?= htmlspecialchars($mode) ?></code>
       — tester aussi <code>csp.php?csp=report</code> et <code>csp.php?csp=enforce</code>.</p>

    <p id="inline">…</p>
    <p id="externe">…</p>

    <p><img src="https://picsum.photos/200" alt="image distante" width="200" height="130"></p>

    <p>Étape 18 : noter dans la Console ce qui est bloqué (erreur CSP), ce qui
       s'exécute, ce qui n'est que signalé par Report-Only. Ne pas confondre
       une ressource absente (404) et une ressource bloquée par la CSP.</p>

    <script>
        // Étape 16 : script INLINE — bloqué par script-src 'self'
        // (sans unsafe-inline) dès que la politique s'applique.
        document.getElementById('inline').textContent =
            'Script inline exécuté.'; // ne s'affichera pas en mode enforce
    </script>

    <!-- Étape 16 : script externe LOCAL — autorisé par script-src 'self' -->
    <script src="/csp.js"></script>
</body>
</html>
