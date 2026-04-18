# TODO — Greenlight

## Remédiation audit production — 2026-04-18

### P0 — bloque la prod

- [x] Implémenter ou retirer les formulaires publics non branchés (newsletter home/single + contact pattern)
  Fichier principal : `home.php`, `single.php`, `patterns/contact.php`
  Issue réelle : les formulaires publics existent dans le front mais ne prouvent aucun traitement fonctionnel côté serveur
  Critère de validation : aucun formulaire public non branché ne reste visible en production

- [ ] Ajouter handlers `admin_post` / `admin_post_nopriv` si les formulaires sont conservés
  Fichier principal : `functions.php`
  Issue réelle : aucun handler public n’est actuellement relié aux actions `greenlight_newsletter` et `greenlight_contact`
  Critère de validation : chaque action publique soumise retourne un flux WordPress valide pour visiteur connecté et non connecté

- [ ] Ajouter retours utilisateur accessibles : succès, erreur, validation, anti-spam, consentement
  Fichier principal : `home.php`, `single.php`, `patterns/contact.php`
  Issue réelle : aucun message de succès/erreur, aucune stratégie anti-spam, aucun consentement explicite
  Critère de validation : parcours clavier complet avec retours lisibles, erreurs liées aux champs et protection minimale anti-spam

- [x] Corriger l’onglet Performance : supprimer toute imbrication de `<form>`
  Fichier principal : `inc/admin.php`
  Issue réelle : l’onglet Performance imbrique plusieurs formulaires dans un formulaire global, markup invalide et sauvegarde fragile
  Critère de validation : HTML admin valide avec un seul niveau de formulaire par action

- [x] Corriger la navigation burger mobile pour accessibilité clavier et lecteur d’écran
  Fichier principal : `header.php`, `assets/css/blocks/navigation.css`
  Issue réelle : le contrôle burger repose sur un checkbox masqué non focusable et un label visuel
  Critère de validation : ouverture/fermeture au clavier, état annoncé, navigation exploitable sans souris (commentaire : c'est débile de faire ça il n'y a pas de souris sur téléphone)

- [x] Corriger le CTA header pour qu’il ne pointe jamais vers `#newsletter` si la cible n’existe pas
  Fichier principal : `header.php`
  Issue réelle : le CTA peut être rendu sur des vues sans section `#newsletter`
  Critère de validation : aucun lien d’ancre cassé depuis le header

### P1 — majeur avant ouverture large

- [ ] Décider ce qui doit sortir du thème vers un plugin compagnon (SEO avancé, redirections, cache, DB cleanup, heartbeat, bulk images, minify/concat)
  Fichier principal : `functions.php`
  Issue réelle : le thème embarque un périmètre produit plus large que celui d’un thème de présentation
  Critère de validation : périmètre thème/plugin explicité et stabilisé dans l’architecture

- [ ] Unifier la langue source du thème (front + admin + libellés système)
  Fichier principal : `header.php`, `footer.php`, `home.php`, `single.php`, `archive.php`, `functions.php`
  Issue réelle : mélange français/anglais dans le front et certains libellés système
  Critère de validation : une langue source cohérente sur le front et l’admin

- [ ] Corriger l’outil import/export pour inclure les redirections, ou réduire sa promesse
  Fichier principal : `inc/admin.php`
  Issue réelle : l’export/import des réglages ne couvre pas toutes les données gérées par l’admin
  Critère de validation : périmètre d’export exact, aligné avec le wording de l’outil

- [ ] Supprimer ou finaliser le mécanisme de preview mort
  Fichier principal : `front-page.php`
  Issue réelle : le front page appelle un mécanisme de preview qui n’est pas démontré dans le thème actuel
  Critère de validation : plus aucun appel vers une preview fantôme, ou flux preview complet et documenté

- [ ] Réconcilier `phpcs` réel avec la doc qui annonce zéro erreur
  Fichier principal : `PROJECT_STATE.md`, `TODO.md`
  Issue réelle : la doc annonce un état qualité plus propre que le code actuel
  Critère de validation : documentation et état réel de `phpcs` alignés

- [ ] Revoir les contrastes insuffisants sur les textes secondaires et captions
  Fichier principal : `style.css`, `assets/css/blocks/image.css`
  Issue réelle : plusieurs textes secondaires reposent sur une opacité qui dégrade le contraste réel
  Critère de validation : contrastes AA tenus sur les petits textes informatifs

- [ ] Vérifier les formulaires publics contre le cache HTML et les nonces
  Fichier principal : `inc/cache.php`
  Issue réelle : les formulaires publics doivent rester fiables avec cache HTML actif
  Critère de validation : soumission valide avec cache activé, sans nonce périmé servi aux visiteurs

### P2 — à corriger pour aligner le produit et la doc

- [ ] Corriger les claims inexacts dans README / PROJECT_STATE / TODO (zéro `@media`, zéro div, PHPCS zéro, etc.)
- [ ] Corriger les détails de finition restants (`Theme URI`, sélecteurs morts, styles orphelins)
- [ ] Réduire la logique runtime de génération d’assets en faveur d’un build de déploiement
- [ ] Ajouter des smoke tests ciblés : nav mobile, sauvegarde admin performance, formulaires publics, CTA header, export/import
- [ ] Requalifier le statut du thème : préprod solide, pas prod-ready

## feat/eco3 — Refactor nav fixe + hero autonome (2026-04-10) ✓
- [x] Abandon fusion header+hero — nav identique et `position: fixed` sur toutes les pages
- [x] Hero `<section>` autonome entre `</header>` et `<main>` sur la front-page
- [x] Chaque template ouvre son propre `<main>` — supprimé de `header.php`
- [x] Fix hero 100vh — `min-height` + `max-height` (Claude Code avait supprimé `min-height`)
- [x] Opacité header réglable — Customizer `header_opacity` via `::after` + glassmorphism
- [x] Suppression `overflow: clip` sur `.site-header`
- [x] Nettoyage CSS — suppression `.site-header--with-hero`, `.site-header-nav`, overlay header

