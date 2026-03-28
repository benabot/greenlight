( function( wp ) {
	if ( ! wp || ! wp.plugins || ! wp.editPost || ! wp.data || ! wp.components ) {
		return;
	}

	var registerPlugin = wp.plugins.registerPlugin;
	var PluginDocumentSettingPanel = wp.editPost.PluginDocumentSettingPanel;
	var components = wp.components;
	var element = wp.element;
	var data = wp.data;
	var i18n = wp.i18n;

	var createElement = element.createElement;
	var TextControl = components.TextControl;
	var __ = i18n.__;

	var META_KEY = "_greenlight_seo_focus_kw";

	/**
	 * Returns a colour for a score value.
	 */
	function scoreColor( score ) {
		if ( score < 40 ) {
			return "#dc3232";
		}
		if ( score <= 70 ) {
			return "#f0b849";
		}
		return "#46b450";
	}

	/**
	 * Renders a score badge.
	 */
	function ScoreBadge( props ) {
		var color = scoreColor( props.score );

		return createElement(
			"span",
			{
				style: {
					display: "inline-block",
					width: "12px",
					height: "12px",
					borderRadius: "50%",
					backgroundColor: color,
					verticalAlign: "middle",
					marginRight: "8px"
				}
			}
		);
	}

	/**
	 * Renders a single check item.
	 */
	function CheckItem( props ) {
		var icon = props.pass ? "\u2713" : "\u2717";
		var color = props.pass ? "#46b450" : "#dc3232";

		return createElement(
			"div",
			{ style: { marginBottom: "4px" } },
			createElement( "span", { style: { color: color, marginRight: "6px", fontWeight: "bold" } }, icon ),
			createElement( "span", null, props.label )
		);
	}

	/**
	 * Counts syllables in a French word (simplified).
	 */
	function countSyllablesFr( word ) {
		word = word.toLowerCase().replace( /[^a-z\u00e0\u00e2\u00e4\u00e9\u00e8\u00ea\u00eb\u00ef\u00ee\u00f4\u00f9\u00fb\u00fc\u00ff\u0153\u00e6]/g, "" );
		if ( ! word ) {
			return 1;
		}
		var matches = word.match( /[aeiouy\u00e0\u00e2\u00e4\u00e9\u00e8\u00ea\u00eb\u00ef\u00ee\u00f4\u00f9\u00fb\u00fc\u00ff\u0153\u00e6]+/g );
		return matches ? Math.max( 1, matches.length ) : 1;
	}

	/**
	 * Computes SEO analysis from the current editor content.
	 */
	function computeAnalysis( content, title, focusKw ) {
		var details = [];
		var points = 0;
		var max = 0;

		var plainContent = content.replace( /<[^>]+>/g, "" );
		var words = plainContent.split( /\s+/ ).filter( function( w ) { return w.length > 0; } );
		var wordCount = words.length;
		var lowerContent = plainContent.toLowerCase();
		var lowerTitle = title.toLowerCase();
		var lowerKw = focusKw.toLowerCase().trim();

		// 1. Focus keyword defined.
		max += 10;
		if ( lowerKw ) {
			points += 10;
			details.push( { pass: true, label: __( "Mot-cl\u00e9 principal d\u00e9fini", "greenlight" ) } );
		} else {
			details.push( { pass: false, label: __( "Mot-cl\u00e9 principal non d\u00e9fini", "greenlight" ) } );
			return { score: 0, details: details };
		}

		// 2. Keyword in title.
		max += 15;
		if ( lowerTitle.indexOf( lowerKw ) !== -1 ) {
			points += 15;
			details.push( { pass: true, label: __( "Mot-cl\u00e9 pr\u00e9sent dans le titre", "greenlight" ) } );
		} else {
			details.push( { pass: false, label: __( "Mot-cl\u00e9 absent du titre", "greenlight" ) } );
		}

		// 3. Keyword density.
		max += 15;
		if ( wordCount > 0 ) {
			var escapedKw = lowerKw.replace( /[.*+?^${}()|[\]\\]/g, "\\$&" );
			var regex = new RegExp( escapedKw, "gi" );
			var kwMatches = lowerContent.match( regex );
			var kwCount = kwMatches ? kwMatches.length : 0;
			var density = ( kwCount / wordCount ) * 100;

			if ( density >= 0.5 && density <= 3.0 ) {
				points += 15;
				details.push( { pass: true, label: __( "Densit\u00e9 du mot-cl\u00e9", "greenlight" ) + " : " + density.toFixed( 1 ) + "%" } );
			} else {
				details.push( { pass: false, label: __( "Densit\u00e9 du mot-cl\u00e9", "greenlight" ) + " : " + density.toFixed( 1 ) + "% (" + __( "id\u00e9al : 0,5-3%", "greenlight" ) + ")" } );
			}
		} else {
			details.push( { pass: false, label: __( "Contenu vide", "greenlight" ) } );
		}

		// 4. Keyword in first paragraph.
		max += 10;
		var firstPara = "";
		var paraMatch = content.match( /<p[^>]*>(.*?)<\/p>/i );
		if ( paraMatch ) {
			firstPara = paraMatch[1].replace( /<[^>]+>/g, "" ).toLowerCase();
		}
		if ( firstPara && firstPara.indexOf( lowerKw ) !== -1 ) {
			points += 10;
			details.push( { pass: true, label: __( "Mot-cl\u00e9 pr\u00e9sent dans le premier paragraphe", "greenlight" ) } );
		} else {
			details.push( { pass: false, label: __( "Mot-cl\u00e9 absent du premier paragraphe", "greenlight" ) } );
		}

		// 5. Keyword in first H2.
		max += 10;
		var h2Match = content.match( /<h2[^>]*>(.*?)<\/h2>/i );
		if ( h2Match && h2Match[1].replace( /<[^>]+>/g, "" ).toLowerCase().indexOf( lowerKw ) !== -1 ) {
			points += 10;
			details.push( { pass: true, label: __( "Mot-cl\u00e9 pr\u00e9sent dans le premier H2", "greenlight" ) } );
		} else {
			details.push( { pass: false, label: __( "Mot-cl\u00e9 absent du premier H2", "greenlight" ) } );
		}

		// 6. Keyword in image alt.
		max += 10;
		var altRegex = /alt=["']([^"']*?)["']/gi;
		var altMatch;
		var altFound = false;
		while ( ( altMatch = altRegex.exec( content ) ) !== null ) {
			if ( altMatch[1].toLowerCase().indexOf( lowerKw ) !== -1 ) {
				altFound = true;
				break;
			}
		}
		if ( altFound ) {
			points += 10;
			details.push( { pass: true, label: __( "Mot-cl\u00e9 pr\u00e9sent dans le texte alt d\u2019une image", "greenlight" ) } );
		} else {
			details.push( { pass: false, label: __( "Mot-cl\u00e9 absent des textes alt", "greenlight" ) } );
		}

		// 7. Internal links.
		max += 10;
		var linkRegex = /<a\s[^>]*href=["']([^"']+)["']/gi;
		var linkMatch;
		var hasInternal = false;
		var hasExternal = false;
		while ( ( linkMatch = linkRegex.exec( content ) ) !== null ) {
			var href = linkMatch[1];
			if ( href.indexOf( "/" ) === 0 || href.indexOf( window.location.origin ) === 0 ) {
				hasInternal = true;
			} else if ( /^https?:\/\//.test( href ) ) {
				hasExternal = true;
			}
		}
		if ( hasInternal ) {
			points += 10;
			details.push( { pass: true, label: __( "Liens internes d\u00e9tect\u00e9s", "greenlight" ) } );
		} else {
			details.push( { pass: false, label: __( "Aucun lien interne", "greenlight" ) } );
		}

		// 8. External links.
		max += 5;
		if ( hasExternal ) {
			points += 5;
			details.push( { pass: true, label: __( "Liens externes d\u00e9tect\u00e9s", "greenlight" ) } );
		} else {
			details.push( { pass: false, label: __( "Aucun lien externe", "greenlight" ) } );
		}

		// 9. Content length.
		max += 15;
		if ( wordCount >= 300 ) {
			points += 15;
			details.push( { pass: true, label: __( "Longueur du contenu", "greenlight" ) + " : " + wordCount + " " + __( "mots", "greenlight" ) } );
		} else {
			details.push( { pass: false, label: __( "Contenu trop court", "greenlight" ) + " : " + wordCount + " " + __( "mots (objectif : 300+)", "greenlight" ) } );
		}

		var score = max > 0 ? Math.round( ( points / max ) * 100 ) : 0;

		return { score: score, details: details };
	}

	/**
	 * Computes readability score (Kandel-Moles for French).
	 */
	function computeReadability( content ) {
		var plainContent = content.replace( /<[^>]+>/g, "" );
		plainContent = plainContent.replace( /&[^;]+;/g, " " );

		var sentences = plainContent.split( /[.!?]+/ ).filter( function( s ) { return s.trim().length > 0; } );
		var sentenceCount = sentences.length;

		if ( sentenceCount < 1 ) {
			return { score: 0, avgSentenceLength: 0, longSentencesPct: 0 };
		}

		var totalWords = 0;
		var totalSyllables = 0;
		var longSentences = 0;

		for ( var i = 0; i < sentences.length; i++ ) {
			var sentenceWords = sentences[i].trim().split( /\s+/ ).filter( function( w ) { return w.length > 0; } );
			var wc = sentenceWords.length;
			totalWords += wc;

			if ( wc > 20 ) {
				longSentences++;
			}

			for ( var j = 0; j < sentenceWords.length; j++ ) {
				totalSyllables += countSyllablesFr( sentenceWords[j] );
			}
		}

		if ( totalWords < 1 ) {
			return { score: 0, avgSentenceLength: 0, longSentencesPct: 0 };
		}

		var avgSentenceLength = totalWords / sentenceCount;
		var avgSyllables = totalSyllables / totalWords;
		var rawScore = 207 - ( 1.015 * avgSentenceLength ) - ( 73.6 * avgSyllables );
		var score = Math.max( 0, Math.min( 100, Math.round( rawScore ) ) );
		var longPct = Math.round( ( longSentences / sentenceCount ) * 100 * 10 ) / 10;

		return {
			score: score,
			avgSentenceLength: Math.round( avgSentenceLength * 10 ) / 10,
			longSentencesPct: longPct
		};
	}

	function SeoAnalysisPanel() {
		var meta = data.useSelect( function( select ) {
			return select( "core/editor" ).getEditedPostAttribute( "meta" ) || {};
		}, [] );

		var content = data.useSelect( function( select ) {
			return select( "core/editor" ).getEditedPostContent() || "";
		}, [] );

		var title = data.useSelect( function( select ) {
			return select( "core/editor" ).getEditedPostAttribute( "title" ) || "";
		}, [] );

		var focusKw = meta[ META_KEY ] || "";

		var analysis = computeAnalysis( content, title, focusKw );
		var readability = computeReadability( content );

		function updateFocusKw( value ) {
			data.dispatch( "core/editor" ).editPost( {
				meta: Object.assign( {}, meta, ( function() {
					var next = {};
					next[ META_KEY ] = value;
					return next;
				}() ) )
			} );
		}

		return createElement(
			PluginDocumentSettingPanel,
			{
				name: "greenlight-seo-analysis-panel",
				title: __( "Analyse SEO", "greenlight" )
			},
			createElement( TextControl, {
				label: __( "Mot-cl\u00e9 principal", "greenlight" ),
				value: focusKw,
				onChange: updateFocusKw
			} ),

			// SEO Score.
			createElement(
				"div",
				{ style: { marginBottom: "12px", padding: "8px", background: "#f9f9f9", borderRadius: "4px" } },
				createElement( ScoreBadge, { score: analysis.score } ),
				createElement( "strong", null, __( "Score SEO", "greenlight" ) + " : " + analysis.score + "/100" )
			),

			// SEO details.
			analysis.details.map( function( item, index ) {
				return createElement( CheckItem, { key: "seo-" + index, pass: item.pass, label: item.label } );
			} ),

			// Readability Score.
			createElement( "hr", { style: { margin: "16px 0" } } ),
			createElement(
				"div",
				{ style: { marginBottom: "12px", padding: "8px", background: "#f9f9f9", borderRadius: "4px" } },
				createElement( ScoreBadge, { score: readability.score } ),
				createElement( "strong", null, __( "Lisibilit\u00e9", "greenlight" ) + " : " + readability.score + "/100" )
			),
			createElement(
				"div",
				{ style: { marginBottom: "4px" } },
				__( "Longueur moyenne des phrases", "greenlight" ) + " : " + readability.avgSentenceLength + " " + __( "mots", "greenlight" )
			),
			createElement(
				"div",
				null,
				__( "Phrases longues (>20 mots)", "greenlight" ) + " : " + readability.longSentencesPct + "%"
			)
		);
	}

	registerPlugin( "greenlight-seo-analysis", {
		render: SeoAnalysisPanel
	} );
}( window.wp ) );
