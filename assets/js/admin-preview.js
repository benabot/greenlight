/**
 * Greenlight admin — prévisualisation live des couleurs.
 * Vanilla JS uniquement. Synchronise les color pickers avec l'iframe
 * de prévisualisation en injectant les CSS variables dans le document cible.
 *
 * @package Greenlight
 */
(function () {
  'use strict';

  var previewFrame = document.getElementById( 'greenlight-preview-frame' );
  if ( ! previewFrame ) { return; }

  // Mapping nom d'input → CSS variable cible dans l'iframe
  var colorMap = {
    'greenlight_appearance_options[color_primary]':            '--wp--preset--color--primary',
    'greenlight_appearance_options[color_surface]':            '--wp--preset--color--surface',
    'greenlight_appearance_options[color_text]':               '--wp--preset--color--text',
    'greenlight_appearance_options[color_background]':         '--wp--preset--color--background',
    'greenlight_appearance_options[color_tertiary]':           '--wp--preset--color--tertiary',
    'greenlight_appearance_options[color_border]':             '--wp--preset--color--border',
    'greenlight_appearance_options[color_on_surface_variant]': '--wp--preset--color--on-surface-variant',
    'greenlight_appearance_options[color_header_bg]':          '--greenlight-header-bg',
    'greenlight_appearance_options[color_footer_bg]':          '--greenlight-footer-bg',
  };

  var lastValues = {};

  function updatePreview() {
    var doc;
    try {
      doc = previewFrame.contentDocument || previewFrame.contentWindow.document;
    } catch ( e ) { return; }

    if ( ! doc || ! doc.documentElement ) { return; }

    Object.keys( colorMap ).forEach( function ( name ) {
      var input = document.querySelector( 'input[name="' + name + '"]' );
      if ( input && input.value ) {
        doc.documentElement.style.setProperty( colorMap[ name ], input.value );
      }
    } );
  }

  // Polling — wp_color_picker (jQuery/iris) ne déclenche pas d'events DOM standards.
  function checkForChanges() {
    var changed = false;
    document.querySelectorAll( '.greenlight-color-picker' ).forEach( function ( input ) {
      var val = input.value;
      if ( lastValues[ input.name ] !== val ) {
        lastValues[ input.name ] = val;
        changed = true;
      }
    } );
    if ( changed ) { updatePreview(); }
  }

  previewFrame.addEventListener( 'load', updatePreview );
  setInterval( checkForChanges, 600 );
}());
