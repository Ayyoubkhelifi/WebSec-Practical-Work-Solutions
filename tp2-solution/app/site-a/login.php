<?php
// TP 02 — Partie B, étapes 5 à 7 : session PHP.
//
// Étape 6 : AVANT session_start() —
//   - session.use_strict_mode activé,
//   - cookie de session : HttpOnly + SameSite=Lax,
//   - Secure désactivé uniquement parce que le TP tourne en HTTP local.
ini_set('session.use_strict_mode', '1');

session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => '',
    'secure'   => false,   // à activer (true) dès qu'un HTTPS réel est utilisé
    'httponly' => true,
    'samesite' => 'Lax',
]);

session_start();

// Étape 5 : identifiants fictifs locaux (aucune base de données)
$identifiantAttendu = 'etudiant';
$motDePasseAttendu  = 'tp2026';

$erreur = null;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $identifiant = $_POST['identifiant'] ?? '';
    $motDePasse  = $_POST['mot_de_passe'] ?? '';

    if (hash_equals($identifiantAttendu, $identifiant) && hash_equals($motDePasseAttendu, $motDePasse)) {
        // Étape 7 : régénérer l'identifiant de session après une identification correcte,
        // puis mémoriser l'utilisateur en $_SESSION.
        session_regenerate_id(true);
        $_SESSION['user'] = $identifiant;

        header('Location: profile.php');
        exit;
    }

    $erreur = 'Identifiants incorrects.';
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>TP 02 — Connexion</title>
</head>
<body>
    <h1>Partie B — Connexion (session PHP)</h1>

    <?php if ($erreur !== null): ?>
        <p style="color: darkred;"><strong><?= htmlspecialchars($erreur, ENT_QUOTES, 'UTF-8') ?></strong></p>
    <?php endif; ?>

    <!-- Étape 5 : formulaire POST -->
    <form method="post" action="login.php">
        <p><label>Identifiant : <input type="text" name="identifiant"></label></p>
        <p><label>Mot de passe : <input type="password" name="mot_de_passe"></label></p>
        <p><button type="submit">Se connecter</button></p>
    </form>

    <p>(Identifiants de test : <code>etudiant</code> / <code>tp2026</code>)</p>

    <p>Étape 8 : comparer le cookie <code>PHPSESSID</code> visible dans
       Application → Cookies avec les données de <code>$_SESSION</code>
       présentées dans profile.php.</p>

    <p><a href="profile.php">Aller à profile.php</a></p>
</body>
</html>
