( function( api ) {
	'use strict';

	if ( ! api ) {
		return;
	}

	var config = window.greenlightCustomizerPreview || {};
	var root = document.documentElement;
	var state = {};

	var selectors = {
		header: '.site-header',
		tagline: '.site-tagline',
		subscribe: '.cta-subscribe',
		heroAdvanced: '.page-hero',
		heroSimple: '.page-intro-simple',
		footer: '.site-footer',
	};

	function getElement( selector ) {
		return document.querySelector( selector );
	}

	function getElements( selector ) {
		return Array.prototype.slice.call( document.querySelectorAll( selector ) );
	}

	function setRootVar( name, value ) {
		if ( 'string' !== typeof value || '' === value ) {
			root.style.removeProperty( name );
			return;
		}

		root.style.setProperty( name, value );
	}

	function applyVars( vars ) {
		Object.keys( vars || {} ).forEach( function( name ) {
			setRootVar( name, vars[ name ] );
		} );
	}

	function collectVariantVars() {
		var presetKey = state.theme_preset || 'editorial';
		var densityKey = state.density_scale || 'balanced';
		var archiveKey = state.archive_card_style || 'balanced';
		var singleKey = state.single_layout || 'editorial';
		var footerKey = state.footer_layout || 'split';
		var vars = {};

		if ( config.presets && config.presets[ presetKey ] && config.presets[ presetKey ].vars ) {
			Object.assign( vars, config.presets[ presetKey ].vars );
		}

		if ( config.densities && config.densities[ densityKey ] && config.densities[ densityKey ].vars ) {
			Object.assign( vars, config.densities[ densityKey ].vars );
		}

		if ( config.archiveCardStyles && config.archiveCardStyles[ archiveKey ] && config.archiveCardStyles[ archiveKey ].vars ) {
			Object.assign( vars, config.archiveCardStyles[ archiveKey ].vars );
		}

		if ( config.singleLayouts && config.singleLayouts[ singleKey ] && config.singleLayouts[ singleKey ].vars ) {
			Object.assign( vars, config.singleLayouts[ singleKey ].vars );
		}

		if ( config.footerLayouts && config.footerLayouts[ footerKey ] && config.footerLayouts[ footerKey ].vars ) {
			Object.assign( vars, config.footerLayouts[ footerKey ].vars );
		}

		return vars;
	}

	function applyVariantVars() {
		applyVars( collectVariantVars() );
	}

	function applyColorVars() {
		setRootVar( '--wp--preset--color--primary', state.color_primary || '' );
		setRootVar( '--wp--preset--color--surface', state.color_surface || '' );
		setRootVar( '--wp--preset--color--text', state.color_text || '' );
		setRootVar( '--wp--preset--color--background', state.color_background || '' );
		setRootVar( '--wp--preset--color--tertiary', state.color_tertiary || '' );
		setRootVar( '--wp--preset--color--border', state.color_border || '' );
		setRootVar( '--wp--preset--color--on-surface-variant', state.color_on_surface_variant || '' );
		setRootVar( '--greenlight-header-bg', state.color_header_bg || '' );
		setRootVar( '--greenlight-header-text', state.color_header_text || '' );
		setRootVar( '--greenlight-header-accent', state.color_header_accent || '' );
		setRootVar( '--greenlight-footer-bg', state.color_footer_bg || '' );
	}

	function applyHeaderState() {
		var header = getElement( selectors.header );
		var tagline = getElement( selectors.tagline );
		var subscribe = getElement( selectors.subscribe );

		if ( header ) {
			header.classList.toggle( 'site-header--layout-inline', 'split' !== state.header_layout && 'stacked' !== state.header_layout );
			header.classList.toggle( 'site-header--layout-split', 'split' === state.header_layout );
			header.classList.toggle( 'site-header--layout-stacked', 'stacked' === state.header_layout );
			header.classList.toggle( 'site-header--sticky', !! state.header_sticky );
			header.classList.toggle( 'site-header--nav-uppercase', 'uppercase' === state.nav_link_case );
			header.classList.toggle( 'site-header--nav-normal', 'uppercase' !== state.nav_link_case );
			header.classList.toggle( 'site-header--submenu-surface', 'surface' === state.submenu_style );
			header.classList.toggle( 'site-header--submenu-plain', 'surface' !== state.submenu_style );
		}

		if ( tagline ) {
			tagline.hidden = ! state.show_tagline;
		}

		if ( subscribe ) {
			subscribe.hidden = ! state.show_header_cta || ! state.newsletter_enabled;
			subscribe.setAttribute( 'aria-hidden', subscribe.hidden ? 'true' : 'false' );
		}
	}

	function getHeroSourceText( hero ) {
		if ( ! hero || ! hero.dataset ) {
			return {
				title: '',
				excerpt: '',
			};
		}

		return {
			title: hero.dataset.greenlightPageTitle || '',
			excerpt: hero.dataset.greenlightPageExcerpt || '',
		};
	}

	function applyHeroSection( hero, enabled ) {
		var source = getHeroSourceText( hero );
		var heading = hero.querySelector( 'h1' );
		var description = hero.querySelector( '.hero-description' );
		var titleMode = state.hero_heading_mode || 'page_title';
		var subtitleMode = state.hero_subheading_mode || 'page_excerpt';
		var titleText = '';
		var subtitleText = '';
		var gradientValue = '';

		hero.hidden = ! enabled;

		if ( enabled && hero.classList.contains( 'page-hero' ) ) {
			hero.classList.toggle( 'page-hero--centered', 'centered' === state.hero_style );
			hero.classList.toggle( 'page-hero--asymmetric', 'centered' !== state.hero_style );
			hero.classList.toggle( 'page-hero--background-none', 'none' === state.hero_background_mode );
			hero.classList.toggle( 'page-hero--background-color', 'color' === state.hero_background_mode );
			hero.classList.toggle( 'page-hero--background-gradient', 'gradient' === state.hero_background_mode );
			hero.classList.toggle( 'page-hero--background-image', 'image' === state.hero_background_mode );
			hero.classList.toggle( 'page-hero--height-content', 'content' === state.hero_height_mode );
			hero.classList.toggle( 'page-hero--height-tall', 'tall' === state.hero_height_mode );
			hero.classList.toggle( 'page-hero--height-full', 'full' === state.hero_height_mode );
			hero.classList.toggle( 'page-hero--overlay-none', 'none' === state.hero_overlay_strength );
			hero.classList.toggle( 'page-hero--overlay-soft', 'soft' === state.hero_overlay_strength );
			hero.classList.toggle( 'page-hero--overlay-strong', 'strong' === state.hero_overlay_strength );

			if ( 'color' === state.hero_background_mode && state.hero_background_color ) {
				hero.style.setProperty( '--greenlight-hero-background', state.hero_background_color );
				hero.style.removeProperty( '--greenlight-hero-background-image' );
			} else if ( 'gradient' === state.hero_background_mode ) {
				gradientValue = config.gradients && config.gradients[ state.hero_gradient_preset || 'moss' ] ? config.gradients[ state.hero_gradient_preset || 'moss' ] : '';
				if ( gradientValue ) {
					hero.style.setProperty( '--greenlight-hero-background', gradientValue );
					hero.style.removeProperty( '--greenlight-hero-background-image' );
				}
			} else if ( 'image' === state.hero_background_mode && state.hero_background_image ) {
				hero.style.setProperty( '--greenlight-hero-background-image', 'url("' + state.hero_background_image.replace( /"/g, '\\"' ) + '")' );
				hero.style.removeProperty( '--greenlight-hero-background' );
			} else {
				hero.style.removeProperty( '--greenlight-hero-background' );
				hero.style.removeProperty( '--greenlight-hero-background-image' );
			}
		}

		if ( 'site_title' === titleMode ) {
			titleText = config.siteTitle || '';
		} else if ( 'custom' === titleMode ) {
			titleText = state.hero_heading_text || '';
		} else if ( 'none' !== titleMode ) {
			titleText = source.title || config.siteTitle || '';
		}

		if ( 'site_tagline' === subtitleMode ) {
			subtitleText = config.siteTagline || '';
		} else if ( 'custom' === subtitleMode ) {
			subtitleText = state.hero_subheading_text || '';
		} else if ( 'none' !== subtitleMode ) {
			subtitleText = source.excerpt || config.siteTagline || '';
		}

		if ( '' === subtitleText && state.hero_text ) {
			subtitleText = state.hero_text;
		}

		if ( heading ) {
			heading.textContent = titleText;
		}

		if ( description ) {
			description.textContent = subtitleText;
		}

		getElements( '.page-hero .carbon-badge, .page-intro-simple .carbon-badge' ).forEach( function( badge ) {
			badge.hidden = ! state.carbon_badge_enabled || ! state.show_hero_badge || 'top' !== ( state.carbon_badge_position || 'top' );
		} );

		getElements( '.site-footer .carbon-badge' ).forEach( function( badge ) {
			badge.hidden = ! state.carbon_badge_enabled || 'footer' !== ( state.carbon_badge_position || 'top' );
		} );
	}

	function applyHeroState() {
		var advancedHero = getElement( selectors.heroAdvanced );
		var simpleIntro = getElement( selectors.heroSimple );
		var useAdvanced = !! state.hero_enabled;

		if ( advancedHero ) {
			applyHeroSection( advancedHero, useAdvanced );
		}

		if ( simpleIntro ) {
			applyHeroSection( simpleIntro, ! useAdvanced );
		}
	}

	function applyFooterState() {
		var footer = getElement( selectors.footer );
		var copy = footer ? footer.querySelector( '.footer-copy' ) : null;
		var footerNav = footer ? footer.querySelector( '.footer-nav' ) : null;
		var lowEmission = footer ? footer.querySelector( '.footer-emission' ) : null;
		var badge = footer ? footer.querySelector( '.footer-copy__badge' ) : null;

		if ( footer ) {
			footer.classList.toggle( 'site-footer--split', 'split' === state.footer_layout );
			footer.classList.toggle( 'site-footer--stacked', 'stacked' === state.footer_layout );
			footer.classList.toggle( 'site-footer--compact', 'compact' === state.footer_layout );
		}

		if ( copy && 'string' === typeof state.custom_copyright ) {
			var copyBadge = copy.querySelector( '.footer-copy__badge' );

			if ( '' !== state.custom_copyright ) {
				copy.textContent = state.custom_copyright;
				if ( copyBadge ) {
					copy.appendChild( copyBadge );
				}
			} else {
				copy.innerHTML = '';
				copy.appendChild( document.createTextNode( '© ' + new Date().getFullYear() + ' ' ) );

				var copyStrong = document.createElement( 'strong' );
				copyStrong.textContent = ( config.siteTitle || '' ).toUpperCase() + '.';
				copy.appendChild( copyStrong );
				copy.appendChild( document.createTextNode( ' DESIGNED FOR PERMANENCE.' ) );

				if ( copyBadge ) {
					copy.appendChild( copyBadge );
				}
			}
		}

		if ( footerNav ) {
			footerNav.hidden = ! state.show_footer_nav;
		}

		if ( lowEmission ) {
			lowEmission.hidden = ! state.show_low_emission;
		}

		if ( badge ) {
			badge.hidden = ! state.carbon_badge_enabled || 'footer' !== ( state.carbon_badge_position || 'top' );
		}
	}

	function render() {
		applyVariantVars();
		applyColorVars();
		applyHeaderState();
		applyHeroState();
		applyFooterState();
	}

	function bindSetting( settingId, stateKey ) {
		api( settingId, function( setting ) {
			state[ stateKey ] = setting.get();
			setting.bind( function( value ) {
				state[ stateKey ] = value;
				render();
			} );
		} );
	}

	[
		'theme_preset',
		'density_scale',
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
		'show_header_cta',
		'newsletter_enabled',
		'carbon_badge_enabled',
		'carbon_badge_position',
		'carbon_badge_value',
		'custom_copyright',
		'show_low_emission',
		'show_footer_nav',
		'hero_enabled',
		'hero_style',
		'hero_background_mode',
		'hero_background_color',
		'hero_gradient_preset',
		'hero_background_image',
		'hero_heading_mode',
		'hero_heading_text',
		'hero_subheading_mode',
		'hero_subheading_text',
		'hero_text',
		'hero_height_mode',
		'hero_overlay_strength',
		'show_hero_badge'
	].forEach( function( stateKey ) {
		bindSetting( 'greenlight_appearance_options[' + stateKey + ']', stateKey );
	} );

	render();
}( window.wp && window.wp.customize ) );
