<?php
// TP 01 — Partie D : observer les en-têtes (étapes 12 et 13).
//
// Étape 13 : définir d'abord deux en-têtes de RÉPONSE avec header(), placés
// AVANT toute sortie HTML :
//   - Content-Type: text/html; charset=UTF-8
//   - X-Lab-HTTP: initiation
header('Content-Type: text/html; charset=UTF-8');
header('X-Lab-HTTP: initiation');

// Étape 12 : lire côté RÊQUETE les entrées pertinentes de $_SERVER.
// - Host        → $_SERVER['HTTP_HOST']
// - User-Agent  → $_SERVER['HTTP_USER_AGENT']
// - Accept      → $_SERVER['HTTP_ACCEPT']
// - Content-Type → $_SERVER['CONTENT_TYPE'] (nom SANS préfixe HTTP_)
// - Referer     → $_SERVER['HTTP_REFERER']

$afficher = static function (?string $valeur): string {
    return ($valeur === null || $valeur === '')
        ? '(absent)'
        : htmlspecialchars($valeur, ENT_QUOTES, 'UTF-8');
};

$host        = $_SERVER['HTTP_HOST']        ?? null;
$userAgent   = $_SERVER['HTTP_USER_AGENT']  ?? null;
$accept      = $_SERVER['HTTP_ACCEPT']      ?? null;
$contentType = $_SERVER['CONTENT_TYPE']     ?? null;
$referer     = $_SERVER['HTTP_REFERER']     ?? null;
?>
<!doctype html>
<html lang="fr">
<head>
    <title>TP 01 — En-têtes</title>
</head>
<body>
    <h1>Partie D — Observer les en-têtes</h1>

    <h2>En-têtes de la requête (lus dans $_SERVER par le serveur, étape 12)</h2>
    <ul>
        <li>Host : <code><?= $afficher($host) ?></code></li>
        <li>User-Agent : <code><?= $afficher($userAgent) ?></code></li>
        <li>Accept : <code><?= $afficher($accept) ?></code></li>
        <li>Content-Type : <code><?= $afficher($contentType) ?></code></li>
        <li>Referer : <code><?= $afficher($referer) ?></code></li>
    </ul>

    <h2>En-têtes de la réponse (définis par header() avant toute sortie, étape 13)</h2>
    <ul>
        <li>Content-Type: text/html; charset=UTF-8</li>
        <li>X-Lab-HTTP: initiation</li>
    </ul>

    <h2>Test demandé (étape 14)</h2>
    <ol>
        <li>Ouvrir F12 → Réseau sur cette page : le serveur affiche Host /
            User-Agent / Accept, le navigateur montre les mêmes valeurs côté
            requête.</li>
        <li>Accéder à la page <strong>directement</strong> (barre d'adresse) :
            Referer est absent.</li>
        <li>Accéder via le lien suivant depuis
            <a href="index.php">index.php</a> : Referer = URL d'index.php ; puis
            revenir ici depuis ce lien :
            <a href="headers.php">headers.php</a></li>
    </ol>

    <p><a href="index.php">← Accueil</a></p>
</body>
</html>
