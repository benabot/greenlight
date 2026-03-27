# TODO — Greenlight

## Phase 1 — Squelette du thème ✓
- [x] Créer le dossier `greenlight/` dans wp-content/themes/
- [x] `style.css` : header WordPress (Theme Name: Greenlight, Version: 1.0.0, Text Domain: greenlight)
- [x] `theme.json` v3 : design system complet (couleurs, typo system-ui fluid, spacing clamp, layout, block settings, custom:false)
- [x] `functions.php` : add_theme_support (title-tag, post-thumbnails, html5, responsive-embeds, editor-styles, wp-block-styles, align-wide), deregister jQuery côté front, enqueue style.css
- [x] `index.php` : template fallback minimal (DOM léger)
- [x] `header.php` : skip link + `<header>` + `<nav aria-label>` + wp_head() — zéro div wrapper
- [x] `footer.php` : `<footer>` + wp_footer() — zéro div wrapper
- [ ] `screenshot.png` 1200×900
- [ ] Valider : thème activable, Gutenberg charge theme.json, zéro jQuery côté front

## Phase 2 — Templates PHP (DOM minimal) ✓
- [x] `front-page.php` : accueil — HTML sémantique pur, sections avec aria-labelledby
- [x] `single.php` : article — `<article>` + `<header>` + `<footer>` article, zéro div
- [x] `page.php` : page standard
- [x] `archive.php` : liste articles avec pagination native
- [x] `search.php` : résultats, meta robots noindex
- [x] `404.php` : page d'erreur accessible
- [x] `comments.php` : commentaires accessibles
- [x] Vérifier : aucun `<div>` non nécessaire, tout est balises sémantiques

## Phase 3 — CSS éco-conçu
- [ ] `style.css` : reset Josh Comeau + styles globaux minimaux (uniquement ce que theme.json ne gère pas)
- [ ] `assets/css/blocks/` : styles par bloc (core/image, core/group, core/columns, etc.)
- [ ] Enqueue conditionnel via `wp_enqueue_block_style()` dans functions.php
- [ ] Layout responsive : flexbox + clamp() uniquement, zéro @media pour le layout
- [ ] Focus visible sur tous les interactifs
- [ ] `.sr-only`, `.skip-link`
- [ ] Audit : compter les lignes de CSS total, objectif < 200 lignes hors reset

## Phase 4 — Patterns Gutenberg
- [ ] `patterns/hero.php` : image 16:9 + titre + texte + CTA — modifiable dans l'éditeur
- [ ] `patterns/cards.php` : grille flexbox responsive — modifiable
- [ ] `patterns/contact.php` : section formulaire
- [ ] `patterns/header.php` : titre site + nav
- [ ] `patterns/footer.php` : copyright + liens
- [ ] Catégorie "Greenlight" dans l'inserter de patterns
- [ ] Vérifier : tous les patterns sont modifiables à 100% dans Gutenberg

## Phase 5 — SEO autonome + champs éditables
- [ ] `inc/seo.php` : meta tags via wp_head (title, description, robots)
- [ ] `inc/seo.php` : Open Graph (og:title, og:description, og:image, og:url, og:type)
- [ ] `inc/seo.php` : Twitter Card (summary_large_image)
- [ ] `inc/seo.php` : Canonical URL (archives, pagination, taxonomies)
- [ ] `inc/seo-fields.php` : register_post_meta() pour les 4 champs SEO (show_in_rest: true)
- [ ] `inc/seo-fields.php` : Meta box PHP natif (add_meta_box, save_post, nonce)
- [ ] `assets/js/seo-sidebar.js` : Sidebar Gutenberg (PluginDocumentSettingPanel)
  - [ ] Champ titre SEO (input text)
  - [ ] Champ meta description (textarea avec compteur caractères)
  - [ ] Champ image OG (MediaUpload)
  - [ ] Toggle noindex (ToggleControl)
  - [ ] Enqueue uniquement dans l'éditeur (enqueue_block_editor_assets)
- [ ] `inc/seo-json-ld.php` : Schema.org via wp_footer
  - [ ] WebSite (front-page)
  - [ ] Article (single)
  - [ ] BreadcrumbList (toutes les pages)
- [ ] `inc/seo-sitemap.php` : sitemap XML natif
  - [ ] Route /sitemap.xml + /sitemap-posts.xml + /sitemap-pages.xml
  - [ ] Exclure noindex, brouillons, privés
  - [ ] Cache transient (régénéré à chaque publish/update)
- [ ] `inc/seo-settings.php` : page Apparence > Greenlight > SEO
  - [ ] Titre du site pour les SERPs
  - [ ] Description globale
  - [ ] Séparateur titre
  - [ ] Toggle sitemap
  - [ ] Toggle noindex archives auteur / tags
- [ ] Meta robots : noindex archives auteur, résultats recherche, pages de tags (configurable)

## Phase 6 — Optimisation images
- [ ] `inc/images.php` : tailles custom (greenlight-hero, greenlight-card, greenlight-thumb)
- [ ] `inc/images.php` : remove_image_size() pour medium_large, 1536x1536, 2048x2048
- [ ] `inc/images.php` : conversion WebP à l'upload (wp_handle_upload hook)
  - [ ] Détecter support GD/Imagick
  - [ ] Fallback gracieux si pas de support
  - [ ] Conserver l'original + générer le WebP
- [ ] `inc/images.php` : filtre wp_get_attachment_image_attributes pour lazy/eager automatique
- [ ] `inc/images.php` : preload hero image dans wp_head
- [ ] `inc/images-settings.php` : page Apparence > Greenlight > Images
  - [ ] Toggle conversion WebP
  - [ ] Qualité WebP (slider 1-100)
  - [ ] Toggle suppression tailles inutiles
  - [ ] Afficher espace disque économisé (info)

## Phase 7 — Tests et finalisation
- [ ] Lighthouse : perf ≥ 95, a11y ≥ 95, SEO ≥ 95, best practices ≥ 95
- [ ] Theme Check plugin (conformité WordPress.org)
- [ ] PHPCS WordPress Coding Standards
- [ ] Validation W3C HTML (validator.w3.org)
- [ ] Test VoiceOver macOS
- [ ] Test responsive 320px → 1920px (pas de breakpoint cassé)
- [ ] Test sans JavaScript (tout fonctionne côté front)
- [ ] Compter : lignes de CSS, lignes de JS, taille totale du thème
- [ ] README.md (installation, configuration, utilisation)
- [ ] CHANGELOG.md

## Fait
- [x] Planification du projet Greenlight (2025-03-27)
