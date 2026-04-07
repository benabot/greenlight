# Changelog

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