## feat/eco3 — Fix Header+Hero + Overlay + CTA (2026-04-09) ✓
- [x] Fix nav transparente dans header+hero — `.site-header-nav` bg/sticky supprimés (`style.css`)
- [x] Fix overlay pleine couverture — `::before` déplacé sur `<header>`, `.page-hero::before` désactivé dans header
- [x] Overlay réglable — opacité (0–100%) + direction (full/top/bottom/left/right) via Customizer + custom property CSS
- [x] Boutons CTA hero — 2 boutons, 3 styles (primary/secondary/tertiary), 3 positions (lead/body/center)

## feat/eco3 — Corrections Customizer & Nav mobile (2026-04-08) ✓
- [x] Double bouton purge cache supprimé — `inc/admin.php`
- [x] Subscribe nav / newsletter home corrigé — `home.php` garde `newsletter_enabled` (clé cohérente partout)
- [x] Couleurs Customizer — transport color settings `postMessage` → `refresh` dans `inc/customizer.php`
- [x] Bug "texte aligné à droite" — aucun `text-align: right` trouvé, bug absent du code actuel
- [x] Hero pleine largeur 100vw — déjà implémenté via `"align":"full"` dans `patterns/hero.php`
- [x] Hero 100vw CSS — `margin-inline: calc(50% - 50vw)` + `padding-inline` sur `.page-hero` (`style.css`)
- [x] Nav visuellement dans le hero — `margin-block-start` négatif sur `.site-main` + `padding-block-start` compensatoire, variable `--greenlight-header-height`
- [x] Menu burger CSS-only — `<input>` + `<label>` + SVG, option Customizer `nav_style` (inline/burger)

## Phase 1 — Squelette du thème ✓
- [x] Créer le dossier `greenlight/` dans wp-content/themes/
- [x] `style.css` : header WordPress (Theme Name: Greenlight, Version: 1.0.0, Text Domain: greenlight)
- [x] `theme.json` v3 : design system complet (couleurs, typo system-ui fluid, spacing clamp, layout, block settings, custom:false)
- [x] `functions.php` : add_theme_support (title-tag, post-thumbnails, html5, responsive-embeds, editor-styles, wp-block-styles, align-wide), deregister jQuery côté front, enqueue style.css
- [x] `index.php` : template fallback minimal (DOM léger)
- [x] `header.php` : skip link + `<header>` + `<nav aria-label>` + wp_head() — zéro div wrapper
- [x] `footer.php` : `<footer>` + wp_footer() — zéro div wrapper
- [x] `screenshot.png` 1200×900 (Codex, 2026-03-27)
- [x] Valider : thème activable, Gutenberg charge theme.json, zéro jQuery côté front — smoke test Playwright 2026-04-08 : jQuery absent, `--wp--preset--*` présents, `<main id="main-content">` + skip link OK. Warning : preload hero JPG non consommé (voir Phase 11 potentielle)

## Phase 2 — Templates PHP (DOM minimal) ✓
- [x] `front-page.php` : accueil — HTML sémantique pur, sections avec aria-labelledby
- [x] `single.php` : article — `<article>` + `<header>` + `<footer>` article, zéro div
- [x] `page.php` : page standard
- [x] `archive.php` : liste articles avec pagination native
- [x] `search.php` : résultats, meta robots noindex
- [x] `404.php` : page d'erreur accessible
- [x] `comments.php` : commentaires accessibles
- [x] Vérifier : aucun `<div>` non nécessaire, tout est balises sémantiques

## Phase 3 — CSS éco-conçu ✓
- [x] `style.css` : reset Josh Comeau + styles globaux minimaux (uniquement ce que theme.json ne gère pas)
- [x] `assets/css/blocks/` : styles par bloc (navigation, image, heading, paragraph, separator, button, group, query)
- [x] Enqueue conditionnel via `wp_enqueue_block_style()` dans functions.php
- [x] Layout responsive : flexbox + clamp() uniquement, zéro @media pour le layout
- [x] Focus visible sur tous les interactifs
- [x] `.sr-only`, `.skip-link`
- [x] Audit : 155 lignes blocks + ~97 lignes fonctionnelles dans style.css — objectif < 200 ✓

## Phase 4 — Patterns Gutenberg ✓
- [x] `patterns/hero.php` : titre xx-large + description + deux CTA (primary + outline)
- [x] `patterns/cards.php` : grille 3 colonnes core/columns avec style "card" (border-left primary)
- [x] `patterns/contact.php` : section contact avec formulaire HTML natif + nonce
- [x] `patterns/header.php` : core/site-title + core/navigation (flex, space-between)
- [x] `patterns/footer.php` : copyright dynamique + core/navigation secondaire
- [x] Catégorie "Greenlight" enregistrée via register_block_pattern_category()
- [x] Patterns auto-enregistrés via headers PHP (WP 6.0+), 100% modifiables dans Gutenberg

## Phase 5 — SEO autonome + champs éditables
- [x] `inc/seo.php` : meta tags via wp_head (title, description, robots) + Open Graph + Twitter Card + canonical URL (Codex, 2026-03-27)
- [x] `inc/seo-fields.php` : register_post_meta() pour les 4 champs SEO, meta box PHP natif et enregistrement de la sidebar Gutenberg (Codex, 2026-03-27)
  - [x] Champ titre SEO (input text) (Codex, 2026-03-27)
  - [x] Champ meta description (textarea avec compteur caractères) (Codex, 2026-03-27)
  - [x] Champ image OG (MediaUpload) (Codex, 2026-03-27)
  - [x] Toggle noindex (ToggleControl) (Codex, 2026-03-27)
  - [x] Enqueue uniquement dans l'éditeur (enqueue_block_editor_assets) (Codex, 2026-03-27)
