# TP 01 — Guide des questions : réponse et marche à suivre détaillée pour chacune

Chaque entrée indique : le **type** (pratique sur PC / théorique), la
**marche à suivre** (fichiers à lancer, commandes exactes, actions dans le
navigateur), l'**observation attendue** et la **réponse** pour les questions
théoriques. Les réponses figurent également dans `RESULT.md`.

> Emplacement des fichiers : `tp1-solution/tp-http/public/`.
> Les deux chemins PHP sont possibles — utiliser l'un ou l'autre constant :
> ```powershell
> # PHP installé mais non ajouté aux variables d'environnement (notre machine) :
> $PHP = "C:\Users\Ayoub\php-8.5\php.exe"
> # ou, si PHP est dans le PATH :
> $PHP = "php"
> ```

---

## 0. Préparation de l'environnement (pratique — obligatoire pour tout le TP)

### Étape 0.1 — Vérifier PHP
- **Faire :** dans PowerShell, lancer `$PHP -v`.
- **Attendu :** une version affichée. Notre machine : `PHP 8.5.12 (cli)`.
- **Critère de la fiche :** PHP 8.0 ou supérieur.

### Étape 0.2 — Créer le dossier et lancer le serveur
- **Fichiers :** `tp-http/public/` contient déjà les 7 fichiers demandés :
  `index.php`, `get.php`, `post.php`, `headers.php`, `status.php`,
  `redirect.php`, `method.php`.
- **Faire :**
  ```powershell
  cd "C:\Users\Ayoub\StudioProjects\WebSec TP\tp1-solution\tp-http"
  & $PHP -S 127.0.0.1:8080 -t public
  ```
- **Attendu :** `PHP 8.5.12 Development Server (http://127.0.0.1:8080) started`,
  `Document root is ...`, `Press Ctrl-C to quit`.

### Étape 0.3 — Ouvrir dans le navigateur
- **Faire :** ouvrir `http://127.0.0.1:8080/` (réponse : page d'accueil avec
  titre + liens).

### Étape 0.4 — En cas de « port déjà utilisé »
- **Faire :** si le navigateur ne charge pas ou le terminal affiche
  `Address already in use`, choisir **un autre port** (ex. 8082) :
  `& $PHP -S 127.0.0.1:8082 -t public`, puis **reporter ce port dans toutes les
  URL** (y compris dans les commandes curl du guide).

---

## Partie A — Observer et créer la page d'accueil

### Étape 1 (pratique) — Créer index.php avec un titre et des liens
- **Fichier :** `public/index.php` (déjà créé).
- **Contenu :** un titre `<h1>TP 01 — Découvrir et expérimenter HTTP</h1>` et la
  liste `<a href="get.php">…</a>`, `post.php`, `headers.php`, lien de test de
  chaque code (`status.php?code=200|400|404|405`), `redirect.php`,
  `method.php`, `fichier-inexistant.php`.
- **Faire :** ouvrir `http://127.0.0.1:8080/index.php` — tous les liens doivent
  être cliquables.

### Étape 2 (pratique) — Ouvrir F12 → Réseau et recharger
- **Faire :**
  1. `F12` → onglet **Réseau**.
  2. Cocher la case **Conserver le journal** (à gauche, au-dessus des lignes).
  3. Appuyer `Ctrl+R` (ou F5) pour recharger `index.php`.
- **Attendu :** une ligne `index.php` s'ajoute à la table.

### Étape 3 (pratique) — Relever la requête et la réponse
- **Faire :** cliquer la ligne `index.php`, ouvrir les onglets
  **En-têtes** :
  - côté requête, relever : méthode `GET`, URL cible `/index.php`,
    en-têtes `Host: 127.0.0.1:8080`, `User-Agent: Mozilla/5.0 …`,
    `Accept: text/html,…`.
  - côté réponse, relever : statut `200 OK`, `Content-Type:
    text/html; charset=UTF-8`.
- **Vérifié (test réel) :** conforme au tableau ci-dessus.
- **Curl équivalent :** `curl -i http://127.0.0.1:8080/index.php`

