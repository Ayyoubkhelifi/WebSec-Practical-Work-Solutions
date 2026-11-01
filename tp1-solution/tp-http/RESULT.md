# TP 01 — Compte rendu (RESULT)

Environnement : PHP 8+ (`php -S 127.0.0.1:8080 -t public` lancé depuis
`tp-http/`), navigateur avec outils de développement (F12 → Réseau), `curl`.
Travail exclusivement en local sur `http://127.0.0.1:8080/`.

---

## Partie A — Observer et créer la page d'accueil

Réalisation : création de `index.php` (titre + liens vers les pages du TP) ;
ouverture de F12 → Réseau avec conservation du journal, puis rechargement de
`index.php`.

Observations côté requête : méthode `GET`, URL
`http://127.0.0.1:8080/index.php`, en-têtes `Host: 127.0.0.1:8080`,
`User-Agent: Mozilla/5.0 …` (identité du navigateur) et `Accept: text/html,…`
(formats souhaités par le client). Côté réponse : statut `200 OK` et
`Content-Type: text/html; charset=UTF-8`.

### Q A1 — Quelle est la différence entre l'URL et le chemin de la ressource ?

L'**URL** est l'adresse complète de la ressource :
`scheme://hôte:port/chemin?paramètres` (ex.
`http://127.0.0.1:8080/get.php?nom=Sara&ville=Oran`). Le **chemin de la
ressource** n'est qu'une partie de cette URL : c'est le segment envoyé sur la
ligne de démarrage de la requête (`GET /get.php?nom=Sara&ville=Oran HTTP/1.1`).
L'URL ajoute donc au chemin le protocole, l'hôte et le port, qui servent à
trouver le serveur ; le chemin sert ensuite seulement à cibler la ressource
une fois la connexion établie.

### Q A2 — Pourquoi une réponse 200 peut-elle être reçue même sans saisir de paramètres ?

Un statut 200 signifie « la requête a réussi et le serveur renvoie une
représentation de la ressource demandée ». Demander `get.php` sans paramètres
est une requête valide : le fichier existe, s'exécute et le formulaire est
affiché (les champs prennent leurs valeurs par défaut). Le code de statut porte
sur le traitement de la **requête et de la ressource**, pas sur la présence de
paramètres : `$_GET['nom']` est simplement absent, ce qui n'empêche ni le
routage ni l'exécution du script.

---

## Partie B — Formulaire GET

Réalisation : `get.php` avec formulaire `method="get" action="get.php"` et deux
champs `nom` et `ville` portant chacun un attribut `name` ; affichage des
valeurs récupérées via `$_GET` avec valeurs par défaut (`Anonyme` /
`Inconnue`) ; affichage de `$_SERVER['QUERY_STRING']`.

Tests : nom = `Ali Ben` et ville = `Oran & environ` → URL
`get.php?nom=Ali+Ben&ville=Oran+%26+environ` ; dans Réseau → Payload, le
navigateur affiche les valeurs décodées alors que `QUERY_STRING` garde la
chaîne brute. URL directe `get.php?nom=Sara&ville=Oran` : le serveur voit
exactement la même requête que lors d'une soumission du formulaire.

### Q B1 — Où apparaissent les paramètres ?

Dans la **query string**, après un `?` en fin d'URL, sous la forme
`nom=valeur&nom2=valeur2` : visibles dans la barre d'adresse et sur la **ligne
de requête** `GET /get.php?nom=Sara&ville=Oran HTTP/1.1`. Noyés dans l'URL, ils
n'ont ni en-tête ni corps dédié (GET n'a pas de corps) ; l'onglet Réseau →
Payload/Paramètres les présente décodés.

### Q B2 — Pourquoi les espaces et le symbole & sont-ils encodés ?

Certains caractères ont un **rôle syntaxique** dans une URL : `&` sépare les
paires `nom=valeur`, `?` sépare la query string du chemin, l'espace n'est pas
admissible. Envoyés bruts, `Oran & environ` seraient lus comme la valeur
`Oran ` suivie d'un paramètre nommé ` environ` : l'URL deviendrait ambiguë.
L'encodage percent (`&` → `%26`, espace → `+` ou `%20`) garantit que chaque
caractère est transmis sous une forme autorisée, puis **décodé** à
l'identique par le serveur ; c'est ce qui rend la valeur unique et non
ambiguë, conforme au jeu de caractères admissible dans une URL.

