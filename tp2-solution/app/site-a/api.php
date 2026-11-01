<?php
// TP 02 — Parties C et D : API JSON du site A avec trois modes.
// - mode=none     : répond en JSON SANS Access-Control-Allow-Origin (étape 9)
// - mode=allow    : ACAO = http://127.0.0.1:8081 uniquement  (étape 12)
// - mode=wildcard : ACAO = * pour des données publiques        (étape 15)
// Préflight OPTIONS traité pour le mode allow (étapes 13–14).

$origineAutorisee  = 'http://127.0.0.1:8081';
$origineRecue      = $_SERVER['HTTP_ORIGIN'] ?? '';
$methode           = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$mode              = $_GET['mode'] ?? 'none';

// Étape 14 : réponse au preflight — le navigateur envoie OPTIONS avec
// Origin, Access-Control-Request-Method et Access-Control-Request-Headers.
// Le serveur répond avec les en-têtes correspondants.
if ($methode === 'OPTIONS') {
    if ($origineRecue === $origineAutorisee) {
        header('Access-Control-Allow-Origin: ' . $origineAutorisee);
        // Étape 13 : méthodes et en-têtes explicitement autorisés
        header('Access-Control-Allow-Methods: GET, OPTIONS');
        header('Access-Control-Allow-Headers: X-TP-Lab, Content-Type');
    }
    // Réponse minimaliste au preflight (pas de corps utile)
    http_response_code(204);
    exit;
}

// Étape 9/12/15 : réponse réelle
header('Content-Type: application/json');

switch ($mode) {
    case 'none':
        // Aucun en-tête CORS : le navigateur enverra la requête (CORS ne
        // bloque pas l'envoi d'une requête simple) mais JavaScript ne pourra
        // PAS lire la réponse à cause de la SOP.
        http_response_code(200);
        echo json_encode([
            'mode'     => 'none',
            'api'      => 'site-a',
            'contenu'  => 'réponse JSON sans Access-Control-Allow-Origin',
            'public'   => false,
        ], JSON_UNESCAPED_UNICODE);
        break;

    case 'allow':
        if ($origineRecue === $origineAutorisee) {
            header('Access-Control-Allow-Origin: ' . $origineAutorisee);
        }
        http_response_code(200);
        echo json_encode([
            'mode'     => 'allow',
            'api'      => 'site-a',
            'contenu'  => 'réponse JSON réservée à l\'origine ' . $origineAutorisee,
            'public'   => false,
        ], JSON_UNESCAPED_UNICODE);
        break;

    case 'wildcard':
        // Étape 15 : wildcard — uniquement des données publiques.
        header('Access-Control-Allow-Origin: *');
        http_response_code(200);
        echo json_encode([
            'mode'     => 'wildcard',
            'api'      => 'site-a',
            'contenu'  => 'données publiques accessibles à toutes les origines',
            'public'   => true,
        ], JSON_UNESCAPED_UNICODE);
        break;

    default:
        http_response_code(400);
        echo json_encode(['erreur' => 'mode inconnu'], JSON_UNESCAPED_UNICODE);
}
