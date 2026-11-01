<?php
// TP 01 — Partie E, étape 17 : method.php accessible en GET, mais renvoyant
// 405 et Allow: GET pour une requête POST.

$methode = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($methode === 'POST') {
    // La ressource existe mais la méthode n'est pas permise → 405
    http_response_code(405);
    // Allow indique la ou les méthodes admises sur cette ressource
    header('Allow: GET');
    ?>
    <!doctype html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title>TP 01 — 405</title>
    </head>
    <body>
        <h1>405 Method Not Allowed</h1>
        <p>La ressource method.php n'accepte que GET pour cette requête
           (en-tête Allow: GET).</p>
        <p><a href="index.php">← Accueil</a></p>
    </body>
    </html>
    <?php
    exit;
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>TP 01 — method.php</title>
</head>
<body>
    <h1>Partie E — method.php</h1>
    <p>Requête reçue avec la méthode <code><?= htmlspecialchars($methode, ENT_QUOTES, 'UTF-8') ?></code> :
       accès en GET autorisé.</p>

    <h2>Test demandé</h2>
    <ul>
        <li>Requête POST vers method.php → réponse 405 avec en-tête
            <code>Allow: GET</code> (cible :
            <code>POST /method.php → 405 + Allow: GET</code>).</li>
        <li>Avec curl : <code>curl -i -X POST http://127.0.0.1:8080/method.php</code></li>
    </ul>

    <p><a href="index.php">← Accueil</a></p>
</body>
</html>
