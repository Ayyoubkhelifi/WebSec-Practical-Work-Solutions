# TP 02 — Guide des questions : réponse et marche à suivre détaillée pour chacune

Chaque entrée indique : le **type** (pratique sur PC / théorique), la
**marche à suivre** (fichiers à lancer, commandes exactes, actions navigateur),
l'**observation attendue** (déjà vérifiée en test réel via curl) et la
**réponse** pour les questions théoriques. Les réponses figurent aussi dans
`RESULT.md`.

> Emplacement des fichiers : `tp2-solution/app/site-a/` (PHP) et
> `tp2-solution/app/site-b/` (HTML/JS).
> PHP : utiliser `$PHP = "C:\Users\Ayoub\php-8.5\php.exe"` (n'est pas dans
> le PATH) ou `$PHP = "php"` s'il a été ajouté au PATH.

---

## 0. Préparation — Démarrage (pratique, obligatoire)

### Étape 0.1 — Lancer les DEUX serveurs (deux terminaux)
- **Faire :**
  ```powershell
  # Terminal 1
  cd "C:\Users\Ayoub\StudioProjects\WebSec TP\tp2-solution\app"
  & $PHP -S 127.0.0.1:8080 -t site-a

  # Terminal 2
  cd "C:\Users\Ayoub\StudioProjects\WebSec TP\tp2-solution\app"
  & $PHP -S 127.0.0.1:8081 -t site-b
  ```
- **Attendu :** deux lignes « Development Server started »,
  `http://127.0.0.1:8080` et `http://127.0.0.1:8081`.
- **Point-clé de l'architecture :** deux **ports** = deux **origines
  distinctes** même same-site (même hôte).

### Étape 0.2 — Smoke test rapide
- **Faire :**
  ```powershell
  curl -o NUL -w "%{http_code}`n" http://127.0.0.1:8080/cookie.php   # → 200
  curl -o NUL -w "%{http_code}`n" http://127.0.0.1:8081/index.html   # → 200
  ```
- **Attendu :** deux `200`.

---

## Partie A — Cookies HTTP

### Étape 1 (pratique) — Créer cookie.php + lien « Créer »
- **Fichier :** `site-a/cookie.php` (déjà créé).
- **Réalisation :** trois liens :
  - `cookie.php?action=create` → `setcookie('tp2_theme','clair',[ 'expires'
    => time()+600, 'path' => '/', 'samesite' => 'Lax' ])`, **redirection**
    vers la lecture (header Location puis exit) ;
  - `cookie.php?action=update` → même portée, valeur `sombre` ;
  - `cookie.php?action=delete` → même Path + `expires` dans le passé.

### Étape 2 (pratique) — Lecture $_COOKIE + liens Modifier / Supprimer
- **Faire :**
  1. Cliquez le lien **Créer** → la page se recharge : affichage
     `$_COOKIE['tp2_theme'] = clair`.
  2. Cliquez **Modifier** → la page affiche `sombre`.
  3. Cliquez **Supprimer** → la page affiche `(absent)`.
- **Vérifié en réel (curl) :**
  - create → `302` + `Set-Cookie: tp2_theme=clair; Max-Age=600; path=/; SameSite=Lax`.
  - lecture avec cookie envoyé → page affiche `clair`.
  - delete → `Set-Cookie: tp2_theme=deleted; expires=01 Jan 1970 …; path=/; SameSite=Lax`.

### Étape 3 (pratique) — Inspecter Set-Cookie / Cookie / Stockage
- **Faire :**
  1. F12 → Réseau ; cliquer **Créer** : dans la requête redirigée, l'en-tête
     **réponse** de `cookie.php?action=create` contient
     `Set-Cookie: tp2_theme=clair; Max-Age=600; Path=/; SameSite=Lax`.
  2. La requête **suivante** (celle de la redirection vers cookie.php) porte
     l'en-tête **requête** `Cookie: tp2_theme=clair`.
  3. Comparer avec **Application → Stockage → Cookies** : l'entrée
     `tp2_theme`, avec Path `/`, expiration ~+600 s, SameSite Lax.
- **À noter à l'écrit :** Set-Cookie est un en-tête **de réponse** (serveur →
  navigateur stocke), Cookie un en-tête **de requête** (navigateur → serveur).

### Étape 4 (pratique) — Copie expérimentale HttpOnly + document.cookie
- **Fichier :** `site-a/cookie-httponly.php` (déjà créé) : pose
  `tp2_httponly_demo` avec `httponly => true` et affiche le résultat de
  `document.cookie` côté script.
- **Faire :**
  1. Ouvrir `http://127.0.0.1:8080/cookie-httponly.php`, cliquer « Créer le
     cookie HttpOnly ».
  2. Regarder dans la page la ligne **document.cookie** : le cookie
     `tp2_httponly_demo` **n'apparaît pas**.
  3. F12 → Réseau → recharger : l'en-tête requête `Cookie` contient
     bien `tp2_httponly_demo=valeur-secret-tp2` (le navigateur l'envoie
     quand même) — et Application → Stockage le listes.
