# Greenlight

Greenlight est un theme WordPress hybride, sobre et ecoresponsable, pense pour rester tres leger, accessible et maintenable.

Le theme s inscrit dans une logique GreenIT: DOM leger, peu de code, peu de poids, peu de scripts, responsive simple a maintenir, et front-end original sans complexite inutile.

> Statut au 2026-04-21 : GO prod avec reserves. Le front a ete requalifie en runtime reel, mais le theme garde un perimetre large et demande encore une vigilance produit et editoriale avant diffusion plus large.

## Objectif

Le theme vise un socle technique simple et durable:

- zero jQuery;
- zero dependance front externe;
- JavaScript front quasi nul;
- compatibilite Gutenberg via `theme.json` v3 et patterns natifs;
- responsive par HTML semantique, CSS moderne, flexbox ou grid selon le besoin, et valeurs fluides;
- SEO natif sans plugin externe;
- optimisation d images native;
- code PHP propre, securise et traduisible.

## Contraintes absolues

1. Ne jamais ajouter de jQuery.
2. Ne jamais introduire de dependance front externe.
3. Ne jamais ajouter de JavaScript front sans necessite stricte.
4. Garder un DOM minimal, sans wrappers inutiles.
5. Prioriser `theme.json` avant le CSS custom.
6. Garder le CSS au strict necessaire.
7. Eviter les breakpoints tant que `clamp()`, `min()`, `max()`, flexbox ou grid suffisent.
8. Maintenir l accessibilite comme exigence de base.
9. Produire un theme compatible avec les standards WordPress actuels.
10. Tout echapper, tout sanitiser, tout internationaliser.

## Architecture cible

```text
greenlight/
├── style.css
├── theme.json
├── functions.php
├── index.php
├── front-page.php
├── single.php
├── page.php
├── archive.php
├── search.php
├── 404.php
├── header.php
├── footer.php
├── comments.php
├── screenshot.png
├── inc/
│   ├── seo.php
│   ├── seo-fields.php
│   ├── seo-json-ld.php
│   ├── seo-sitemap.php
│   ├── seo-settings.php
│   ├── images.php
│   └── images-settings.php
├── patterns/
│   ├── hero.php
│   ├── cards.php
│   ├── contact.php
│   ├── header.php
│   └── footer.php
├── assets/
│   ├── css/
│   │   └── blocks/
│   └── js/
│       └── seo-sidebar.js
└── TODO.md
```

## Standards de developpement

### WordPress et PHP

- Utiliser les API natives avant tout code custom.
- Respecter les WordPress Coding Standards.
- Prefixer les fonctions projet avec `greenlight_`.
- Proteger les traitements admin avec nonce et controle de droits.
- Echaper les sorties avec le bon `esc_*()` selon le contexte.
- Sanitizer les entrees avec les fonctions WordPress adaptees.
- Garder les responsabilites separees par fichier dans `inc/`.

### HTML et accessibilite

- Utiliser les balises semantiques natives.
- Conserver un seul `main` par page.
- Garder une hierarchie de titres coherente.
- Fournir un skip link.
- Rendre le focus visible.
- Utiliser `aria-*` seulement quand cela apporte une information utile.

### CSS

- Mettre le design system dans `theme.json` autant que possible.
- Garder `style.css` pour le reset et les regles globales strictement utiles.
- Favoriser flexbox ou grid quand ils simplifient vraiment la mise en page, avec des tailles fluides.
- Eviter les selecteurs profonds et les conventions de nommage lourdes.
- Charger les styles de blocs de maniere conditionnelle quand c est pertinent.

### JavaScript

- Aucun JS front par defaut.
- JS autorise uniquement si la valeur fonctionnelle est claire.
- Cote editeur, reutiliser les packages WordPress deja fournis.

## Fonctionnalites attendues

### Socle du theme

- activation immediate sans erreur PHP;
- `title-tag`, `post-thumbnails`, `html5`, `editor-styles`, `wp-block-styles`, `align-wide`, `responsive-embeds`;
- deregistration de jQuery cote front;
- templates legers et markup semantique minimal.

### Gutenberg

- `theme.json` v3 complet;
- styles globaux pilotant couleurs, typo, espacement et layout;
- patterns natifs modifiables;
- compatibilite avec l edition de contenu sans verrouiller la mise en page.

### SEO natif

- title SEO, meta description, noindex, image OG;
- canonical;
- Open Graph;
- Twitter Card;
- JSON-LD;
- sitemap XML;
- page de reglages SEO dans l admin.

### Images

- tailles d images utiles seulement;
- suppression des tailles inutiles;
- WebP si le serveur le permet;
- strategie lazy/eager coherente;
- preload de l image hero si necessaire.

## Installation

1. Installer WordPress sur le serveur local ou de preproduction.
2. Placer le dossier `greenlight` dans `wp-content/themes/`, ou creer un symlink vers le repository si tu travailles en local.
3. Activer le theme dans **Apparence > Themes**.
4. Installer les dependances de qualite si tu veux lancer PHPCS localement:

