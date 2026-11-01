# TP 02 — Compte rendu (RESULT)

Environnement :
- Terminal 1 : `php -S 127.0.0.1:8080 -t site-a` (depuis `app/`)
- Terminal 2 : `php -S 127.0.0.1:8081 -t site-b`
- Navigateur : DevTools → Réseau, Console, Application (Stockage/Cookies)
- PHP 8.1+, sans base de données ; identifiants fictifs `etudiant` / `tp2026`.

Les deux ports créent **deux origines distinctes** (`http://127.0.0.1:8080` et
`http://127.0.0.1:8081`), même si elles sont same-site (même hôte). Les
attributs `Secure` et le comportement cross-site de `SameSite`, ainsi que
`Domain`, sont expliqués mais non démontrables sur ces deux ports HTTP.

---

## Partie A — Cookies HTTP

Réalisation : `cookie.php` avec trois liens :
- **Créer** (`?action=create`) : `setcookie('tp2_theme', 'clair', [ 'expires' => time()+600, 'path' => '/', 'samesite' => 'Lax' ])`, puis redirection vers la lecture ;
- **Modifier** (`?action=update`) : nouvelle valeur `sombre`, mêmes attributs ;
- **Supprimer** (`?action=delete`) : même nom et même `Path`, date
  d'expiration dans le passé → le navigateur retire le cookie.

Observations (étape 3) : la réponse de « Créer » porte
`Set-Cookie: tp2_theme=clair; Max-Age=600; Path=/; SameSite=Lax` ; la requête
suivante (celle de la redirection) porte `Cookie: tp2_theme=clair`. L'onglet
Application → Stockage → Cookies montre le cookie stocké par le navigateur,
avec les mêmes attributs. La valeur posée par `setcookie()` n'apparaît pas
dans `$_COOKIE` de la requête qui émet `Set-Cookie` — elle n'est lisible
qu'après le rechargement/redirection.

Étape 4 (copie expérimentale `cookie-httponly.php`) : un second cookie est posé
avec `httponly => true`. `document.cookie` ne le liste pas, alors qu'il figure
bien dans la requête (en-tête `Cookie` reçu par le serveur, visible dans
Réseau) et dans l'onglet Stockage.

### Q A1 — Pourquoi la valeur mise par setcookie() n'apparaît-elle pas immédiatement dans $_COOKIE ?

Parce que `Set-Cookie` est un en-tête de la **réponse** : `setcookie()` ne
modifie pas la requête courante, il ajoute cet en-tête pour que le navigateur
**stocke** le cookie. La superglobale `$_COOKIE` est remplie par PHP au début
d'une requête à partir de l'en-tête `Cookie` **de cette requête**, qui ne
contient encore que les cookies déjà connus. La valeur n'est donc lisible via
`$_COOKIE` qu'à la **requête suivante**, après stockage par le navigateur —
d'où la redirection vers la page de lecture.

### Q A2 — Quelle différence entre Set-Cookie et Cookie ?

- `Set-Cookie` : en-tête **de réponse**, envoyé par le serveur pour créer ou
  mettre à jour un cookie ; il porte la paire `nom=valeur` et ses attributs
  (`Expires`/`Max-Age`, `Path`, `Domain`, `Secure`, `HttpOnly`, `SameSite`).
- `Cookie` : en-tête **de requête**, envoyé par le navigateur ; il contient la
  liste des cookies stockés qui correspondent à la portée
  (hôte/chemin/expiration) de la ressource demandée.

Ce sont donc les deux sens du même mécanisme : le serveur propose, le
navigateur mémorise puis re-transmet.

### Q A3 — Pourquoi une suppression exige-t-elle la même portée ?

Pour le navigateur, un cookie est identifié par son **nom plus sa portée**
(essentiellement domaine + path). Supprimer un cookie, c'est envoyer un
`Set-Cookie` avec le **même nom et les mêmes attributs de portée** et une
expiration passée ; si le `Path` (ou le domaine) diffère, le navigateur ne
remplace pas l'ancien cookie, il crée en réalité un **deuxième** cookie et
l'original continue d'être envoyé. D'où la consigne de la fiche : réutiliser
le même `Path` et fournir une date passée.

