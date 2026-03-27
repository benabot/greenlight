# TODO — Greenlight

## Phase 1 — Squelette du thème ✓
- [x] Créer le dossier `greenlight/` dans wp-content/themes/
- [x] `style.css` : header WordPress (Theme Name: Greenlight, Version: 1.0.0, Text Domain: greenlight)
- [x] `theme.json` v3 : design system complet (couleurs, typo system-ui fluid, spacing clamp, layout, block settings, custom:false)
- [x] `functions.php` : add_theme_support (title-tag, post-thumbnails, html5, responsive-embeds, editor-styles, wp-block-styles, align-wide), deregister jQuery côté front, enqueue style.css
- [x] `index.php` : template fallback minimal (DOM léger)
- [x] `header.php` : skip link + `<header>` + `<nav aria-label>` + wp_head() — zéro div wrapper
- [x] `footer.php` : `<footer>` + wp_footer() — zéro div wrapper
- [x] `screenshot.png` 1200×900 (Codex, 2026-03-27)
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
