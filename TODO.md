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

## Phase 6C — UI, Admin unifiée & Éco-optimisation

> Branche : `feat/ui-improvement` depuis `dev`
> Référence visuelle : maquettes EcoEditorial (page.png, single.png, index.png, archives.png)
> Contrainte permanente : zéro jQuery, zéro dépendance externe, DOM minimal, CSS minimal

### Volet A — Redesign visuel éditorial

Objectif : aligner le rendu front sur l'esthétique "Organic Minimalism" des maquettes EcoEditorial — palette off-white/vert, typographie serrée, asymétrie, whitespace généreux, zéro bordure traditionnelle.

- [ ] **Palette DESIGN.md** : mettre à jour `theme.json` — background `#faf9f4`, surface `#f4f4ee`, surface-alt `#ffffff`, text `#2f342d`, primary `#4c6547`, primary-dim `#41593c`, border `#afb3aa` (opacity 15%), tertiary `#e5f4c9`
- [ ] **Header** : `header.php` — site-title à gauche, nav centrée, CTA Subscribe à droite (flex, `justify-content: space-between`, pas de div wrapper)
- [ ] **Hero (front-page)** : `front-page.php` + pattern `hero.php` — titre surdimensionné (xx-large, letter-spacing -0.03em) aligné gauche, paragraphe descriptif aligné droite (asymétrie volontaire), Carbon Badge pill en haut
- [ ] **Carbon Badge** : composant PHP `greenlight_carbon_badge()` — calcul simplifié (DOM count + poids estimé page) affiché en pill `tertiary-container`, surcharge manuelle possible via option admin
- [ ] **Index/home** : `home.php` + `index.php` — premier article en layout 50/50 (image + texte via flex), articles suivants en alternance image gauche/droite, section newsletter CTA en bas (surface background)
- [ ] **Single** : `single.php` — catégorie en pill + CO2 badge, titre large (xx-large), meta auteur + date en flex space-between, image hero pleine largeur, contenu 65ch, blockquote avec bordure gauche primary + italique, tags en pills, section newsletter en bas
- [ ] **Archive** : `archive.php` — titre éditorial large, lead text, grille asymétrique (premier article large, suivants en 2 colonnes flex), pagination stylisée
- [ ] **Footer** : `footer.php` — copyright + liens secondaires (flex wrap) + mention "Low Emission Mode" à droite, surface background
- [ ] **Surfaces & profondeur** : CSS — pas de bordures 1px, différenciation par chromatic shifts (surfaces), ombres "Whisper Shadow" (`0 20px 40px rgba(47, 52, 45, 0.04)`) uniquement sur éléments flottants
- [ ] **Boutons** : `assets/css/blocks/button.css` — primary `#4c6547` avec gradient satin 135deg vers `#41593c`, border-radius `md`, secondary en `secondary_container`, tertiary text-only underline
- [ ] **Labels catégories** : uppercase, letter-spacing +0.05em, font-size `small`, couleur `on_surface_variant`
- [ ] **Patterns mis à jour** : `hero.php`, `cards.php`, `contact.php`, `header.php`, `footer.php` — alignés sur la nouvelle palette et la direction esthétique
- [ ] **`screenshot.png`** : nouvelle capture 1200×900 reflétant le redesign

### Volet B — Interface admin unifiée "Greenlight"

Objectif : regrouper tous les réglages du thème dans une page admin top-level unique avec onglets, tout en gardant temporairement les anciennes sous-pages.

- [ ] **`inc/admin.php`** : `add_menu_page( 'Greenlight', 'Greenlight', 'manage_options', 'greenlight', ... )` — page principale avec navigation par onglets CSS-only (pas de JS admin supplémentaire)
- [ ] **Icône menu** : SVG inline Dashicons-compatible (feuille verte ou éco-icône) encodée en base64 dans `add_menu_page`
- [ ] **Onglet SEO** : reprend le contenu de `inc/seo-settings.php` existant, formulaire identique, même `option_group`
- [ ] **Onglet Images** : reprend le contenu de `inc/images-settings.php` existant, formulaire identique, même `option_group`
- [ ] **Onglet Performance** :
  - [ ] Toggle minification CSS (active/désactive le chargement des `.min.css` ou le fallback PHP)
  - [ ] Toggle minification JS (idem pour `.min.js`)
  - [ ] Toggle page cache HTML (active/désactive le cache statique)
  - [ ] Bouton "Purger le cache" (supprime les fichiers cache HTML + transients)
  - [ ] Durée de vie du cache (select : 1h, 6h, 12h, 24h, 1 semaine)
  - [ ] Affichage : nombre de pages cachées, taille totale du cache