```bash
composer install
```

## Configuration

La configuration du theme se fait dans **Apparence > Greenlight**:

- **SEO** : titre global, description globale, separator, sitemap, noindex archives;
- **Images** : WebP, qualite, suppression des tailles inutiles;
- **Performance** : minification, cache HTML, prefetch DNS / preconnect manuel, nettoyage du head;
- **Apparence** : hub vers le Customizer natif pour presets, header, hero, contenus et footer, avec action de reset visuel limitee aux options d apparence;
- **SVG** : sanitisation et validation des imports;
- **Outils** : import/export JSON des reglages et redirections.

Le cache HTML est ecrit dans `wp-content/cache/greenlight/`. Les fichiers minifies sont generes localement et ne doivent pas etre relies a une edition manuelle.
Le reset visuel restaure uniquement `greenlight_appearance_options` et ne touche ni au SEO, ni aux redirections, ni au cache, ni aux images.

## Validation locale fiable

Dans l environnement local actuel, la source de verite pour la recette Greenlight n est pas `wp-cli` Homebrew.

- Utiliser le front HTTP reel servi par l instance locale pour valider le rendu.
- Utiliser l admin HTTP reel pour valider les actions de reglages, de reset et les redirects de confirmation.
- Utiliser le PHP MAMP (`/Applications/MAMP/bin/php/php8.2.26/bin/php`) pour lire ou muter les options WordPress quand une verification CLI est necessaire.
- Considerer `wp-cli` Homebrew comme non fiable tant que son acces DB local n est pas corrige dans cet environnement.

En pratique :

- recette front : `curl` + navigateur reel / headless sur `http://localhost:8888/greenlight/`
- recette admin : HTTP reel sur `wp-admin`
- verification runtime ponctuelle : bootstrap WordPress via le PHP MAMP

Ne pas utiliser `wp-cli` Homebrew comme source de verite pour conclure qu un reset, une purge de cache ou une mutation d options a echoue, tant que l environnement local reste incoherent.

### Recommandations production

Activer dans l onglet **Performance** pour minimiser les requetes HTTP CSS (9 requetes → 1) :

1. **Concatenation CSS** — groupe `style.css` + les 8 CSS blocs dans un bundle unique `assets/css/greenlight-bundle.css`, construit au deploiement via `bin/minify.sh`. Les CSS blocs restent charges conditionnellement via `wp_enqueue_block_style()` si le bundle est absent ou desactive.
2. **Minification CSS/JS** — produit `style.min.css`, les `*.min.css` de blocs et `seo-sidebar.min.js` pendant le build. Le front sert la source si les fichiers minifies ne sont pas presents.
3. **Cache HTML** — ecrit les pages en `.html` statique, servi directement sans execution PHP sur les visites suivantes.
4. **Critical CSS** — inline `assets/css/critical.css` dans `<head>` et differe le CSS principal via `media="print" onload`.

## Utilisation

- Modifier les contenus dans l editeur WordPress classique ou Gutenberg.
- Regler le SEO par page ou article via la sidebar et la meta box.
- Utiliser les patterns Greenlight pour construire les sections reutilisables.
- Preferer les blocs natifs et les reglages globaux avant d ajouter du CSS supplementaire.
- L interface front doit rester utilisable sans JavaScript cote visiteur.

## Mesures

Mesure prise sur la branche courante le 2026-03-28 (mise a jour Phase 10 : 2026-04-08):

- lignes CSS sources: 1191 (`style.css` 1191 + blocs ~155 = ~1346 total, avant Phase 10 : 1276 + blocs)
- lignes JS sources: 551
- total CSS + JS sources: ~1742
- taille du theme hors `.git`, `.playwright-cli` et `vendor`: 772 KB (estimation Phase 10 inchangee)

## Eco-conception GreenIT

- DOM leger et structure minimale.
- Theme le plus leger possible.
- Front-end original mais facilement customisable.
- Responsive pense pour limiter les ruptures de layout.
- Breakpoints utilises seulement si la mise en page le demande vraiment.
- Chaque ajout doit justifier son cout en poids, complexite et maintenabilite.

## Workflow de developpement

1. Verifier si WordPress fournit deja la fonctionnalite attendue.
2. Stabiliser le modele de markup avant d ajouter du CSS.
3. Definir ou ajuster `theme.json` avant le CSS custom.
4. Garder les templates PHP courts et previsibles.
5. Isoler le SEO et les images dans `inc/`.
6. Ajouter les patterns seulement quand ils apportent une vraie reutilisation.
7. Tester la sortie finale et simplifier ce qui peut l etre.

## Validation minimale

- theme activable sans erreur;
- aucun jQuery charge sur le front;
- front utilisable sans JavaScript;
- HTML semantique et lisible;
- navigation clavier correcte;
- champs SEO propres et securises;
- images chargees selon la bonne strategie;
- compatibilite Gutenberg conservee;
- code PHP et sorties maintenables.

