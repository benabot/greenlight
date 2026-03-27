# PROJECT_STATE.md — Greenlight

> État du projet au 28 mars 2026. Référence rapide pour reprendre le travail en contexte.

---

## Branche active

`feat/theme-files-creation`

## Commits réalisés

| Hash | Phase | Description |
|------|-------|-------------|
| `755382c` | Phase 1 | Squelette du thème — style.css, theme.json, functions.php, templates de base |
| `928e025` | Phase 2 | Templates PHP — DOM sémantique minimal, zéro div, aria-labelledby |
| `c1ba006` | Phase 3 | CSS éco-conçu — block styles conditionnels, Green Precision aesthetic |
| `43801df` | Phase 4 | Patterns Gutenberg — hero, cards, contact, header, footer + catégorie Greenlight |

---

## Fichiers créés

### Phase 1 — Squelette

| Fichier | Contenu |
|---------|---------|
| `style.css` | Header WP complet + reset Josh Comeau + skip-link, sr-only, focus-visible |
| `theme.json` | Design system v3 : 5 font sizes fluid (clamp), 5 spacing fluid, 6 couleurs, layout contentSize/wideSize, styles éléments (h1-h6, button, link) |
| `functions.php` | `greenlight_setup()` (add_theme_support, editor-styles + `add_editor_style('style.css')`), `greenlight_enqueue()` (style.css + deregister jQuery), `greenlight_disable_emojis()` (suppression des assets emoji WordPress côté front), `greenlight_block_styles()` (wp_enqueue_block_style), `greenlight_pattern_categories()` (Codex, 2026-03-27) |
| `header.php` | DOCTYPE, charset, viewport, wp_head(), skip link, `<header>` + `<nav aria-label>` (wp_nav_menu sans container), `<main id="main-content">` |
| `footer.php` | `</main>`, `<footer>` avec copyright dynamique (gmdate + bloginfo), wp_footer() |
| `index.php` | Loop fallback : `<article>` sémantique, the_title/the_excerpt, the_posts_pagination() |
| `screenshot.png` | Capture 1200×900 du thème Greenlight (Codex, 2026-03-27) |

### Phase 2 — Templates PHP

| Fichier | Structure HTML |
|---------|---------------|
| `front-page.php` | Loop + `<section aria-labelledby="welcome-heading">` fallback |
| `single.php` | `<article>` + `<header>` (h1 + `<time datetime="c">` + auteur) + content + `<footer>` (tags + wp_link_pages) + navigation + comments |
| `page.php` | `<article>` + `<header>` h1 + content + wp_link_pages + comments |
| `archive.php` | `<section aria-labelledby="archive-heading">` + loop avec `<time>` + pagination |
| `search.php` | `<section aria-labelledby="search-heading">` + loop + get_search_form() fallback |
| `404.php` | `<section aria-labelledby="error-heading">` + get_search_form() |
| `comments.php` | Guard post_password_required + `<section id="comments">` + `<ol>` wp_list_comments + comment_form() |

### Phase 3 — CSS éco-conçu