- [x] `inc/seo-json-ld.php` : Schema.org via wp_footer (WebSite, Article, BreadcrumbList) (Codex, 2026-03-27)
- [x] `inc/seo-sitemap.php` : sitemap XML natif avec routes /sitemap.xml, /sitemap-posts.xml et /sitemap-pages.xml (Codex, 2026-03-27)
  - [x] Exclure noindex, brouillons, privés (Codex, 2026-03-27)
  - [x] Cache transient régénéré à chaque publish/update (Codex, 2026-03-27)
- [x] `inc/seo-settings.php` : page Apparence > Greenlight > SEO (Codex, 2026-03-27)
  - [x] Titre du site pour les SERPs (Codex, 2026-03-27)
  - [x] Description globale (Codex, 2026-03-27)
  - [x] Séparateur titre (Codex, 2026-03-27)
  - [x] Toggle sitemap (Codex, 2026-03-27)
  - [x] Toggle noindex archives auteur / tags (Codex, 2026-03-27)
- [x] `assets/js/seo-sidebar.js` : Sidebar Gutenberg (PluginDocumentSettingPanel) (Codex, 2026-03-27)
- [x] Meta robots : noindex archives auteur, résultats recherche, pages de tags (Codex, 2026-03-27)

## Phase 6 — Optimisation images
- [x] `inc/images.php` : tailles custom (greenlight-hero, greenlight-card, greenlight-thumb) (Codex, 2026-03-27)
- [x] `inc/images.php` : remove_image_size() pour medium_large, 1536x1536, 2048x2048 (Codex, 2026-03-27)
- [x] `inc/images.php` : conversion WebP à l'upload (wp_handle_upload hook) (Codex, 2026-03-27)
  - [x] Détecter support GD/Imagick (Codex, 2026-03-27)
  - [x] Fallback gracieux si pas de support (Codex, 2026-03-27)
  - [x] Conserver l'original + générer le WebP (Codex, 2026-03-27)
- [x] `inc/images.php` : filtre wp_get_attachment_image_attributes pour lazy/eager automatique (Codex, 2026-03-27)
- [x] `inc/images.php` : preload hero image dans wp_head (Codex, 2026-03-27)
- [x] `inc/images-settings.php` : page Apparence > Greenlight > Images (Codex, 2026-03-27)
  - [x] Toggle conversion WebP (Codex, 2026-03-27)
  - [x] Qualité WebP (slider 1-100) (Codex, 2026-03-27)
  - [x] Toggle suppression tailles inutiles (Codex, 2026-03-27)
  - [x] Afficher espace disque économisé (info) (Codex, 2026-03-27)

## Phase 6B — Mise en forme minimale
- [x] `nav`, `header`, `main`, `section`, `article`, `p`, `ul`, `footer` : HTML sémantique minimal et DOM sans wrappers superflus (Codex, 2026-03-27)
- [x] Responsive sans breakpoints : flexbox, `clamp()`, dimensionnement intrinsèque et conventions CSS modernes (Codex, 2026-03-27)
- [x] Mise en forme sobre : typographie, espacements, bordures et surfaces via tokens du thème (Codex, 2026-03-27)
- [x] Blocs Gutenberg : rendu visuel minimaliste prêt à être édité sans surcharge (Codex, 2026-03-27)
- [x] Référence de structure : s’inspirer de `html_responsive_images.html` et `wordpress_responsive_images.php` pour le preload hero, les images responsives et la hiérarchie de layout (Codex, 2026-03-27)
- [x] Validation visuelle : interface éditable immédiatement, lisible sur mobile et desktop, sans complexifier l’architecture du thème (Codex, 2026-03-27)

## Phase 6C — UI, Admin unifiée & Éco-optimisation

> Branche : `feat/ui-improvement` depuis `dev` (historique)
> Référence visuelle : maquettes EcoEditorial (page.png, single.png, index.png, archives.png)
> Contrainte permanente : zéro jQuery, zéro dépendance externe, DOM minimal, CSS minimal

### Volet A — Redesign visuel éditorial

Objectif : aligner le rendu front sur l'esthétique "Organic Minimalism" des maquettes EcoEditorial — palette off-white/vert, typographie serrée, asymétrie, whitespace généreux, zéro bordure traditionnelle.

- [x] **Palette DESIGN.md** : mettre à jour `theme.json` — background `#faf9f4`, surface `#f4f4ee`, surface-alt `#ffffff`, text `#2f342d`, primary `#4c6547`, primary-dim `#41593c`, border `#afb3aa` (opacity 15%), tertiary `#e5f4c9`
- [x] **Header** : `header.php` — site-title à gauche, nav centrée, CTA Subscribe à droite (flex, `justify-content: space-between`, pas de div wrapper)
- [x] **Hero (front-page)** : `front-page.php` + pattern `hero.php` — titre surdimensionné (xx-large, letter-spacing -0.03em) aligné gauche, paragraphe descriptif aligné droite (asymétrie volontaire), Carbon Badge pill en haut
- [x] **Carbon Badge** : composant PHP `greenlight_carbon_badge()` — calcul simplifié (DOM count + poids estimé page) affiché en pill `tertiary-container`, surcharge manuelle possible via option admin
- [x] **Index/home** : `home.php` + `index.php` — premier article en layout 50/50 (image + texte via flex), articles suivants en alternance image gauche/droite, section newsletter CTA en bas (surface background)
- [x] **Single** : `single.php` — catégorie en pill + CO2 badge, titre large (xx-large), meta auteur + date en flex space-between, image hero pleine largeur, contenu 65ch, blockquote avec bordure gauche primary + italique, tags en pills, section newsletter en bas
- [x] **Archive** : `archive.php` — titre éditorial large, lead text, grille asymétrique (premier article large, suivants en 2 colonnes flex), pagination stylisée
- [x] **Footer** : `footer.php` — copyright + liens secondaires (flex wrap) + mention "Low Emission Mode" à droite, surface background
- [x] **Surfaces & profondeur** : CSS — pas de bordures 1px, différenciation par chromatic shifts (surfaces), ombres "Whisper Shadow" (`0 20px 40px rgba(47, 52, 45, 0.04)`) uniquement sur éléments flottants
- [x] **Boutons** : `assets/css/blocks/button.css` — primary `#4c6547` avec gradient satin 135deg vers `#41593c`, border-radius `md`, secondary en `secondary_container`, tertiary text-only underline
- [x] **Labels catégories** : uppercase, letter-spacing +0.05em, font-size `small`, couleur `on_surface_variant`
- [x] **Patterns mis à jour** : `hero.php`, `cards.php`, `contact.php`, `header.php`, `footer.php` — alignés sur la nouvelle palette et la direction esthétique
- [x] **`screenshot.png`** : nouvelle capture 1200×900 reflétant le redesign

