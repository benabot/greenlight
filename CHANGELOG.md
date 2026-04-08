# Changelog

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
