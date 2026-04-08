# PROJECT_STATE.md — Greenlight

> État du projet au 8 avril 2026. Référence rapide pour reprendre le travail en contexte.

---

## Branche active

`feat/eco2` (depuis `dev`)

## Positionnement produit

- Greenlight remplace une partie importante de Yoast Premium et WP Rocket pour un site éditorial sobre.
- Greenlight n’est pas un clone complet de Yoast Premium + WP Rocket.
- La prochaine vraie valeur sans alourdir : personnalisation du thème d’abord, durcissement sécurité léger ensuite.

## État des phases

| Phase | Statut | Notes |
|-------|--------|-------|
| **10 — Audit éco-conception front** | 🔄 En cours | Branche `feat/eco2` — 22 tâches : CSS poids, DOM, HTTP, responsive |
| **9B — Durcissement sécurité** | ✅ Terminé | SVG allowlist, IP anonymisée, upload guards, OPTIMIZE scope |
| **9A — Personnalisation avancée** | ✅ Terminé | Presets, hero avancé, nav CSS-only, densité par contexte |
| **8 — Admin UI premium** | ✅ Terminé | Shell premium, navigation numérotée, onglets SEO/Performance/Images/SVG/Outils, smoke test validé |

## Commits réalisés

| Hash | Phase | Description |
|------|-------|-------------|
| `755382c` | Phase 1 | Squelette du thème — style.css, theme.json, functions.php, templates de base |
| `928e025` | Phase 2 | Templates PHP — DOM sémantique minimal, zéro div, aria-labelledby |
| `c1ba006` | Phase 3 | CSS éco-conçu — block styles conditionnels, Green Precision aesthetic |
| `43801df` | Phase 4 | Patterns Gutenberg — hero, cards, contact, header, footer + catégorie Greenlight |
| `44bde7b` | Phase 7 | Fix breadcrumbs and Composer tooling |
| `a36e799` | Phase 7 | PHPCS cleanup for admin and SEO fields |

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

## Historique des phases

| Phase | Statut | Notes |
|-------|--------|-------|
| **9B — Durcissement sécurité** | ✅ Terminé | SVG allowlist, IP anonymisée, upload guards, OPTIMIZE scope |
| **9A — Personnalisation avancée** | ✅ Terminé | Presets, hero avancé, nav CSS-only, densité par contexte |
| **8 — Admin UI premium** | ✅ Terminé | Shell premium, navigation numérotée, onglets, smoke test validé |
| **7 — Tests** | ✅ Terminé | Lighthouse, Theme Check, PHPCS global, W3C, responsive 320→1920px, JS-off |
| **6C/C — Éco-optimisation** | ✅ Terminé | bin/minify.sh, inc/minify.php, inc/cache.php, inc/svg.php |
| **6C/B — Admin unifiée** | ✅ Terminé | Page top-level Greenlight avec onglets |
| **6C/A — Redesign visuel** | ✅ Terminé | Palette DESIGN.md, templates front, patterns, Carbon Badge |
| **6B — Mise en forme minimale** | ✅ Terminé | Flexbox, clamp(), DOM minimal |
| **6 — Optimisation images** | ✅ Terminé | WebP/AVIF, bulk, tailles custom |
| **5 — SEO autonome** | ✅ Terminé | Meta, JSON-LD, sitemap, redirections, breadcrumbs |
| **1→4 — Squelette, templates, CSS, patterns** | ✅ Terminé | |

### Phase 7 — Avancement actuel

- [x] PHPCS ciblé validé sur `inc/admin.php` et `inc/seo-fields.php`
- [x] PHPCS global du thème sans erreurs ni warnings
- [x] Runtime admin corrigé: `inc/heartbeat.php` ne déclenche plus `get_current_screen()` hors contexte compatible, ce qui supprime le 502 sur `wp-admin/`
- [x] Smoke-test navigateur: home, archive et login admin validés dans Playwright sur `http://localhost:8888/greenlight/`
- [x] Lighthouse validé: perf 100, a11y 95, SEO 100, best practices 100
- [x] Vérifications responsive 320px → 1920px et front sans JS validées dans Playwright
- [x] Theme Check exécuté dans l’admin; warnings WordPress.org attendus consignés et assumés
- [x] W3C HTML validé sans erreur sur home et archive; seuls des avertissements informatifs sur les slashes des void elements persistent
- [x] Vérification accessibilité proxy VoiceOver via Playwright: landmarks, skip link et focus clavier cohérents
- [x] VoiceOver macOS natif non requis pour cette phase
- [x] Tests responsive 320px → 1920px validés dans Playwright
- [x] Test sans JavaScript validé dans Playwright
- [x] Métriques consolidées: 1150 lignes CSS, 551 lignes JS, 1701 lignes CSS+JS, 772 KB hors `.git`/`.playwright-cli`/`vendor`
- [x] README.md enrichi (installation, configuration, utilisation)
- [x] CHANGELOG.md ajouté

