<?php
// TP 02 — Partie A, étape 4 : copie expérimentale avec HttpOnly activé.
// Le script JS essaie de lire document.cookie : le cookie HttpOnly doit en
// être absent, tout en continuant d'être envoyé par le navigateur.

if (($_GET['action'] ?? null) === 'create') {
    setcookie('tp2_httponly_demo', 'valeur-secret-tp2', [
        'expires'  => time() + 600,
        'path'     => '/',
        'secure'   => false,
        'httponly' => true,   // ← le point de la démonstration
        'samesite' => 'Lax',
    ]);
    header('Location: cookie-httponly.php');
    exit;
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>TP 02 — HttpOnly</title>
</head>
<body>
    <h1>Copie expérimentale — HttpOnly</h1>

    <p><a href="cookie-httponly.php?action=create">Créer le cookie HttpOnly</a></p>

    <p>Ce que JavaScript voit dans <code>document.cookie</code> :</p>
    <pre id="doc-cookie">(lecture en cours par le script…)</pre>

    <p><code>$_COOKIE côté serveur</code> (envoyé par le navigateur malgré HttpOnly) :</p>
    <pre id="serveur"></pre>

    <p>Attendu : le navigateur continue d'envoyer <code>tp2_httponly_demo</code>
       dans l'en-tête <code>Cookie</code> (visible côté serveur et dans
       Réseau), mais <code>document.cookie</code> ne le montre pas.</p>

    <script>
        // Étape 4 : tester document.cookie en présence de HttpOnly.
        var vu = document.cookie;
        document.getElementById('doc-cookie').textContent = vu === '' ? '(vide)' : vu;
    </script>

    <p><?php
        echo htmlspecialchars(
            '$_COOKIE[\'tp2_httponly_demo\'] = '
            . ($_COOKIE['tp2_httponly_demo'] ?? '(absent — cliquer sur Créer puis recharger)'),
            ENT_QUOTES,
            'UTF-8'
        );
    ?></p>

    <p><a href="cookie.php">← cookie.php</a></p>
</body>
</html>
