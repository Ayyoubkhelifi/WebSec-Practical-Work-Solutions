# TP 01 — Plan : Découvrir et expérimenter HTTP

**Fiche :** TP 01 — Découvrir et expérimenter HTTP (3 h, binômes, PHP natif + navigateur).
**Contrainte :** travail uniquement en local sur `http://127.0.0.1:8080/`, aucun site tiers.

## Objectifs couverts

- Différencier requête, réponse, méthode, URL, en-têtes et corps.
- Observer GET, POST, paramètres de requête et données du corps.
- Manipuler `$_GET`, `$_POST` et `$_SERVER` en PHP natif.
- Identifier 200, 302, 400, 404, 405 + `Content-Type`, `Location`, `Set-Cookie` (côté fiche : 200, 302 implicite, 400, 404, 405).
- Utiliser les outils Réseau du navigateur (et `curl -i` en Partie F).

## Environnement de lancement

```
cd tp-http
php -S 127.0.0.1:8080 -t public
# puis ouvrir http://127.0.0.1:8080/
```
(En cas de port occupé : choisir un autre port et le reporter dans les URL.)

## Fichiers à créer (structure imposée par la fiche)

```
tp-http/
├── PLAN.md, PROGRESS.md, RESULT.md
└── public/
    ├── index.php      → Parte A : page d'accueil + liens (étape 1)
    ├── get.php        → Parte B : formulaire GET, $_GET, QUERY_STRING (étapes 4–7)
    ├── post.php       → Parte C : formulaire POST, $_POST, REQUEST_METHOD (étapes 8–11)
    ├── headers.php    → Parte D : en-têtes requête ($_SERVER) + header() (étapes 12–14)
    ├── status.php     → Parte E : codes 200/400/404/405 via http_response_code() (étape 15)
    ├── redirect.php   → Parte E : 302 + Location: index.php + exit (étape 16)
    └── method.php     → Parte E : GET ok, POST → 405 + Allow: GET (étape 17)
```

## Ordre de travail (parties de la fiche)

| Partie | Contenu | fiches d'étapes |
|--------|---------|-----------------|
| A | index.php + observation Réseau (méthode, URL, Host, User-Agent, Accept ; statut, Content-Type) | 1–3 |
| B | get.php : method="get", champs `nom`/`ville`, valeurs par défaut, `$_GET`, `$_SERVER['QUERY_STRING']`, test `nom="Ali Ben"`, `ville="Oran & environ"`, URL directe `?nom=Sara&ville=Oran` | 4–7 |
| C | post.php : method="post", `nom`/`message`, affichage uniquement après soumission, test via `$_SERVER['REQUEST_METHOD']`, Payload + Content-Type | 8–11 |
| D | headers.php : Host, User-Agent, Accept, Content-Type, Referer ; `header()` : `Content-Type: text/html; charset=UTF-8` + `X-Lab-HTTP: initiation`, avant toute sortie | 12–14 |
| E | status.php (+ redirect.php, method.php) : tests ciblés de la table (200 / 302→index / 400 / 404 / 405+Allow / 404 du serveur), inspection 302 et 404 du serveur | 15–18 |
| F | curl -i sur headers.php, curl -d sur post.php, modification d'un paramètre GET dans l'URL / du corps POST, interprétation | 19–22 |

## Questions à traiter dans RESULT.md (compte rendu)

- A1–A2 : différence URL/chemin ; pourquoi un 200 sans paramètres.
- B1–B4 : endroit des paramètres ; encodage des espaces/& ; `$_GET` décodé vs URL brute ; mot de passe dans une URL ?
- C1–C4 : apartenance des données POST ; pourquoi pas dans la query string ; corps POST chiffré sous http:// ? ; mécanisme de confidentialité.
- D1–D3 : en-têtes pouvant être absents ; rôle de Content-Type ; Referer différence lien/directe.
- E1–E4 : qui décide du statut ; pourquoi 302 → 2e requête ; 404 vs 405 ; contenu de Allow.
- F1–F3 : confiance serveur dans les valeurs de formulaire ; POST vs confidentialité ; contrôles pour une application réelle.

## Règles de codage pendant le développement

- PHP natif uniquement, pas de dépendance.
- Échapper toute sortie dynamique avec `htmlspecialchars()` (bonne pratique cohérente avec la partie F).
- Réponses en français (langue de la fiche).