### Phase 8 — Terminé (2026-04-07)

- [x] Shell admin premium, navigation numérotée, mini design system `assets/css/admin-ui.css`
- [x] Onglets SEO, Performance, Images, SVG, Outils refondus
- [x] Apparence simplifié en hub vers le Customizer (rail latéral supprimé)
- [x] Fatal error `$emit_perf_hidden_fields` corrigé (closure manquante)
- [x] PHPCS zéro erreur sur 30 fichiers PHP modifiés
- [x] Smoke test navigateur : SEO ✓ Performance ✓ Images ✓
- [x] Responsive 768px et 1280px validés, zéro erreur JS console

### Phase 9 — Terminé (2026-04-07)

- [x] Presets éditoriaux (Minimal, Magazine, Studio, Journal)
- [x] Hero avancé : image/couleur/dégradé, titre, sous-titre, hauteur, mode simple
- [x] Navigation avancée : couleurs, sticky, sous-menus CSS-only
- [x] Variantes archives, cartes, single et footer
- [x] Densité visuelle par contexte (home, archives, single, pages)
- [x] SVG allowlist (19 éléments sûrs) + suppression `foreignObject`/`animate`/`style`
- [x] IP logs 404 anonymisée (`wp_privacy_anonymize_ip`)
- [x] `is_uploaded_file()` + limites taille sur import JSON (512 Ko) et CSV (256 Ko, 500 règles)
- [x] OPTIMIZE TABLE restreint aux tables `$wpdb->prefix`
- [x] Source redirect forcée à commencer par `/`
- [x] `wp_delete_file()` + `is_file()` sur purge cache
- [x] README.md : `Permissions-Policy` Apache + HSTS commenté nginx + Apache

---

## Phase 6C — Décisions techniques (2026-03-28)

### Minification
- **Approche** : CLI (`bin/minify.sh`) génère les `.min.css`/`.min.js` en dev + fallback PHP (`inc/minify.php`) à la volée si `.min` absent
- **Raison** : pas de build npm, pas de dépendance externe, CLI rapide en dev, fallback sécurise la prod

### Cache
- **Périmètre** : headers HTTP (`Cache-Control`, `Expires`, `ETag`) + page cache HTML statique (`wp-content/cache/greenlight/`)
- **Purge** : automatique sur `save_post`/`publish_post`/`delete_post`/`switch_theme` + bouton manuel dans l'admin Greenlight
- **Exclusions** : admin, preview, POST, utilisateurs connectés, query strings
- **Durée** : configurable (1h à 1 semaine)
- **Compatibilité serveur** : logique 100% PHP (pas de dépendance nginx ou Apache). Headers envoyés via `header()` PHP. Documentation dans `README.md` avec config recommandée nginx (gzip/brotli, `try_files`) ET Apache (`.htaccess` avec `mod_deflate`, `mod_expires`, `mod_headers`)

### Inline Gutenberg
- **Stratégie** : compromis — les `global-styles` inline et block-supports restent en place, pas de dequeue agressif
- **Raison** : préserver la compatibilité éditeur Gutenberg, optimiser tout le reste

### Admin
- **Structure** : page top-level "Greenlight" avec 6 onglets CSS-only (SEO, Images, Performance, Apparence, SVG, Outils)
- **Outils** : Import/Export JSON de tous les réglages (`greenlight_handle_export`, `greenlight_handle_import`)
- **Apparence** : hub léger vers le Customizer WordPress natif — Global, Header, Hero, Single, Archive, Footer, avec aperçu live
- **Performance** : tableau statut fichiers `.min`, détection serveur (nginx/Apache), bouton régénération
- **Prefetch** : uniquement les domaines explicites saisis dans l'admin; plus de détection automatique des URLs externes ni de cron associé
- **Preview** : Customizer WordPress natif pour l’aperçu live de l’apparence
- **JS admin** : aucun JS dédié pour l’aperçu d’Apparence ; plus d’iframe maison

