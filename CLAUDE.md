# CLAUDE.md — Greenlight

> **Context** — `TODO.md` (tâches par phase)

## Project overview

**Greenlight** — thème WordPress hybride avancé, éco-conçu. Templates PHP + `theme.json` v3 pour intégration Gutenberg. Zéro jQuery, quasi-zéro JS, responsive via HTML/CSS uniquement. SEO intégré autonome avec champs éditables par page/post. Optimisation images intégrée. 100% customisable via Gutenberg + page de réglages.

## Direction esthétique

Le fichier `DESIGN.md` à la racine du projet définit la direction visuelle cible : "The Digital Lithograph" — Organic Minimalism, palette off-white/vert, typographie serrée, asymétrie volontaire, whitespace généreux, zéro bordure traditionnelle. S'en inspirer pour toute modification front (templates, CSS, patterns, theme.json). Les maquettes de référence sont dans `page.png`, `single.png`, `index.png`, `archives.png`.

## Architecture

```
greenlight/
├── style.css                  # Header WP + reset Josh Comeau + styles globaux minimaux
├── theme.json                 # v3 — design system complet, block settings
├── functions.php              # Supports, enqueue, hooks, deregister jQuery front
├── index.php                  # Fallback
├── front-page.php
├── single.php
├── page.php
├── home.php
├── archive.php
├── search.php
├── 404.php
├── header.php                 # Skip link, nav, wp_head — DOM minimal
├── footer.php                 # wp_footer — DOM minimal
├── comments.php
├── screenshot.png             # 1200×900
├── DESIGN.md                  # Direction esthétique — palette, typo, surfaces, composants
├── inc/
│   ├── seo.php                # Meta, OG, canonical, robots — hook wp_head
│   ├── seo-fields.php         # Meta box + sidebar Gutenberg (register_meta)
│   ├── seo-json-ld.php        # JSON-LD Schema.org — hook wp_footer
│   ├── seo-sitemap.php        # Sitemap XML natif
│   ├── seo-settings.php       # Page réglages admin SEO
│   ├── images.php             # WebP, tailles custom, lazy/eager
│   ├── images-settings.php    # Page réglages admin images
│   ├── admin.php              # Page admin top-level Greenlight (onglets)
│   ├── svg.php                # Upload SVG + sanitisation DOMDocument
│   ├── minify.php             # Fallback minification PHP à la volée
│   └── cache.php              # Page cache HTML + headers HTTP
├── patterns/
│   ├── hero.php
│   ├── cards.php
│   ├── contact.php
│   ├── header.php
│   └── footer.php
├── assets/
│   ├── css/
│   │   └── blocks/            # Styles par bloc (enqueue conditionnel)
│   └── js/
│       └── seo-sidebar.js     # Sidebar Gutenberg SEO (seul JS du thème)
├── bin/
│   └── minify.sh              # Script CLI minification (dev)
└── README.md
```

## Contraintes absolues

1. **Zéro jQuery** — `wp_deregister_script('jquery')` côté front. Jamais en dépendance.
2. **Zéro dépendance externe** — pas de CDN, pas de polyfill, pas de librairie.
3. **Quasi-zéro JavaScript** — seul JS autorisé : `seo-sidebar.js` pour le panel SEO Gutenberg (utilise `@wordpress/plugins` et `@wordpress/edit-post` déjà chargés par l'éditeur). Aucun JS côté front.
4. **DOM le plus léger possible** — HTML sémantique pur. Pas de `<div>` wrapper inutile. `<header>`, `<main>`, `<article>`, `<section>`, `<nav>`, `<footer>` uniquement. Pas de classes BEM à rallonge.
5. **Le moins de CSS possible** — design system dans `theme.json`, WP génère les custom properties. `style.css` = reset + strict nécessaire. Styles de blocs conditionnels via `wp_enqueue_block_style()`.
6. **Responsive sans breakpoints** — flexbox + `clamp()` + dimensionnement intrinsèque. Zéro `@media` pour le layout.
7. **100% customisable via Gutenberg** — couleurs, typo, spacing via Styles globaux. Patterns modifiables. Page de réglages Apparence > Greenlight. Menus via Site Editor.
8. **SEO autonome** — champs éditables par page/post. Aucun plugin nécessaire.
9. **i18n** — text domain `greenlight`, toute chaîne dans `__()` ou `_e()`.
10. **Sécurité** — `esc_*()` sur toute sortie, `sanitize_*()` sur toute entrée, nonce sur formulaires.

## Design system (theme.json v3)

Tout passe par les tokens. Jamais de valeur en dur.

- **Typo** : system-ui stack, 5 paliers fluid (small → x-large via clamp)
- **Spacing** : 5 paliers fluid (xs → xl via clamp)
- **Layout** : contentSize min(90vw, 1200px), wideSize min(95vw, 1400px)
- **Couleurs** : text, background, surface, surface-alt, primary, border
- **Custom** : désactivé (custom colors false, custom font-size false)

## Champs SEO par page/post

Deux interfaces, même jeu de `post_meta` :

| Champ | Meta key | Type |
|-------|----------|------|
| Titre SEO | `_greenlight_seo_title` | string |
| Meta description | `_greenlight_seo_description` | textarea |
| Image OG | `_greenlight_seo_image` | attachment_id |
| Noindex | `_greenlight_seo_noindex` | boolean |

- **Meta box PHP** (`inc/seo-fields.php`) : `add_meta_box()` + `save_post` hook + nonce
- **Sidebar Gutenberg** (`assets/js/seo-sidebar.js`) : `registerPlugin()` + `PluginDocumentSettingPanel` + `useEntityProp()`. Enqueue via `enqueue_block_editor_assets` uniquement.

## Images

Tailles custom : `greenlight-hero` (1200×675), `greenlight-card` (600×450), `greenlight-thumb` (300×300).
Tailles WP supprimées : `medium_large`, `1536x1536`, `2048x2048`.
Conversion WebP à l'upload (GD/Imagick). Hero = fetchpriority high + eager + preload. Reste = lazy + async.

## Accessibility (WCAG 2.1 AA)

Skip link, `<main id="main-content">`, `<nav aria-label>`, `aria-labelledby` sur sections, `.sr-only`, focus visible 2px solid, pas de `tabindex` positif.

## Layout — flexbox only

```css
.cards { display: flex; flex-wrap: wrap; gap: var(--wp--preset--spacing--md); }
.cards > * { flex: 1 1 clamp(250px, calc(33.333% - var(--wp--preset--spacing--md)), 400px); }
```

Pas de Grid, pas de float, pas de position absolute pour le layout.

## Performance

Hero preload, lazy partout ailleurs, jQuery deregistered, zéro JS front, zéro font externe, styles blocs conditionnels, zéro requête HTTP non nécessaire.

## Commandes (Claude Code)

```bash
php -l filename.php                      # Lint PHP
phpcs --standard=WordPress filename.php  # Standards WP
```