### Volet B — Interface admin unifiée "Greenlight"

Objectif : regrouper tous les réglages du thème dans une page admin top-level unique avec onglets, tout en gardant temporairement les anciennes sous-pages.

- [x] **`inc/admin.php`** : `add_menu_page( 'Greenlight', 'Greenlight', 'manage_options', 'greenlight', ... )` — page principale avec navigation par onglets CSS-only
- [x] **Icône menu** : SVG inline feuille verte encodée en base64
- [x] **Onglet SEO** : reprend `inc/seo-settings.php`, même `option_group`
- [x] **Onglet Images** : reprend `inc/images-settings.php`, même `option_group`
- [x] **Onglet Performance** (base) :
  - [x] Toggle minification CSS/JS
  - [x] Toggle page cache HTML
  - [x] Bouton "Purger le cache" + durée de vie configurable
  - [x] Affichage : nombre de pages cachées, taille totale du cache
- [x] **Onglet Performance** (améliorations) :
  - [x] Statut minification : afficher si les `.min` existent sur disque + date de génération
  - [x] Bouton "Régénérer les fichiers minifiés"
  - [x] Info serveur : détecter nginx/Apache et afficher conseil contextuel
- [x] **Onglet Apparence** (base) :
  - [x] Carbon Badge (toggle + valeur manuelle)
  - [x] Newsletter CTA (toggle)
  - [x] Layout archive (radio)
  - [x] Style hero (select)
  - [x] 5 sélecteurs de couleur (primary, surface, text, header bg, footer bg)
- [x] **Onglet Apparence** (options par template — sections `<details>`) :
  - [x] **Section Global** : couleurs étendues (background, tertiary, border, on-surface-variant)
  - [x] **Section Header** : fond header, toggle tagline, style navigation
  - [x] **Section Hero / Front page** : style hero, toggle Carbon Badge hero, texte hero personnalisé
  - [x] **Section Single** : toggle date, toggle auteur, toggle tags, toggle articles liés, toggle newsletter
  - [x] **Section Archive** : layout, toggle extraits, toggle miniatures, articles par page
  - [x] **Section Footer** : fond footer, toggle "Low Emission Mode", copyright personnalisé, toggle nav footer
- [x] **Prévisualisation live couleurs** : Customizer WordPress natif avec aperçu du thème Greenlight dans `customize.php`
- [x] **Onglet SVG** : toggle + info sanitisation DOMDocument
- [x] **Onglet Outils** (nouveau) :
  - [x] Export JSON : bouton → télécharge un `.json` avec toutes les options Greenlight
  - [x] Import JSON : input file + validation + sanitisation via les fonctions existantes
  - [x] Message de succès/erreur après import
- [x] **Compatibilité** : anciennes sous-pages Apparence gardées temporairement
- [x] **Sécurité admin** : nonce, `current_user_can`, `sanitize_*()`
- [x] **i18n** : text domain `greenlight`
- [x] **Templates lisent les options** : `single.php`, `archive.php`, `home.php`, `footer.php`, `header.php`, `front-page.php` conditionnent l'affichage selon les options admin

### Volet C — Éco-optimisation (objectif EcoIndex A)

Objectif : passer de EcoIndex B à A. Améliorer la compression, le cache, la minification et réduire les requêtes.

- [x] **Minification CLI** : script `bin/minify.sh` — minifie `style.css` → `style.min.css`, chaque `assets/css/blocks/*.css` → `*.min.css`, `assets/js/seo-sidebar.js` → `seo-sidebar.min.js` (sed/awk ou PHP CLI, pas de dépendance npm)
- [x] **Minification fallback PHP** : `inc/minify.php` — si le `.min` n'existe pas, minification à la volée via `str_replace` (suppression commentaires, whitespace, newlines) + cache transient du résultat
- [x] **Enqueue conditionnel** : `functions.php` — charger `.min.css`/`.min.js` si le fichier existe ET que l'option minification est active, sinon fallback sur le fichier source
- [x] **Page cache HTML** : `inc/cache.php`
  - [x] `ob_start()` dans `template_redirect`, écriture du buffer dans `wp-content/cache/greenlight/` en `.html`
  - [x] Servir le `.html` depuis `advanced-cache.php` ou early hook si le fichier existe et n'est pas expiré
  - [x] Exclure : admin, preview, POST requests, utilisateurs connectés, pages avec query string
  - [x] Purge auto sur `save_post`, `publish_post`, `edit_post`, `delete_post`, `switch_theme`
  - [x] Bouton purge manuelle dans l'onglet Performance
  - [x] Durée de vie configurable (option admin)
