<?php
/**
 * SEO content analysis and readability scoring.
 *
 * @package Greenlight
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the focus keyword post meta.
 *
 * @return void
 */
function greenlight_register_seo_focus_kw_meta() {
	$post_types = greenlight_get_seo_post_types();

	foreach ( $post_types as $post_type ) {
		register_post_meta(
			$post_type,
			'_greenlight_seo_focus_kw',
			array(
				'single'            => true,
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'show_in_rest'      => true,
				'auth_callback'     => 'greenlight_can_edit_seo_meta',
			)
		);
	}
}
add_action( 'init', 'greenlight_register_seo_focus_kw_meta' );

/**
 * Analyses SEO quality of a post and returns a score with details.
 *
 * @param int $post_id Post ID.
 * @return array{score: int, details: array}
 */
function greenlight_seo_analysis_score( $post_id ) {
	$post = get_post( $post_id );

	if ( ! $post ) {
		return array( 'score' => 0, 'details' => array() );
	}

	$focus_kw = strtolower( trim( (string) get_post_meta( $post_id, '_greenlight_seo_focus_kw', true ) ) );
	$content  = wp_strip_all_tags( $post->post_content );
	$title    = strtolower( $post->post_title );
	$details  = array();
	$points   = 0;
	$max      = 0;

	// 1. Focus keyword defined (10 pts).
	$max += 10;
	if ( '' !== $focus_kw ) {
		$points += 10;
		$details[] = array( 'pass' => true, 'label' => __( 'Mot-clé principal défini', 'greenlight' ) );
	} else {
		$details[] = array( 'pass' => false, 'label' => __( 'Mot-clé principal non défini', 'greenlight' ) );

		return array(
			'score'   => 0,
			'details' => $details,
		);
	}

	// 2. Keyword in title (15 pts).
	$max += 15;
	if ( false !== strpos( $title, $focus_kw ) ) {
		$points += 15;
		$details[] = array( 'pass' => true, 'label' => __( 'Mot-clé présent dans le titre', 'greenlight' ) );
	} else {
		$details[] = array( 'pass' => false, 'label' => __( 'Mot-clé absent du titre', 'greenlight' ) );
	}

	// 3. Keyword density (15 pts).
	$max      += 15;
	$words     = str_word_count( strtolower( $content ) );
	$kw_count  = 0;

	if ( $words > 0 ) {
		$kw_count = substr_count( strtolower( $content ), $focus_kw );
		$density  = ( $kw_count / $words ) * 100;

		if ( $density >= 0.5 && $density <= 3.0 ) {
			$points += 15;
			$details[] = array(
				'pass'  => true,
				/* translators: %s: keyword density percentage */
				'label' => sprintf( __( 'Densité du mot-clé : %.1f%%', 'greenlight' ), $density ),
			);
		} else {
			$details[] = array(
				'pass'  => false,
				/* translators: %s: keyword density percentage */
				'label' => sprintf( __( 'Densité du mot-clé : %.1f%% (idéal : 0,5-3%%)', 'greenlight' ), $density ),
			);
		}
	} else {
		$details[] = array( 'pass' => false, 'label' => __( 'Contenu vide', 'greenlight' ) );
	}

	// 4. Keyword in first paragraph (10 pts).
	$max += 10;
	$blocks = parse_blocks( $post->post_content );
	$first_para = '';
	foreach ( $blocks as $block ) {
		if ( 'core/paragraph' === $block['blockName'] && ! empty( $block['innerHTML'] ) ) {
			$first_para = strtolower( wp_strip_all_tags( $block['innerHTML'] ) );
			break;
		}
	}
	if ( '' === $first_para ) {
		$paragraphs = preg_split( '/\n\s*\n/', $content, 2 );
		$first_para = strtolower( trim( $paragraphs[0] ?? '' ) );
	}
	if ( '' !== $first_para && false !== strpos( $first_para, $focus_kw ) ) {
		$points += 10;
		$details[] = array( 'pass' => true, 'label' => __( 'Mot-clé présent dans le premier paragraphe', 'greenlight' ) );
	} else {
		$details[] = array( 'pass' => false, 'label' => __( 'Mot-clé absent du premier paragraphe', 'greenlight' ) );
	}

	// 5. Keyword in first H2 (10 pts).
	$max += 10;
	if ( preg_match( '/<h2[^>]*>(.*?)<\/h2>/si', $post->post_content, $h2_match ) ) {
		if ( false !== strpos( strtolower( wp_strip_all_tags( $h2_match[1] ) ), $focus_kw ) ) {
			$points += 10;
			$details[] = array( 'pass' => true, 'label' => __( 'Mot-clé présent dans le premier H2', 'greenlight' ) );
		} else {
			$details[] = array( 'pass' => false, 'label' => __( 'Mot-clé absent du premier H2', 'greenlight' ) );
		}
	} else {
		$details[] = array( 'pass' => false, 'label' => __( 'Aucun H2 trouvé', 'greenlight' ) );
	}

	// 6. Keyword in image alt (10 pts).
	$max += 10;
	$alt_found = false;
	if ( preg_match_all( '/alt=["\']([^"\']*?)["\']/i', $post->post_content, $alt_matches ) ) {
		foreach ( $alt_matches[1] as $alt ) {
			if ( false !== strpos( strtolower( $alt ), $focus_kw ) ) {
				$alt_found = true;
				break;
			}
		}
	}
	if ( $alt_found ) {
		$points += 10;
		$details[] = array( 'pass' => true, 'label' => __( 'Mot-clé présent dans le texte alt d\'une image', 'greenlight' ) );
	} else {
		$details[] = array( 'pass' => false, 'label' => __( 'Mot-clé absent des textes alt', 'greenlight' ) );
	}

	// 7. Internal links (10 pts).
	$max += 10;
	$home_url = home_url();
	if ( preg_match_all( '/<a\s[^>]*href=["\']([^"\']+)["\']/i', $post->post_content, $link_matches ) ) {
		$has_internal = false;
		foreach ( $link_matches[1] as $href ) {
			if ( 0 === strpos( $href, $home_url ) || 0 === strpos( $href, '/' ) ) {
				$has_internal = true;
				break;
			}
		}
		if ( $has_internal ) {
			$points += 10;
			$details[] = array( 'pass' => true, 'label' => __( 'Liens internes détectés', 'greenlight' ) );
		} else {
			$details[] = array( 'pass' => false, 'label' => __( 'Aucun lien interne', 'greenlight' ) );
		}
	} else {
		$details[] = array( 'pass' => false, 'label' => __( 'Aucun lien dans le contenu', 'greenlight' ) );
	}

	// 8. External links (5 pts).
	$max += 5;
	if ( preg_match_all( '/<a\s[^>]*href=["\']([^"\']+)["\']/i', $post->post_content, $link_matches ) ) {
		$has_external = false;
		foreach ( $link_matches[1] as $href ) {
			if ( preg_match( '#^https?://#i', $href ) && 0 !== strpos( $href, $home_url ) ) {
				$has_external = true;
				break;
			}
		}
		if ( $has_external ) {
			$points += 5;
			$details[] = array( 'pass' => true, 'label' => __( 'Liens externes détectés', 'greenlight' ) );
		} else {
			$details[] = array( 'pass' => false, 'label' => __( 'Aucun lien externe', 'greenlight' ) );
		}
	}

	// 9. Content length > 300 words (15 pts).
	$max += 15;
	if ( $words >= 300 ) {
		$points += 15;
		$details[] = array(
			'pass'  => true,
			/* translators: %d: word count */
			'label' => sprintf( __( 'Longueur du contenu : %d mots', 'greenlight' ), $words ),
		);
	} else {
		$details[] = array(
			'pass'  => false,
			/* translators: %d: word count */
			'label' => sprintf( __( 'Contenu trop court : %d mots (objectif : 300+)', 'greenlight' ), $words ),
		);
	}

	$score = ( $max > 0 ) ? (int) round( ( $points / $max ) * 100 ) : 0;

	return array(
		'score'   => $score,
		'details' => $details,
	);
}