- [ ] **Onglet Apparence** :
  - [ ] Sélecteurs de couleur pour : header bg, footer bg, primary, surface, text (via `wp_color_picker` côté admin uniquement)
  - [ ] Toggle Carbon Badge (on/off + valeur manuelle optionnelle)
  - [ ] Toggle section newsletter CTA (on/off dans footer/single)
  - [ ] Choix layout archive : grille asymétrique / liste simple (radio)
  - [ ] Choix style hero : asymétrique (titre gauche + texte droite) / centré (select)
- [ ] **Onglet SVG** :
  - [ ] Toggle autoriser l'upload SVG (actif/inactif)
  - [ ] Info sécurité : mention DOMDocument sanitization
- [ ] **Compatibilité** : les anciennes pages Apparence > Greenlight > SEO et Apparence > Greenlight > Images restent fonctionnelles temporairement (même options WP, pas de duplication de données)
- [ ] **Sécurité admin** : nonce sur chaque formulaire, `current_user_can('manage_options')`, `sanitize_*()` sur chaque input
- [ ] **i18n** : toutes les chaînes admin dans `__()` / `esc_html_e()` avec text domain `greenlight`

### Volet C — Éco-optimisation (objectif EcoIndex A)

Objectif : passer de EcoIndex B à A. Améliorer la compression, le cache, la minification et réduire les requêtes.

- [ ] **Minification CLI** : script `bin/minify.sh` — minifie `style.css` → `style.min.css`, chaque `assets/css/blocks/*.css` → `*.min.css`, `assets/js/seo-sidebar.js` → `seo-sidebar.min.js` (sed/awk ou PHP CLI, pas de dépendance npm)
- [ ] **Minification fallback PHP** : `inc/minify.php` — si le `.min` n'existe pas, minification à la volée via `str_replace` (suppression commentaires, whitespace, newlines) + cache transient du résultat
- [ ] **Enqueue conditionnel** : `functions.php` — charger `.min.css`/`.min.js` si le fichier existe ET que l'option minification est active, sinon fallback sur le fichier source
- [ ] **Page cache HTML** : `inc/cache.php`
  - [ ] `ob_start()` dans `template_redirect`, écriture du buffer dans `wp-content/cache/greenlight/` en `.html`
  - [ ] Servir le `.html` depuis `advanced-cache.php` ou early hook si le fichier existe et n'est pas expiré
  - [ ] Exclure : admin, preview, POST requests, utilisateurs connectés, pages avec query string
  - [ ] Purge auto sur `save_post`, `publish_post`, `edit_post`, `delete_post`, `switch_theme`
  - [ ] Bouton purge manuelle dans l'onglet Performance
  - [ ] Durée de vie configurable (option admin)
- [ ] **Headers HTTP** : `inc/cache.php` — hook `send_headers` pour `Cache-Control`, `Expires`, `ETag` sur les assets statiques (CSS, JS, images, fonts)
- [ ] **Compression** : documenter la config nginx recommandée pour gzip/brotli dans `README.md` (le thème ne gère pas la compression lui-même, c'est au serveur)
- [ ] **Upload SVG** : `inc/svg.php`
  - [ ] `greenlight_allow_svg_upload()` : filtre `upload_mimes` pour ajouter `image/svg+xml`
  - [ ] `greenlight_sanitize_svg()` : hook `wp_handle_upload_prefilter`, sanitisation via `DOMDocument` (suppression scripts, événements JS, xlink malveillants)
  - [ ] Conditionné au toggle SVG dans l'admin
- [ ] **Nettoyage WP** : `functions.php` — supprimer `wp_generator`, RSD link, wlwmanifest, shortlink, feed links inutiles, REST API link du head (via `remove_action` sur `wp_head`)
- [ ] **Audit DOM** : vérifier que chaque template type reste sous 80 éléments DOM — documenter le comptage dans `PROJECT_STATE.md`
- [ ] **Inline Gutenberg** : compromis accepté — les global-styles inline restent, pas de dequeue agressif (préserver la compatibilité éditeur)
- [ ] **Documentation nginx** : bloc recommandé dans `README.md` pour gzip, cache static assets, headers security, et `try_files` WordPress

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
git checkout -b feat/ui-improvement
# Travailler volet par volet, commit par volet
git add -A && git commit -m "Phase 6C/A: Redesign visuel éditorial — palette DESIGN.md, templates, patterns"
git add -A && git commit -m "Phase 6C/B: Interface admin unifiée Greenlight — onglets, réglages"
git add -A && git commit -m "Phase 6C/C: Éco-optimisation — minification, cache, SVG, nettoyage head"
```

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