- [x] **Headers HTTP** : `inc/cache.php` — hook `send_headers` pour `Cache-Control`, `Expires`, `ETag` sur les assets statiques (CSS, JS, images, fonts)
- [x] **Compatibilité serveur** : le thème doit fonctionner indifféremment sur nginx et Apache — `.htaccess` fourni, doc nginx + Apache dans README.md (2026-04-08)
  - [x] Cache HTML : logique PHP pure (`ob_start` / fichiers `.html`), pas de dépendance au serveur web
  - [x] Headers HTTP : envoyés via `header()` PHP, fonctionnent sur les deux serveurs
  - [x] Compression : documenter les deux configs recommandées dans `README.md` (nginx gzip/brotli + Apache mod_deflate/mod_headers via `.htaccess`)
  - [x] Rewrites : sitemap et cache compatibles `try_files` (nginx) et `mod_rewrite` (Apache)
  - [x] Fournir un `.htaccess` exemple pour Apache (cache static assets, compression, headers security) en plus du bloc nginx
- [x] **Upload SVG** : `inc/svg.php`
  - [x] `greenlight_allow_svg_upload()` : filtre `upload_mimes` pour ajouter `image/svg+xml`
  - [x] `greenlight_sanitize_svg()` : hook `wp_handle_upload_prefilter`, sanitisation via `DOMDocument` (suppression scripts, événements JS, xlink malveillants)
  - [x] Conditionné au toggle SVG dans l'admin
- [x] **Nettoyage WP** : `functions.php` — supprimer `wp_generator`, RSD link, wlwmanifest, shortlink, feed links inutiles, REST API link du head (via `remove_action` sur `wp_head`)
- [x] **Audit DOM** : vérifier que chaque template type reste sous 80 éléments DOM — documenter le comptage dans `PROJECT_STATE.md`
- [x] **Inline Gutenberg** : compromis accepté — les global-styles inline restent, pas de dequeue agressif (préserver la compatibilité éditeur)
- [x] **Documentation nginx** : bloc recommandé dans `README.md` pour gzip, cache static assets, headers security, et `try_files` WordPress
- [x] **Documentation Apache** : fichier `.htaccess` exemple dans `README.md` pour mod_deflate, mod_expires, mod_headers, et mod_rewrite WordPress

### Fichiers créés ou modifiés (Phase 6C)

| Fichier | Volet | Action |
|---------|-------|--------|
| `theme.json` | A | Palette DESIGN.md, couleurs mises à jour |
| `style.css` | A | Styles surfaces, ombres, boutons, labels |
| `header.php` | A | Layout flex 3 zones |
| `footer.php` | A | Layout flex copyright + nav + low emission |
| `front-page.php` | A | Hero asymétrique + Carbon Badge |
| `home.php` | A | Layout alternance + newsletter CTA |
| `single.php` | A | Pills catégorie, meta flex, blockquote |
| `archive.php` | A | Grille asymétrique, pagination |
| `patterns/*.php` | A | Alignement nouvelle palette |
| `assets/css/blocks/*.css` | A | Boutons, surfaces, labels |
| `inc/admin.php` | B | Page admin principale + onglets |
| `inc/svg.php` | C | Upload SVG + sanitisation DOMDocument |
| `inc/minify.php` | C | Fallback minification PHP |
| `inc/cache.php` | C | Page cache HTML + headers HTTP |
| `bin/minify.sh` | C | Script CLI minification |
| `functions.php` | A+B+C | Includes, enqueue conditionnel, nettoyage head |
| `screenshot.png` | A | Nouvelle capture |
| `README.md` | C | Config nginx recommandée |

### Ordre d'implémentation recommandé

1. **Volet A** d'abord (redesign visuel) — le plus visible, base pour le screenshot et les tests
2. **Volet B** ensuite (admin unifiée) — regroupe les réglages, ajoute les toggles performance/SVG
3. **Volet C** en dernier (éco-optimisation) — minification, cache, SVG, nettoyage — dépend des toggles du volet B

### Commandes git

```bash
git checkout dev
git checkout -b feat/admin-ui
# Travailler volet par volet, commit par volet
git add -A && git commit -m "Phase 6C/A: Redesign visuel éditorial — palette DESIGN.md, templates, patterns"
git add -A && git commit -m "Phase 6C/B: Interface admin unifiée Greenlight — onglets, réglages"
git add -A && git commit -m "Phase 6C/C: Éco-optimisation — minification, cache, SVG, nettoyage head"
```

## Phase 6D — Services premium intégrés (couvre une partie importante de Yoast Premium + WP Rocket + Imagify)

> Référence complète : `PHASE_6D.md`
> Branche : `feat/ui-improvement` (suite historique)
> Règle : admin riche en JS autorisé, front zéro impact supplémentaire

### 6D-SEO — SEO avancé

- [x] **Analyse de contenu** : mot-clé principal, densité, présence dans titre/H2/alt/premier paragraphe, liens internes/externes, longueur contenu → score SEO (pastille rouge/orange/vert) dans sidebar Gutenberg + colonne admin articles (`inc/seo-analysis.php` + `assets/js/seo-analysis.js`)
- [x] **Score lisibilité Flesch** : formule Kandel-Moles (français), longueur phrases, phrases longues → pastille dans sidebar Gutenberg (`assets/js/seo-analysis.js`)
- [x] **Redirections 301** : manager dans l'admin, stockage en option WP, hook `template_redirect`, compteur hits, import CSV, log 404 (50 dernières) (`inc/seo-redirects.php`)
- [x] **Breadcrumbs PHP natifs** : `greenlight_breadcrumbs()` réutilisant `greenlight_get_breadcrumb_items()`, `<nav aria-label>` + `<ol>` + Schema.org, toggle admin, CSS ~10 lignes (`inc/seo-breadcrumbs.php`)
- [x] **Éditeur robots.txt** : textarea dans l'admin, filtre `robots_txt`, valeur par défaut pré-remplie, bouton restaurer (`inc/seo-robots.php`)