### Q A4 — HttpOnly empêche-t-il le navigateur d'envoyer le cookie ?

**Non.** `HttpOnly` est une restriction **JavaScript** : le cookie devient
invisible et inaccessible pour `document.cookie` et pour les scripts de la
page, mais il continue d'être normalement envoyé par le navigateur dans
l'en-tête `Cookie` de toute requête dans sa portée. Rôle : limiter le vol du
cookie par un script injecté (atténuation du risque de vol de session via
XSS), sans casser l'usage du cookie pour l'application.

### Attributs Secure, Domain, SameSite (étape 4)

- `Secure` : n'est **pas démontrable** ici car il exige HTTPS ; les cookies
  Secure ne sont pas transmis via `http://`, et les deux sites du TP sont en
  HTTP local.
- `Domain` : sert à élargir un cookie à des sous-domaines (ex.
  `example.com` pour `a.example.com`) ; or on n'a ici qu'un hôte
  (`127.0.0.1`), sans sous-domaines à tester.
- `SameSite` : son comportement cross-site (Lax/Strict/None) ne peut être
  démontré car nos deux sites sont **same-site** (même hôte `127.0.0.1`,
  seuls les ports diffèrent). Ces attributs sont donc à
  **expliquer**, non à prouver sur cette architecture — ce que la fiche
  prévoit explicitement.

---

## Partie B — Sessions PHP

Réalisation :
- `login.php` : formulaire POST, identifiants fictifs locaux
  (`etudiant` / `tp2026`), aucune BDD. Avant `session_start()` :
  `ini_set('session.use_strict_mode', '1')` et `session_set_cookie_params([`
  `lifetime 0, path '/', secure false, httponly true, samesite 'Lax'])`.
- Après identification correcte : `session_regenerate_id(true)` puis
  `$_SESSION['user'] = 'etudiant'`, redirection vers `profile.php`.
- `profile.php` : même configuration avant `session_start()` ; si
  `$_SESSION['user']` est absent, l'accès est refusé/redirigé vers le login.
- `logout.php` : vide `$_SESSION`, supprime le cookie de session (même
  nom/path, date passée) et `session_destroy()`.

Observations (étape 8) : `PHPSESSID` ne contient qu'une suite de caractères
aléatoires, jamais le nom d'utilisateur. Les données de session (`user`)
appartenaient uniquement au serveur (`$_SESSION`). Accès anonyme à
`profile.php` : refus (aucun PHPSESSID valide à authentifier). Accès
authentifié : profil visible avec le contenu de `$_SESSION`.

### Q B1 — Que contient PHPSESSID ?

Un **identifiant** de session (chaîne aléatoire prudente), rien d'autre : pas
le nom d'utilisateur, pas de données applicatives. C'est la « clé d'armoire »
que le navigateur renvoie à chaque requête pour que le serveur retrouve la
session enregistrée sous cet identifiant. Toutes les données de session
(`$_SESSION['user']`, etc.) restent côté serveur — c'est précisément ce qu'on
observe entre `PHPSESSID` (Application → Cookies) et le contenu de
`$_SESSION` (profile.php).

### Q B2 — Où résident les données $_SESSION ?

Côté **serveur** : par défaut dans des fichiers placés dans le répertoire de
sauvegarde de sessions configuré (directive `session.save_path`, par exemple
`/tmp` ou le répertoire temporaire de PHP), un fichier par session, nommé
d'après `sess_` + identifiant. Le fichier ne contient que la donnée sérialisée
de `$_SESSION` ; le navigateur, lui, n'a jamais que le PHPSESSID. Ce point
enseigne la base d'un principe : un identifiant côté client, les données côté
serveur.

### Q B3 — Pourquoi régénérer l'identifiant après connexion ?