### SVG
- **Sanitisation** : `DOMDocument` PHP natif — **allowlist** de 19 éléments sûrs (strategy switch denylist→allowlist en Phase 9B). `foreignObject`, `animate`, `iframe`, `embed`, `object`, `set`, `style` bloqués par défaut. Attributs `on*`, `style` et `href javascript:` supprimés. `<use>` externe supprimé.
- **Activation** : conditionnée au toggle dans l'onglet SVG de l'admin

### Carbon Badge
- **Calcul** : simplifié — comptage DOM (`DOMDocument` ou `substr_count`) + estimation poids page
- **Affichage** : pill `tertiary-container` (#e5f4c9), texte `on_tertiary_container` (#505d3c)
- **Surcharge** : valeur manuelle possible via option admin (onglet Apparence)

### Direction esthétique Phase 6C/A
- **Palette** : off-white `#faf9f4`, vert `#4c6547`, surfaces `#f4f4ee`, texte `#2f342d`
- **Règle "No-Line"** : pas de bordures 1px pour le sectioning, chromatic shifts uniquement
- **Ombres** : "Whisper Shadow" `0 20px 40px rgba(47, 52, 45, 0.04)` — couleur dérivée de on_surface, pas de noir pur
- **Boutons** : gradient satin 135deg primary → primary-dim, border-radius md
- **Labels** : uppercase, letter-spacing +0.05em, font-size small
- **Asymétrie** : hero titre gauche / description droite, articles en alternance

### Branche git
```bash
git checkout dev
git checkout -b feat/admin-ui
```

### Phase 6C/C — Éco-optimisation (2026-03-28)

| Fichier | Rôle |
|---------|------|
| `bin/minify.sh` | CLI PHP — génère `.min.css`/`.min.js` ; préserve le header WP `/* ... */` |
| `inc/minify.php` | Fallback lazy-generation sur disque + transient 24h ; `greenlight_ensure_min_file()`, `greenlight_clear_min_files()` |
| `inc/cache.php` | Page cache HTML statique (`wp-content/cache/greenlight/`), headers `Cache-Control + Expires + ETag`, purge auto |
| `inc/svg.php` | Upload SVG conditionnel, sanitisation `DOMDocument` (scripts, `on*`, xlink externes), fix MIME check |
| `inc/customizer.php` | Customizer natif de l’apparence — Hero, Navigation, Footer, couleurs et variantes de rendu |

**Nouvelles options `greenlight_appearance_options`** (s'ajoutent à celles déjà existantes) :

| Clé | Défaut | Onglet |
|-----|--------|--------|
| `color_background`, `color_tertiary`, `color_border`, `color_on_surface_variant` | `''` | Apparence > Global |
| `show_tagline` | `0` | Apparence > Header |
| `show_hero_badge`, `hero_text` | `1`, `''` | Apparence > Hero |
| `show_date`, `show_author`, `show_tags`, `show_newsletter_single` | `1` | Apparence > Single |
| `show_excerpts_archive`, `show_thumbnails_archive` | `1` | Apparence > Archive |
| `show_low_emission`, `custom_copyright`, `show_footer_nav` | `1`, `''`, `1` | Apparence > Footer |

**`greenlight_output_custom_colors()`** — nouvelles variables CSS injectées dans `wp_head` :
`--wp--preset--color--background`, `--wp--preset--color--tertiary`, `--wp--preset--color--border`, `--wp--preset--color--on-surface-variant`

**Nettoyage `<head>`** (`greenlight_clean_wp_head`) : suppression `wp_generator`, `rsd_link`, `wlwmanifest_link`, `wp_shortlink_wp_head`, `adjacent_posts_rel_link_wp_head`, `rest_output_link_wp_head`, `wp_oembed_add_discovery_links`, `feed_links_extra`

---

## Environnement local (2026-03-28)

- **WordPress** : MAMP sur `localhost:8888/greenlight/` → `/Applications/MAMP/htdocs/greenlight/`
- **Serveur** : nginx 1.27.2 (pas Apache — `.htaccess` inutile)
- **Config nginx** : `/Applications/MAMP/conf/nginx/nginx.conf` — bloc `/greenlight/` ajouté avec `try_files $uri $uri/ /greenlight/index.php?$args`
- **Thème** : symlink `/Applications/MAMP/htdocs/greenlight/wp-content/themes/greenlight` → `/Users/benoitabot/Sites/greenlight/greenlight`
- **Source de vérité** : toujours `/Users/benoitabot/Sites/greenlight/greenlight`

## Audit DOM — Phase 6C (2026-03-28)

Objectif : chaque template reste sous 80 éléments DOM produits par le template lui-même (hors `the_content()` et sorties WordPress dynamiques).

### Méthode

Deux métriques distinctes :
- **Éléments template** : balises HTML explicitement écrites dans le fichier PHP (hors `the_content()`, `wp_nav_menu()`, `the_post_thumbnail()`, boucles loop)
- **Total estimé** : shell + template + sorties WP typiques (menu 3 items, contenu 5 blocs, miniature)

### Shell partagé (header.php + footer.php)

| Élément | Quantité |
|---------|----------|
| `<a>` skip-link | 1 |
| `<header>` | 1 |
| `<a>` site-brand | 1 |
| `<nav>` principale | 1 |
| Menu 3 items (WP) : `<ul>` + 3×`<li>` + 3×`<a>` | 7 |
| `<a>` cta-subscribe | 1 |
| `<main>` | 1 |
| `<footer>` | 1 |
| `<p>` copyright | 1 |
| `<nav>` footer + 3 items (WP, optionnel) | 0–8 |
| **Sous-total shell** | **15–23** |

Config typique retenue (footer nav actif, tagline off, low-emission off) : **22 éléments**.

### Tableaux par template

| Template | Éléments template | Total estimé (config typique) | Statut |
|----------|-------------------|-------------------------------|--------|
| `front-page.php` | 5 | ~29 | ✅ |
| `page.php` | 4 | ~35 (+blocs WP) | ✅ |
| `single.php` (sans newsletter, 3 tags) | 25 | ~57 | ✅ |
| `single.php` (avec newsletter, 3 tags) | 34 | ~66 | ✅ |
| `404.php` | 3 | ~26 | ✅ |
| `search.php` (aucun résultat) | 4 | ~27 | ✅ |
| `search.php` (5 résultats) | 4 + 3×5=19 | ~47 | ✅ |
| `archive.php` / `home.php` (1 featured + 3 grid) | 34 | ~68 | ✅ |
| `archive.php` / `home.php` (1 featured + 5 grid) | 34 + 2×14=62 | ~96 | ⚠️ scalable |
| `home.php` + newsletter (1 featured + 5 grid) | 71 | ~105 | ⚠️ scalable |

### Détail single.php (cas le plus riche)

```
article                                    1
  header                                   1
    p.entry-badges                         1
      a[cat-pill]                          1
    h1                                     1
    p.entry-meta                           1
      a[author]                            1
      span.entry-date / time               2
  figure.entry-hero-media (WP)             2 (figure + img)
  p.entry-intro                            1
  section.entry-content (the_content)      1 + blocs WP
  footer.entry-footer                      1
    ul.entry-tags                          1
      li + a × 3                           6
    nav[post-nav] (WP)                     ~4
[section#newsletter]                       1
  h2 + p + form                            3
  input[hidden] + label + input + button   4
```

### Note sur archive/home

Les templates d'archive dépassent 80 éléments dès `posts_per_page ≥ 5` — c'est inévitable pour une liste d'articles avec miniatures. L'objectif < 80 est atteint pour la **structure fixe** (shell + intro + 1 article featured = ~42 éléments). Le scaling est linéaire et maîtrisé : **~14 éléments par article supplémentaire**.

Recommandation : régler `posts_per_page = 6` en production (valeur WP par défaut) pour un total d'environ ~110 éléments — acceptable pour EcoIndex.

---

## Commandes utiles

```bash
php -l filename.php                       # Lint PHP
phpcs --standard=WordPress filename.php   # Standards WP Coding Standards
```