### Q A1 (théorique) — Quelle est la différence entre l'URL et le chemin de la ressource ?
- **Réponse :** l'**URL** est l'adresse complète
  `scheme://hôte:port/chemin?paramètres` (ex.
  `http://127.0.0.1:8080/get.php?nom=Sara&ville=Oran`) : elle contient le
  protocole, l'hôte et le port qui servent à trouver le serveur, puis le
  chemin et la chaîne de paramètres. Le **chemin de la ressource** est le
  segment `/get.php?nom=Sara&ville=Oran` : c'est la partie après
  l'`hôte:port`, celle qui est envoyée sur la ligne de démarrage de la
  requête (`GET /get.php?nom=Sara&ville=Oran HTTP/1.1`). Autrement dit : le
  chemin n'est qu'une **portion** de l'URL, utilisée par le serveur pour
  cibler la ressource une fois la connexion établie.

### Q A2 (théorique) — Pourquoi une réponse 200 peut-elle être reçue même sans saisir de paramètres ?
- **Réponse :** parce que le statut 200 indique « la requête a réussi et le
  serveur livre une représentation de la ressource ». Demander `get.php`
  sans paramètres est une requête valide : le fichier existe, s'exécute et
  affiche le formulaire — les champs prennent simplement leurs valeurs par
  défaut (`Anonyme`/`Inconnue` — constaté lors du test réel avec `$_GET` vide,
  GET /get.php → 200). Le code de statut porte sur le traitement de
  **requête + ressource**, pas sur la présence des paramètres.

---

## Partie B — Formulaire GET

### Étape 4 (pratique) — Créer get.php avec le formulaire
- **Fichier :** `public/get.php` (déjà créé).
- **Exigence de la fiche satisfaite :** `<form method="get" action="get.php">`
  contenant deux champs avec **attribut name** :
  `<input type="text" name="nom">` et `<input type="text" name="ville">`.
- **Faire :** ouvrir `http://127.0.0.1:8080/get.php`.

### Étape 5 (pratique) — Afficher les valeurs via $_GET avec valeur par défaut
- **Réalisé dans le code :**
  ```php
  $nom   = $_GET['nom']   ?? 'Anonyme';
  $ville = $_GET['ville'] ?? 'Inconnue';
  ```
- **Faire :** charger `get.php` SANS paramètres → la page affiche
  `nom = Anonyme` / `ville = Inconnue` (vérifié : sans paramètre, GET
  donne bien ces valeurs par défaut). Puis soumettre le formulaire avec
  n'importe quelles valeurs → la page affiche les valeurs saisies.

### Étape 6 (pratique) — Afficher la chaîne brute + TEST à faire
- **Réalisé dans le code :** valeur affichée de `$_SERVER['QUERY_STRING']`.
- **Test demandé (à faire dans le navigateur) :**
  1. Saisir dans le champ « Nom » : `Ali Ben`
  2. Saisir dans le champ « Ville » : `Oran & environ`
  3. Cliquer « Envoyer (GET) ».
- **Attendu (vérifié en réel) :**
  - barre d'adresse : `get.php?nom=Ali+Ben&ville=Oran+%26+environ`
  - page : `nom = Ali Ben` ; `ville = Oran & environ` (valeurs DÉCODÉES)
  - `QUERY_STRING` affichée : `nom=Ali+Ben&ville=Oran+%26+environ`
    (chaîne BRUTE).
- **Curl équivalent :**
  `curl http://127.0.0.1:8080/get.php?nom=Ali+Ben&ville=Oran+%26+environ`

### Étape 7 (pratique) — Observer l'URL et le Réseau → tester l'URL directe
- **Faire :**
  1. F12 → Réseau → recharger, cliquer la requête `get.php` → onglet
     **Payload / Paramètres** : le navigateur liste `nom` et `ville`
     DÉCODÉS.
  2. **Saisir directement dans la barre d'adresse**
     `http://127.0.0.1:8080/get.php?nom=Sara&ville=Oran` puis Entrée.
- **Attendu :** page affiche `nom = Sara`, `ville = Oran` — le serveur voit
  exactement la même requête qu'après une soumission formulaire (lien de
  test aussi présent dans la page).