Pour contrer l'attaque de **fixation de session** (session fixation) : si un
attaquant arrive à imposer à une victime un identifiant de session qu'il
connaît (avantage pré-connexion — lien, injection de cookie, etc.), cet
identifiant restera celui de la victime après sa connexion : en connaissant
`PHPSESSID`, l'attaquant hériterait de la session authentifiée. En appelant
`session_regenerate_id(true)` à l'instant où le statut
d'un visiteur change (passage anonyme → authentifié), l'ancien identifiant est
invalidé et un nouveau est émis : l'identifiant que l'attaquant aurait connu
n'est plus utile. C'est pourquoi la régénération est recommandée à **chaque
montée de privilège** (connexion, changement de mot de passe, etc.).

### Q B4 — Quel risque subsiste en l'absence de HTTPS ?

Sans HTTPS, tout l'échange circule en clair, notamment le cookie de session.
Un écouteur sur le chemin (Wi-Fi ou réseau partagé, périphérique
intermédiaire) peut lire et usurper le `PHPSESSID` (reprise de session par
usurpation) ou modifier toutes les données échangées. C'est ce que l'attribut
`Secure` prévient en rendant le cookie inaccessible hors HTTPS : c'est pour
cela que, dès qu'on quitte le HTTP local, il faut activer `Secure` et servir
l'application uniquement en HTTPS. La connexion authentifiée du TP reste
donc une simulation pédagogique, pas une configuration de production.

---

## Partie C — Same-Origin Policy

Réalisation : `site-b/index.html` (port 8081) exécute
`fetch("http://127.0.0.1:8080/api.php?mode=none")`. L'API A répond en JSON
(200, `Content-Type: application/json`) mais **sans** l'en-tête
`Access-Control-Allow-Origin`.

Observations :
- Console : erreur de blocage CORS de type « … has been blocked by CORS
  policy: No 'Access-Control-Allow-Origin' header is present on the
  requested resource ».
- Réseau : la requête `GET /api.php?mode=none` a **bien été envoyée** (statut
  200) et la réponse JSON apparaît dans l'inspecteur — c'est l'accès depuis
  le **script JavaScript** qui échoue (le `Promise` de `fetch` est rejeté).
- Deux ports différents → deux origines différentes, y compris même hôte,
  même schéma : la comparaison d'origine inclut le **port**.

### Q C1 — Donner les triplets d'origine.

Une origine = triplet (schéma, hôte, port) :
- Site A : `("http", "127.0.0.1", 8080)`
- Site B : `("http", "127.0.0.1", 8081)`

B ≠ A même si les deux sont same-site (même hôte) : le port fait partie des
composants d'une origine, c'est exactement le point que la fiche demande de
constater.

### Q C2 — SOP interdit-elle tout envoi cross-origin ? Pourquoi un script ne peut-il pas lire la réponse ?

**Non.** La SOP ne bloque pas l'**envoi** : une requête simple (GET sans
en-têtes personnalisés ici) est transmise au serveur, qui la traite et
répond — c'est ce qu'on observe dans Réseau. Ce que la SOP protège, c'est le
client : un script de la page ne peut pas **Lire** les données d'une réponse
cross-origin tant que celle-ci n'autorise pas son origine (le navigateur
déduit l'origine du script, compare au triplet de la réponse — absent ici —
et `fetch()` est rejeté). L'interdiction porte sur la **lecture des données**,
pas sur l'émission de la requête : le site A « voit » quand même la requête
(si son traitement déclenche un effet — déclenchement d'action, compteur —,
le serveur doit le valider lui-même, voir D1).

---

## Partie D — CORS et OPTIONS

Réalisation (`api.php`) :
- **mode=allow** (étape 12) : le serveur ajoute
  `Access-Control-Allow-Origin: http://127.0.0.1:8081` ; depuis B, `fetch`
  réussit et le JSON est lisible en JS ; avec `mode=none`, c'est toujours
  bloqué.
- **Étapes 13–14** : le serveur ajoute
  `Access-Control-Allow-Methods: GET, OPTIONS` et
  `Access-Control-Allow-Headers: X-TP-Lab, Content-Type`. Depuis B, un
  `fetch` avec l'en-tête personnalisé `X-TP-Lab` transforme la requête
  en requête **non simple** : le navigateur envoie d'abord un **preflight**
  `OPTIONS` avec `Origin`, `Access-Control-Request-Method: GET` et
  `Access-Control-Request-Headers: x-tp-lab` ; l'API répond 204 avec les
  en-têtes correspondants (ACAO, ACAM, ACAH), puis le navigateur émet le GET
  réel.