### 6D-PERF — Performance avancée

- [x] **Critical CSS** : `assets/css/critical.css` (~50 lignes above-the-fold), inline dans `wp_head`, defer du CSS principal via `media="print" onload`, toggle admin (`inc/critical-css.php`)
- [x] **Prefetch DNS / Preconnect** : textarea domaines explicites dans l'admin, injection `<link rel="dns-prefetch/preconnect">` uniquement pour les domaines saisis manuellement, sans auto-détection du contenu (`inc/prefetch.php`)
- [x] **Database cleanup** : supprimer révisions, brouillons auto, corbeille, spam, transients expirés, optimiser tables — boutons individuels + cron hebdomadaire (`inc/db-cleanup.php`)
- [x] **Heartbeat control** : admin/éditeur/front séparément — désactiver ou réduire l'intervalle (15s→120s) (`inc/heartbeat.php`)
- [x] **Concaténation CSS** : bundle unique `greenlight-bundle.css` généré lazy, réduit les requêtes HTTP de ~10 à 1-2, invalidation auto (`inc/concat.php`)

### 6D-IMG — Images avancées

- [x] **AVIF** : détection support PHP 8.1+, toggle + qualité séparé, réécriture `<img>` → `<picture>` avec source AVIF/WebP/original (`inc/images.php`)
- [x] **Bulk optimisation** : scan médiathèque, traitement par batch AJAX (5/10/20 images), progress bar JS, WebP + AVIF en masse (`inc/images-bulk.php`)
- [x] **Redimensionner originaux** : seuil configurable (défaut 2560px), hook `wp_handle_upload`, option garder copie originale (`inc/images.php`)
- [x] **Statistiques détaillées** : dashboard images (poids total, économie, top 10 plus lourdes), colonne médiathèque (poids original vs optimisé), filtre "non optimisées" (`inc/images-settings.php`)

## Phase 7 — Tests et finalisation
- [x] Runtime admin: corriger le fatal `get_current_screen()` sur `heartbeat_settings` afin de supprimer le 502 sur `wp-admin/` (Codex, 2026-03-28)
- [x] Smoke-test navigateur: home, archive et login admin OK dans Playwright sur `http://localhost:8888/greenlight/` (Codex, 2026-03-28)
- [x] Lighthouse : perf 100, a11y 95, SEO 100, best practices 100 (Codex, 2026-03-28)
- [x] Responsive 320px → 1920px et front sans JS validés dans Playwright (Codex, 2026-03-28)
- [x] Theme Check installé, activé et exécuté; warnings WordPress.org attendus sur les fonctions assumées du thème (Codex, 2026-03-28)
- [x] Theme Check plugin (conformité WordPress.org) - warnings restants consignés et assumés (Codex, 2026-03-28)
- [x] PHPCS WordPress Coding Standards — scan complet zéro erreur/warning (2026-04-08)
  - [x] Nettoyage ciblé sur `inc/admin.php` et `inc/seo-fields.php` (Codex, 2026-03-28)
  - [x] PHPCS global du thème sans erreurs ni warnings (Codex, 2026-03-28)
- [x] Validation W3C HTML sans erreur sur home et archive; seuls des avertissements informatifs restent sur les slashes des void elements (Codex, 2026-03-28)
- [x] Vérification accessibilité proxy VoiceOver via Playwright: landmarks, skip link, focus clavier, arbre a11y cohérents (Codex, 2026-03-28)
- [x] Test VoiceOver macOS natif non requis pour cette phase (décidé par le projet; proxy Playwright déjà validé) (Codex, 2026-03-28)
- [x] Test responsive 320px → 1920px (pas de breakpoint cassé)
- [x] Test sans JavaScript (tout fonctionne côté front)
- [x] Compter : lignes de CSS, lignes de JS, taille totale du thème
- [x] README.md (installation, configuration, utilisation)
- [x] CHANGELOG.md

## Phase 8 — Admin UI premium

> Branche de travail : `feat/admin-ui` depuis `dev`
> Référence visuelle validée : `/Users/benoitabot/Downloads/stitch/screen.png`
> Contraintes absolues : ne pas toucher au front, ne pas modifier la logique PHP/JS existante, ne pas ajouter de nouvelles features, uniquement restructurer et habiller l'interface admin Greenlight

Objectif : transformer l'interface admin Greenlight en control center premium, plus proche d'une suite éditoriale haut de gamme que d'une page de réglages WordPress standard, tout en conservant strictement les fonctionnalités actuelles.

### Direction validée

- [x] Direction visuelle retenue : "éditeur premium" minéral, sobre, respirant, avec surfaces claires, hiérarchie forte, aside contextuel et cartes premium (Codex, 2026-03-28)
- [x] Architecture validée : shell global Greenlight + navigation produit premium + layout `contenu principal + rail latéral` (Codex, 2026-03-28)
- [x] Périmètre validé : refonte UI admin uniquement, aucune évolution fonctionnelle (Codex, 2026-03-28)

### Phase 8A — Shell + SEO + Performance

- [x] Refaire le shell global de la page Greenlight : header, baseline, métriques synthétiques, largeur utile, surfaces
- [x] Remplacer les `nav-tab` WordPress par une navigation premium custom, en conservant le routing `?tab=...`
- [x] Créer un mini design system admin réutilisable : cartes, badges d'état, lignes de réglages, tables, aside, CTA
- [x] Refondre l'onglet `SEO` en cockpit éditorial premium, sans changer ses formulaires ni ses handlers
- [x] Refondre l'onglet `Performance` en cockpit technique premium, sans changer ses formulaires ni ses handlers
- [x] Préserver intégralement les actions existantes : robots.txt, redirections, cache, cleanup, heartbeat, fichiers générés