/**
 * Computes a Flesch-Kincaid readability score adapted for French (Kandel-Moles).
 *
 * Formula: 207 - 1.015 * (words/sentences) - 73.6 * (syllables/words)
 *
 * @param int $post_id Post ID.
 * @return array{score: int, avg_sentence_length: float, long_sentences_pct: float}
 */
function greenlight_seo_readability_score( $post_id ) {
	$post = get_post( $post_id );

	if ( ! $post ) {
		return array( 'score' => 0, 'avg_sentence_length' => 0, 'long_sentences_pct' => 0 );
	}

	$content = wp_strip_all_tags( $post->post_content );
	$content = html_entity_decode( $content, ENT_QUOTES, 'UTF-8' );

	// Split into sentences.
	$sentences = preg_split( '/[.!?]+/', $content, -1, PREG_SPLIT_NO_EMPTY );
	$sentences = array_filter( array_map( 'trim', $sentences ) );

	$sentence_count = count( $sentences );
	if ( $sentence_count < 1 ) {
		return array( 'score' => 0, 'avg_sentence_length' => 0, 'long_sentences_pct' => 0 );
	}

	$total_words     = 0;
	$total_syllables = 0;
	$long_sentences  = 0;

	foreach ( $sentences as $sentence ) {
		$words_in_sentence = preg_split( '/\s+/', $sentence, -1, PREG_SPLIT_NO_EMPTY );
		$word_count        = count( $words_in_sentence );
		$total_words      += $word_count;

		if ( $word_count > 20 ) {
			$long_sentences++;
		}

		foreach ( $words_in_sentence as $word ) {
			$total_syllables += greenlight_count_syllables_fr( $word );
		}
	}

	if ( $total_words < 1 ) {
		return array( 'score' => 0, 'avg_sentence_length' => 0, 'long_sentences_pct' => 0 );
	}

	$avg_sentence_length = $total_words / $sentence_count;
	$avg_syllables       = $total_syllables / $total_words;
	$long_pct            = ( $long_sentences / $sentence_count ) * 100;

	// Kandel-Moles formula for French.
	$raw_score = 207 - ( 1.015 * $avg_sentence_length ) - ( 73.6 * $avg_syllables );
	$score     = (int) max( 0, min( 100, round( $raw_score ) ) );

	return array(
		'score'              => $score,
		'avg_sentence_length' => round( $avg_sentence_length, 1 ),
		'long_sentences_pct' => round( $long_pct, 1 ),
	);
}