### Q B3 — Différenciez la valeur décodée dans $_GET de la chaîne brute dans l'URL.

- Chaîne brute (`$_SERVER['QUERY_STRING']`) :
  `nom=Ali+Ben&ville=Oran+%26+environ` — l'encodage produit par le navigateur
  sans aucune interprétation.
- Valeur décodée dans `$_GET` : `$_GET['nom'] = "Ali Ben"` et
  `$_GET['ville'] = "Oran & environ"` — PHP décode la chaîne (espace → `+`,
  `%26` → `&`), la découpe au `&`, puis range chaque valeur à sa clé.

Le contenu logique est identique ; seule la représentation change : encodée
dans l'URL, décodée dans `$_GET`.

### Q B4 — Est-il conseillé de placer un mot de passe dans une URL ?

**Non.** L'URL apparaît en clair dans la barre d'adresse, l'historique du
navigateur, les journaux du serveur et des intermédiaires, et peut fuiter via
l'en-tête `Referer` si le client suit un lien. La méthode GET n'est pas faite
pour des données sensibles ; on utilise POST + HTTPS, et même ainsi, on évite
que le mot de passe se retrouve dans des URLs (liens de confirmation, logs,
paramètres de redirection).

---

## Partie C — Formulaire POST

Réalisation : `post.php` avec formulaire `method="post"` contenant `nom` et
`message` ; affichage des champs **uniquement après soumission**, détecté par
`$_SERVER['REQUEST_METHOD'] === 'POST'` ; dans Réseau → Payload on relève les
données du formulaire et l'en-tête `Content-Type:
application/x-www-form-urlencoded`. Comparaison avec GET en saisissant un
texte avec espaces et caractères spéciaux : en POST, l'URL ne change pas et
l'URL reste `post.php`.

### Q C1 — Où se trouvent les données de POST ?

Dans le **corps de la requête**, sous la même syntaxe `nom=valeur&nom2=valeur2`
mais après les en-têtes et une ligne vide. Le navigateur définit
`Content-Type: application/x-www-form-urlencoded` pour signaler ce format, et
PHP fournit le résultat dans la superglobale `$_POST`. L'URL, elle, reste
`post.php` (aucun `?`, aucun paramètre visible).

### Q C2 — Pourquoi ne sont-elles pas dans la query string ?

Parce que la méthode POST est définie par le protocole pour envoyer les
données dans le corps du message : la query string est réservée aux paramètres
visibles et limités en longueur. Mettre les données d'un POST dans la query
string la détruirait probablement (limites d'URL) et fonctionnerait davantage
avec la sémantique de GET (données visibles, à re-saisir en re-chargement).
On ne trouve donc pas de paramètres dans l'URL à moins de les y ajouter
explicitement.

### Q C3 — Le corps POST est-il chiffré lorsque le serveur utilise http:// ?

**Non.** Avec `http://`, tout le message — ligne de démarrage, en-têtes, corps
— circule **en clair** : le corps POST, comme les données GET, est lisible par
tout intermédiaire sur le chemin (réseau local, proxies, points d'écoute).
Chiffrer le corps seul ne suffirait pas, car la ligne de démarrage et les
en-têtes resteraient exposés ; la seule protection complète est de chiffrer
toute la connexion.

### Q C4 — Quel mécanisme apporte la confidentialité du transport ?

**HTTPS (TLS)**, qui chiffre l'échange complet entre navigateur et serveur
(URL demandée, en-têtes, corps, cookies). Avec HTTPS, les paramètres GET et
le corps POST restent invisibles pour un écouteur réseau, et
l'authentification du serveur est également garantie. Sans HTTPS, le corps
POST n'apporte aucune confidentialité : changer `http://` en `https://` est
la seule mesure qui chiffre réellement le transport.

---

## Partie D — Observer les en-têtes

Réalisation : `headers.php` affiche, lorsqu'ils existent, `Host`
(`$_SERVER['HTTP_HOST']`), `User-Agent` (`HTTP_USER_AGENT`), `Accept`
(`HTTP_ACCEPT`), `Content-Type` (`CONTENT_TYPE`, nom sans préfixe `HTTP_`) et
`Referer` (`HTTP_REFERER`). Il définit **avant toute sortie HTML** les deux
en-têtes de réponse `Content-Type: text/html; charset=UTF-8` et
`X-Lab-HTTP: initiation` via `header()`.

Test (étape 14) : accès direct à la page (barre d'adresse) → `Referer` absent
dans la requête et « (absent) » côté serveur ; accès depuis un lien
d'`index.php` → `Referer` vaut l'URL d'`index.php`, côté serveur comme dans
l'onglet Réseau, ce qui montre bien que le serveur reçoit les en-têtes réels
envoyés par le navigateur.

### Q D1 — Quels en-têtes peuvent être absents ?

Pour une requête GET d'accueil : `Content-Type` (la requête n'a pas de corps)
et `Referer` (saisie directe, navigation depuis un favori, ou
`Referrer-Policy: no-referrer` qui supprime l'envoi). Un en-tête n'est
présent que s'il décrit une donnée réellement existante (un corps pour
Content-Type, une page précédente pour Referer). `Host`, `User-Agent` et
`Accept` sont pratiquement toujours envoyés par les navigateurs mais restent
techniquement optionnels du point de vue du protocole.

### Q D2 — Quel est le rôle de Content-Type dans la réponse ?

Il indique au navigateur le **type du corps renvoyé** (type MIME) :
`text/html; charset=UTF-8` signifie « le corps est du HTML, interprété avec
l'encodage UTF-8 ». C'est à partir de cet en-tête que le navigateur décide
d'afficher du HTML, de télécharger un fichier, de déclencher un lecteur video,
etc., et non pas de s'en remettre à l'extension du fichier, qui est
faillible. Un `Content-Type` faux ou dépourvu est source de vulnérabilités de
type **XSS**, puisqu'un navigateur peut identifier autrement le contenu
(text/plain, application/json, sniffing de contenu iš de image).

### Q D3 — Pourquoi un lien suivi depuis une page peut-il produire un Referer différent d'une saisie directe ?

En **saisie directe** (barre d'adresse, favori), aucune page précédente n'est
à l'origine de la navigation : le navigateur n'envoie aucun Referer. En
cliquant un **lien**, la navigation part d'une URL précise, laquelle est
envoyée dans `Referer` (ou seulement son origine, selon `Referrer-Policy`).
Le Referer dépend donc du **point de départ** de la navigation, pas de la
ressource cible : la même page peut afficher un Referer différent, ou absent,
selon l'entrée. Il en résulte que le Referer n'est pas une information
fiable à exploiter côté client-serveur : contextuel au navigateur, il est
facilement falsifiable.

---

## Partie E — Codes de statut

Réalisation :
- `status.php?code=…` : accepte 200/400/404/405 et renvoie le statut demandé
  grâce à `http_response_code(...)` ; toute autre valeur (ou un code absent)
  déclenche un 400.
- `redirect.php` : `header('Location: index.php', true, 302)` puis `exit`.
- `method.php` : accessible en GET ; sur POST, renvoie 405 avec `Allow: GET`.

Table de tests :

| Test | Résultat obtenu | Résultat cible |
|---|---|---|
| GET /status.php?code=200 | 200 OK | 200 OK ✔ |
| GET /redirect.php | 302 puis nouvelle requête vers /index.php et affichage de la page | 302 puis nouvelle requête vers index.php ✔ |
| GET /status.php?code=400 | 400 Bad Request | 400 Bad Request ✔ |
| GET /status.php?code=404 | 404 Not Found | 404 Not Found ✔ |
| POST /method.php | 405 + en-tête Allow: GET | 405 + Allow: GET ✔ |
| GET /fichier-inexistant.php | **PHP 8.4– : 404 émis par le serveur lui-même** ; **PHP 8.4+ (testé, 8.5.12) : 200** — le serveur retombe sur index.php (voir note) | 404 du serveur (cible du TP) |

> **Note vérifiée (PHP 8.4+).** Depuis PHP 8.4, le serveur intégré applique la
> recherche de `index.php`/`index.html` même lorsque le chemin demandé
> « ressemble à un fichier » introuvable (« As of PHP 8.4.0, this index file
> lookup is performed even when the requested path looks like a file »,
> doc officielle). Sur notre PHP 8.5.12, `GET /fichier-inexistant.php` est donc
> servi en 200 (contenu d'index.php), et non en 404. Sur PHP 8.0–8.3,
> l'observation correspond à la cible : `404` émis par le serveur web
> lui-même, avant toute exécution PHP, avec sa page d'erreur propre
> (« 404 Not Found » sans en-tête `X-Powered-By`).

Comparaison des deux 404 (sur un PHP où le 404 du serveur est observable,
PHP ≤ 8.3) : `status.php?code=404` est un 404 **choisi par le code PHP** (la
ressource existe, le script décide ce statut) ; `/fichier-inexistant.php` est
un 404 **généré par le serveur web** avant toute exécution PHP, avec sa propre
page d'erreur.

Inspection du 302 (étape 18) : dans Réseau on voit `redirect.php` (statut
302), puis dans sa réponse l'en-tête `Location: index.php` et, de suite, la
requête suivante `GET /index.php` en 200. Avec `curl -i`, qui ne suit pas
automatiquement la redirection, on observe seulement la réponse 302 brute.

### Q E1 — Qui décide du code de statut ?

Le **serveur**, en pratique le couple serveur web (le serveur intégré de PHP
ici, Apache/Nginx en situation réelle) + code d'application. Le serveur web
décide seul pour tout ce qui se produit avant l'exécution du PHP (fichier
absent → 404, requête malformée → 400), et le code PHP décide pour la logique
métier (`http_response_code()`, `header('Location: …')`, `header('Allow: …')`).
Le client n'a pas à décider du statut : il le reçoit et l'interprète.

### Q E2 — Pourquoi 302 déclenche-t-il une seconde requête ?

Parce que 302 est le code d'une **redirection temporaire** : il signifie
« la ressource est provisoirement à une autre adresse, dont l'URL est fournie
par l'en-tête `Location` ». Le comportement des navigateurs est standardisé :
ils lisent `Location: index.php` et émettent immédiatement une nouvelle
requête GET vers cette URL. C'est donc le navigateur, et non le serveur, qui
réalise le second aller-retour ; le serveur a fini son travail avec la seule
réponse 302 et `Location`.

### Q E3 — Quelle différence entre 404 et 405 ?

- **404 Not Found** : la ressource **n'existe pas** à cette URL (fichier ou
  script introuvable) ; redemander la même URL avec une autre méthode donnera
  encore 404 tant que le chemin est inexistant.
- **405 Method Not Allowed** : la ressource **existe**, mais la méthode
  utilisée n'est pas permise sur elle ; la réponse doit alors contenir
  l'en-tête `Allow`, qui liste les méthodes admissibles.

En clair : 404 = problème de **chemin**, 405 = problème de **méthode**. On
observe les deux dans le TP : 404 pour un fichier absent, 405 sur
`method.php` avec POST.

### Q E4 — Que contient Allow dans une réponse 405 ?

La liste, séparée par des virgules, des **méthodes HTTP autorisées** sur la
ressource de cette URL : `Allow: GET` pour `method.php`, ou encore
`Allow: GET, POST, OPTIONS` pour un endpoint qui en admettrait plusieurs. Ce
champ indique au client la bonne méthode à utiliser dans sa requête
suivante ; l'en-tête `Allow` est prévu pour les réponses 405 (et 501).

---

## Partie F — Manipulations et expérimentation

Réalisation :
- `curl -i http://127.0.0.1:8080/headers.php` : affiche la réponse complète
  (en-têtes `Content-Type`, `Content-Length`, `X-Lab-HTTP: initiation`, …)
  puis le corps HTML ; la même image que l'onglet Réseau, mais en texte brut.
- `curl -i -d "nom=Ali&message=Bonjour" http://127.0.0.1:8080/post.php` :
  envoie un POST brut ; comparé à la soumission du formulaire HTML observée
  dans Réseau, on retrouve le même corps `nom=Ali&message=Bonjour` avec le
  même `Content-Type: application/x-www-form-urlencoded` et le même résultat
  côté serveur (`$_POST['nom']='Ali'`, `$_POST['message']='Bonjour'`).
- Étape 21 : modification d'un paramètre GET directement dans l'URL
  (ex. `get.php?nom=Lila&ville=Tlemcen`). Pour POST, Burp Repeater n'étant pas
  disponible, on a édité les valeurs dans le formulaire HTML pour ré-émettre
  la requête avec un autre corps.
- Étape 22 : décision côté serveur — PHP reçoit toujours les valeurs
  **réellement transmises** dans la requête (`$_GET`, `$_POST`), jamais les
  valeurs initialement affichées dans la page HTML : changer `nom=Ali` en
  `nom=X` (URL ou corps) modifie bien ce que le serveur affiche ensuite.

### Q F1 — Un serveur peut-il faire confiance aux valeurs d'un formulaire ?

**Non.** Tout ce qui vient du client peut être modifié : les champs d'un
formulaire, l'URL, les en-têtes, le corps POST, les scripts exécutés, tout
cela est sous le contrôle de l'utilisateur (c'est la conclusion directe des
étapes 21 et 22). Un serveur qui se fie aux valeurs d'un formulaire sans
vérification accepte n'importe quels contenus, y compris malveillants : on
doit considérer toute entrée comme non fiable et la **valider côté serveur**
avant de l'utiliser (voir F3).

