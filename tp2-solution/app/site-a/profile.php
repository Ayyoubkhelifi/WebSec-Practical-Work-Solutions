<?php
// TP 02 — Partie B, étapes 7 et 8 : page protégée.
// Mêmes réglages de cookie/strict mode que login.php, avant session_start().
ini_set('session.use_strict_mode', '1');

session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => '',
    'secure'   => false,
    'httponly' => true,
    'samesite' => 'Lax',
]);

session_start();

$utilisateur = $_SESSION['user'] ?? null;
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>TP 02 — Profil</title>
</head>
<body>
<?php if ($utilisateur === null): ?>
    <!-- Étape 8 : observer un accès anonyme -->
    <h1>Accès refusé</h1>
    <p>Vous n'êtes pas authentifié : aucun <code>$_SESSION['user']</code>.</p>
    <p><a href="login.php">Se connecter</a></p><?php else: ?>
    <!-- Étape 8 : accès authentifié -->
    <h1>Profil</h1>
    <p>Utilisateur authentifié : <strong><?= htmlspecialchars($utilisateur, ENT_QUOTES, 'UTF-8') ?></strong></p>
    <p>Contenu côté serveur de <code>$_SESSION</code> : <?= htmlspecialchars(json_encode($_SESSION), ENT_QUOTES, 'UTF-8') ?></p>
    <p>Étape 8 : comparer ce contenu avec le cookie <code>PHPSESSID</code> du navigateur —
       le cookie ne contient que l'identifiant, les données restent côté serveur.</p>
    <p><a href="logout.php">Se déconnecter</a></p>
<?php endif; ?>
</body>
</html>