/**
 * Estimates the number of syllables in a French word.
 *
 * @param string $word Word to analyse.
 * @return int
 */
function greenlight_count_syllables_fr( $word ) {
	$word = mb_strtolower( trim( $word ), 'UTF-8' );
	$word = preg_replace( '/[^a-zàâäéèêëïîôùûüÿœæ]/u', '', $word );

	if ( '' === $word ) {
		return 1;
	}

	// Count vowel groups.
	$vowels = preg_match_all( '/[aeiouyàâäéèêëïîôùûüÿœæ]+/u', $word );

	return max( 1, $vowels );
}

/**
 * Enqueues the SEO analysis sidebar script in the block editor.
 *
 * @return void
 */
function greenlight_enqueue_seo_analysis() {
	$screen = get_current_screen();

	if ( ! $screen || 'post' !== $screen->base ) {
		return;
	}

	$post_id = get_the_ID();
	if ( ! $post_id ) {
		return;
	}

	$script_path = get_theme_file_path( 'assets/js/seo-analysis.js' );

	if ( ! file_exists( $script_path ) ) {
		return;
	}

	wp_enqueue_script(
		'greenlight-seo-analysis',
		get_theme_file_uri( 'assets/js/seo-analysis.js' ),
		array(
			'wp-components',
			'wp-data',
			'wp-edit-post',
			'wp-element',
			'wp-i18n',
			'wp-plugins',
		),
		filemtime( $script_path ),
		true
	);

	$analysis    = greenlight_seo_analysis_score( $post_id );
	$readability = greenlight_seo_readability_score( $post_id );

	wp_localize_script( 'greenlight-seo-analysis', 'greenlightSeoAnalysis', array(
		'postId'      => $post_id,
		'analysis'    => $analysis,
		'readability' => $readability,
		'restNonce'   => wp_create_nonce( 'wp_rest' ),
	) );
}
add_action( 'enqueue_block_editor_assets', 'greenlight_enqueue_seo_analysis' );

/**
 * Adds the SEO score column to the posts list table.
 *
 * @param array $columns Existing columns.
 * @return array
 */
function greenlight_add_seo_score_column( $columns ) {
	$columns['greenlight_seo_score'] = __( 'SEO', 'greenlight' );

	return $columns;
}
add_filter( 'manage_posts_columns', 'greenlight_add_seo_score_column' );

/**
 * Renders the SEO score column value.
 *
 * @param string $column  Column name.
 * @param int    $post_id Post ID.
 * @return void
 */
function greenlight_render_seo_score_column( $column, $post_id ) {
	if ( 'greenlight_seo_score' !== $column ) {
		return;
	}

	$analysis = greenlight_seo_analysis_score( $post_id );
	$score    = $analysis['score'];

	if ( $score < 40 ) {
		$color = '#dc3232';
	} elseif ( $score <= 70 ) {
		$color = '#f0b849';
	} else {
		$color = '#46b450';
	}

	printf(
		'<span style="display:inline-block;width:12px;height:12px;border-radius:50%%;background:%s;vertical-align:middle" title="%s"></span> %d',
		esc_attr( $color ),
		esc_attr( sprintf(
			/* translators: %d: SEO score */
			__( 'Score SEO : %d/100', 'greenlight' ),
			$score
		) ),
		(int) $score
	);
}
add_action( 'manage_posts_custom_column', 'greenlight_render_seo_score_column', 10, 2 );
