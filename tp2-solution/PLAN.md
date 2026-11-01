# TP 02 — Plan : Cookies, sessions, SOP, CORS et CSP

**Fiche :** TP 02 (4 h 30, PHP natif, HTML, JavaScript vanilla). Fil conducteur :
deux sites locaux et une API PHP, en s'appuyant sur le TP1.

## Architecture imposée

| Composant | Adresse | Rôle |
|-----------|---------|------|
| Site A | `http://127.0.0.1:8080` | PHP : cookies, connexion, API, CSP |
| Site B | `http://127.0.0.1:8081` | HTML/JS : appels cross-origin vers A |

Deux ports = deux **origines distinctes** (même hôte). Cookies rattachés à
l'hôte et au chemin, pas au port. `Secure` et le comportement cross-site de
`SameSite` : à expliquer, pas démontrables sur deux ports HTTP.

## Lancement

```
Terminal 1 : php -S 127.0.0.1:8080 -t site-a   (depuis app/)
Terminal 2 : php -S 127.0.0.1:8081 -t site-b
```

## Fichiers à créer

```
app/
├── site-a/
│   ├── cookie.php          → Partie A : Créer / Modifier / Supprimer (étapes 1–3)
│   ├── cookie-httponly.php → Partie A : copie expérimentale HttpOnly + document.cookie (étape 4)
│   ├── login.php           → Partie B : formulaire POST etudiant/tp2026 (étapes 5–7)
│   ├── profile.php         → Partie B : page protégée (étape 7–8)
│   ├── logout.php          → Partie B : destruction session + cookie (étape 7)
│   ├── api.php             → Parties C/D : modes none/allow/wildcard + preflight (étapes 9–15)
│   ├── csp.php             → Partie E : inline + /csp.js + image distante ; modes none/report/enforce (étapes 16–18)
│   └── csp.js              → Partie E : script externe local (étape 16)
└── site-b/
    └── index.html          → Parties C/D : fetch cross-origin depuis B (étapes 9–15)
```

## Ordre de travail

| Partie | Points clés | Étapes fiche |
|--------|-------------|--------------|
| A — Cookies | `setcookie('tp2_theme','clair')`, Path=/, Max-Age ~600, SameSite=Lax ; redirection vers la lecture ; Modifier ; Supprimer (même Path + date passée) ; Réseau Set-Cookie/Cookie vs onglet Stockage ; copie HttpOnly + `document.cookie` ; discussion Secure/Domain/SameSite | 1–4 |
| B — Sessions | `login.php` POST (etudiant/tp2026, sans BDD) ; `session.use_strict_mode` ; cookie de session HttpOnly + SameSite=Lax + Secure OFF (HTTP local) via réglages **avant** `session_start()` ; `session_regenerate_id(true)` puis `$_SESSION['user']` ; `profile.php` protégé ; `logout.php` | 5–8 |
| C — SOP | `site-b/index.html` fait `fetch("http://127.0.0.1:8080/api.php?mode=none")` ; l'API répond JSON **sans** `Access-Control-Allow-Origin` ; observer Console/Réseau ; origine ≠ origine | 9–11 |
| D — CORS | mode `allow` (ACAO = B) ; `Access-Control-Allow-Methods: GET, OPTIONS` + `Access-Control-Allow-Headers: X-TP-Lab, Content-Type` ; fetch avec `X-TP-Lab` → OPTIONS preflight (Origin, ACRM, ACRH) ; mode `wildcard` (`*`) pour données publiques | 12–15 |
| E — CSP | `csp.php` : script inline + `/csp.js` externe + image distante ; sans CSP → Report-Only → `default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self'` ; ce qui est bloqué/exécuté/signalé | 16–18 |

## Questions à traiter dans RESULT.md

- A1–A4 : `$_COOKIE` pas immédiat ; Set-Cookie vs Cookie ; portée exigée pour la suppression ; HttpOnly et envoi.
- B1–B4 : contenu de PHPSESSID ; où résident les `$_SESSION` ; régénération d'id ; risque sans HTTPS.
- C1–C2 : triplets d'origine ; SOP n'interdit pas tout envoi ; lecture interdite.
- D1–D4 : CORS vs curl ; vérifications du preflight ; autorisation de lecture vs contrôle d'accès serveur ; Allow-Credentials systématique à éviter.
- E1–E3 : script bloqué par `script-src 'self'` ; utilité de Report-Only ; CSP n'est pas un substitut à l'échappement/validation.

## Règles

- PHP natif (8.1+), HTML, JavaScript vanilla uniquement ; pas de BDD.
- Ne rien implémenter au-delà des étapes 1–18 ; sécurisations demandées
  (HttpOnly, SameSite=Lax, strict mode, regénération d'id) uniquement.
- Réponses du compte rendu en français.