## Configuration serveur recommandee

Le theme fonctionne sans configuration serveur specifique pour le HTML dynamique: le cache HTML et les headers de page sont geres en PHP pur. En revanche, les fichiers statiques (`style.css`, `.min.css`, `.js`, images, uploads) passent par le serveur web et ont besoin d'un bloc explicite pour obtenir `Cache-Control` / `Expires`.

Le cache HTML est ecrit dans `wp-content/cache/greenlight/`. Les fichiers minifies (`style.min.css`, `assets/css/blocks/*.min.css`, `assets/js/*.min.js`) et le bundle CSS (`assets/css/greenlight-bundle.css`) sont produits via `bin/minify.sh` dans le flux de build/deploiement et ne sont pas destines a etre versionnes.

### nginx

Ajouter dans le bloc `server {}` ou dans un fichier inclus, sur l'instance qui sert `/greenlight/` :

```nginx
# WordPress rewrite
location /greenlight/ {
    try_files $uri $uri/ /greenlight/index.php?$args;
    index index.php;
}

# Greenlight static assets
location ~* ^/greenlight/wp-content/(themes/greenlight|uploads)/.*\.(css|js|woff2?|ttf|otf|eot|svg|png|jpe?g|webp|avif|gif|ico)$ {
    expires 1y;
    add_header Cache-Control "public, max-age=31536000, immutable" always;
    add_header Vary "Accept-Encoding" always;
    access_log off;
}

# Compression
gzip on;
gzip_vary on;
gzip_min_length 1024;
gzip_proxied any;
gzip_comp_level 6;
gzip_types
    text/plain
    text/css
    text/javascript
    application/javascript
    application/json
    image/svg+xml;

# Cache static assets
location ~* \.(css|js|woff2?|ttf|otf|eot|svg|png|jpg|jpeg|webp|avif|ico|gif)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
    add_header Vary Accept-Encoding;
    access_log off;
}

# Security headers
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-Content-Type-Options "nosniff" always;
add_header Referrer-Policy "strict-origin-when-cross-origin" always;
add_header Permissions-Policy "camera=(), microphone=(), geolocation=()" always;
# Activer HSTS uniquement si le site est servi exclusivement en HTTPS
# add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
```

### Apache (theme .htaccess)

Placer ce fichier a la racine du theme Greenlight pour renforcer les assets statiques lorsque le site est servi par Apache.
Pour les uploads, recopier le meme bloc dans `wp-content/uploads/.htaccess`.

```apache
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 year"
    ExpiresByType application/javascript "access plus 1 year"
    ExpiresByType text/javascript "access plus 1 year"
    ExpiresByType image/svg+xml "access plus 1 year"
    ExpiresByType image/webp "access plus 1 year"
    ExpiresByType image/avif "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/x-icon "access plus 1 year"
    ExpiresByType font/woff2 "access plus 1 year"
</IfModule>

<IfModule mod_headers.c>
    <FilesMatch "\.(css|js|woff2?|ttf|otf|eot|svg|png|jpe?g|webp|avif|gif|ico)$">
        Header always set Cache-Control "public, max-age=31536000, immutable"
        Header always append Vary Accept-Encoding
    </FilesMatch>
</IfModule>
```

### Apache (.htaccess)

Placer a la racine du dossier WordPress. Requiert `mod_deflate`, `mod_expires`, `mod_headers`, `mod_rewrite`.

```apache
# WordPress rewrite
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /greenlight/
    RewriteRule ^index\.php$ - [L]
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule . /greenlight/index.php [L]
</IfModule>

# Compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain text/css text/html
    AddOutputFilterByType DEFLATE text/javascript application/javascript
    AddOutputFilterByType DEFLATE application/json image/svg+xml
</IfModule>

# Cache static assets
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 year"
    ExpiresByType application/javascript "access plus 1 year"
    ExpiresByType image/webp "access plus 1 year"
    ExpiresByType image/avif "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/svg+xml "access plus 1 year"
    ExpiresByType font/woff2 "access plus 1 year"
</IfModule>

# Security headers
<IfModule mod_headers.c>
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-Content-Type-Options "nosniff"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    Header always set Permissions-Policy "camera=(), microphone=(), geolocation=()"
    # Activer HSTS uniquement si le site est servi exclusivement en HTTPS
    # Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
    Header always append Vary Accept-Encoding
</IfModule>

# Protect sensitive files
<FilesMatch "\.(log|md|json|sh|sql)$">
    Order allow,deny
    Deny from all
</FilesMatch>
```

### Brotli (nginx, optionnel)

Si le module `ngx_brotli` est disponible :

```nginx
brotli on;
brotli_comp_level 6;
brotli_types text/plain text/css application/javascript application/json image/svg+xml;
```

## Rappel de conduite

Avant chaque ajout, se poser trois questions:

1. WordPress sait-il deja faire cela nativement ?
2. Le navigateur peut-il le faire sans JavaScript ?
3. Peut-on le faire plus simple ?
