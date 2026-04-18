<?php
/**
 * Greenlight Customizer registrations.
 *
 * @package Greenlight
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns a Customizer setting ID for an appearance option field.
 *
 * @param string $field Option field key.
 * @return string
 */
function greenlight_customize_appearance_setting_id( $field ) {
	return GREENLIGHT_APPEARANCE_OPTION_KEY . '[' . sanitize_key( (string) $field ) . ']';
}

/**
 * Builds a Customizer URL for the Greenlight appearance panels.
 *
 * @param string $section Optional section ID to autofocus.
 * @return string
 */
function greenlight_get_customizer_url( $section = '' ) {
	$url = add_query_arg(
		array(
			'theme'  => get_stylesheet(),
			'return' => admin_url( 'admin.php?page=greenlight&tab=appearance' ),
		),
		admin_url( 'customize.php' )
	);

	if ( '' !== $section ) {
		$url = add_query_arg( 'autofocus[section]', sanitize_key( (string) $section ), $url );
	}

	return $url;
}

/**
 * Registers Greenlight appearance controls in the Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager.
 * @return void
 */
function greenlight_customize_register( $wp_customize ) {
	if ( ! $wp_customize instanceof WP_Customize_Manager ) {
		return;
	}

	$defaults            = greenlight_get_appearance_defaults();
	$presets             = greenlight_get_appearance_presets();
	$densities           = greenlight_get_appearance_densities();
	$density_contexts    = greenlight_get_appearance_density_contexts();
	$archive_card_styles = greenlight_get_archive_card_styles();
	$single_layouts      = greenlight_get_single_layout_variants();
	$footer_layouts      = greenlight_get_footer_layout_variants();
	$positions           = greenlight_get_carbon_badge_positions();
	$gradients           = greenlight_get_hero_gradient_presets();

	$theme_preset_choices = array();
	foreach ( $presets as $preset_key => $preset_data ) {
		$theme_preset_choices[ $preset_key ] = isset( $preset_data['label'] ) ? $preset_data['label'] : $preset_key;
	}

	$density_choices = array();
	foreach ( $densities as $density_key => $density_data ) {
		$density_choices[ $density_key ] = isset( $density_data['label'] ) ? $density_data['label'] : $density_key;
	}

	$density_choices_with_inherit = greenlight_get_density_choices( true );

	$archive_card_choices = array();
	foreach ( $archive_card_styles as $archive_key => $archive_data ) {
		$archive_card_choices[ $archive_key ] = isset( $archive_data['label'] ) ? $archive_data['label'] : $archive_key;
	}

	$single_layout_choices = array();
	foreach ( $single_layouts as $single_key => $single_data ) {
		$single_layout_choices[ $single_key ] = isset( $single_data['label'] ) ? $single_data['label'] : $single_key;
	}

	$hero_gradient_choices = array();
	foreach ( $gradients as $gradient_key => $gradient_data ) {
		$hero_gradient_choices[ $gradient_key ] = isset( $gradient_data['label'] ) ? $gradient_data['label'] : $gradient_key;
	}

	$carbon_badge_position_choices = array();
	foreach ( $positions as $position_key => $position_data ) {
		$carbon_badge_position_choices[ $position_key ] = isset( $position_data['label'] ) ? $position_data['label'] : $position_key;
	}

	$hero_background_choices = array(
		'none'     => __( 'Aucun', 'greenlight' ),
		'color'    => __( 'Couleur', 'greenlight' ),
		'gradient' => __( 'Dégradé', 'greenlight' ),
		'image'    => __( 'Image', 'greenlight' ),
	);

	$hero_style_choices = array(
		'asymmetric' => __( 'Asymétrique', 'greenlight' ),
		'centered'   => __( 'Centré', 'greenlight' ),
	);

	$hero_heading_choices = array(
		'page_title' => __( 'Titre de page', 'greenlight' ),
		'site_title' => __( 'Titre du site', 'greenlight' ),
		'custom'     => __( 'Personnalisé', 'greenlight' ),
		'none'       => __( 'Aucun', 'greenlight' ),
	);

	$hero_subheading_choices = array(
		'page_excerpt' => __( 'Extrait de page', 'greenlight' ),
		'site_tagline' => __( 'Slogan du site', 'greenlight' ),
		'custom'       => __( 'Personnalisé', 'greenlight' ),
		'none'         => __( 'Aucun', 'greenlight' ),
	);

	$hero_height_choices = array(
		'content' => __( 'Contenu', 'greenlight' ),
		'tall'    => __( '70vh', 'greenlight' ),
		'full'    => __( '100vh', 'greenlight' ),
	);

	$submenu_choices = array(
		'plain'   => __( 'Discrets', 'greenlight' ),
		'surface' => __( 'En surface', 'greenlight' ),
	);

	$header_layout_choices = array(
		'inline'  => __( 'Ligne simple', 'greenlight' ),
		'split'   => __( 'Séparé', 'greenlight' ),
		'stacked' => __( 'Empilé', 'greenlight' ),
	);

	$nav_case_choices = array(
		'normal'    => __( 'Normale', 'greenlight' ),
		'uppercase' => __( 'Majuscules', 'greenlight' ),
	);

	$archive_layout_choices = array(
		'asymmetric' => __( 'Grille asymétrique', 'greenlight' ),
		'list'       => __( 'Liste simple', 'greenlight' ),
	);

	$footer_layout_choices = array();
	foreach ( $footer_layouts as $layout_key => $layout_data ) {
		$footer_layout_choices[ $layout_key ] = isset( $layout_data['label'] ) ? $layout_data['label'] : $layout_key;
	}

	$appearance_sections = array(
		'greenlight_appearance_foundations' => array(
			'title'       => __( 'Greenlight · Fondations', 'greenlight' ),
			'description' => __( 'Preset, rythme et couleurs globales.', 'greenlight' ),
			'priority'    => 30,
		),
		'greenlight_appearance_navigation'  => array(
			'title'       => __( 'Greenlight · Navigation', 'greenlight' ),
			'description' => __( 'Header, tagline et sous-menus.', 'greenlight' ),
			'priority'    => 31,
		),
		'greenlight_appearance_hero'        => array(
			'title'       => __( 'Greenlight · Hero', 'greenlight' ),
			'description' => __( 'Entrée simple ou hero avancé.', 'greenlight' ),
			'priority'    => 32,
		),
		'greenlight_appearance_content'     => array(
			'title'       => __( 'Greenlight · Contenu', 'greenlight' ),
			'description' => __( 'Articles, archives et lecture.', 'greenlight' ),
			'priority'    => 33,
		),
		'greenlight_appearance_footer'      => array(
			'title'       => __( 'Greenlight · Footer', 'greenlight' ),
			'description' => __( 'Pied de page, badge et mentions.', 'greenlight' ),
			'priority'    => 34,
		),
	);

	foreach ( $appearance_sections as $section_id => $section_args ) {
		$wp_customize->add_section( $section_id, $section_args );
	}

	$sanitize_choice = static function ( array $choices, $fallback ) {
		return static function ( $value ) use ( $choices, $fallback ) {
			$key = sanitize_key( (string) $value );

			return isset( $choices[ $key ] ) ? $key : $fallback;
		};
	};

	$sanitize_bool = static function ( $value ) {
		return ! empty( $value ) ? 1 : 0;
	};

	$sanitize_text = static function ( $value ) {
		return sanitize_text_field( (string) $value );
	};

	$sanitize_textarea = static function ( $value ) {
		return sanitize_textarea_field( (string) $value );
	};

	$sanitize_url = static function ( $value ) {
		return esc_url_raw( (string) $value );
	};

	$sanitize_color = static function ( $value ) {
		$color = sanitize_hex_color( (string) $value );

		return $color ? $color : '';
	};

	$add_setting = static function ( $field, $default_value, $sanitize_callback, $transport = 'postMessage' ) use ( $wp_customize ) {
		$wp_customize->add_setting(
			greenlight_customize_appearance_setting_id( $field ),
			array(
				'default'           => $default_value,
				'type'              => 'option',
				'capability'        => 'edit_theme_options',
				'transport'         => $transport,
				'sanitize_callback' => $sanitize_callback,
			)
		);
	};

	$add_select = static function ( $section, $field, $label, array $choices, $default_value, $description = '', $priority = 10, $transport = 'postMessage' ) use ( $wp_customize, $add_setting, $sanitize_choice ) {
		$add_setting( $field, $default_value, $sanitize_choice( $choices, $default_value ), $transport );

		$args = array(
			'label'    => $label,
			'section'  => $section,
			'settings' => greenlight_customize_appearance_setting_id( $field ),
			'type'     => 'select',
			'choices'  => $choices,
			'priority' => $priority,
		);

		if ( '' !== $description ) {
			$args['description'] = $description;
		}

		$wp_customize->add_control( greenlight_customize_appearance_setting_id( $field ), $args );
	};

	$add_checkbox = static function ( $section, $field, $label, $default_value = 0, $description = '', $priority = 10, $transport = 'postMessage' ) use ( $wp_customize, $add_setting, $sanitize_bool ) {
		$add_setting( $field, $default_value, $sanitize_bool, $transport );

		$args = array(
			'label'    => $label,
			'section'  => $section,
			'settings' => greenlight_customize_appearance_setting_id( $field ),
			'type'     => 'checkbox',
			'priority' => $priority,
		);

		if ( '' !== $description ) {
			$args['description'] = $description;
		}

		$wp_customize->add_control( greenlight_customize_appearance_setting_id( $field ), $args );
	};

	$add_text = static function ( $section, $field, $label, $default_value = '', $description = '', $priority = 10, $transport = 'postMessage' ) use ( $wp_customize, $add_setting, $sanitize_text ) {
		$add_setting( $field, $default_value, $sanitize_text, $transport );

		$args = array(
			'label'    => $label,
			'section'  => $section,
			'settings' => greenlight_customize_appearance_setting_id( $field ),
			'type'     => 'text',
			'priority' => $priority,
		);

		if ( '' !== $description ) {
			$args['description'] = $description;
		}

		$wp_customize->add_control( greenlight_customize_appearance_setting_id( $field ), $args );
	};

	$add_textarea = static function ( $section, $field, $label, $default_value = '', $description = '', $priority = 10, $transport = 'postMessage' ) use ( $wp_customize, $add_setting, $sanitize_textarea ) {
		$add_setting( $field, $default_value, $sanitize_textarea, $transport );

		$args = array(
			'label'    => $label,
			'section'  => $section,
			'settings' => greenlight_customize_appearance_setting_id( $field ),
			'type'     => 'textarea',
			'priority' => $priority,
		);

		if ( '' !== $description ) {
			$args['description'] = $description;
		}

		$wp_customize->add_control( greenlight_customize_appearance_setting_id( $field ), $args );
	};

	$add_url = static function ( $section, $field, $label, $default_value = '', $description = '', $priority = 10, $transport = 'postMessage' ) use ( $wp_customize, $add_setting, $sanitize_url ) {
		$add_setting( $field, $default_value, $sanitize_url, $transport );

		$args = array(
			'label'    => $label,
			'section'  => $section,
			'settings' => greenlight_customize_appearance_setting_id( $field ),
			'type'     => 'url',
			'priority' => $priority,
		);

		if ( '' !== $description ) {
			$args['description'] = $description;
		}

		$wp_customize->add_control( greenlight_customize_appearance_setting_id( $field ), $args );
	};

	$add_color = static function ( $section, $field, $label, $default_value = '', $description = '', $priority = 10, $transport = 'refresh' ) use ( $wp_customize, $add_setting, $sanitize_color ) {
		$add_setting( $field, $default_value, $sanitize_color, $transport );

		$args = array(
			'label'    => $label,
			'section'  => $section,
			'settings' => greenlight_customize_appearance_setting_id( $field ),
			'priority' => $priority,
		);

		if ( '' !== $description ) {
			$args['description'] = $description;
		}

		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				greenlight_customize_appearance_setting_id( $field ),
				$args
			)
		);
	};

	// Fondations.
	$add_setting( 'theme_preset', $defaults['theme_preset'], $sanitize_choice( array_keys( $presets ), $defaults['theme_preset'] ) );
	$wp_customize->add_control(
		greenlight_customize_appearance_setting_id( 'theme_preset' ),
		array(
			'label'    => __( 'Style éditorial', 'greenlight' ),
			'section'  => 'greenlight_appearance_foundations',
			'settings' => greenlight_customize_appearance_setting_id( 'theme_preset' ),
			'type'     => 'select',
			'choices'  => $theme_preset_choices,
			'priority' => 10,
		)
	);

	$add_select( 'greenlight_appearance_foundations', 'density_scale', __( 'Rythme de page', 'greenlight' ), $density_choices, $defaults['density_scale'], '', 20 );
	$priority = 30;
	foreach ( $density_contexts as $context_data ) {
		$field = isset( $context_data['field'] ) ? $context_data['field'] : '';
		if ( '' === $field ) {
			continue;
		}

		$add_select(
			'greenlight_appearance_foundations',
			$field,
			isset( $context_data['label'] ) ? $context_data['label'] : $field,
			$density_choices_with_inherit,
			isset( $defaults[ $field ] ) ? $defaults[ $field ] : 'inherit',
			isset( $context_data['description'] ) ? $context_data['description'] : '',
			$priority
		);

		$priority += 10;
	}
	$add_checkbox( 'greenlight_appearance_foundations', 'carbon_badge_enabled', __( 'Afficher le badge CO₂', 'greenlight' ), $defaults['carbon_badge_enabled'], '', $priority );
	$priority += 10;
	$add_text( 'greenlight_appearance_foundations', 'carbon_badge_value', __( 'Valeur CO₂', 'greenlight' ), $defaults['carbon_badge_value'], __( 'Laisser vide pour la valeur par défaut.', 'greenlight' ), $priority );
	$priority += 10;
	$add_select( 'greenlight_appearance_foundations', 'carbon_badge_position', __( 'Emplacement du badge', 'greenlight' ), $carbon_badge_position_choices, $defaults['carbon_badge_position'], '', $priority );
	$priority += 10;
	$add_color( 'greenlight_appearance_foundations', 'color_primary', __( 'Couleur primaire', 'greenlight' ), $defaults['color_primary'], '', $priority );
	$priority += 10;
	$add_color( 'greenlight_appearance_foundations', 'color_background', __( 'Fond de page', 'greenlight' ), $defaults['color_background'], '', $priority );
	$priority += 10;
	$add_color( 'greenlight_appearance_foundations', 'color_surface', __( 'Surface', 'greenlight' ), $defaults['color_surface'], '', $priority );
	$priority += 10;
	$add_color( 'greenlight_appearance_foundations', 'color_text', __( 'Texte', 'greenlight' ), $defaults['color_text'], '', $priority );
	$priority += 10;
	$add_color( 'greenlight_appearance_foundations', 'color_tertiary', __( 'Tertiaire', 'greenlight' ), $defaults['color_tertiary'], '', $priority );
	$priority += 10;
	$add_color( 'greenlight_appearance_foundations', 'color_border', __( 'Bordure', 'greenlight' ), $defaults['color_border'], '', $priority );
	$priority += 10;
	$add_color( 'greenlight_appearance_foundations', 'color_on_surface_variant', __( 'Texte secondaire', 'greenlight' ), $defaults['color_on_surface_variant'], '', $priority );

	// Navigation.
	$add_color( 'greenlight_appearance_navigation', 'color_header_bg', __( 'Fond header', 'greenlight' ), $defaults['color_header_bg'], '', 10 );
	$add_color( 'greenlight_appearance_navigation', 'color_header_text', __( 'Texte header', 'greenlight' ), $defaults['color_header_text'], '', 20 );
	$add_color( 'greenlight_appearance_navigation', 'color_header_accent', __( 'Accent header', 'greenlight' ), $defaults['color_header_accent'], '', 30 );
	$add_select( 'greenlight_appearance_navigation', 'header_layout', __( 'Layout header', 'greenlight' ), $header_layout_choices, $defaults['header_layout'], '', 40 );
	$add_checkbox( 'greenlight_appearance_navigation', 'header_sticky', __( 'Header collant', 'greenlight' ), $defaults['header_sticky'], '', 50 );

	$add_setting( 'header_opacity', $defaults['header_opacity'], function( $v ) { return max( 0, min( 100, absint( $v ) ) ); } );
	$wp_customize->add_control(
		greenlight_customize_appearance_setting_id( 'header_opacity' ),
		array(
			'label'       => __( 'Opacité du header (%)', 'greenlight' ),
			'section'     => 'greenlight_appearance_navigation',
			'settings'    => greenlight_customize_appearance_setting_id( 'header_opacity' ),
			'type'        => 'number',
			'input_attrs' => array( 'min' => 0, 'max' => 100, 'step' => 5 ),
			'priority'    => 55,
		)
	);

	$add_checkbox( 'greenlight_appearance_navigation', 'show_tagline', __( 'Afficher la description du site', 'greenlight' ), $defaults['show_tagline'], '', 60 );
	$add_select( 'greenlight_appearance_navigation', 'nav_link_case', __( 'Casse menu', 'greenlight' ), $nav_case_choices, $defaults['nav_link_case'], '', 80 );
	$add_select( 'greenlight_appearance_navigation', 'submenu_style', __( 'Sous-menus', 'greenlight' ), $submenu_choices, $defaults['submenu_style'], '', 90 );
	$add_select(
		'greenlight_appearance_navigation',
		'nav_style',
		__( 'Navigation mobile', 'greenlight' ),
		array(
			'inline' => __( 'Inline (défaut)', 'greenlight' ),
			'burger' => __( 'Menu burger natif', 'greenlight' ),
		),
		$defaults['nav_style'],
		'',
		100,
		'refresh'
	);

	// Hero.
	$add_checkbox( 'greenlight_appearance_hero', 'hero_enabled', __( 'Utiliser le hero avancé', 'greenlight' ), $defaults['hero_enabled'], __( 'Désactivé = intro simple.', 'greenlight' ), 10 );
	$add_select( 'greenlight_appearance_hero', 'hero_style', __( 'Style hero', 'greenlight' ), $hero_style_choices, $defaults['hero_style'], '', 20 );
	$add_select( 'greenlight_appearance_hero', 'hero_background_mode', __( 'Fond hero', 'greenlight' ), $hero_background_choices, $defaults['hero_background_mode'], '', 30 );
	$add_color( 'greenlight_appearance_hero', 'hero_background_color', __( 'Couleur hero', 'greenlight' ), $defaults['hero_background_color'], '', 40 );
	$add_select( 'greenlight_appearance_hero', 'hero_gradient_preset', __( 'Dégradé hero', 'greenlight' ), $hero_gradient_choices, $defaults['hero_gradient_preset'], '', 50 );
	$add_url( 'greenlight_appearance_hero', 'hero_background_image', __( 'Image hero', 'greenlight' ), $defaults['hero_background_image'], __( 'URL d’image de fond.', 'greenlight' ), 60 );
	$add_select( 'greenlight_appearance_hero', 'hero_heading_mode', __( 'Titre hero', 'greenlight' ), $hero_heading_choices, $defaults['hero_heading_mode'], '', 70 );
	$add_text( 'greenlight_appearance_hero', 'hero_heading_text', __( 'Titre personnalisé', 'greenlight' ), $defaults['hero_heading_text'], '', 80 );
	$add_select( 'greenlight_appearance_hero', 'hero_subheading_mode', __( 'Sous-titre hero', 'greenlight' ), $hero_subheading_choices, $defaults['hero_subheading_mode'], '', 90 );
	$add_textarea( 'greenlight_appearance_hero', 'hero_subheading_text', __( 'Sous-titre personnalisé', 'greenlight' ), $defaults['hero_subheading_text'], __( 'Le texte historique reste en secours.', 'greenlight' ), 100 );
	$add_select( 'greenlight_appearance_hero', 'hero_height_mode', __( 'Hauteur hero', 'greenlight' ), $hero_height_choices, $defaults['hero_height_mode'], '', 110 );
	$add_select(
		'greenlight_appearance_hero',
		'hero_overlay_strength',
		__( 'Overlay', 'greenlight' ),
		array(
			'none'   => __( 'Aucun', 'greenlight' ),
			'soft'   => __( 'Léger', 'greenlight' ),
			'strong' => __( 'Soutenu', 'greenlight' ),
		),
		$defaults['hero_overlay_strength'],
		'',
		120
	);

	// Overlay opacity.
	$add_setting( 'hero_overlay_opacity', $defaults['hero_overlay_opacity'], static function ( $v ) { return max( 0, min( 100, absint( $v ) ) ); } );
	$wp_customize->add_control(
		greenlight_customize_appearance_setting_id( 'hero_overlay_opacity' ),
		array(
			'label'       => __( 'Opacité overlay (%)', 'greenlight' ),
			'section'     => 'greenlight_appearance_hero',
			'settings'    => greenlight_customize_appearance_setting_id( 'hero_overlay_opacity' ),
			'type'        => 'number',
			'input_attrs' => array( 'min' => 0, 'max' => 100, 'step' => 5 ),
			'priority'    => 125,
		)
	);
	$add_select(
		'greenlight_appearance_hero',
		'hero_overlay_direction',
		__( 'Direction overlay', 'greenlight' ),
		array(
			'full'   => __( 'Pleine couverture', 'greenlight' ),
			'top'    => __( 'Haut → transparent', 'greenlight' ),
			'bottom' => __( 'Bas → transparent', 'greenlight' ),
			'left'   => __( 'Gauche → transparent', 'greenlight' ),
			'right'  => __( 'Droite → transparent', 'greenlight' ),
		),
		$defaults['hero_overlay_direction'],
		'',
		126
	);

	$add_checkbox( 'greenlight_appearance_hero', 'show_hero_badge', __( 'Afficher le badge CO₂ dans le hero', 'greenlight' ), $defaults['show_hero_badge'], '', 130 );
	$add_textarea( 'greenlight_appearance_hero', 'hero_text', __( 'Texte de secours', 'greenlight' ), $defaults['hero_text'], __( 'Ancien sous-titre conservé pour compatibilité.', 'greenlight' ), 140 );

	// CTA boutons hero.
	$add_checkbox( 'greenlight_appearance_hero', 'hero_cta_enabled', __( 'Bouton principal', 'greenlight' ), 0, '', 145 );
	$add_text( 'greenlight_appearance_hero', 'hero_cta_text', __( 'Texte du bouton', 'greenlight' ), '', '', 150 );
	$add_url( 'greenlight_appearance_hero', 'hero_cta_url', __( 'URL du bouton', 'greenlight' ), '', '', 155 );
	$add_select(
		'greenlight_appearance_hero',
		'hero_cta_style',
		__( 'Style du bouton', 'greenlight' ),
		array(
			'primary'   => __( 'Primaire', 'greenlight' ),
			'secondary' => __( 'Secondaire', 'greenlight' ),
			'tertiary'  => __( 'Tertiaire', 'greenlight' ),
		),
		'primary',
		'',
		160
	);
	$add_select(
		'greenlight_appearance_hero',
		'hero_cta_position',
		__( 'Position des boutons', 'greenlight' ),
		array(
			'lead'   => __( 'Sous le titre', 'greenlight' ),
			'body'   => __( 'Zone description', 'greenlight' ),
			'center' => __( 'Centré pleine largeur', 'greenlight' ),
		),
		'lead',
		'',
		165
	);
	$add_checkbox( 'greenlight_appearance_hero', 'hero_cta2_enabled', __( 'Bouton secondaire', 'greenlight' ), 0, '', 170 );
	$add_text( 'greenlight_appearance_hero', 'hero_cta2_text', __( 'Texte bouton 2', 'greenlight' ), '', '', 175 );
	$add_url( 'greenlight_appearance_hero', 'hero_cta2_url', __( 'URL bouton 2', 'greenlight' ), '', '', 180 );
	$add_select(
		'greenlight_appearance_hero',
		'hero_cta2_style',
		__( 'Style bouton 2', 'greenlight' ),
		array(
			'primary'   => __( 'Primaire', 'greenlight' ),
			'secondary' => __( 'Secondaire', 'greenlight' ),
			'tertiary'  => __( 'Tertiaire', 'greenlight' ),
		),
		'secondary',
		'',
		185
	);

	// Content.
	$add_checkbox( 'greenlight_appearance_content', 'show_date', __( 'Afficher la date de publication', 'greenlight' ), $defaults['show_date'], '', 10 );
	$add_checkbox( 'greenlight_appearance_content', 'show_author', __( 'Afficher l’auteur', 'greenlight' ), $defaults['show_author'], '', 20 );
	$add_checkbox( 'greenlight_appearance_content', 'show_tags', __( 'Afficher les tags', 'greenlight' ), $defaults['show_tags'], '', 30 );
	$add_select( 'greenlight_appearance_content', 'archive_layout', __( 'Archives', 'greenlight' ), $archive_layout_choices, $defaults['archive_layout'], '', 50 );
	$add_select( 'greenlight_appearance_content', 'archive_card_style', __( 'Cartes d’archive', 'greenlight' ), $archive_card_choices, $defaults['archive_card_style'], '', 60 );
	$add_checkbox( 'greenlight_appearance_content', 'show_excerpts_archive', __( 'Afficher les extraits', 'greenlight' ), $defaults['show_excerpts_archive'], '', 70 );
	$add_checkbox( 'greenlight_appearance_content', 'show_thumbnails_archive', __( 'Afficher les miniatures', 'greenlight' ), $defaults['show_thumbnails_archive'], '', 80 );
	$add_select( 'greenlight_appearance_content', 'single_layout', __( 'Article', 'greenlight' ), $single_layout_choices, $defaults['single_layout'], '', 90 );

	// Footer.
	$add_color( 'greenlight_appearance_footer', 'color_footer_bg', __( 'Fond footer', 'greenlight' ), $defaults['color_footer_bg'], '', 10 );
	$add_select( 'greenlight_appearance_footer', 'footer_layout', __( 'Mise en page footer', 'greenlight' ), $footer_layout_choices, $defaults['footer_layout'], '', 20 );
	$add_checkbox( 'greenlight_appearance_footer', 'show_low_emission', __( 'Afficher la mention Low Emission', 'greenlight' ), $defaults['show_low_emission'], '', 30 );
	$add_text( 'greenlight_appearance_footer', 'custom_copyright', __( 'Copyright personnalisé', 'greenlight' ), $defaults['custom_copyright'], '', 40 );
	$add_checkbox( 'greenlight_appearance_footer', 'show_footer_nav', __( 'Afficher le menu de navigation footer', 'greenlight' ), $defaults['show_footer_nav'], '', 50 );
}
add_action( 'customize_register', 'greenlight_customize_register' );