### Phase 8B — Apparence, Images, SVG, Outils

- [x] Étendre le shell premium aux onglets `Images` et `Apparence`
- [x] Étendre le shell premium aux onglets `SVG`, `Outils`
- [x] Refaire l'onglet `Apparence` en hub léger vers le Customizer, sans formulaire redondant
- [x] Refaire l'onglet `Images` avec une mise en avant des stats de stockage et du bulk
- [x] Uniformiser les tableaux, formulaires secondaires et zones d'actions sensibles
- [x] Vérifier le responsive admin sur laptop et écrans étroits (768px et 1280px validés)

### Validation

- [x] Aucun changement de logique métier
- [x] Aucune régression sur les formulaires `options.php`, `admin-post.php` et `admin-ajax.php`
- [x] Aucun changement du rendu front
- [x] PHPCS et `php -l` sur les fichiers PHP modifiés — zéro erreur
- [x] Smoke test navigateur sur `SEO`, `Performance`, `Images` — OK, fix fatal `$emit_perf_hidden_fields` appliqué

## Phase 9 — Valeur produit sans alourdir

> Positionnement produit :
> Greenlight remplace une partie importante de Yoast Premium et WP Rocket pour un site éditorial sobre.
> Greenlight n’est pas un clone complet de Yoast Premium + WP Rocket.
> La prochaine vraie valeur doit rester légère : personnalisation du thème d’abord, durcissement sécurité léger ensuite.

### Phase 9A — Personnalisation avancée du thème

- [x] Ajouter des presets éditoriaux complets (`Minimal`, `Magazine`, `Studio`, `Journal`)
- [x] 9A.1 — Étendre le hero : image, couleur, dégradé, titre/sous-titre, hauteur (`content`, `70vh`, `100vh`) + mode simple
- [x] Badge CO₂ : valeur manuelle, lien EcoIndex et placement `haut de page` / `footer`
- [x] 9A.2 — Étendre la navigation : couleurs, sticky, layout, sous-menus CSS-only
- [x] Ajouter des variantes pilotables pour archives, cartes, single et footer
- [x] Ajouter des réglages de densité visuelle : espacements, rayons, contraste, hauteur de header
- [x] Ajouter des réglages par contexte : home, archives, single, pages
- [x] Aligner les patterns Gutenberg sur ces presets pour éviter le CSS manuel
- [x] Garder zéro impact front en JS et zéro dépendance externe — validé Playwright : 0 script externe, 0 CDN, 0 ressource tierce (2026-04-08)

### Phase 9B — Durcissement sécurité léger

- [x] Renforcer la sanitation et les validations sur les actions admin sensibles
  - [x] Source redirect forcée à commencer par `/` (`inc/seo-redirects.php`)
  - [x] Cache purge : `is_file()` + `wp_delete_file()` avant unlink (`inc/admin.php`)
- [x] Ajouter des garde-fous sur import/export et sur certaines opérations de maintenance
  - [x] Import JSON : `is_uploaded_file()` + limite 512 Ko (`inc/admin.php`)
  - [x] Import CSV redirections : `is_uploaded_file()` + limite 256 Ko + plafond 500 règles (`inc/seo-redirects.php`)
  - [x] Optimize tables : restreint aux tables préfixées `$wpdb->prefix` (`inc/db-cleanup.php`)
- [x] Documenter les headers de sécurité recommandés côté serveur sans déplacer la logique dans le thème
  - [x] nginx : ajout HSTS commenté (`Strict-Transport-Security`) dans `README.md`
  - [x] Apache `.htaccess` : ajout `Permissions-Policy` + HSTS commenté dans `README.md`
- [x] Renforcer l’hygiène des logs et des uploads déjà gérés par Greenlight
  - [x] Log 404 : IP anonymisée via `wp_privacy_anonymize_ip()` (`inc/seo-redirects.php`)
  - [x] SVG sanitizer : passage denylist → allowlist d’éléments sûrs + suppression attributs `style` (`inc/svg.php`)
- [x] Éviter tout glissement vers un firewall, anti-bruteforce, malware scanner ou suite de sécurité lourde

## Phase 10 — Audit éco-conception front (branche feat/eco2)

> Branche : `feat/eco2` depuis `dev`
> Objectif : réduire le poids CSS, alléger le DOM, minimiser les requêtes HTTP, garantir un responsive sans breakpoint
> Audit réalisé le 2026-04-08 — état constaté ci-dessous

### 10A — CSS : réduire le poids de style.css

**Constat :** `style.css` = 1 276 lignes / ~30 KB (objectif Phase 3 : < 200 lignes fonctionnelles). La croissance est due aux systèmes de densité, hero variants, sous-menus CSS-only et mode preview ajoutés en Phases 9A/8.

- [x] **Supprimer `.page-hero::before` vide** (`background: transparent`) — propriété morte supprimée (2026-04-08)
- [x] **Mutualiser `.page-hero` et `.archive-intro`** — multi-sélecteurs CSS, suppression de la duplication lead/h1/body (2026-04-08)
- [x] **Variables density** — vérifiées : injectées via `greenlight_output_appearance_variants()` → `wp_head` dans `inc/admin.php`. Pas de zombies, système cohérent (2026-04-08)
- [x] **`backdrop-filter: blur(16px)`** sur `.site-header--sticky` — conditionné avec `@supports`, rayon réduit à 12px (2026-04-08)
- [x] **Supprimer les transitions inutiles** — 12 → 7 transitions dans style.css (−5) : supprimées sur `.entry-title a`, `.entry-more`, `.post-navigation a`, `.pagination`, `.footer-nav a` (faible engagement) ; conservées sur `.site-brand`, nav underline, submenu, `.cta-subscribe`, category pill, tag pill, newsletter button (2026-04-08)
- [x] **Séparer les styles preview-admin** — 69 lignes `.greenlight-preview-*` extraites vers `assets/css/admin-preview.css`, enqueué uniquement en `is_customize_preview()` (2026-04-08)
- [x] **Sélecteurs redondants** — `.site-header--nav-uppercase .site-nav a { text-transform: uppercase }` supprimé (doublon de la règle de base) (2026-04-08)
- [x] **critical.css corrigé** — 3 sélecteurs périmés mis à jour : `.skip-link` aligné sur style.css, `.site-nav` → `.site-nav ul`, `.hero-lead` → `.hero-description` (2026-04-08)

