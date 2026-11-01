<?php
// TP 01 — Partie A, étape 1 : page d'accueil avec un titre et des liens
// vers les différentes pages du TP.
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>TP 01 — HTTP</title>
</head>
<body>
    <h1>TP 01 — Découvrir et expérimenter HTTP</h1>
    <p>Page d'accueil du TP. F12 → Réseau => recharger la page (étape 2),
       relever la méthode, l'URL, Host, User-Agent, Accept ; côté réponse,
       le statut et Content-Type (étape 3).</p>

    <ul>
        <li><a href="get.php">Partie B — Formulaire GET (get.php)</a></li>
        <li><a href="post.php">Partie C — Formulaire POST (post.php)</a></li>
        <li><a href="headers.php">Partie D — En-têtes HTTP (headers.php)</a></li>
        <li><a href="status.php?code=200">Partie E — status.php?code=200</a></li>
        <li><a href="status.php?code=400">status.php?code=400</a></li>
        <li><a href="status.php?code=404">status.php?code=404</a></li>
        <li><a href="status.php?code=405">status.php?code=405</a></li>
        <li><a href="redirect.php">redirect.php (302 → index.php)</a></li>
        <li><a href="method.php">Partie E — method.php (GET admis, POST refusé)</a></li>
        <li><a href="fichier-inexistant.php">fichier-inexistant.php (404 du serveur)</a></li>
    </ul>
</body>
</html>