- **Curl (vérifié) :** `Set-Cookie: tp2_httponly_demo=…; Max-Age=600; path=/;
  HttpOnly; SameSite=Lax`.

### Discussion attendue (étape 4 : Secure, Domain, SameSite)
- **Secure** : nécessite HTTPS ; les deux sites du TP sont en HTTP local, on
  ne peut donc pas démontrer le « ne pas envoyer hors HTTPS » (les cookies
  `Secure` ne seraient jamais `Send` via http://).
- **Domain** : sert à élargir à des sous-domaines ; on n'a qu'un hôte
  (`127.0.0.1`), pas de sous-domaines à tester → non démontrable.
- **SameSite** : comportement cross-site (Lax/Strict/None) ne peut être
  démontré car nos deux sites sont **same-site** (seuls les ports diffèrent).
- Tout cela est **expliqué** (fiche) et non prouvé sur cette architecture.

### Q A1 (théorique) — Pourquoi la valeur mise par setcookie() n'apparaît-elle pas immédiatement dans $_COOKIE ?
- **Réponse :** `Set-Cookie` est un en-tête de la **réponse** : `setcookie()`
  ne modifie pas la requête courante, il demande au navigateur de stocker le
  cookie. `$_COOKIE` est rempli par PHP au début d'une requête à partir de
  l'en-tête `Cookie` **de cette requête**, qui ne contient encore que les
  cookies déjà connus. La valeur n'est lisible dans `$_COOKIE` qu'à la
  **requête suivante** — d'où la redirection vers la page de lecture.

### Q A2 (théorique) — Quelle différence entre Set-Cookie et Cookie ?
- **Réponse :**
  - `Set-Cookie` : en-tête **de réponse** — le serveur crée/met à jour un
    cookie et en fixe les attributs (`Expires`/`Max-Age`, `Path`, `Domain`,
    `Secure`, `HttpOnly`, `SameSite`).
  - `Cookie` : en-tête **de requête** — le navigateur liste les cookies
    stockés correspondant à la portée de la ressource demandée.
  - Sens : le serveur **propose** (Set-Cookie), le navigateur **mémorise puis
    re-transmet** (Cookie).

### Q A3 (théorique) — Pourquoi une suppression exige-t-elle la même portée ?
- **Réponse :** pour le navigateur, un cookie est identifié par son **nom +
  sa portée** ( domaine + path). Supprimer = renvoyer `Set-Cookie` avec le
  **même nom et les mêmes attributs de portée** et une expiration passée.
  Si le `Path` (ou le domaine) diffère, le navigateur crée un **second**
  cookie au lieu de remplacer le premier ; l'original continue d'être envoyé.
  D'où la consigne : réutiliser le même Path et une date passée.

### Q A4 (théorique) — HttpOnly empêche-t-il le navigateur d'envoyer le cookie ?
- **Réponse :** **non.** `HttpOnly` est une restriction **JavaScript** :
  le cookie devient invisible/inaccessible pour `document.cookie` et les
  scripts de la page, mais continue d'être envoyé normalement dans
  l'en-tête `Cookie` de toute requête de sa portée (vérifié). Rôle : réduire
  le vol du cookie par un script injecté (atténuer le risque de vol de
  session via XSS), sans casser les usages applicatifs du cookie.

---

## Partie B — Sessions PHP

### Étape 5 (pratique) — login.php (POST, identifiants fictifs, sans BDD)
- **Fichier :** `site-a/login.php` (déjà créé).
- **Exigences satisfaites :** formulaire `<form method="post" action="login.php">`
  (champs `identifiant`, `mot_de_passe`), comparaison à des identifiants
  fictifs locaux `etudiant` / `tp2026` (`hash_equals`), aucune BDD.

### Étape 6 (pratique) — Configuration AVANT session_start()
- **Réalisé dans login.php / profile.php / logout.php (avant session_start) :**
  ```php
  ini_set('session.use_strict_mode', '1');
  session_set_cookie_params([
      'lifetime' => 0, 'path' => '/', 'domain' => '',
      'secure'   => false,    // HTTP local uniquement
      'httponly' => true,     // HttpOnly pour le cookie de session
      'samesite' => 'Lax',
  ]);
  ```
- **Faire :** relire le code (les réglages sont écrits AVANT `session_start()`,
  comme exigé), puis tester le login :
  1. Ouvrir `http://127.0.0.1:8080/login.php`, saisir `etudiant` /
     `tp2026`, cliquer « Se connecter » → redirigé vers profile.php.
  2. Vue Application → Cookies : `PHPSESSID` avec **HttpOnly** et
     **SameSite=Lax** ; `Secure` absent (HTTP local).

### Étape 7 (pratique) — session_regenerate_id(true), profile.php, logout.php

- **Réalisé dans login.php, après identification correcte :**
  ```php
  session_regenerate_id(true);
  $_SESSION['user'] = $identifiant;
  header('Location: profile.php'); exit;
  ```
- **profile.php** : `$_SESSION['user']` absent → page « Accès refusé » +
  lien « Se connecter » ; présent → profil, contenu de `$_SESSION`, lien
  logout.
- **logout.php** : vide `$_SESSION`, supprime le cookie de session (même
  nom/path, date passée) et `session_destroy()`, puis redirige vers
  login.php.
- **Tests à faire (vérifiés réels) :**
  ```powershell
  # démonstration de la régénération d'identifiant :
  curl -c c.txt -o NUL http://127.0.0.1:8080/login.php              # session id initiale
  curl -b c.txt -c c.txt -X POST -d "identifiant=etudiant&mot_de_passe=tp2026" -o NUL http://127.0.0.1:8080/login.php
  # → le PHPSESSID dans c.txt a CHANGÉ après login (constaté)
  # authentifié :
  curl -b c.txt http://127.0.0.1:8080/profile.php                    # ← « Utilisateur authentifié : etudiant »
  # déconnexion :
  curl -b c.txt -o NUL http://127.0.0.1:8080/logout.php              # → 302 login.php
  curl -b c.txt http://127.0.0.1:8080/profile.php                    # ← « Accès refusé »
  ```

### Étape 8 (pratique) — Comparer PHPSESSID et $_SESSION ; accès anonyme vs authentifié
- **Faire :**
  1. Ouvrir **Application → Cookies** et relever la valeur de `PHPSESSID`
     (une suite de caractères aléatoires, jamais le nom d'utilisateur).
  2. Ouvrir `http://127.0.0.1:8080/profile.php` **anonyme** → « Accès
     refusé » ; s'authentifier puis re-ouvrir → profil authentifié.
  3. Si possible, côté serveur imprimer `print_r($_SESSION)` (déjà présent
     sur la page authentifiée) : la donnée est côté serveur, jamais en
     Cookies.
- **Conclusion à écrire :** le cookie porte l'IDENTIFIANT ; les données
  (`user`) résident côté serveur.

### Q B1 (théorique) — Que contient PHPSESSID ?
- **Réponse :** un **identifiant** de session (chaîne aléatoire) et rien
  d'autre : pas de nom d'utilisateur, pas de données applicatives. C'est la
  « clé d'armoire » que le navigateur renvoie à chaque requête pour que le
  serveur retrouve la session enregistrée sous cet identifiant (constaté :
  `PHPSESSID = d807d110…` complet non interprétable).

### Q B2 (théorique) — Où résident les données $_SESSION ?
- **Réponse :** côté **serveur**, par défaut sous forme de fichiers dans le
  répertoire configuré (`session.save_path`, par exemple `/tmp` ou le
  répertoire temporaire de PHP), un fichier nommé `sess_<PHPSESSID>` par
  session, avec `$_SESSION` sérialisée. Le navigateur n'a jamais que
  l'identifiant.

### Q B3 (théorique) — Pourquoi régénérer l'identifiant après connexion ?
- **Réponse :** pour contrer l'attaque de **fixation de session** : si un
  attaquant arrive à faire utiliser à la victime un ID qu'il connaît
  (pré-connexion), cet ID redevient celui de la session authentifiée après
  le login — l'attaquant hériterait de la session. `session_regenerate_id(true)`
  à l'instant de la montée de privilège invalide l'ancien ID et en émet un
  nouveau (constaté : l'ID change après login). Recommandé à chaque montée
  de privilège.

### Q B4 (théorique) — Quel risque subsiste en l'absence de HTTPS ?
- **Réponse :** sans HTTPS, tout l'échange circule en clair, notamment le
  cookie de session : un écouteur sur le chemin peut lire et **usurper** le
  `PHPSESSID` (reprendre la session authentifiée) ou modifier les données.
  L'attribut `Secure` (non démontrable en local HTTP) limite la fuite en
  ne laissant le cookie se transmettre que sur HTTPS ; en production il faut
  `Secure` + HTTPS obligatoire.

---

## Partie C — Same-Origin Policy

### Étape 9 (pratique) — fetch cross-origin depuis B vers A, mode=none
- **Fichiers :** `site-b/index.html` (boutons) et `site-a/api.php`.
- **Faire :** sur le site B
  (`http://127.0.0.1:8081/index.html`), cliquer le bouton
  **« Étape 9 — mode=none »**. Le script exécute
  `fetch("http://127.0.0.1:8080/api.php?mode=none")`.
- **Réalisé dans api.php (mode=none) :**
  ```php
  header('Content-Type: application/json');
  // aucun en-tête Access-Control-Allow-Origin
  echo json_encode([...]);
  ```
- **Faire réellement, en curl (vérifié) :**
  ```powershell
  curl -i http://127.0.0.1:8080/api.php?mode=none
  # → 200, Content-Type: application/json, AUCUN en-tête Access-Control-*
  ```

### Étape 10 (pratique) — Comparer Console et Réseau
- **Faire :** F12 sur le site B : **Console** → erreur CORS (« … has been
  blocked by CORS policy: No 'Access-Control-Allow-Origin' header is present
  … ») ; **Réseau** → la requête `GET /api.php?mode=none` a bien été envoyée
  (statut 200, la réponse JSON visible dans l'inspecteur).
- **À noter :** la requête **PART** ; c'est la **lecture** de la réponse par
  le script JavaScript qui est refusée (le Promise de `fetch` est rejeté).

### Étape 11 (pratique) — Deux ports, deux origines
- **Faire :** sur B, ouvrir l'onglet Réseau → requête vers A ; comparer
  les URL `http://127.0.0.1:8081/index.html` (origine du script) et
  `http://127.0.0.1:8080/api.php` (URL de la requête) : même hôte
  `127.0.0.1`, même schéma, ports différents → **origines différentes**.
- **Constat clé :** la requête peut **partir** même sans en-tête CORS
  d'autorisation — la SOP interdit la lecture, pas l'envoi.

### Q C1 (théorique) — Donner les triplets d'origine.
- **Réponse :** une origine = triplet **(schéma, hôte, port)** :
  - Site A : `("http", "127.0.0.1", 8080)`
  - Site B : `("http", "127.0.0.1", 8081)`
- Le port fait partie de l'origine : bien que les deux soient
  « same-site » (même hôte `127.0.0.1`), elles sont **cross-origin** l'une
  pour l'autre.

### Q C2 (théorique) — SOP interdit-elle tout envoi cross-origin ? Pourquoi un script ne peut-il pas lire la réponse ?
- **Réponse :** **non.** La SOP n'interdit pas l'**envoi** : les requêtes
  simples (GET sans en-tête personnalisé) partent normalement (constaté dans
  l'onglet Réseau). Ce que la SOP protège, c'est le **client** : un script ne
  peut pas **lire** les données d'une réponse cross-origin tant que celle-ci
  ne déclare pas son origine (`Access-Control-Allow-Origin` absent ici) ;
  le `Promise` de `fetch` est rejeté. L'interdiction porte sur la **lecture**,
  pas sur l'émission : le serveur A voit néanmoins la requête — d'où
  l'intérêt d'un contrôle d'accès propre côté serveur (voir D1).

---

## Partie D — CORS et OPTIONS

### Étape 12 (pratique) — mode=allow, ACAO sur B uniquement
- **Réalisé dans api.php (mode=allow) :**
  ```php
  if ($origineRecue === 'http://127.0.0.1:8081') {
      header('Access-Control-Allow-Origin: http://127.0.0.1:8081');
  }
  ```
- **Faire :** sur `index.html` du site B, cliquer
  **« Étape 12 — mode=allow »** : la réponse est lisible en JS (pas
  d'erreur CORS) et la page affiche `Statut HTTP : 200`.
- **Réponse (vérifié en curl) :** avec `Origin: http://127.0.0.1:8081` →
  `200` + `Access-Control-Allow-Origin: http://127.0.0.1:8081`. Avec un
  autre Origin → aucun ACAO (lecture bloquée par le navigateur).

### Étape 13 (pratique) — Allow-Methods + Allow-Headers ; fetch avec X-TP-Lab
- **Réalisé dans api.php :**
  ```php
  header('Access-Control-Allow-Methods: GET, OPTIONS');
  header('Access-Control-Allow-Headers: X-TP-Lab, Content-Type');
  ```
  (dans la branche OPTIONS du preflight)
- **Faire :** sur B, cliquer **« Étape 13 — fetch avec X-TP-Lab »** :
  `fetch(apiBase + '?mode=allow', { headers: { 'X-TP-Lab': 'tp2' } })`.
  L'en-tête personnalisé transforme la requête en requête **non simple** → le
  navigateur fait d'abord un **preflight** OPTIONS.

### Étape 14 (pratique) — Repérer la requête OPTIONS et les en-têtes correspondants
- **Faire :** F12 → Réseau sur B, cliquer le bouton preflight : repérer
  les 2 requêtes :
  1) `OPTIONS http://127.0.0.1:8080/api.php` avec en-têtes de requête
     `Origin`, `Access-Control-Request-Method: GET`,
     `Access-Control-Request-Headers: x-tp-lab` ;
  2) et de suite `GET http://127.0.0.1:8080/api.php?mode=allow` (la vraie
     requête).
- **Vérifié en curl (équivalent) :**
  ```powershell
  curl -i -X OPTIONS -H "Origin: http://127.0.0.1:8081" `
       -H "Access-Control-Request-Method: GET" `
       -H "Access-Control-Request-Headers: x-tp-lab" `
       http://127.0.0.1:8080/api.php
  # → 204 No Content + Access-Control-Allow-Origin, Allow-Methods, Allow-Headers
  ```
- **Origine non autorisée (constaté) :** OPTIONS → 204 **sans** en-têtes
  CORS → le navigateur refusera ensuite la lecture du GET réel.

### Étape 15 (pratique) — mode=wildcard pour les données publiques
- **Faire :** cliquer **« Étape 15 — mode=wildcard »** sur B : la réponse est
  lisible sans aucune condition d'origine.
- **Curl (vérifié) :** n'importe quel Origin → `Access-Control-Allow-Origin: *`
  et données marquées `public: true`.
- **Point d'explication à écrire (de la fiche) :** ce réglage ne convient
  **pas** à des données de compte privées : avec `*`, toute origine peut lire
  ces données ; en présence de cookies (credentials), le navigateur refuse
  d'ailleurs la combinaison avec `*` et exige une origine exacte.

### Q D1 (théorique) — CORS protège-t-il l'API contre curl ?
- **Réponse :** **non.** CORS est une règle appliquée **par le navigateur**
  : `curl` envoie les requêtes et lit les réponses, ignore les en-têtes
  Access-Control-*. Un contrôle d'accès réel pour `curl` passe par une
  **authentification/autorisation côté serveur**. CORS n'est qu'un
  complément de confidentialité **navigateur** (empêcher un script tiers de
  lire une réponse livrée par le serveur).

### Q D2 (théorique) — Que vérifie le navigateur au preflight ?
- **Réponse :** le navigateur envoie `OPTIONS` avec :
  - `Origin` : l'origine du script à l'initiative de l'appel,
  - `Access-Control-Request-Method` : la méthode de la vraie requête,
  - `Access-Control-Request-Headers` : les en-têtes non simples demandés.
- Il vérifie ensuite que la réponse autorise exactement cette **origine**
  (`ACAO`), cette **méthode** (`ACAM`) et ces **en-têtes** (`ACAH`). Si
  l'un manque, le GET réel n'est jamais envoyé — c'est le navigateur qui
  décide (constaté : réponse 204 sans ACAO pour origine non autorisée).

### Q D3 (théorique) — Quelle est la différence entre une autorisation de lecture et un contrôle d'accès serveur ?
- **Réponse :** l'**autorisation de lecture** (CORS) est côté **navigateur**
  : elle répond, pour chaque origine, à « ce script peut-il lire cette
  réponse ? ». Un **contrôle d'accès serveur** : le serveur valide
  (session, token, certificat, etc.) **ce que le demandeur peut faire sur la
  ressource**, pour TOUS les clients (curl compris). CORS n'est qu'un
  complément — pas un substitut à la protection applicative de l'API.

### Q D4 (théorique) — Pourquoi ne pas ajouter systématiquement Allow-Credentials: true ?
- **Réponse :** `Access-Control-Allow-Credentials: true` autorise l'envoi de
  cookies/identifiants cross-origin.
  1. Cela **élargit la portée** d'un éventuel défaut CORS (une origine
     mal listée lirait des données personnelles / de session).
  2. Associé à `Access-Control-Allow-Origin: *`, le navigateur **refuse**
     la combinaison (exige une origine exacte avec credentials).
  3. Cela augmente l'exposition à la **CSRF** (les cookies de session sont
     automatiquement transmis).
- On ne l'ajoute que si vraiment nécessaire, et uniquement avec une liste
  d'origines **explicites et restreintes**.

---

## Partie E — Content Security Policy

### Étape 16 (pratique) — csp.php : inline + externe local /csp.js + image distante
- **Fichiers :** `site-a/csp.php` et `site-a/csp.js` (déjà créés).
- **Contenu de la page :** script inline dans la page HTML, `<script
  src="/csp.js"></script>` externe local, `<img
  src="https://picsum.photos/200">` distante. La page affiche un éventuel
  « Script inline exécuté. » et « Script externe /csp.js exécuté. ».

### Étape 17 (pratique) — Comparer : sans CSP / Report-Only / politique
- **Faire dans le navigateur (trois ouvertures) :**
  1. `http://127.0.0.1:8080/csp.php` (ou `?csp=none`) — **aucun en-tête
     CSP** : tout fonctionne (inline, /csp.js, image).
  2. `http://127.0.0.1:8080/csp.php?csp=report` — en-tête
     `Content-Security-Policy-Report-Only:
     default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self'` :
     la Console **signale** les violations mais **rien** n'est bloqué
     (inline s'exécute, l'image se charge).
  3. `http://127.0.0.1:8080/csp.php?csp=enforce` — en-tête
     `Content-Security-Policy: default-src 'self'; script-src 'self';
     style-src 'self'; img-src 'self'` :
     inline **bloqué**, `/csp.js` (même origine) **autorisé**, image
     distante **bloquée** par `img-src`.
- **Curl (vérifié) :** chacun des 3 modes renvoie exactement l'en-tête
  correspondant (aucun en-tête / Report-Only / CSP) et `/csp.js` est bien
  servi (200, `application/javascript`).

### Étape 18 (pratique) — Décrire dans la Console ce qui est bloqué / exécuté / signalé
- **Faire :** ouvrir la Console (F12) sur chaque mode et noter :
  - **mode enforce** : `Refused to execute inline script because it
    violates the following CSP directive: script-src 'self'` (langue de la
    Console) ; le message « Script externe /csp.js exécuté. » s'affiche
    quand même ; pour l'image, `Refused to load the image … violates
    directive: img-src 'self'`.
  - **mode report** : les mêmes erreurs CSP sont **signalées** mais
    l'exécution continue.
  - **mode none** : aucun message CSP.
- **Piège de la fiche :** ne pas confondre **ressource absente** (404 : la
  requête réseau arrive mais le fichier n'existe pas) et **ressource
  bloquée par CSP** (requête non émise/refusée, message distinct « Refused »
  dans la Console).
- **Vérifier aussi côté Réseau :** en enforce, la requête de l'image
  distante n'est pas envoyée (elle est bloquée avant), contrairement à une
  image 404 locale qui elle partirait.

### Q E1 (théorique) — Quel script est bloqué par script-src 'self' sans unsafe-inline ?
- **Réponse :** le **script inline** (le bloc `<script>…</script>` dans le
  HTML). `script-src 'self'` n'autorise que les scripts chargés depuis la
  même origine (ici `/csp.js`), et interdit par défaut le code intégré à la
  page (inline, `eval`, `onclick`, …). Le script **externe local**
  `/csp.js` **s'exécute**. L'image distante est bloquée
  par `img-src 'self'`, un éventuel style inline par `style-src 'self'`
  (d'où les entrées signalées dans la Console en mode enforce).

### Q E2 (théorique) — Quelle est l'utilité de Report-Only ?
- **Réponse :** `Content-Security-Policy-Report-Only` permet de **tester une
  politique sans l'appliquer** : le navigateur évalue la politique et
  signale les violations dans la Console (éventuellement vers un endpoint de
  rapport), mais **ne bloque rien**. Cela permet de déployer la politique
  d'abord en mode détection, de trouver ce qui serait cassé (scripts
  inline, CDN, images distantes, …) et de l'ajuster avant d'activer le mode
  bloquant — une méthode d'essai pour écrire une CSP compatible avec la page.

### Q E3 (théorique) — CSP remplace-t-elle l'échappement des sorties et la validation côté serveur ?
- **Réponse :** **non.** La CSP est une couche de défense **complémentaire** :
  elle limite ce qu'une injection peut faire une fois entrée (pas de script
  inline, pas de ressources distantes), mais elle ne reçoit pas les données,
  ne les filtre pas et ne valide rien. Si le serveur injecte un
  `<img onerror=…>` dans une sortie non échappée, la CSP peut réduire
  l'exploitabilité de cette injection, mais la faille reste présente :
  la correction est l'**échappement du contenu généré** (ex.
  `htmlspecialchars`) et la **validation des entrées** côté serveur, qui
  doivent être conservées — la CSP s'ajoute **en plus** (défense en
  profondeur).

---

## Synthèse — Récapitulatif des URL/pages à connaître

| URL (site A 8080 sauf mention) | Ce qui est testé |
|---|---|
| `/cookie.php` (+ `?action=create|update|delete`) | Création / modification / suppression du cookie |
| `/cookie-httponly.php` | Cookie HttpOnly + `document.cookie` |
| `/login.php`, `/profile.php`, `/logout.php` | Connexion, régénération d'ID, profil protégé, déconnexion |
| `/api.php?mode=none` / `allow` / `wildcard` | SOP en jeu, CORS restreint, CORS `*` |
| `/csp.php` (+ `?csp=report|enforce`) et `/csp.js` | CSP none / Report-Only / enforce |
| `http://127.0.0.1:8081/index.html` (site B, 8081) | fetch cross-origin depuis B vers A |

---

## Compte-rendu
Les réponses sont également écrites « en contexte » dans `RESULT.md`
(compte rendu TP 02). À remettre avec :
- les observations de chaque étape : cookies, sessions, comportements
  Réseau et Console, en-têtes de réponse API et CSP ;
- réponses aux questions A1–A4, B1–B4, C1–C2, D1–D4, E1–E3.