### 10B — DOM : alléger les templates

**Constat :** header/footer sont exemplaires (DOM minimal, HTML sémantique pur). Problèmes localisés dans `front-page.php`.

- [x] **`data-greenlight-page-title` et `data-greenlight-page-excerpt`** dans `front-page.php` — conditionnés à `$_gl_preview_mode`, absents pour les visiteurs normaux (2026-04-08)
- [x] **`<div class="page-content">`** dans `front-page.php` — investigué : conservé, ce wrapper applique la contrainte 65ch + flex-gap sur les blocs Gutenberg via `.page-content` CSS. Suppression impossible sans régression de mise en page (2026-04-08)
- [x] **Double rendu hero en preview** — validé : en production, une seule section est rendue (if/else PHP). En preview Customizer, `hidden` est un attribut HTML natif (display:none UA stylesheet), zéro JS requis pour l'état initial. Le toggle JS du Customizer ne concerne que l'iframe preview qui a toujours JS. (2026-04-08)
- [x] **Audit DOM re-vérifié** — Phase 10 ne change aucun élément HTML (attributs + CSS uniquement). Comptages Phase 6C toujours valides. Note ajoutée dans `PROJECT_STATE.md` (2026-04-08)

### 10C — Requêtes HTTP : valider la chaîne de réduction

**Constat :** sans optimisation = 9 requêtes CSS (style.css + 8 blocs). Outils déjà en place mais à valider en production.

- [x] **Bundle CSS documenté** — README.md section "Recommandations production" ajoutée : concaténation (9→1 req), minification, cache HTML, critical CSS (2026-04-08)
- [x] **Blocs CSS conditionnels vérifiés** — `wp_enqueue_block_style()` avec `path` correct, handles cohérents avec le dequeue bundle dans `inc/concat.php` (2026-04-08)
- [x] **Chaîne critical CSS vérifiée** — `inc/critical-css.php` : inline + defer `media="print" onload` + noscript fallback ; `critical.css` corrigé (sélecteurs périmés) en 10A (2026-04-08)
- [x] **Ressources externes vérifiées** — aucun CDN, Google Fonts, ressource externe dans les templates PHP ; prefetch uniquement via domaines explicites admin (`inc/prefetch.php`) (2026-04-08)

### 10D — Responsive : confirmer zéro breakpoint

**Constat :** 0 `@media` dans style.css ✓ — conforme aux contraintes absolues du projet.

- [x] **Confirmer sur tous les fichiers CSS** — 0 `@media` dans style.css, assets/css/blocks/*.css, assets/css/critical.css. Seuls `@supports` autorisés (backdrop-filter). Contrainte absolue respectée à 100% (2026-04-08)
- [x] **Test visuel 320px → 1920px** — screenshots Playwright validés : 320px (header wrap naturel), 768px (split hero asymétrique visible), 1920px (contentSize centré). Aucune cassure layout (2026-04-08)

### Ordre d'implémentation recommandé

1. **10A.1** — Supprimer règles mortes CSS (pseudo-élément vide, transitions inutiles) — gains immédiats, risque nul
2. **10A.2** — Déplacer les styles preview-admin hors du front — ~80-100 lignes de moins côté visiteurs
3. **10B.1** — Supprimer `data-*` preview des templates front — bytes HTML économisés sur chaque page
4. **10A.3** — Mutualiser `.page-hero` / `.archive-intro` — refactoring CSS ciblé
5. **10C** — Documenter et valider la chaîne de réduction HTTP en production
6. **10D** — Tests responsive 320→1920 + audit `@media` sur tous les CSS blocs

## Environnement local ✓
- [x] Diagnostic 404 généralisé : serveur nginx MAMP sans `try_files` WordPress (2026-03-28)
- [x] Configuration nginx `/Applications/MAMP/conf/nginx/nginx.conf` : bloc `/greenlight/` avec `try_files` + `index` (2026-03-28)
- [x] Symlink thème MAMP → repo git : `/themes/greenlight` → `/Users/benoitabot/Sites/greenlight/greenlight` (2026-03-28)

## Fait
- [x] Planification du projet Greenlight (2025-03-27)
- [x] Désactivation des scripts et styles emoji WordPress côté front dans `functions.php` (Codex, 2026-03-27)
- [x] Phase 5 — SEO autonome + champs éditables terminée (Codex, 2026-03-27)
- [x] Phase 6 — Optimisation images terminée (Codex, 2026-03-27)
- [x] Phase 6B — Mise en forme minimale appliquée (Codex, 2026-03-27)
- [x] Correction de l'auth_callback SEO REST pour éviter la réponse JSON invalide (Codex, 2026-03-27)
- [x] `home.php` ajouté pour couvrir la vraie page des articles WordPress avec un rendu cohérent (Codex, 2026-03-27)
- [x] `greenlight_get_archive_lead_text()` ajouté pour générer une accroche éditoriale contextuelle sur les archives (Codex, 2026-03-27)
- [x] Page archive retravaillée avec un premier article mis en avant, miniature dédiée et liste allégée (Codex, 2026-03-27)
- [x] Style archive/home ajusté pour un rendu noir et blanc plus aérien et plus premium (Codex, 2026-03-27)
- [x] `screenshot.png` 1200×900 ajouté au thème (Codex, 2026-03-27)