/**
 * Enqueues the Customizer preview script.
 *
 * @return void
 */
function greenlight_customize_preview_init() {
	$script_path = get_theme_file_path( 'assets/js/customizer-preview.js' );
	$script_url  = get_theme_file_uri( 'assets/js/customizer-preview.js' );

	if ( ! file_exists( $script_path ) ) {
		return;
	}

	wp_enqueue_script(
		'greenlight-customizer-preview',
		$script_url,
		array( 'customize-preview' ),
		filemtime( $script_path ),
		true
	);

	$preview_data = array(
		'siteTitle'         => get_bloginfo( 'name' ),
		'siteTagline'       => get_bloginfo( 'description' ),
		'presets'           => array(),
		'densities'         => array(),
		'archiveCardStyles' => array(),
		'singleLayouts'     => array(),
		'footerLayouts'     => array(),
		'gradients'         => array(),
	);

	foreach ( greenlight_get_appearance_presets() as $preset_key => $preset_data ) {
		$preview_data['presets'][ $preset_key ] = array(
			'vars' => isset( $preset_data['vars'] ) ? (array) $preset_data['vars'] : array(),
		);
	}

	foreach ( greenlight_get_appearance_densities() as $density_key => $density_data ) {
		$preview_data['densities'][ $density_key ] = array(
			'vars' => isset( $density_data['vars'] ) ? (array) $density_data['vars'] : array(),
		);
	}

	foreach ( greenlight_get_archive_card_styles() as $style_key => $style_data ) {
		$preview_data['archiveCardStyles'][ $style_key ] = array(
			'vars' => isset( $style_data['vars'] ) ? (array) $style_data['vars'] : array(),
		);
	}

	foreach ( greenlight_get_single_layout_variants() as $layout_key => $layout_data ) {
		$preview_data['singleLayouts'][ $layout_key ] = array(
			'vars' => isset( $layout_data['vars'] ) ? (array) $layout_data['vars'] : array(),
		);
	}

	foreach ( greenlight_get_footer_layout_variants() as $layout_key => $layout_data ) {
		$preview_data['footerLayouts'][ $layout_key ] = array(
			'vars' => isset( $layout_data['vars'] ) ? (array) $layout_data['vars'] : array(),
		);
	}

	foreach ( greenlight_get_hero_gradient_presets() as $gradient_key => $gradient_data ) {
		$preview_data['gradients'][ $gradient_key ] = isset( $gradient_data['value'] ) ? (string) $gradient_data['value'] : '';
	}

	wp_add_inline_script(
		'greenlight-customizer-preview',
		'window.greenlightCustomizerPreview = ' . wp_json_encode( $preview_data ) . ';',
		'before'
	);
}
add_action( 'customize_preview_init', 'greenlight_customize_preview_init' );
