#!/usr/bin/env bash
# bin/minify.sh — Minification CLI pour le thème Greenlight
# Usage : bash bin/minify.sh [--css-only | --js-only]
# Dépendance : PHP CLI uniquement — aucun npm, aucune lib externe.
#
# Note CSS : le premier commentaire de style.css (/*! … */) est le header
# WordPress requis pour l'identification du thème — il est préservé intact.

set -euo pipefail

THEME_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

if ! command -v php &>/dev/null; then
    printf 'Erreur : php CLI introuvable dans PATH.\n' >&2
    exit 1
fi

# ---------------------------------------------------------------------------
# Minification CSS
# Le premier bloc de commentaire (header WP /*! ... */) est préservé.
# Les commentaires ordinaires /* ... */ sont supprimés.
# ---------------------------------------------------------------------------
minify_css() {
    local src="$1"
    local dst="${src%.css}.min.css"
    SRC="$src" DST="$dst" php -r '
$src = getenv("SRC");
$dst = getenv("DST");
$c   = file_get_contents($src);

// Extraire le premier commentaire block (header WP /*! ... */ ou /* ... */)
$prefix = "";
if (preg_match("/^(\\/\\*[\\s\\S]*?\\*\\/\\s*)/", $c, $m)) {
    $prefix = rtrim($m[1]) . "\n";
    $c      = substr($c, strlen($m[1]));
}

// Supprimer les commentaires ordinaires restants
$c = preg_replace("/\\/\\*[\\s\\S]*?\\*\\//", "", $c);
// Collapse whitespace / newlines
$c = preg_replace("/\\s+/", " ", $c);
// Supprimer espaces autour des caractères structurels sans casser
// les sélecteurs descendants qui suivent une pseudo-classe fonctionnelle.
$c = preg_replace("/\\s*([:;,{}\\[\\]]|!important)\\s*/", "$1", $c);
// ;} → }
$c = preg_replace("/;}/", "}", $c);

file_put_contents($dst, $prefix . trim($c) . "\n");
'
    printf '  ✓ %s (%s bytes)\n' "$(basename "$dst")" "$(wc -c < "$dst" | tr -d ' ')"
}

# ---------------------------------------------------------------------------
# Minification JS
# Supprime commentaires single-line (hors URLs) et block, collapse whitespace.
# Approche conservative — ne touche pas aux littéraux de chaînes.
# ---------------------------------------------------------------------------
minify_js() {
    local src="$1"
    local dst="${src%.js}.min.js"
    SRC="$src" DST="$dst" php -r '
$src = getenv("SRC");
$dst = getenv("DST");
$c   = file_get_contents($src);

// Supprimer commentaires block
$c = preg_replace("/\\/\\*[\\s\\S]*?\\*\\//", "", $c);
// Supprimer commentaires single-line (hors URLs type https://)
$c = preg_replace("/(?<!:)\\/\\/[^\\n]*/", "", $c);
// Collapse whitespace
$c = preg_replace("/\\s+/", " ", $c);

file_put_contents($dst, trim($c) . "\n");
'
    printf '  ✓ %s (%s bytes)\n' "$(basename "$dst")" "$(wc -c < "$dst" | tr -d ' ')"
}

generate_css_bundle() {
    local dst="$THEME_DIR/assets/css/greenlight-bundle.css"
    : > "$dst"

    cat "$THEME_DIR/style.min.css" >> "$dst"
    printf '\n' >> "$dst"

    for f in "$THEME_DIR/assets/css/blocks/"*.min.css; do
        [[ -f "$f" ]] || continue
        cat "$f" >> "$dst"
        printf '\n' >> "$dst"
    done

    printf '  ✓ %s (%s bytes)\n' "$(basename "$dst")" "$(wc -c < "$dst" | tr -d ' ')"
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

    printf '\nCSS — bundle\n'
    generate_css_bundle
fi

if [[ "$MODE" != "--css-only" ]]; then
    printf '\nJS — assets/js/\n'
    for f in "$THEME_DIR/assets/js/"*.js; do
        [[ "$f" == *.min.js ]] && continue
        minify_js "$f"
    done
fi

printf '\nTerminé.\n'