**Direction esthétique : "Green Precision"** — typographie serrée (letter-spacing -0.02em), hairline borders, underline animé scaleX sur nav, focus-visible assumé, bleu électrique (#2563eb) seul accent.

| Fichier | Lignes | Contenu |
|---------|--------|---------|
| `assets/css/blocks/navigation.css` | 35 | Underline animé `scaleX` sur `::after`, uppercase small caps, aria-current |
| `assets/css/blocks/heading.css` | 8 | `letter-spacing: -0.02em`, `font-feature-settings: kern + liga`, line-height 1.15 |
| `assets/css/blocks/paragraph.css` | 15 | `max-width: 65ch`, drop cap avec float + color primary |
| `assets/css/blocks/button.css` | 21 | Border-radius 0, uppercase, transition, style outline |
| `assets/css/blocks/group.css` | 13 | Styles `is-style-surface` et `is-style-card` (border-left primary 3px) |
| `assets/css/blocks/image.css` | 13 | `height: auto`, figcaption italic opacity 0.6 |
| `assets/css/blocks/separator.css` | 11 | Border-top 1px solid border token, margin LG |
| `assets/css/blocks/query.css` | 39 | Post list border-top, titre hover primary, pagination avec état current |

**Audit CSS :** 155 lignes blocks + ~97 lignes fonctionnelles dans style.css (hors reset ~50 lignes). Objectif < 200 lignes hors reset : **atteint**.

Enqueue conditionnel via `wp_enqueue_block_style()` dans `greenlight_block_styles()` — le CSS d'un bloc n'est chargé que si ce bloc est présent sur la page.

### Phase 4 — Patterns Gutenberg

Auto-enregistrés via headers PHP (WP 6.0+), catégorie `greenlight` enregistrée via `register_block_pattern_category()`.

| Fichier | Slug | Description |
|---------|------|-------------|
| `patterns/hero.php` | `greenlight/hero` | Group pleine largeur, bg surface, h1 xx-large letter-spacing -0.03em, description large, 2 boutons (primary + outline) |
| `patterns/cards.php` | `greenlight/cards` | Group wide + h2 + core/columns 3 colonnes, chaque colonne = group is-style-card |
| `patterns/contact.php` | `greenlight/contact` | Group wide bg surface + h2 centré + formulaire HTML natif (admin-post.php + nonce WP) |
| `patterns/header.php` | `greenlight/header` | Group full flex space-between : core/site-title bold -0.02em + core/navigation |
| `patterns/footer.php` | `greenlight/footer` | Group full flex bg surface : copyright dynamique + core/navigation secondaire |

### Phase 5 — SEO autonome

| Fichier | Slug | Description |
|---------|------|-------------|
| `inc/seo.php` | `greenlight/seo` | Meta tags, Open Graph, Twitter Card, canonical, robots, title separator (Codex, 2026-03-27) |
| `inc/seo-fields.php` | `greenlight/seo-fields` | Meta box native, `register_post_meta()`, sidebar Gutenberg (Codex, 2026-03-27) |
| `inc/seo-json-ld.php` | `greenlight/seo-json-ld` | JSON-LD `WebSite`, `Article`, `BreadcrumbList` (Codex, 2026-03-27) |
| `inc/seo-sitemap.php` | `greenlight/seo-sitemap` | Rewrite rules, sitemap XML, cache transient, noindex filtering (Codex, 2026-03-27) |
| `inc/seo-settings.php` | `greenlight/seo-settings` | Page de réglages SEO sous Apparence > Greenlight (Codex, 2026-03-27) |
| `assets/js/seo-sidebar.js` | `greenlight/seo-sidebar` | Panel Gutenberg `PluginDocumentSettingPanel` pour les métadonnées SEO (Codex, 2026-03-27) |

### Phase 6 — Optimisation images

| Fichier | Slug | Description |
|---------|------|-------------|
| `inc/images.php` | `greenlight/images` | Tailles `greenlight-hero`, `greenlight-card`, `greenlight-thumb`, suppression des tailles core inutiles, génération WebP, preload hero, attributs responsive images (Codex, 2026-03-27) |
| `inc/images-settings.php` | `greenlight/images-settings` | Page de réglages Images sous Apparence > Greenlight avec toggle WebP, qualité, suppression tailles et infos espace économisé (Codex, 2026-03-27) |

### Phase 6B — Cibles visuelles

| Fichier | Contenu |
|---------|---------|
| `header.php` | Header monochrome avec site-brand, tagline optionnelle et navigation principale alignée en flexbox (Codex, 2026-03-27) |
| `footer.php` | Footer minimal avec copyright centré et fin de flux claire (Codex, 2026-03-27) |
| `index.php` | Home / blog index avec intro, liste d’articles en UL et pagination sobre (Codex, 2026-03-27) |
| `archive.php` | Archives avec intro éditoriale, liste d’articles en UL et hiérarchie discrète (Codex, 2026-03-27) |
| `single.php` | Article unique en noir et blanc avec meta ligne, contenu encadré et navigation d’article (Codex, 2026-03-27) |
| `page.php` | Page standard minimaliste avec contenu aéré et pagination de blocs (Codex, 2026-03-27) |
| `style.css` | Layout monochrome, flexbox, espacements fluides, navigation discrète et styles Gutenberg sobres (Codex, 2026-03-27) |
| `theme.json` | Palette noir et blanc, tokens de surface/bordure et couleurs de boutons/lien alignées sur le minimalisme (Codex, 2026-03-27) |

### Phase 6B — Mise en forme minimale

Objectif : proposer une interface visuelle minimaliste, éditable immédiatement, avec le minimum de HTML et un responsive géré par flexbox, `clamp()` et conventions CSS modernes.

| Domaine | Cible |
|---------|-------|
| Structure HTML | `nav`, `header`, `main`, `section`, `article`, `p`, `ul`, `footer` uniquement quand nécessaire, sans wrappers superflus |
| Responsive | Flexbox, dimensionnement intrinsèque, `clamp()`, aucun breakpoint de mise en page |
| Style | Hiérarchie légère, surfaces sobres, espacements fluides, typographie lisible |
| Gutenberg | Blocs prêts à être édités sans surcharge visuelle ni complexité structurelle |
| Références | S’inspirer de `html_responsive_images.html` et `wordpress_responsive_images.php` pour le preload hero et les images responsives |

### Phase 6B — Raffinement éditorial

| Fichier | Contenu |
|---------|---------|
| `functions.php` | Helper `greenlight_get_archive_lead_text()` pour une accroche éditoriale contextuelle sur home, archives de taxonomie, archives de date et archives auteur (Codex, 2026-03-27) |
| `archive.php` | Intro avec lead éditorial, note secondaire, premier article mis en avant, miniature optionnelle et liste allégée pour une lecture plus aérienne (Codex, 2026-03-27) |
| `home.php` | Vraie page des articles WordPress calée sur le même traitement éditorial que les archives (Codex, 2026-03-27) |
| `style.css` | Ajustements de hiérarchie, d’espacement et de surface pour un rendu monochrome plus premium et plus respirant (Codex, 2026-03-27) |

---

## Design system actif (theme.json v3)

### Couleurs
| Slug | Hex | Usage |
|------|-----|-------|
| `text` | `#1a1a1a` | Texte principal |
| `background` | `#ffffff` | Fond de page |
| `surface` | `#f5f5f5` | Sections alternées, cards |
| `surface-alt` | `#e8e8e8` | Hover states, variations |
| `primary` | `#2563eb` | Liens, CTA, accents |
| `border` | `#e5e7eb` | Bordures, séparateurs |

### Typographie
| Slug | Taille (clamp) |
|------|---------------|
| `small` | `clamp(0.875rem, 0.8rem + 0.4vw, 1rem)` |
| `medium` | `clamp(1rem, 0.95rem + 0.25vw, 1.125rem)` |
| `large` | `clamp(1.125rem, 1rem + 0.6vw, 1.5rem)` |
| `x-large` | `clamp(1.5rem, 1.25rem + 1.25vw, 2rem)` |
| `xx-large` | `clamp(2rem, 1.5rem + 2.5vw, 3rem)` |

Font family : `system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', sans-serif`

### Spacing
| Slug | Taille (clamp) |
|------|---------------|
| `xs` | `clamp(0.25rem, 0.2rem + 0.25vw, 0.5rem)` |
| `sm` | `clamp(0.5rem, 0.4rem + 0.5vw, 1rem)` |
| `md` | `clamp(1rem, 0.75rem + 1.25vw, 2rem)` |
| `lg` | `clamp(2rem, 1.5rem + 2.5vw, 4rem)` |
| `xl` | `clamp(4rem, 3rem + 5vw, 8rem)` |

### Layout
- `contentSize` : `min(90vw, 1200px)`
- `wideSize` : `min(95vw, 1400px)`
- `useRootPaddingAwareAlignments` : `true`

---

## Contraintes respectées

- [x] Zéro jQuery (`wp_deregister_script('jquery')` côté front)
- [x] Zéro dépendance externe (CDN, polyfill, librairie)
- [x] Quasi-zéro JS front (seul `seo-sidebar.js` côté éditeur)
- [x] DOM minimal — zéro `<div>` wrapper non sémantique
- [x] CSS minimal — design system dans theme.json, styles blocs conditionnels
- [x] Responsive sans `@media` — flexbox + clamp() uniquement
- [x] i18n — toutes chaînes dans `__()` / `_e()` / `esc_html_e()` avec text domain `greenlight`
- [x] Sécurité — `esc_*()` sur toute sortie PHP
- [x] Emoji WordPress désactivés côté front via `greenlight_disable_emojis()` (Codex, 2026-03-27)

---

## Phases restantes

| Phase | Tâches principales |
|-------|--------------------|
| **7 — Tests** | Lighthouse, PHPCS, W3C, VoiceOver, responsive 320→1920px |

---

## Environnement local (2026-03-28)

- **WordPress** : MAMP sur `localhost:8888/greenlight/` → `/Applications/MAMP/htdocs/greenlight/`
- **Serveur** : nginx 1.27.2 (pas Apache — `.htaccess` inutile)
- **Config nginx** : `/Applications/MAMP/conf/nginx/nginx.conf` — bloc `/greenlight/` ajouté avec `try_files $uri $uri/ /greenlight/index.php?$args`
- **Thème** : symlink `/Applications/MAMP/htdocs/greenlight/wp-content/themes/greenlight` → `/Users/benoitabot/Sites/greenlight/greenlight`
- **Source de vérité** : toujours `/Users/benoitabot/Sites/greenlight/greenlight`

## Commandes utiles

```bash
php -l filename.php                       # Lint PHP
phpcs --standard=WordPress filename.php   # Standards WP Coding Standards
```