- **mode=wildcard** (étape 15) : le serveur pose `Access-Control-Allow-Origin: *`
  et ne sert que des données publiques. Explication : avec `*`, **toute
  origine peut lire ces données** ; ce réglage ne convient pas à des données
  de compte privées, qui doivent ne rejoindre que les origines explicitement
  autorisées — et, en présence de cookies (credentials), le navigateur refuse
  la combinaison `*` et exige une origine exacte.

### Q D1 — CORS protège-t-il l'API contre curl ?

**Non.** CORS est une règle appliquée **par le navigateur** : il s'agit d'un
contrôle client (« qui peut lire quoi, depuis où »). `curl` n'implémente ni
n'applique CORS : il envoie les requêtes, ignore les en-têtes
Access-Control-*, et affiche la réponse telle quelle. Si une ressource doit
rester seulement accessible aux utilisateurs autorisés, cette protection doit
être mise en œuvre **côté serveur** (authentification, autorisation, etc.) ;
CORS n'est qu'un complément de confidentialité au niveau du navigateur
(empêcher un script tiers de lire une réponse que le serveur a livrée).

### Q D2 — Que vérifie le navigateur au preflight ?

Le navigateur envoie `OPTIONS` avec :
- `Origin` : l'origine du script à l'initiative de l'appel,
- `Access-Control-Request-Method` : la méthode de la vraie requête,
- `Access-Control-Request-Headers` : les en-têtes non simples demandés.

Il vérifie ensuite que la réponse au preflight autorise effectivement cette
**origine** (`Access-Control-Allow-Origin`), cette **méthode**
(`Access-Control-Allow-Methods`) et ces **en-têtes**
(`Access-Control-Allow-Headers`). Si au moins une de ces autorisations manque,
le GET réel avec `X-TP-Lab` n'est jamais envoyé : c'est le navigateur qui
interprète la policy et décide.

### Q D3 — Quelle est la différence entre une autorisation de lecture et un contrôle d'accès serveur ?

Autorisation de lecture (CORS) : le navigateur se positionne au niveau du
**client** et demande « ce script issu de l'origine X peut-il lire cette
réponse ? » ; elle ne concerne que les navigateurs qui l'appliquent et ne
sait rien des données elles-mêmes. Contrôle d'accès serveur : le serveur
valide (par des informations incluses dans la requête, session, mot de passe,
jeton, etc.) **ce que le demandeur a le droit de faire sur la ressource**, pour
tous les clients, y compris `curl` ou des scripts non navigateurs. CORS
empêche la lecture par des sites web tiers ; il ne remplace en aucune façon
une authentification/autorisation côté serveur sur l'API.

### Q D4 — Pourquoi ne pas ajouter systématiquement Allow-Credentials: true ?

`Access-Control-Allow-Credentials: true` autorise l'envoi de cookies/identifiants
dans les requêtes cross-origin. Si on l'ajoute systématiquement :
1. on **élargit la portée** de tout éventuel défaut CORS (une origine
   incorrectement listée pourrait lire des données personnelles ou de
   session) ;
2. combiné à `Access-Control-Allow-Origin: *`, le navigateur **refuse** la
   combinaison (il exige une origine exacte en credentials) ;
3. cela augmente l'exposition à la **CSRF** (les cookies de session sont
   transmis automatiquement à l'API dès qu'ils sont admis cross-origin).

On ne l'ajoute que lorsque l'application a besoin réellement d'identifiants
cross-origin, et uniquement avec une liste d'origines explicites et restreinte.

---

## Partie E — Content Security Policy

Réalisation (`csp.php`) : la page contient
1. un script **inline**,
2. un script **externe local** `/csp.js`,
3. une **image distante** (`https://picsum.photos/200`).

