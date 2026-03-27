# Project Memory — wp-light

Decisions, context, and non-obvious state. Update as the project evolves.

## Theme Type

Currently a **classic PHP theme** (no FSE/block theme). The CLAUDE.md reflects this.
If the project migrates to a block theme, update both this file and CLAUDE.md line 7.

## Design Decisions

- No dark mode for now — palette is light-only, intentional
- No JavaScript at all currently — layout is 100% CSS
- Josh Comeau's CSS reset inlined in `<head>` (not via `wp_enqueue_style`)

## Environment

- Local dev: (document your local URL here, e.g. http://wp-light.local)
- WordPress version: (document here)
- PHP version: (document here)

## Active Constraints

- No SEO plugin installed — theme handles canonical + OG meta itself
- No page builder — templates are hand-written PHP

## Notes

(Add decisions, discoveries, and non-obvious context here as the project grows)
