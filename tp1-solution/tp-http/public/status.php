<?php
// TP 01 — Partie E, étape 15 : produire les codes de statut.
// Accepte 200, 400, 404 et 405 et utilise http_response_code() pour renvoyer
// le statut demandé. Refuse les autres valeurs avec 400.

$codesAutorises = [200, 400, 404, 405];

$code = isset($_GET['code']) ? (int) $_GET['code'] : null;

if ($code === null || !in_array($code, $codesAutorises, true)) {
    // Statut demandé absent ou non autorisé → 400 Bad Request
    http_response_code(400);
    $statut = 400;
    $message = 'Valeur de code absente ou non autorisée. Valeurs admises : 200, 400, 404, 405.';
} else {
    http_response_code($code);
    $statut = $code;
    $message = 'Statut demandé renvoyé par http_response_code().';
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>TP 01 — Statuts</title>
</head>
<body>
    <h1>Partie E — Codes de statut</h1>
    <p><code>http_response_code(<?= $statut ?>)</code> a été appelé.</p>
    <p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>

    <ul>
        <li><a href="status.php?code=200">status.php?code=200</a> → 200 OK (cible)</li>
        <li><a href="status.php?code=400">status.php?code=400</a> → 400 Bad Request</li>
        <li><a href="status.php?code=404">status.php?code=404</a> → 404 Not Found</li>
        <li><a href="status.php?code=405">status.php?code=405</a> → 405 Method Not Allowed</li>
        <li><a href="status.php?code=500">status.php?code=500</a> → refusé → 400</li>
    </ul>

    <p><a href="index.php">← Accueil</a></p>
</body>
</html>
