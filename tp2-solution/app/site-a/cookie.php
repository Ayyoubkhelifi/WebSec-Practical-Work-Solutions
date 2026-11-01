<?php
// TP 02 — Partie A, étapes 1 et 2 : cookie tp2_theme avec setcookie().
// - Étape 1 : lien « Créer » → cookie tp2_theme=clair, Path=/, Max-Age ~600 s,
//   SameSite=Lax, puis redirection vers la page de lecture.
// - Étape 2 : lecture via $_COOKIE + liens « Modifier » et « Supprimer »
//   (la suppression réutilise le même Path et date dans le passé).

// Attributs communs : la portée (path) doit être identique pour remplacer
// puis supprimer le même cookie.
$options = [
    'expires'  => time() + 600, // Max-Age équivalent ≈ 600 s
    'path'     => '/',
    'domain'   => '',           // hôte courant uniquement
    'secure'   => false,        // désactivé : HTTP local uniquement (à expliquer)
    'httponly' => false,        // HttpOnly testé sur la copie expérimentale
    'samesite' => 'Lax',
];

$action = $_GET['action'] ?? null;

if ($action === 'create') {
    setcookie('tp2_theme', 'clair', $options);
    // La page est rechargée : la lecture ($_COOKIE) n'a de sens qu'à la
    // requête SUIVANTE.
    header('Location: cookie.php');
    exit;
}

if ($action === 'update') {
    setcookie('tp2_theme', 'sombre', $options);
    header('Location: cookie.php');
    exit;
}

if ($action === 'delete') {
    // Suppression : même nom + même Path (même portée) + date passée
    $optionsSuppression = $options;
    $optionsSuppression['expires'] = time() - 3600;
    setcookie('tp2_theme', '', $optionsSuppression);
    header('Location: cookie.php');
    exit;
}

// Lecture ; le cookie « demandé » apparaît après rechargement uniquement
$theme = $_COOKIE['tp2_theme'] ?? null;
$echapper = static function (?string $v): string {
    return ($v === null || $v === '') ? '(absent)' : htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
};
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>TP 02 — Cookies</title>
</head>
<body>
    <h1>Partie A — Cookies HTTP</h1>

    <p>Valeur reçue par PHP via <code>$_COOKIE['tp2_theme']</code> :
       <strong><?= $echapper($theme) ?></strong></p>

    <p>Liens :</p>
    <ul>
        <li><a href="cookie.php?action=create">Créer (tp2_theme=clair)</a> — envoie Set-Cookie puis redirige vers la lecture</li>
        <li><a href="cookie.php?action=update">Modifier (tp2_theme=sombre)</a></li>
        <li><a href="cookie.php?action=delete">Supprimer</a> — même Path, date passée</li>
        <li><a href="cookie-httponly.php">Copie expérimentale avec HttpOnly (document.cookie)</a></li>
    </ul>

    <h2>Observations demandées (étape 3)</h2>
    <ol>
        <li>Réseau : la réponse de « Créer » porte <code>Set-Cookie:
            tp2_theme=clair; Max-Age=600; Path=/; SameSite=Lax</code>.</li>
        <li>La requête suivante (la redirection vers cookie.php) porte
            <code>Cookie: tp2_theme=clair</code>.</li>
        <li>Comparer à l'onglet Application → Stockage → Cookies.</li>
    </ol>

    <p><a href="http://127.0.0.1:8081/index.html">Site B (8081)</a></p>
</body>
</html>
