# Changelog

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