Trois modes par `?csp=…` :
| Mode | En-tête | Effet |
|---|---|---|
| `none` (défaut) | aucun | aucun blocage |
| `report` | `Content-Security-Policy-Report-Only: default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self'` | violations **signalées** dans la Console, rien n'est bloqué |
| `enforce` | `Content-Security-Policy: …` (même politique) | ressources non conformes **bloquées** |

Observations (étape 18) : en mode `enforce`, la Console signale une violation
CSP pour le script inline (« Refused to execute inline script because it
violates the following CSP directive: script-src 'self' »), le script
`/csp.js` s'exécute (message « Script externe /csp.js exécuté. »), et l'image
distante est bloquée (« Refused to load the image … violates CSP
directive: img-src 'self' »). En mode `report`, les mêmes erreurs apparaissent
mais **sans interruption réelle** : les scripts s'exécutent quand même et
l'image se charge. Bien distinguer une ressource **absente** (404 : la
requête réseau existe, réponse 404) et une ressource **bloquée par la CSP**
(entrées visibles dans la Console, requête non émise ou réponse non utilisée).

### Q E1 — Quel script est bloqué par script-src 'self' sans unsafe-inline ?

Le **script inline** (le bloc `<script>…</script>` dans la page HTML).
L'instruction `script-src 'self'` n'autorise que les scripts chargés depuis
la même origine (ici `/csp.js`), et exclut par défaut tout code exécuté dans
la page : inline, `eval`, attributs `onclick`, etc. C'est le script
**externe local** `/csp.js` qui reste autorisé. Une image distante est
bloquée par `img-src 'self'`, un éventuel style inline par `style-src 'self'` —
d'où leur listing dans la Console en mode enforce.

### Q E2 — Quelle est l'utilité de Report-Only ?

`Content-Security-Policy-Report-Only` permet de **tester une politique sans
l'appliquer** : le navigateur évalue la politique et signale les violations
dans la Console (il peut aussi les envoyer vers un endpoint de rapport), mais
**ne bloque rien** : le script s'exécute, l'image se charge. On peut donc
préparer la politique en avance et détecter ce qui serait cassé (scripts
inline, librairies externes, CDN, images distantes) avant d'activer le mode
bloquant : c'est une méthode d'essai pour écrire une CSP compatible avec la
page.

### Q E3 — CSP remplace-t-elle l'échappement des sorties et la validation côté serveur ?

**Non.** La CSP est une couche de défense **complémentaire** : elle limite ce
que peut faire une injection une fois qu'elle est entrée (pas de script
inline, pas de ressources distantes), mais elle ne reçoit pas les données, ne
les filtre pas et ne valide rien. Si le serveur injecte un `<img onerror=…>`
dans une sortie non échappée, la CSP peut réduire l'exploitabilité de cette
injection, mais la faille de persistance est toujours là : la correction est
l'échappement du contenu généré, pas la CSP. De plus, une CSP ne couvre pas
tout (les origines de la page restent autorisées ; injection d'HTML, d'images
ou de frames selon la politique choisie). L'échappement des sorties côté
serveur (`htmlspecialchars`) et la validation des entrées côté serveur
**doivent donc être conservés** ; la CSP s'ajoute en plus, comme défense en
profondeur qui limite l'impact d'une faille qui aurait échappé à ces
traitements.

---

## Synthèse du TP 02

- HTTP est sans état : l'état d'application s'entretient par **cookies**
  (Set-Cookie/Cookie, portée, En-têtes, SameSite) et par des **sessions**
  (identifiant en cookie, données côté serveur, régénération d'identifiant au
  passage de privilège).
- **SOP** interdit la lecture cross-origin des réponses ; **CORS** définit de
  façon explicite qui peut lire quoi (ACAO/ACAM/ACAH, preflight OPTIONS) ;
  c'est un contrôle navigateur, pas une protection serveur.
- La **CSP** est la dernière couche de défense en profondeur du navigateur :
  elle limite l'exécution de scripts inline et le chargement des ressources
  distantes, sans jamais remplacer l'échappement ni la validation serveur.
