#!/usr/bin/env bash
# bin/minify.sh — Minification CLI pour le thème Greenlight
# Usage : bash bin/minify.sh [--css-only | --js-only]
# Dépendance : PHP CLI uniquement (aucun npm, aucune lib externe)

set -euo pipefail

THEME_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

if ! command -v php &>/dev/null; then
    printf 'Erreur : php CLI introuvable dans PATH.\n' >&2
    exit 1
fi

# ---------------------------------------------------------------------------
# Helpers
# ---------------------------------------------------------------------------

minify_css() {
    local src="$1"
    local dst="${src%.css}.min.css"
    SRC="$src" DST="$dst" php -r '
$c = file_get_contents(getenv("SRC"));
$c = preg_replace("/\/\*[\s\S]*?\*\//", "", $c);
$c = preg_replace("/\s+/", " ", $c);
$c = preg_replace("/\s*([:;,{}()])\s*/", "$1", $c);
$c = preg_replace("/;}/", "}", $c);
file_put_contents(getenv("DST"), trim($c));
'
    printf '  ✓ %s\n' "$(basename "$dst")"
}

minify_js() {
    local src="$1"
    local dst="${src%.js}.min.js"
    SRC="$src" DST="$dst" php -r '
$c = file_get_contents(getenv("SRC"));
$c = preg_replace("/(?<!:)\/\/[^\n]*/", "", $c);
$c = preg_replace("/\/\*[\s\S]*?\*\//", "", $c);
$c = preg_replace("/\s+/", " ", $c);
file_put_contents(getenv("DST"), trim($c));
'
    printf '  ✓ %s\n' "$(basename "$dst")"
}

# ---------------------------------------------------------------------------
# Main
# ---------------------------------------------------------------------------

MODE="${1:-all}"

printf 'Greenlight — Minification\n\n'

if [[ "$MODE" != "--js-only" ]]; then
    printf 'CSS — style.css\n'
    minify_css "$THEME_DIR/style.css"

    printf 'CSS — blocks/\n'
    for f in "$THEME_DIR/assets/css/blocks/"*.css; do
        [[ "$f" == *.min.css ]] && continue
        minify_css "$f"
    done
fi

if [[ "$MODE" != "--css-only" ]]; then
    printf 'JS — seo-sidebar.js\n'
    minify_js "$THEME_DIR/assets/js/seo-sidebar.js"
fi

printf '\nTerminé.\n'
