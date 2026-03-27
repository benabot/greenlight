( function( wp ) {
	if ( ! wp || ! wp.plugins || ! wp.editPost || ! wp.data || ! wp.components ) {
		return;
	}

	var registerPlugin = wp.plugins.registerPlugin;
	var PluginDocumentSettingPanel = wp.editPost.PluginDocumentSettingPanel;
	var components = wp.components;
	var blockEditor = wp.blockEditor || wp.editor;
	var element = wp.element;
	var data = wp.data;
	var i18n = wp.i18n;

	if ( ! blockEditor || ! blockEditor.MediaUpload || ! blockEditor.MediaUploadCheck ) {
		return;
	}

	var createElement = element.createElement;
	var Fragment = element.Fragment;
	var TextControl = components.TextControl;
	var TextareaControl = components.TextareaControl;
	var ToggleControl = components.ToggleControl;
	var Button = components.Button;
	var Spinner = components.Spinner;
	var MediaUpload = blockEditor.MediaUpload;
	var MediaUploadCheck = blockEditor.MediaUploadCheck;
	var __ = i18n.__;

	var META_KEYS = {
		title: "_greenlight_seo_title",
		description: "_greenlight_seo_description",
		image: "_greenlight_seo_image",
		noindex: "_greenlight_seo_noindex"
	};

	function SeoPanel() {
		var meta = data.useSelect( function( select ) {
			return select( "core/editor" ).getEditedPostAttribute( "meta" ) || {};
		}, [] );

		var imageId = parseInt( meta[ META_KEYS.image ], 10 ) || 0;
		var media = data.useSelect( function( select ) {
			if ( ! imageId ) {
				return null;
			}

			return select( "core" ).getMedia( imageId );
		}, [ imageId ] );

		function updateMeta( key, value ) {
			data.dispatch( "core/editor" ).editPost( {
				meta: Object.assign( {}, meta, ( function() {
					var next = {};
					next[ key ] = value;
					return next;
				}() ) )
			} );
		}

		return createElement(
			PluginDocumentSettingPanel,
			{
				name: "greenlight-seo-panel",
				title: __( "SEO", "greenlight" )
			},
			createElement( TextControl, {
				label: __( "Titre SEO", "greenlight" ),
				value: meta[ META_KEYS.title ] || "",
				onChange: function( value ) {
					updateMeta( META_KEYS.title, value );
				}
			} ),
			createElement( TextareaControl, {
				label: __( "Meta description", "greenlight" ),
				help: ( meta[ META_KEYS.description ] || "" ).length + "/160",
				value: meta[ META_KEYS.description ] || "",
				onChange: function( value ) {
					updateMeta( META_KEYS.description, value );
				}
			} ),
			createElement(
				MediaUploadCheck,
				null,
				createElement( MediaUpload, {
					allowedTypes: [ "image" ],
					value: imageId,
					onSelect: function( mediaObject ) {
						updateMeta( META_KEYS.image, mediaObject && mediaObject.id ? mediaObject.id : 0 );
					},
					render: function( renderProps ) {
						return createElement(
							Fragment,
							null,
							createElement( "p", null, __( "Image Open Graph", "greenlight" ) ),
							media && media.source_url ? createElement( "img", {
								src: media.source_url,
								alt: media.alt_text || "",
								style: {
									display: "block",
									width: "100%",
									height: "auto",
									marginBottom: "12px"
								}
							} ) : null,
							! media && imageId ? createElement( Spinner ) : null,
							createElement(
								Button,
								{
									isSecondary: true,
									onClick: renderProps.open
								},
								imageId ? __( "Remplacer l'image OG", "greenlight" ) : __( "Choisir une image OG", "greenlight" )
							),
							imageId ? createElement(
								Button,
								{
									isLink: true,
									isDestructive: true,
									onClick: function() {
										updateMeta( META_KEYS.image, 0 );
									},
									style: {
										marginLeft: "12px"
									}
								},
								__( "Retirer l'image", "greenlight" )
							) : null
						);
					}
				} )
			),
			createElement( ToggleControl, {
				label: __( "Noindex", "greenlight" ),
				checked: !! meta[ META_KEYS.noindex ],
				onChange: function( value ) {
					updateMeta( META_KEYS.noindex, value );
				}
			} )
		);
	}

	registerPlugin( "greenlight-seo-sidebar", {
		render: SeoPanel
	} );
}( window.wp ) );