### Q B1 (théorique) — Où apparaissent les paramètres ?
- **Réponse :** dans la **query string** de l'URL, après un `?`, sous la forme
  `nom=valeur&paires…` : visible dans la barre d'adresse et dans la **ligne de
  requête** (`GET /get.php?nom=Ali+Ben&… HTTP/1.1`). Il n'y a ni en-tête ni
  corps spécifique (GET n'a pas de corps). Dans l'onglet Réseau →
  « Payload/Paramètres », on les retrouve décodés.

### Q B2 (théorique) — Pourquoi les espaces et le symbole & sont-ils encodés ?
- **Réponse :** parce que certains caractères ont un **rôle syntaxique** dans
  une URL : `&` sépare les paires `nom=valeur`, `?` sépare la query string du
  chemin, l'espace est interdit. Envoyés bruts, `Oran & environ` serait lu
  comme la valeur `Oran ` suivi d'un paramètre nommé ` environ` → ambiguïté.
  L'encodage percent (`&` → `%26`, espace → `+` ou `%20`) garantit que la
  valeur est transmise sous une forme admissible puis décodée à l'identique
  par le serveur : la chaîne reste **sans ambiguïté** (vérifié : la valeur
  décodée revient bien `Oran & environ`).

### Q B3 (théorique) — Différenciez la valeur décodée dans $_GET de la chaîne brute dans l'URL.
- **Réponse :**
  - chaîne brute (`$_SERVER['QUERY_STRING']`) :
    `nom=Ali+Ben&ville=Oran+%26+environ` — encodage produit par le navigateur,
    aucune interprétation.
  - valeurs décodées (`$_GET`) : `nom = "Ali Ben"`, `ville = "Oran & environ"`
    — PHP procède au décodage (espace, %26 → caractère), découpe au `&`, associe
    chaque valeur à sa clé.
- **Constat réel (vérifié) :** les deux sont exactement celles-là.

### Q B4 (théorique) — Est-il conseillé de placer un mot de passe dans une URL ?
- **Réponse :** **non.** L'URL apparaît en clair dans la barre d'adresse,
  l'historique du navigateur, les journaux du serveur et des intermédiaires,
  et peut fuiter via `Referer` si le client suit un lien. On utilise POST +
  HTTPS (le corps POST ne va pas dans l'URL, cf. C1), et même ainsi on
  privilégie de ne pas reproduire le mot de passe dans des URLs
  (liens de confirmation, logs, redirection).

---

## Partie C — Formulaire POST

### Étape 8 (pratique) — Créer post.php
- **Fichier :** `public/post.php` (déjà créé).
- **Exigence :** `<form method="post" action="post.php">` avec un champ
  `name="nom"` et un champ `name="message"`.
- **Faire :** ouvrir `http://127.0.0.1:8080/post.php`.

### Étape 9 (pratique) — Afficher $_POST uniquement après soumission
- **Réalisé dans le code :** test via `$_SERVER['REQUEST_METHOD']` :
  ```php
  $apresSoumission = (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST');
  ```
- **Faire :** ouvrir `post.php` (pas de soumission) → la page affiche
  « Aucune soumission » et `REQUEST_METHOD = GET`. Saisir nom et message,
  cliquer « Envoyer (POST) » → la page affiche les champs reçus.
- **Vérifié en réel :** `curl -i -d "nom=Ali&message=Bonjour" .../post.php` →
  200, `nom = Ali`, `message = Bonjour` après soumission.

### Étape 10 (pratique) — Repérer Payload + en-tête Content-Type
- **Faire :** F12 → Réseau → soumettre le formulaire → cliquer la requête
  `post.php` → section **Payload** (Form Data : `nom`, `message`) et
  en-têtes de requête : `Content-Type: application/x-www-form-urlencoded`.
- **Curl équivalent :** `curl -i -d "nom=Ali&message=Bonjour" http://127.0.0.1:8080/post.php`
  → corps visible, même en-tête Content-Type ajouté par le navigateur / curl.

### Étape 11 (pratique) — Comparer avec GET, texte avec caractères spéciaux
- **Faire :** saisir dans le champ Message : « Oran & environ ! » puis
  Envoyer. Regarder la barre d'adresse (reste `post.php`, aucun `?`) et le
  corps dans Réseau → Payload.
- **Attendu :** la chaîne reste entière dans le **corps** : l'URL ne montre
  jamais de chaîne encodée %xx, contrairement à GET.

### Q C1 (théorique) — Où se trouvent les données de POST ?
- **Réponse :** dans le **corps de la requête**, après les en-têtes et une
  ligne vide, sous la syntaxe `nom=valeur&nom2=valeur2`. Le navigateur
  définit `Content-Type: application/x-www-form-urlencoded` pour indiquer ce
  format, et PHP le fournit dans `$_POST`. L'URL, elle, reste `post.php`.

### Q C2 (théorique) — Pourquoi ne sont-elles pas dans la query string ?
- **Réponse :** parce que la méthode POST est définie par le protocole pour
  envoyer les données dans le corps du message. La query string est réservée
  aux paramètres visibles et limités en longueur ; mettre les données
  de POST dans la query string les rendrait visibles/copiables/limitées, comme
  GET. On ne voit donc pas de paramètres dans l'URL, sauf si on les y ajoute
  explicitement.

### Q C3 (théorique) — Le corps POST est-il chiffré lorsque le serveur utilise http:// ?
- **Réponse :** **non.** Avec `http://`, tout le message — ligne de démarrage,
  en-têtes, corps — circule **en clair** : le corps POST est lisible par tout
  intermédiaire. Chiffrer le corps seul ne suffirait pas (la ligne de
  démarrage et les en-têtes restent exposés) ; la protection complète est de
  chiffrer le canal.

### Q C4 (théorique) — Quel mécanisme apporte la confidentialité du transport ?
- **Réponse :** **HTTPS (TLS)** : chiffre l'échange complet (URL, en-têtes,
  corps, cookies), et garantit aussi l'authentification du serveur et
  l'intégrité. Sans HTTPS, le corps POST n'apporte aucune confidentialité —
  passer à `https://` est la seule mesure de transport.

---

## Partie D — Observer les en-têtes

### Étape 12 (pratique) — Créer headers.php
- **Fichier :** `public/headers.php` (déjà créé).
- **Exigence satisfaite :** la page lit dans `$_SERVER` les entrées
  `HTTP_HOST` (Host), `HTTP_USER_AGENT` (User-Agent), `HTTP_ACCEPT` (Accept),
  `CONTENT_TYPE` (Content-Type — sans préfixe `HTTP_`) et `HTTP_REFERER`
  (Referer), et affiche **(absent)** quand l'en-tête n'a pas été envoyé.

### Étape 13 (pratique) — Définir deux en-têtes de réponse
- **Réalisé dans le code (avant toute sortie HTML) :**
  ```php
  header('Content-Type: text/html; charset=UTF-8');
  header('X-Lab-HTTP: initiation');
  ```
- **Faire :** ouvrir `http://127.0.0.1:8080/headers.php`, puis curl :
  `curl -I http://127.0.0.1:8080/headers.php`
- **Attendu (vérifié) :** en-têtes de réponse
  `Content-Type: text/html; charset=UTF-8` et `X-Lab-HTTP: initiation`.

### Étape 14 (pratique) — Comparer affichage serveur vs Réseau ; accès direct vs lien
- **Faire :**
  1. F12 → Réseau → recharger `headers.php` : dans **En-têtes de requête** ce
     sont les mêmes valeurs que celles affichées par le serveur (Host,
     User-Agent, Accept).
  2. **Accès direct :** collez l'URL dans la barre d'adresse → Referer est
     **absent** (page affiche « (absent) »).
  3. **Accès depuis un lien :** depuis `index.php`, cliquer le lien
     « headers.php » → Referer vaut l'URL d'`index.php`.

### Q D1 (théorique) — Quels en-têtes peuvent être absents ?
- **Réponse :** pour une requête GET : `Content-Type` (aucun corps) et
  `Referer` (saisie directe, favori, `Referrer-Policy` qui supprime l'envoi).
  Un en-tête n'apparaît que s'il décrit une donnée réellement présente ;
  Host, User-Agent et Accept sont presque toujours envoyés mais restent
  optionnels du point de vue du protocole.

### Q D2 (théorique) — Quel est le rôle de Content-Type dans la réponse ?
- **Réponse :** il indique le **type du corps** (type MIME) :
  `text/html; charset=UTF-8` = « corps HTML interprété avec UTF-8 ». C'est à
  partir de cet en-tête que le navigateur décide d'afficher du HTML,
  télécharger un fichier, exécuter du JS, etc., au lieu de se fier à
  l'extension du chemin (faillible). Un Content-Type inadéquat est source de
  failles de type **XSS**. Le nôtre est activé globalement : la page servie
  renvoie bien `Content-Type: text/html; charset=UTF-8` (constaté par curl).

### Q D3 (théorique) — Pourquoi un lien suivi depuis une page peut-il produire un Referer différent d'une saisie directe ?
- **Réponse :** en **saisie directe**, aucune page précédente n'est à
  l'origine de la navigation → aucun Referer. En cliquant un **lien**, la
  navigation part d'une URL précise, envoyée dans `Referer` (ou seulement son
  origine selon `Referrer-Policy`). Le Referer dépend du **point de départ**,
  pas de la ressource cible ; il est donc contextuel et n'est pas une
  garantie de sécurité côté serveur.

---

## Partie E — Codes de statut

### Étape 15 (pratique) — status.php accepte 200/400/404/405, autre → 400
- **Fichier :** `public/status.php?code=…` (déjà créé ; utilise
  `http_response_code()`).
- **Tests à faire (vérifiés en réel) :**
  ```powershell
  curl -o NUL -w "code=%{http_code}`n" http://127.0.0.1:8080/status.php?code=200   # → 200
  curl -o NUL -w "code=%{http_code}`n" http://127.0.0.1:8080/status.php?code=400   # → 400
  curl -o NUL -w "code=%{http_code}`n" http://127.0.0.1:8080/status.php?code=404   # → 404
  curl -o NUL -w "code=%{http_code}`n" http://127.0.0.1:8080/status.php?code=405   # → 405
  curl -o NUL -w "code=%{http_code}`n" http://127.0.0.1:8080/status.php?code=500   # → 400 (valeur refusée)
  ```
- **Attendu :** chaque URL renvoie exactement le statut demandé s'il fait
  partie de la liste, sinon 400.

### Étape 16 (pratique) — redirect.php
- **Fichier :** `public/redirect.php` (déjà créé) :
  ```php
  header('Location: index.php', true, 302);
  exit;
  ```
- **Test (vérifié) :** `curl -I http://127.0.0.1:8080/redirect.php` →
  `HTTP/1.1 302 Found` + `Location: index.php`. Dans le navigateur, l'accès à
  redirect.php redirige immédiatement vers index.php (affichage de la page
  d'accueil).

### Étape 17 (pratique) — method.php : GET admis, POST → 405 + Allow: GET
- **Fichier :** `public/method.php` (déjà créé).
- **Tests à faire (vérifiés) :**
  - navigateur / GET : `curl -o NUL -w "%{http_code}`n" http://127.0.0.1:8080/method.php`
    → 200 (page affichée, méthode admise)
  - POST :
    ```powershell
    curl -i -X POST http://127.0.0.1:8080/method.php
    ```
    → `HTTP/1.1 405 Method Not Allowed` + en-tête `Allow: GET`.
  - (alternative navigateur : une requête POST depuis la barre d'adresse
    n'est pas possible — utiliser curl, ou un petit formulaire avec
    `method="post"` pointant `method.php`.)

### Étape 18 (pratique) — Inspecter 302 via Réseau ou curl -i ; comparer les deux 404
- **Faire pour le 302 :**
  - F12 → Réseau → ouvrir `http://127.0.0.1:8080/redirect.php` : on voit la
    requête `redirect.php` (statut 302) et de suite la requête suivante
    `GET /index.php` (200) — la chaîne de redirection.
  - `curl -i http://127.0.0.1:8080/redirect.php` (sans suivi automatique) →
    on ne voit QUE la réponse 302.
- **Faire pour le 404 :**
  - 404 émis par PHP : `http://127.0.0.1:8080/status.php?code=404` → 404
    Not Found, page écrite par le script.
  - Chemin de fichier absent :
    `http://127.0.0.1:8080/fichier-inexistant.php` → sur **PHP ≤ 8.3** :
    `404` du serveur (page d'erreur du serveur, sans `X-Powered-By`).
    Sur **PHP 8.4+ (notre 8.5.12, constaté)** : le serveur retombe sur
    `index.php` et répond `200` — ce changement de comportement est
    documenté, voir note dans `RESULT.md`. Si la machine de démo tourne avec
    un PHP 8.x plus ancien, l'observation correspond à la cible de la fiche.

### Q E1 (théorique) — Qui décide du code de statut ?
- **Réponse :** le **serveur** : le couple serveur web (ici le serveur
  intégré de PHP) + le code applicatif. Le serveur web décide seul pour ce
  qui se passe **avant** l'exécution PHP (fichier absent → 404, requête
  malformée → 400) ; le code PHP décide du statut de la logique métier
  (`http_response_code()`, `header('Location: …')`, `header('Allow: …')`).
  Le client ne « décide » pas du statut, il le reçoit et l'interprète.

### Q E2 (théorique) — Pourquoi 302 déclenche-t-il une seconde requête ?
- **Réponse :** 302 = code de redirection temporaire : la réponse transporte
  un en-tête `Location` qui indique la nouvelle adresse. Le navigateur est
  défini pour suivre cette indication automatiquement : il émet immédiatement
  une **nouvelle requête GET** vers cette URL. C'est le navigateur (pas le
  serveur) qui fait le second aller-retour ; le serveur a fini son travail
  avec la seule réponse 302 (constaté : curl sans suivi ne montre que la 302).

### Q E3 (théorique) — Quelle différence entre 404 et 405 ?
- **Réponse :** **404 Not Found** : la ressource n'existe pas à cette URL
  (fichier/script absent) ; redemander la même URL avec une autre méthode
  donnerait encore 404. **405 Method Not Allowed** : la ressource existe
  mais la méthode utilisée n'est pas permise sur elle ; la réponse doit
  alors porter l'en-tête `Allow`. 404 = problème de chemin ; 405 = problème
  de méthode (vérifié : 405 sur method.php + Allow: GET).

### Q E4 (théorique) — Que contient Allow dans une réponse 405 ?
- **Réponse :** la liste des **méthodes HTTP autorisées** sur la ressource,
  séparées par des virgules : `Allow: GET` pour `method.php` (ou
  `Allow: GET, POST, OPTIONS` pour un point qui les admettrait). Elle
  indique au client la bonne méthode à utiliser ensuite ; `Allow` est
  prévu pour les réponses 405 (et 501). Notre test réel montre bien
  `Allow: GET`.

---

## Partie F — Manipulations et expérimentation

### Étape 19 (pratique) — curl -i sur headers.php
- **Faire :**
  ```powershell
  curl -i http://127.0.0.1:8080/headers.php
  ```
- **Attendu (constaté) :** la réponse brute complète (statut, en-têtes
  `Content-Type`, `Content-Length`, `X-Lab-HTTP: initiation`, …) PUIS la
  page HTML. La même chose que l'onglet Réseau, mais en texte.

### Étape 20 (pratique) — curl -d sur post.php, comparaison avec le formulaire HTML
- **Faire :**
  ```powershell
  curl -i -d "nom=Ali&message=Bonjour" http://127.0.0.1:8080/post.php
  ```
- **Attendu (constaté) :** POST avec corps `nom=Ali&message=Bonjour` = soumission
  formulaire HTML à la même URL avec les mêmes champs : le serveur retourne la
  même page (`$_POST['nom']='Ali'`, `$_POST['message']='Bonjour'`),
  même Content-Type `application/x-www-form-urlencoded`.

### Étape 21 (pratique) — Modifier un paramètre GET / une valeur POST
- **Faire :**
  1. **GET :** dans la barre d'adresse changer `nom=Lila` en `nom=Omar`
     (ou remplir avec autres valeurs) puis Envoyer : le serveur voit les
     nouvelles valeurs, pas les anciennes.
  2. **POST :** si Burp Repeater est disponible, intercepter le POST et
     changer `message=Bonjour` en `message=Modifié`. Sinon (notre machine) :
     éditer directement les champs du formulaire et renvoyer via curl
     `curl -i -d "nom=Ali&message=Modifié" http://127.0.0.1:8080/post.php`.

### Étape 22 (pratique) — Observation : que reçoit PHP ?
- **Attendu :** PHP reçoit les valeurs **réellement transmises** dans la
  requête (`$_GET`/`$_POST`), jamais les valeurs initialement affichées dans
  la page HTML : le changement `nom=Ali` en `nom=X` (URL ou corps) modifie ce
  qui s'affiche ensuite. C'est la base de « ne jamais faire confiance au
  client ».

### Q F1 (théorique) — Un serveur peut-il faire confiance aux valeurs d'un formulaire ?
- **Réponse :** **non.** Tout ce qui vient du client peut être modifié :
  champs, URL, en-têtes, corps POST (constaté en alternant les valeurs via
  l'URL, curl et l'édition du formulaire). Un serveur qui se fie aux valeurs
  d'un formulaire accepte n'importe quel contenu, y compris malveillant.
  Toute entrée doit être considérée comme potentiellement malveillante et
  **validée côté serveur** (voir F3).

### Q F2 (théorique) — Pourquoi POST n'assure-t-il pas la confidentialité ?
- **Réponse :** POST est une **méthode**, pas un protocole : elle définit
  où les données sont placées (dans le corps, pas l'URL), pas sous quel
  transport. Avec `http://`, le corps POST circule **en clair** et est
  lisible/modifiable par tout intermédiaire. La confidentialité dépend du
  transport : **HTTPS/TLS** (cf. C4). POST n'est pas une mesure de sécurité.

### Q F3 (théorique) — Quels contrôles seront nécessaires pour une application réelle ?
- **Réponse :** côté serveur :
  - **Valider/filtrer** toutes les données entrantes (type, longueur, liste
    de valeurs autorisées) — ne jamais se fier au navigateur (F1).
  - **Échapper les sorties** (`htmlspecialchars` pour HTML) et **requêtes
    préparées** côté BDD → protection XSS / injection SQL.
  - **HTTPS** pour le transport + cookies avec attribut `Secure`.
  - **Authentification et autorisation côté serveur** sur chaque action.
  - Gestion d'erreurs sans fuite d'information, protection **CSRF**,
    usage de POST pour les actions modifiantes, limites de taille des champs,
    contrôle des fichiers éventuels.
- Ces axes sont approfondis dans le TP 02 (sessions, SOP/CORS, CSP). Ils
  constituent le socle de la sécurité applicative.

---

## Fin du TP
Il reste à compiler tous ces éléments dans le compte rendu (`RESULT.md` déjà
rédigé) : chaque réponse théorique y est aussi en contexte.

### Commandes de test utiles — récapitulatif
```powershell
$PHP = "C:\Users\Ayoub\php-8.5\php.exe"
# ou "php" s'il est dans le PATH
cd "C:\Users\Ayoub\StudioProjects\WebSec TP\tp1-solution\tp-http"
& $PHP -S 127.0.0.1:8080 -t public

# dans un second terminal :
curl -i http://127.0.0.1:8080/index.php
curl -i http://127.0.0.1:8080/headers.php
curl -i "http://127.0.0.1:8080/get.php?nom=Sara&ville=Oran"
curl -i -d "nom=Ali&message=Bonjour" http://127.0.0.1:8080/post.php
curl -o NUL -w "%{http_code}`n" http://127.0.0.1:8080/status.php?code=200
curl -I http://127.0.0.1:8080/redirect.php
curl -o NUL -w "%{http_code}`n" http://127.0.0.1:8080/method.php
curl -i -X POST http://127.0.0.1:8080/method.php
```
