<?php
// TP 01 — Partie B : formulaire GET (étapes 4 à 7).
// - Étape 4 : formulaire method="get" action="get.php", deux champs nom et
//   ville, chacun avec un attribut name.
// - Étape 5 : valeurs lues dans $_GET avec valeur par défaut si absentes.
// - Étape 6 : affichage de $_SERVER['QUERY_STRING'].

// Étape 5 : valeur par défaut si le champ est absent
$nom   = $_GET['nom']   ?? 'Anonyme';
$ville = $_GET['ville'] ?? 'Inconnue';

// Étape 6 : chaîne brute telle que reçue (ex. : nom=Ali+Ben&ville=Oran+%26+environ)
$chaineBrute = $_SERVER['QUERY_STRING'] ?? '';
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>TP 01 — GET</title>
</head>
<body>
    <h1>Partie B — Formulaire GET</h1>

    <!-- Étape 4 : les deux champs portent un attribut name, indispensable -->
    <form method="get" action="get.php">
        <p>
            <label>Nom : <input type="text" name="nom"></label>
        </p>
        <p>
            <label>Ville : <input type="text" name="ville"></label>
        </p>
        <p><button type="submit">Envoyer (GET)</button></p>
    </form>

    <h2>Valeurs récupérées via $_GET (avec valeur par défaut)</h2>
    <ul>
        <li>nom = <code><?= htmlspecialchars($nom, ENT_QUOTES, 'UTF-8') ?></code></li>
        <li>ville = <code><?= htmlspecialchars($ville, ENT_QUOTES, 'UTF-8') ?></code></li>
    </ul>

    <h2>$_SERVER['QUERY_STRING'] (chaîne brute)</h2>
    <p><code><?= htmlspecialchars($chaineBrute === '' ? '(query string vide)' : $chaineBrute, ENT_QUOTES, 'UTF-8') ?></code></p>

    <h2>Test demandé (étapes 6 à 7)</h2>
    <ul>
        <li>Tester le formulaire avec nom = « Ali Ben » et ville = « Oran &amp; environ ».</li>
        <li>Observer l'URL du navigateur et l'onglet Réseau → Payload/Paramètres.</li>
        <li>Tester l'URL directe :
            <a href="get.php?nom=Sara&amp;ville=Oran">get.php?nom=Sara&amp;ville=Oran</a></li>
    </ul>

    <p><a href="index.php">← Accueil</a></p>
</body>
</html>
