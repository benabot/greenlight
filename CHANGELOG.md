# Changelog

## 2026-04-20 (fix/ui-mobile-1)

### Fixed
- Header mobile recomposé autour d un masthead stable : branding et burger sur la ligne haute, disclosure mobile rendu sous le header au lieu de casser la composition dans le hero.
- Shell admin réaligné avec un onglet **Apparence** réel, servant de hub vers le Customizer et exposant un reset visuel limité aux seules options d apparence.

### Added
- Action admin `greenlight_reset_appearance` avec nonce, contrôle de capacité et confirmation explicite avant restauration du style d origine.
- Smoke tests `tests/appearance-reset-smoke.php` et `tests/mobile-header-masthead-smoke.php`.
- Audit ciblé `docs/audit-prod-ui-mobile-1.md`.

### Changed
- README et TODO mis à jour pour refléter le point d entrée Apparence réel et le statut encore non prod-ready sans validation front finale.

## 2026-04-10 (feat/eco3)

### Changed
- **Architecture header/hero refactorisée** : abandon de la fusion header+hero dans un seul `<header>`. La nav est maintenant un `<header>` identique et fixe (`position: fixed`) sur toutes les pages. Le hero est une `<section>` autonome entre `</header>` et `<main>`. Plus aucune logique front-page dans `header.php`, plus de `$GLOBALS`.
- Chaque template (`front-page.php`, `single.php`, `page.php`, `archive.php`, `home.php`, `index.php`, `404.php`, `search.php`) ouvre son propre `<main>` — `header.php` ne l'ouvre plus.

### Fixed
- Hero `100vh` qui ne faisait pas 100vh — Claude Code avait remplacé `min-height` par `max-height`. Corrigé : `min-height: 100vh` + `max-height: 100vh` (les deux).
- Header `position: fixed` + `inset-inline: 0` pour une nav fixe hors flux, le hero démarre à `top: 0` viewport.
- Hero `padding-block-start` inclut `var(--greenlight-header-height, 5rem)` pour repousser le contenu texte sous la nav fixe.
- Suppression `overflow: clip` sur `.site-header` qui pouvait bloquer les sous-menus.

### Added
- Opacité du header réglable via Customizer (section Navigation) — `header_opacity` (0–100%, pas de 5). Implémenté via pseudo-element `::after` pour ne pas affecter l'opacité du texte, combiné avec `backdrop-filter: blur(12px)` en glassmorphism.

## 2026-04-09 (feat/eco3)

### Fixed
- `.site-header-nav` : suppression du background opaque et du `position: sticky` — la nav est maintenant transparente et en flux normal quand elle est dans le header+hero fusionné.
- Overlay hero tronqué / décalé à droite : overlay déplacé du `.page-hero::before` vers le `<header>::before` parent — couvre désormais l'intégralité header+hero (nav incluse).
- `.page-hero::before` désactivé (`display:none`) quand il est enfant de `.site-header--with-hero` pour éviter les doublons d'overlay.

### Added
- **Overlay réglable** : 2 nouvelles options Customizer (section Hero) — Opacité overlay (0–100%, pas de 5) et Direction overlay (full / haut / bas / gauche / droite).
- **Overlay dynamique** : `--greenlight-overlay-opacity` custom property injectée en style inline sur `<header>` depuis `front-page.php` ; 5 classes CSS `site-header--overlay-dir-*` mappées sur des gradients directionnels.
- **Boutons CTA hero** : 2 boutons configurables (texte, URL, style primary/secondary/tertiary, position lead/body/center) dans la section Hero du Customizer. Les boutons ne s'affichent que si texte ET URL sont renseignés.

## 2026-04-08 (feat/eco3)

### Fixed
- Double bouton "Purger le cache HTML" dans l'onglet Performance de l'admin (`inc/admin.php`).
- Reliquat newsletter/Subscribe retiré ensuite du thème : plus de CTA public ni de réglage zombie associé côté front.
- Bouton Subscribe visible par défaut sans action utilisateur — default `show_header_cta` passé de `1` à `0` dans `inc/admin.php`.
- Nav couvrant le hero sur la home — suppression du `margin-block-start` négatif sur `.site-main` et du `padding-block-start` compensatoire sur `.page-hero` ; nav et hero en flux normal.
- Couleurs du Customizer nécessitant sauvegarde + rechargement pour s'appliquer — transport des color settings changé de `postMessage` à `refresh` dans `inc/customizer.php`.
- Variable CSS orpheline `--greenlight-header-height` retirée de `:root` dans `style.css`.

