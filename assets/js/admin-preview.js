/**
 * Greenlight admin — live appearance preview.
 *
 * Keeps the iframe preview in sync with unsaved appearance changes by
 * applying CSS variables, toggling layout classes, and updating a few
 * visible blocks in-place.
 *
 * @package Greenlight
 */
( function () {
  'use strict';

  var previewConfig = window.greenlightAdminPreview || {};
  var previewFrame = document.getElementById( 'greenlight-preview-frame' );
  var optionKey = previewConfig.optionKey || 'greenlight_appearance_options';

  if ( ! previewFrame ) {
    return;
  }

  var colorMap = {
    color_primary: '--wp--preset--color--primary',
    color_surface: '--wp--preset--color--surface',
    color_text: '--wp--preset--color--text',
    color_background: '--wp--preset--color--background',
    color_tertiary: '--wp--preset--color--tertiary',
    color_border: '--wp--preset--color--border',
    color_on_surface_variant: '--wp--preset--color--on-surface-variant',
    color_header_bg: '--greenlight-header-bg',
    color_header_text: '--greenlight-header-text',
    color_header_accent: '--greenlight-header-accent',
    color_footer_bg: '--greenlight-footer-bg'
  };

  var variantMaps = previewConfig.variants || {};
  var heroGradients = previewConfig.heroGradients || {};
  var lastSnapshot = '';
  var variantVarNames = [];

  Object.keys( variantMaps ).forEach( function ( mapKey ) {
    var map = variantMaps[ mapKey ] || {};

    Object.keys( map ).forEach( function ( variantKey ) {
      var vars = map[ variantKey ] || {};

      Object.keys( vars ).forEach( function ( cssVar ) {
        if ( variantVarNames.indexOf( cssVar ) === -1 ) {
          variantVarNames.push( cssVar );
        }
      } );
    } );
  } );

  function optionName( key ) {
    return optionKey + '[' + key + ']';
  }

  function getVisibleFields( key ) {
    return Array.prototype.slice.call(
      document.querySelectorAll( '[name="' + optionName( key ) + '"]' )
    ).filter( function ( field ) {
      return field.type !== 'hidden';
    } );
  }

  function getFieldValue( key ) {
    var fields = getVisibleFields( key );

    if ( ! fields.length ) {
      return '';
    }

    if ( fields[ 0 ].type === 'radio' ) {
      var checkedRadio = fields.find( function ( field ) {
        return field.checked;
      } );

      return checkedRadio ? checkedRadio.value : '';
    }

    if ( fields[ 0 ].type === 'checkbox' ) {
      return fields[ 0 ].checked ? '1' : '';
    }

    return fields[ 0 ].value || '';
  }

  function getFieldChecked( key ) {
    return getFieldValue( key ) === '1';
  }

  function getPreviewDocument() {
    try {
      return previewFrame.contentDocument || previewFrame.contentWindow.document;
    } catch ( error ) {
      return null;
    }
  }

  function setRootVar( root, cssVar, value ) {
    if ( value ) {
      root.style.setProperty( cssVar, value );
    } else {
      root.style.removeProperty( cssVar );
    }
  }

  function removeClassPrefix( node, prefix ) {
    if ( ! node ) {
      return;
    }

    Array.prototype.slice.call( node.classList ).forEach( function ( className ) {
      if ( className.indexOf( prefix ) === 0 ) {
        node.classList.remove( className );
      }
    } );
  }

  function setNodeHidden( node, hidden ) {
    if ( ! node ) {
      return;
    }

    if ( hidden ) {
      node.setAttribute( 'hidden', 'hidden' );
    } else {
      node.removeAttribute( 'hidden' );
    }
  }

  function syncTextNode( doc, container, selector, tagName, text, prepend ) {
    if ( ! container ) {
      return null;
    }

    var node = container.querySelector( selector );

    if ( ! text ) {
      if ( node ) {
        node.remove();
      }

      return null;
    }

    if ( ! node ) {
      node = doc.createElement( tagName );

      if ( selector.charAt( 0 ) === '.' ) {
        node.className = selector.slice( 1 );
      }

      if ( selector.charAt( 0 ) === '#' ) {
        node.id = selector.slice( 1 );
      }

      if ( prepend ) {
        container.insertBefore( node, container.firstChild );
      } else {
        container.appendChild( node );
      }
    }

    node.textContent = text;

    return node;
  }

  function buildCarbonBadge( doc, value ) {
    var badge = doc.createElement( 'span' );
    var abbr = doc.createElement( 'abbr' );

    badge.className = 'carbon-badge';
    badge.appendChild( doc.createTextNode( value + ' ' ) );

    abbr.setAttribute( 'title', 'dioxyde de carbone' );
    abbr.textContent = 'CO₂';
    badge.appendChild( abbr );
    badge.appendChild( doc.createTextNode( '/vue' ) );

    return badge;
  }

  function syncCarbonBadge( doc, container, wrapperSelector, enabled, value, prepend ) {
    if ( ! container ) {
      return;
    }

    var wrapper = container.querySelector( wrapperSelector );

    if ( ! enabled ) {
      if ( wrapper ) {
        wrapper.remove();
      }

      return;
    }

    if ( ! wrapper ) {
      wrapper = doc.createElement( 'span' );
      wrapper.className = wrapperSelector.replace( '.', '' );

      if ( prepend ) {
        container.insertBefore( wrapper, container.firstChild );
      } else {
        container.appendChild( wrapper );
      }
    }

    while ( wrapper.firstChild ) {
      wrapper.removeChild( wrapper.firstChild );
    }

    wrapper.appendChild( buildCarbonBadge( doc, value ) );
  }

  function getHeroHeading() {
    var mode = getFieldValue( 'hero_heading_mode' ) || 'page_title';
    var customText = getFieldValue( 'hero_heading_text' );

    if ( mode === 'none' ) {
      return '';
    }

    if ( mode === 'site_title' ) {
      return previewConfig.siteName || '';
    }

    if ( mode === 'custom' ) {
      return customText;
    }

    return previewConfig.siteName || '';
  }

  function getHeroDescription() {
    var mode = getFieldValue( 'hero_subheading_mode' ) || 'page_excerpt';
    var customText = getFieldValue( 'hero_subheading_text' );
    var legacyText = getFieldValue( 'hero_text' );

    if ( mode === 'none' ) {
      return legacyText;
    }

    if ( mode === 'site_tagline' ) {
      return previewConfig.siteTagline || legacyText || '';
    }

    if ( mode === 'custom' ) {
      return customText || legacyText || '';
    }

    return customText || legacyText || ( previewConfig.siteTagline || '' );
  }

  function applyVariantVariables( doc ) {
    var root = doc.documentElement;
    var merged = {};

    variantVarNames.forEach( function ( cssVar ) {
      root.style.removeProperty( cssVar );
    } );

    Object.keys( variantMaps ).forEach( function ( fieldKey ) {
      var selected = getFieldValue( fieldKey );
      var vars = variantMaps[ fieldKey ] && variantMaps[ fieldKey ][ selected ]
        ? variantMaps[ fieldKey ][ selected ]
        : {};

      Object.keys( vars ).forEach( function ( cssVar ) {
        merged[ cssVar ] = vars[ cssVar ];
      } );
    } );

    Object.keys( merged ).forEach( function ( cssVar ) {
      setRootVar( root, cssVar, merged[ cssVar ] );
    } );
  }

  function applyColors( doc ) {
    var root = doc.documentElement;

    Object.keys( colorMap ).forEach( function ( fieldKey ) {
      setRootVar( root, colorMap[ fieldKey ], getFieldValue( fieldKey ) );
    } );
  }

  function applyHeader( doc ) {
    var header = doc.querySelector( '.site-header' );
    var branding = doc.querySelector( '.site-branding' );
    var siteTagline = branding ? branding.querySelector( '.site-tagline' ) : null;
    var footerNav = doc.querySelector( '.footer-nav' );
    var footerEmission = doc.querySelector( '.footer-emission' );

    if ( ! header ) {
      return;
    }

    removeClassPrefix( header, 'site-header--layout-' );
    removeClassPrefix( header, 'site-header--nav-' );
    removeClassPrefix( header, 'site-header--submenu-' );

    header.classList.add( 'site-header--layout-' + ( getFieldValue( 'header_layout' ) || 'inline' ) );
    header.classList.add( 'site-header--nav-' + ( getFieldValue( 'nav_link_case' ) || 'normal' ) );
    header.classList.add( 'site-header--submenu-' + ( getFieldValue( 'submenu_style' ) || 'plain' ) );
    header.classList.toggle( 'site-header--sticky', getFieldChecked( 'header_sticky' ) );

    if ( branding && getFieldChecked( 'show_tagline' ) ) {
      if ( ! siteTagline ) {
        siteTagline = doc.createElement( 'p' );
        siteTagline.className = 'site-tagline';
        branding.appendChild( siteTagline );
      }

      siteTagline.textContent = previewConfig.siteTagline || '';
      setNodeHidden( siteTagline, false );
    } else if ( siteTagline ) {
      setNodeHidden( siteTagline, true );
    }

    setNodeHidden( footerNav, ! getFieldChecked( 'show_footer_nav' ) );
    setNodeHidden( footerEmission, ! getFieldChecked( 'show_low_emission' ) );
  }

  function applyHero( doc ) {
    var richHero = doc.querySelector( '.page-hero' );
    var simpleHero = doc.querySelector( '.page-intro-simple' );
    var useAdvancedHero = getFieldChecked( 'hero_enabled' );
    var showTopBadge = getFieldChecked( 'carbon_badge_enabled' ) && ( getFieldValue( 'carbon_badge_position' ) || 'top' ) === 'top';
    var showHeroBadge = showTopBadge && getFieldChecked( 'show_hero_badge' );
    var badgeValue = getFieldValue( 'carbon_badge_value' ) || '0.2g';
    var heading = getHeroHeading();
    var description = getHeroDescription();

    if ( richHero ) {
      removeClassPrefix( richHero, 'page-hero--' );
      richHero.classList.add( 'page-hero--' + ( getFieldValue( 'hero_style' ) || 'asymmetric' ) );
      richHero.classList.add( 'page-hero--background-' + ( getFieldValue( 'hero_background_mode' ) || 'none' ) );
      richHero.classList.add( 'page-hero--height-' + ( getFieldValue( 'hero_height_mode' ) || 'content' ) );
      richHero.classList.add( 'page-hero--overlay-' + ( getFieldValue( 'hero_overlay_strength' ) || 'soft' ) );

      richHero.style.removeProperty( '--greenlight-hero-background' );
      richHero.style.removeProperty( '--greenlight-hero-background-image' );

      if ( getFieldValue( 'hero_background_mode' ) === 'color' && getFieldValue( 'hero_background_color' ) ) {
        richHero.style.setProperty( '--greenlight-hero-background', getFieldValue( 'hero_background_color' ) );
      }

      if ( getFieldValue( 'hero_background_mode' ) === 'gradient' && heroGradients[ getFieldValue( 'hero_gradient_preset' ) ] ) {
        richHero.style.setProperty( '--greenlight-hero-background', heroGradients[ getFieldValue( 'hero_gradient_preset' ) ] );
      }

      if ( getFieldValue( 'hero_background_mode' ) === 'image' && getFieldValue( 'hero_background_image' ) ) {
        richHero.style.setProperty( '--greenlight-hero-background-image', 'url("' + getFieldValue( 'hero_background_image' ) + '")' );
      }

      syncTextNode( doc, richHero.querySelector( '.hero-lead' ), 'h1', 'h1', heading, false );
      syncTextNode( doc, richHero.querySelector( '.hero-body' ) || richHero, '.hero-description', 'p', description, false );
      syncCarbonBadge( doc, richHero.querySelector( '.hero-lead' ), '.greenlight-preview-top-badge', showHeroBadge, badgeValue, true );
      setNodeHidden( richHero, ! useAdvancedHero );
    }

    if ( simpleHero ) {
      syncTextNode( doc, simpleHero, 'h1', 'h1', heading, false );
      syncTextNode( doc, simpleHero, '.hero-description', 'p', description, false );
      syncCarbonBadge( doc, simpleHero, '.greenlight-preview-top-badge', showHeroBadge, badgeValue, true );
      setNodeHidden( simpleHero, useAdvancedHero );
    }
  }

  function applyArchivePreview( doc ) {
    var list = doc.querySelector( '.greenlight-preview-archive-list' );
    var archiveMode = getFieldValue( 'archive_layout' ) || 'asymmetric';
    var showThumbs = getFieldChecked( 'show_thumbnails_archive' );
    var showExcerpts = getFieldChecked( 'show_excerpts_archive' );

    if ( list ) {
      list.classList.toggle( 'post-list--grid', archiveMode !== 'list' );
    }

    Array.prototype.slice.call( doc.querySelectorAll( '.greenlight-preview-featured .entry-media, .greenlight-preview-archive-list .entry-media' ) ).forEach( function ( media ) {
      setNodeHidden( media, ! showThumbs );
    } );

    Array.prototype.slice.call( doc.querySelectorAll( '.greenlight-preview-featured .entry-summary, .greenlight-preview-archive-list .entry-summary' ) ).forEach( function ( summary ) {
      setNodeHidden( summary, ! showExcerpts );
    } );
  }

  function applySinglePreview( doc ) {
    var single = doc.querySelector( '.greenlight-preview-single' );
    var author = single ? single.querySelector( '.entry-author' ) : null;
    var date = single ? single.querySelector( '.entry-date' ) : null;
    var tags = single ? single.querySelector( '.entry-tags' ) : null;
    var newsletter = doc.querySelector( '.greenlight-preview-newsletter' );

    setNodeHidden( author, ! getFieldChecked( 'show_author' ) );
    setNodeHidden( date, ! getFieldChecked( 'show_date' ) );
    setNodeHidden( tags, ! getFieldChecked( 'show_tags' ) );
    setNodeHidden( newsletter, ! getFieldChecked( 'show_newsletter_single' ) );
  }

  function applyFooterBadge( doc ) {
    var footerCopy = doc.querySelector( '.footer-copy' );
    var showFooterBadge = getFieldChecked( 'carbon_badge_enabled' ) && ( getFieldValue( 'carbon_badge_position' ) || 'top' ) === 'footer';
    var badgeValue = getFieldValue( 'carbon_badge_value' ) || '0.2g';

    syncCarbonBadge( doc, footerCopy, '.footer-copy__badge', showFooterBadge, badgeValue, false );
  }

  function applyPreview() {
    var doc = getPreviewDocument();

    if ( ! doc || ! doc.documentElement ) {
      return;
    }

    applyVariantVariables( doc );
    applyColors( doc );
    applyHeader( doc );
    applyHero( doc );
    applyArchivePreview( doc );
    applySinglePreview( doc );
    applyFooterBadge( doc );
  }

  function snapshotFields() {
    return [
      'theme_preset',
      'density_scale',
      'archive_layout',
      'archive_card_style',
      'single_layout',
      'footer_layout',
      'color_primary',
      'color_surface',
      'color_text',
      'color_background',
      'color_tertiary',
      'color_border',
      'color_on_surface_variant',
      'color_header_bg',
      'color_header_text',
      'color_header_accent',
      'color_footer_bg',
      'header_layout',
      'header_sticky',
      'nav_link_case',
      'submenu_style',
      'show_tagline',
      'hero_enabled',
      'hero_style',
      'hero_background_mode',
      'hero_background_image',
      'hero_background_color',
      'hero_gradient_preset',
      'hero_heading_mode',
      'hero_heading_text',
      'hero_subheading_mode',
      'hero_subheading_text',
      'hero_height_mode',
      'hero_overlay_strength',
      'show_hero_badge',
      'hero_text',
      'carbon_badge_enabled',
      'carbon_badge_value',
      'carbon_badge_position',
      'show_thumbnails_archive',
      'show_excerpts_archive',
      'show_author',
      'show_date',
      'show_tags',
      'show_newsletter_single',
      'show_footer_nav',
      'show_low_emission'
    ].map( function ( key ) {
      return key + ':' + getFieldValue( key );
    } ).join( '|' );
  }

  function checkForChanges() {
    var nextSnapshot = snapshotFields();

    if ( nextSnapshot !== lastSnapshot ) {
      lastSnapshot = nextSnapshot;
      applyPreview();
    }
  }

  previewFrame.addEventListener( 'load', function () {
    lastSnapshot = snapshotFields();
    applyPreview();
  } );

  document.addEventListener( 'change', checkForChanges );
  document.addEventListener( 'input', checkForChanges );
  window.setInterval( checkForChanges, 500 );
}() );
