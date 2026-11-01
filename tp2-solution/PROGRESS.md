# TP 02 — Progression

Dernière mise à jour : toutes les étapes de la fiche (1 à 18) sont réalisées.

## Partie A — Cookies HTTP
- [x] Étape 1 : `site-a/cookie.php` + lien « Créer » — `tp2_theme=clair`, Path=/, Max-Age ~600 s, SameSite=Lax, redirection vers la lecture
- [x] Étape 2 : lecture `$_COOKIE` + liens « Modifier » et « Supprimer » (même Path + date passée)
- [x] Étape 3 : observations Set-Cookie / Cookie / onglet Stockage documentées
- [x] Étape 4 : copie expérimentale `site-a/cookie-httponly.php` (HttpOnly + `document.cookie`) ; discussion Secure / Domain / SameSite

## Partie B — Sessions PHP
- [x] Étape 5 : `site-a/login.php` (POST, identifiants fictifs `etudiant`/`tp2026`, sans BDD)
- [x] Étape 6 : `session.use_strict_mode` + cookie de session HttpOnly, SameSite=Lax, Secure=false (HTTP local), AVANT `session_start()`
- [x] Étape 7 : `session_regenerate_id(true)` + `$_SESSION['user']` ; `profile.php` protégé ; `logout.php` (session + cookie détruits)
- [x] Étape 8 : comparaison PHPSESSID / `$_SESSION` + accès anonyme vs authentifié documentés

## Partie C — Same-Origin Policy
- [x] Étape 9 : `site-b/index.html` → `fetch("http://127.0.0.1:8080/api.php?mode=none")` ; API répond JSON sans ACAO
- [x] Étape 10 : comparaison Console / Réseau (requête envoyée, lecture JS bloquée) documentée
- [x] Étape 11 : deux ports = deux origines distinctes vérifié (triplets notés)

## Partie D — CORS et OPTIONS
- [x] Étape 12 : `api.php` mode=allow, ACAO = `http://127.0.0.1:8081`
- [x] Étape 13 : `Access-Control-Allow-Methods: GET, OPTIONS` + `Access-Control-Allow-Headers: X-TP-Lab, Content-Type` ; fetch de B avec X-TP-Lab
- [x] Étape 14 : repérage du preflight OPTIONS (Origin, Access-Control-Request-Method, Access-Control-Request-Headers + en-têtes de réponse) documenté
- [x] Étape 15 : mode=wildcard (`*`) réservé aux données publiques + explication (pas pour données de compte)

## Partie E — Content Security Policy
- [x] Étape 16 : `site-a/csp.php` (script inline + /csp.js externe local + image distante) et `site-a/csp.js`
- [x] Étape 17 : comparaison sans CSP (`?csp=none`) / Report-Only (`?csp=report`) / politique appliquée (`?csp=enforce`)
- [x] Étape 18 : description Console blocage/exécution/signalement (ressource absente ≠ ressource bloquée)

## Questions (compte rendu dans `RESULT.md`)
- [x] A1–A4 (+ attributs Secure / Domain / SameSite)
- [x] B1–B4
- [x] C1–C2
- [x] D1–D4
- [x] E1–E3

## Vérification
- [x] Exécution réelle : les deux serveurs lancés avec PHP 8.5.12
  (`php -S 127.0.0.1:8080 -t site-a` / `php -S 127.0.0.1:8081 -t site-b`), puis
  tests curl (31/10/2026) :

| Test | Résultat observé |
|---|---|
| GET http://127.0.0.1:8081/index.html | 200 text/html (site B joignable) |
| GET /cookie.php?action=create | 302 + `Set-Cookie: tp2_theme=clair; Max-Age=600; path=/; SameSite=Lax` |
| GET /cookie.php (avec Cookie: tp2_theme=clair) | valeur lue via `$_COOKIE` |
| GET /cookie.php?action=delete | `Set-Cookie: tp2_theme=deleted; expires=01 Jan 1970 …; path=/; SameSite=Lax` (même Path, date passée) |
| GET /cookie-httponly.php?action=create | `Set-Cookie: tp2_httponly_demo=…; Max-Age=600; path=/; HttpOnly; SameSite=Lax` |
| GET /profile.php (anonyme) | page « Accès refusé » + lien vers login |
| POST /login.php (etudiant/tp2026) | 302 → profile.php |
| POST /login.php (identifiants erronés) | message « Identifiants incorrects » |
| Session : GET /login.php puis POST /login | PHPSESSID **régénéré** : `d807d110…` → `408c794c…` (session_regenerate_id(true) OK) |
| GET /profile.php (authentifié) | « Utilisateur authentifié : etudiant » |
| POST-like GET /logout.php | 302 → login.php, cookie PHPSESSID supprimé (même nom/path + date passée) ; profile.php de nouveau « Accès refusé » |
| GET /api.php?mode=none | 200 JSON **sans** `Access-Control-Allow-Origin` |
| GET /api.php?mode=allow avec `Origin: http://127.0.0.1:8081` | 200 + `ACAO: http://127.0.0.1:8081` |
| OPTIONS /api.php depuis B (`ACRM: GET`, `ACRH: x-tp-lab`) | 204 + `ACAO`, `ACAM: GET, OPTIONS`, `ACAH: X-TP-Lab, Content-Type` |
| OPTIONS /api.php depuis une origine non autorisée | 204 **sans** en-têtes CORS (le navigateur bloquerait le GET) |
| GET /api.php?mode=wildcard | 200 + `ACAO: *` (données publiques) |
| GET /csp.php?csp=none / report / enforce | aucun en-tête CSP / `Content-Security-Policy-Report-Only …` / `Content-Security-Policy …` |
| GET /csp.js | 200 `application/javascript` |

- [ ] Démonstration navigateur (Consol : script inline bloqué, `document.cookie`
  vide pour le cookie HttpOnly, erreur CORS console) à faire en binôme — les
  en-têtes déclencheurs sont déjà validés côté serveur.
