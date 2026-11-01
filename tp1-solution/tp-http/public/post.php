<?php
// TP 01 — Partie C : formulaire POST (étapes 8 à 11).
// - Étape 8 : formulaire method="post" avec un champ nom et un champ message.
// - Étape 9 : affichage des champs reçus avec $_POST UNIQUEMENT après
//   soumission ; l'état est déterminé via $_SERVER['REQUEST_METHOD'].

// Identification du test : méthode de la requête courante
$methode = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$apresSoumission = ($methode === 'POST');

$nom      = $apresSoumission ? ($_POST['nom'] ?? '') : '';
$message  = $apresSoumission ? ($_POST['message'] ?? '') : '';
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>TP 01 — POST</title>
</head>
<body>
    <h1>Partie C — Formulaire POST</h1>

    <!-- Étape 8 -->
    <form method="post" action="post.php">
        <p>
            <label>Nom : <input type="text" name="nom"></label>
        </p>
        <p>
            <label>Message : <textarea name="message" rows="3" cols="40"></textarea></label>
        </p>
        <p><button type="submit">Envoyer (POST)</button></p>
    </form>

    <?php if ($apresSoumission): ?>
    <h2>Champs reçus via $_POST (seulement après soumission)</h2>
    <ul>
        <li>nom = <code><?= htmlspecialchars($nom, ENT_QUOTES, 'UTF-8') ?></code></li>
        <li>message = <code><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></code></li>
    </ul>
    <?php else: ?>
    <h2>Aucune soumission</h2>
    <p>Les champs ne sont affichés qu'après la soumission du formulaire.</p>
    <?php endif; ?>

    <h2>Identification du test (étape 9)</h2>
    <p><code>$_SERVER['REQUEST_METHOD']</code> vaut
       <code><?= htmlspecialchars($methode, ENT_QUOTES, 'UTF-8') ?></code> pour cette requête.</p>

    <h2>Tests demandés</h2>
    <ul>
        <li>Étape 10 : dans l'onglet Réseau → Payload (requête POST), repérer les
            données du formulaire et l'en-tête Content-Type de la requête
            (<code>application/x-www-form-urlencoded</code>).</li>
        <li>Étape 11 : saisir un texte incluant des espaces et des caractères
            spéciaux (ex. : « Oran &amp; environ ! ») puis comparer avec GET : ici le
            corps POST n'apparaît pas dans l'URL.</li>
    </ul>

    <p><a href="index.php">← Accueil</a></p>
</body>
</html>
