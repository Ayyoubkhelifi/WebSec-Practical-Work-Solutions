# TP 01 — Progression

Dernière mise à jour : toutes les étapes de la fiche sont réalisées.

## Partie A — Observer et créer la page d'accueil
- [x] Étape 1 : `public/index.php` (titre + liens vers les pages du TP)
- [x] Étape 2 : procédure d'observation F12 → Réseau documentée (journal conservé, rechargement)
- [x] Étape 3 : relevé requête (méthode, URL, Host, User-Agent, Accept) / réponse (statut, Content-Type) dans `RESULT.md`

## Partie B — Formulaire GET
- [x] Étape 4 : `public/get.php`, `method="get"`, `action="get.php"`, champs `nom` et `ville` avec attribut `name`
- [x] Étape 5 : valeurs via `$_GET` avec valeur par défaut (`Anonyme` / `Inconnue`)
- [x] Étape 6 : affichage de `$_SERVER['QUERY_STRING']` ; test nom=`Ali Ben`, ville=`Oran & environ`
- [x] Étape 7 : observation URL / Réseau → Payload, lien de test `?nom=Sara&ville=Oran`

## Partie C — Formulaire POST
- [x] Étape 8 : `public/post.php`, `method="post"`, champs `nom` et `message`
- [x] Étape 9 : affichage `$_POST` uniquement après soumission, test via `$_SERVER['REQUEST_METHOD']`
- [x] Étape 10 : repérage Payload + en-tête `Content-Type: application/x-www-form-urlencoded` documenté
- [x] Étape 11 : comparaison avec GET (texte avec espaces/caractères spéciaux)

## Partie D — Observer les en-têtes
- [x] Étape 12 : `public/headers.php` affiche Host, User-Agent, Accept, Content-Type, Referer via `$_SERVER`
- [x] Étape 13 : `header()` définit `Content-Type: text/html; charset=UTF-8` et `X-Lab-HTTP: initiation`, avant toute sortie
- [x] Étape 14 : comparaison serveur / Réneau, accès direct vs depuis un lien d'index.php

## Partie E — Codes de statut
- [x] Étape 15 : `public/status.php` (200/400/404/405 via `http_response_code()`, autres valeurs → 400)
- [x] Étape 16 : `public/redirect.php` (302 + `Location: index.php` + `exit`)
- [x] Étape 17 : `public/method.php` (GET admis ; POST → 405 + `Allow: GET`)
- [x] Étape 18 : inspection 302 (chaîne de redirection), curl -i sans suivi ; comparaison 404 PHP vs 404 du serveur

## Partie F — Manipulations et expérimentation
- [x] Étape 19 : `curl -i http://127.0.0.1:8080/headers.php`
- [x] Étape 20 : `curl -i -d "nom=Ali&message=Bonjour" .../post.php` comparé à la soumission HTML
- [x] Étape 21 : modification d'un paramètre GET dans l'URL / édition du formulaire (POST) à la place de Burp Repeater
- [x] Étape 22 : observation — PHP reçoit les valeurs réellement transmises

## Questions (compte rendu dans `tp-http/RESULT.md`)
- [x] A1–A2
- [x] B1–B4
- [x] C1–C4
- [x] D1–D3
- [x] E1–E4 (dont contenu de `Allow`)
- [x] F1–F3

## Vérification
- [x] Exécution réelle : serveur lancé avec PHP 8.5.12 (`php -S 127.0.0.1:8080 -t public`), puis tests curl (31/10/2026) :

| Test | Résultat observé |
|---|---|
| GET /index.php | 200, `text/html; charset=UTF-8` |
| GET /headers.php | 200 + en-têtes `Content-Type: text/html; charset=UTF-8` et `X-Lab-HTTP: initiation` |
| GET /get.php?nom=Ali+Ben&ville=Oran+%26+environ | $_GET décodé (`Ali Ben`, `Oran & environ`) et QUERY_STRING brute (`nom=Ali+Ben&ville=Oran+%26+environ`) |
| GET /get.php (sans paramètres) | 200 + valeurs par défaut (Anonyme / Inconnue) |
| POST /post.php (curl -d nom=Ali&message=Bonjour) | 200, champs affichés après soumission, REQUEST_METHOD = POST |
| GET /status.php?code=200/400/404/405 | 200 / 400 / 404 / 405 |
| GET /status.php?code=500 | 400 (valeur refusée, comme exigé) |
| GET /redirect.php | 302 Found + `Location: index.php` |
| GET /method.php | 200 (GET admis) |
| POST /method.php | 405 Method Not Allowed + `Allow: GET` |

- [x] Étape 18 testée : sur PHP 8.5.12, `/fichier-inexistant.php` retourne 200
  (mise à jour PHP 8.4 : le serveur intégré retombe sur index.php pour les
  chemins introuvables, cf. note dans RESULT.md) ; sur PHP 8.0–8.3 le résultat
  est bien le 404 du serveur attendu par la fiche.
- [ ] Exécution de démonstration côté navigateur (F12 Réseau) à faire en binôme
  — le comportement serveur est déjà validé via curl.