### Q F2 — Pourquoi POST n'assure-t-il pas la confidentialité ?

Parce que POST n'est qu'une **méthode** : elle définit où les données sont
placées (dans le corps, pas dans la query string), pas sous quel protocole
elles circulent. Avec `http://`, le corps POST voyage **en clair** et peut
être lu ou modifié par n'importe quel intermédiaire sur la route. La
confidentialité ne relève pas de la méthode mais du transport : seul
**HTTPS/TLS** chiffre l'échange complet (voir C3‑C4). POST n'est donc pas une
mesure de sécurité, c'est un choix sémantique de la méthode.

### Q F3 — Quels contrôles seront nécessaires pour une application réelle ?

Contrôles côté serveur :
- **Validation et filtrage systématiques** de toutes les données entrantes
  (type, longueur, valeurs autorisées), car le client n'est pas une référence
  (F1).
- **Échappement des sorties** (`htmlspecialchars` pour le HTML) et
  requêtes préparées côté base pour prévenir **XSS** et **injection SQL**.
- **HTTPS pour le transport**, avec cookies portant l'attribut `Secure`.
- **Authentification et autorisation côté serveur** pour chaque action ; ne
  jamais dériver la décision de sécurité de ce qu'envoie le client.
- Gestion d'erreurs sans fuite d'information, protection **CSRF**,
  méthodes adéquates (POST pour des actions modifiantes), limites de taille
  des données, et vérification des fichiers éventuels.

Ces axes — validation des entrées, échappement des sorties, transport
chiffré, authentification — seront approfondis dans le TP 02 (sessions,
SOP/CORS, CSP) et constituent le socle de la sécurité applicative web.

---

## Synthèse du TP

- Une requête HTTP = ligne de démarrage (méthode + chemin + version) +
  en-têtes + corps éventuel ; une réponse = statut + en-têtes + corps
  éventuel ; HTTP est sans état, chaque échange est traité indépendamment.
- GET porte les paramètres dans l'URL (encodés, visibles, limités) ; POST les
  porte dans le corps ; la confidentialité relève de HTTPS, pas de la méthode.
- `$_GET`, `$_POST`, `$_SERVER`, `header()` et `http_response_code()` sont les
  outils PHP directs de ces mécanismes.
- Aucune donnée client n'est fiable : valider les **entrées**, échapper les
  **sorties**, chiffrer le **transport**, et les codes de statut + en-têtes
  (`Location`, `Allow`, `Content-Type`) sont des choix explicites du serveur.
