<?php
// TP 02 — Partie B, étape 7 : déconnexion — détruit la session et le cookie.
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

// Vider les données de session
$_SESSION = [];

// Si le client possède le cookie de session, supprimer côté navigateur :
// même nom, même Path, date expirée dans le passé.
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), // PHPSESSID
        '',
        [
            'expires'  => time() - 3600,
            'path'     => $params['path'],
            'domain'   => $params['domain'],
            'secure'   => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => $params['samesite'] ?? 'Lax',
        ]
    );
}

session_destroy();

header('Location: login.php');
exit;