### Added
- Menu burger CSS-only pour la navigation mobile — `<input type="checkbox">` + `<label>` + SVG inline, zéro JS front.
- Option Customizer "Navigation mobile" (inline / burger) dans la section Navigation (`inc/customizer.php`).
- Clé `nav_style` ajoutée aux defaults et à la sanitize function (`inc/admin.php`).

### Notes
- Hero pleine largeur 100vw : déjà implémenté via `"align":"full"` dans `patterns/hero.php` — aucune action requise.
- Bug "texte aligné à droite sur la home" : aucun `text-align: right` trouvé dans les CSS/templates/theme.json — bug absent du code actuel.
- Hero pleine largeur CSS : `margin-inline: calc(50% - 50vw)` + `padding-inline: spacing--md` sur `.page-hero` — casse le double padding `<body>` + `<main>`.

## 2026-04-08

### Added
- Phase 10D (branche `feat/eco2`): responsive validé — 0 `@media` confirmé dans tous les CSS (style.css, blocks/*.css, critical.css). Tests Playwright 320px/768px/1920px : aucune cassure layout, flexbox+clamp opérationnel sur toute la plage.
- Phase 10C (branche `feat/eco2`): chaîne HTTP documentée — `inc/concat.php` bundle 9→1 requête CSS, critical CSS inline + defer pattern, cache HTML, minification PHP fallback. Aucune ressource externe vérifiée.
- Phase 10B (branche `feat/eco2`): audit DOM — `<div class="page-content">` conservé (CSS actif 65ch/flex-gap), audit comptages re-vérifié (inchangé vs Phase 6C).
- Phase 10A (branche `feat/eco2`): éco-optimisation CSS front — `style.css` 1 276 → 1 191 lignes (−85).
  - Styles `.greenlight-preview-*` extraits → `assets/css/admin-preview.css`, enqueué uniquement en `is_customize_preview()`
  - `data-greenlight-page-*` dans `front-page.php` conditionnés à `$_gl_preview_mode`
  - `.page-hero` / `.archive-intro` mutualisés via multi-sélecteurs (lead, h1, body)
  - `backdrop-filter: blur(16px)` → `@supports` + rayon 12px
  - Variables density vérifiées : injectées via `greenlight_output_appearance_variants()`, pas de zombies
  - Sélecteur `.site-header--nav-uppercase .site-nav a` redondant supprimé
  - `critical.css` : 3 sélecteurs périmés corrigés (`.skip-link`, `.site-nav ul`, `.hero-description`)

## 2026-04-07

### Added
- Phase 9A: presets éditoriaux (Minimal, Magazine, Studio, Journal), hero avancé (image/couleur/dégradé/hauteur), navigation sticky + sous-menus CSS-only, variantes archives/cartes/single/footer, réglages de densité visuelle par contexte (home, archives, single, pages).
- Phase 9B: durcissement sécurité — SVG allowlist (19 éléments sûrs, suppression `foreignObject`/`animate`/`iframe`/`style`), anonymisation IP logs 404, `is_uploaded_file()` + limites de taille sur import JSON/CSV, OPTIMIZE TABLE restreint au préfixe WP, source redirect forcée à commencer par `/`, `wp_delete_file()` sur purge cache.
- README.md: `Permissions-Policy` Apache + HSTS commenté (nginx + Apache).

### Fixed
- `inc/admin.php`: closure `$emit_perf_hidden_fields` manquante → fatal error sur l'onglet Performance corrigé.
- Phase 8 validation: PHPCS zéro erreur sur 30 fichiers PHP, smoke test SEO/Performance/Images OK, responsive 768px et 1280px validé.

### Changed
- Phase 8: shell admin premium finalisé, onglet Apparence simplifié en hub Customizer, rail latéral supprimé (layout single-column).

## 2026-03-28

### Added
- Unified Greenlight admin with SEO, Images, Performance, Apparence, SVG and Outils tabs.
- Native SEO fields, JSON-LD, sitemap, image helpers, page cache, and SVG sanitization.
- README installation, configuration, usage, and size metrics.

### Changed
- Front templates adjusted for the Organic Minimalism / Digital Lithograph direction.
- Theme documentation updated to match the current runtime and validation state.

### Fixed
- Admin heartbeat guard for `get_current_screen()`.
- Breadcrumb helper duplication.
- W3C issues on the front page, including the unnecessary `section` wrapper and WordPress auto-size CSS fix.

### Validation
- PHPCS cleaned on the main theme files.
- W3C HTML validated on home and archive without errors.
- Playwright checks completed for responsive behavior, JS-off front rendering, and accessibility proxy checks.
