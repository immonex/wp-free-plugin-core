var immonex = immonex || {}
var iwpfpc_version = document.currentScript.id.match( /backend-core-([a-zA-Z0-9\-\_]+)-js/ )
var iwpfpc_subns = "default"

/**
 * Create a sub namespace based on the current core version.
 */
if ( iwpfpc_version && iwpfpc_version[1] ) {
	iwpfpc_subns = iwpfpc_version[1].replace( /\.|-/g, "_" )
	if ( iwpfpc_subns.match( /^[0-9]/ ) ) {
		iwpfpc_subns = "V" + iwpfpc_subns
	}
}

immonex[iwpfpc_subns] = immonex[iwpfpc_subns] || ( function( $ ) {
	/**
	 * Set/Show the currently active section tab in a plugin options page.
	 */
	const set_active_section_tab = function() {
		const new_section_tab = this.id.match( /[0-9]+$/ )[0]

		$( '.nav-tab-section' ).removeClass( 'nav-tab-active' );
		$( '.tabbed-section' ).removeClass( 'is-active' );

		$( '#section-nav-tab-' + new_section_tab ).addClass( 'nav-tab-active' );
		$( '#tab-section-' + new_section_tab ).addClass( 'is-active' );

		let location = window.location.href
		if (location.indexOf('section_tab') !== -1) {
			location = location.replace(/\&section_tab=[0-9a-z]+/, '&section_tab=' + new_section_tab)
		} else {
			location += '&section_tab=' + new_section_tab
		}
		window.history.replaceState(null, '', location);

		const refs = document.getElementsByName( "_wp_http_referer" )

		if ( refs.length ) {
			for ( const ref of refs ) {
				if (ref.value.indexOf('section_tab') !== -1) {
					ref.value = ref.value.replace(/\&section_tab=[0-9]+/, '&section_tab=' + new_section_tab)
				} else if (ref.value.indexOf('#') !== -1) {
					ref.value = ref.value.replace(/\#/, '&section_tab=' + new_section_tab + '#')
				} else {
					ref.value += '&section_tab=' + new_section_tab
				}
			}
		}
	} // set_active_section_tab

	/**
	 * Dismiss a "sticky" admin notice.
	 */
	const dismiss_admin_notice = function( event ) {
		let params = event.data.params

		let notice_id = $( this ).parent().data( 'notice-id' )
		if ( ! notice_id || ! params.ajax_url ) {
			return
		}

		const data = {
			action: 'dismiss_admin_notice',
			plugin_slug: params.plugin_slug,
			notice_id: notice_id
		}

		$.post( params.ajax_url, data )
	} // dismiss_admin_notice

	/**
	 * Preselect the files in the WP media dialog according to the current
	 * value of the related plugin option element.
	 */
	const set_selected_media_items = function( file_frame, field_id ) {
		let current_field_selection = $( "#" + field_id ).val().split( "," ).filter( ( el ) => el ? true : false )

		let selection = file_frame.state().get( "selection" )
		selection.reset()

		if ( current_field_selection.length > 0 ) {
			current_field_selection.forEach( function ( att_id ) {
				let attachment = wp.media.attachment( att_id )
				attachment.fetch()
				selection.add( attachment ? [attachment] : [] )
			} )
		}
	} // set_selected_media_items

	/**
	 * Register event listeners for selecting files using the WP media dialog.
	 */
	const register_media_select_cb = function( field, params ) {
		let file_frame
		let wp_media_post_id = wp.media.model.settings.post.id
		let set_to_post_id = 0

		$( "#" + field.id + "-select-button").on( "click", function( event ) {
			event.preventDefault()

			if ( file_frame ) {
				file_frame.uploader.uploader.param( "post_id", set_to_post_id )
				file_frame.open()
				return
			} else {
				wp.media.model.settings.post.id = set_to_post_id
			}

			let max_files = field.max_files || 1

			file_frame = wp.media.frames.file_frame = wp.media( {
				title: field.media_frame_title || params.default_media_frame_title,
				button: {
					text: field.media_frame_button_text || params.default_media_frame_button_text
				},
				multiple: max_files > 1 ? 'add' : false
			} )

			file_frame.on( "open", function() {
				set_selected_media_items( file_frame, field.id )
			} )

			file_frame.on( "select", function() {
				let media_wrapper = $( "#" + field.id + "-media-wrapper" )
				const selected_attachments = file_frame.state().get( "selection" ).toJSON()
				let attachment_ids = []

				media_wrapper.html("")

				for ( attachment of selected_attachments ) {
					attachment_ids.push( String( attachment.id ) )

					let attachment_url = typeof attachment.sizes.thumbnail.url !== "undefined" ?
						attachment.sizes.thumbnail.url : attachment.url

					media_wrapper.append(
						'<div class="immonex-plugin-options__thumbnail" data-field-id="' + field.id + '" data-att-id="' + attachment.id + '">' +
						'<img src="' + attachment_url + '" alt="thumbnail">' +
						'<div class="immonex-plugin-options__delete-icon"></div>' +
						'</div>'
					)
					media_wrapper.find( '*[data-att-id="' + attachment.id + '"] .immonex-plugin-options__delete-icon' ).on( "click", remove_media_attachment )

					if ( attachment_ids.length === max_files ) {
						break
					}
				}

				$( "#" + field.id ).val( attachment_ids.join( "," ) )

				wp.media.model.settings.post.id = wp_media_post_id
			} )

			file_frame.open()
		} )

		$( "a.add_media" ).on( "click", function() {
			wp.media.model.settings.post.id = wp_media_post_id;
		} )
	} // register_media_select_cb

	/**
	 * Remove an image selected on a plugin options page.
	 */
	const remove_media_attachment = function() {
		field_id = $( this ).parent().data( "fieldId" )
		attachment_id = String( $( this ).parent().data( "attId" ) )

		if ( ! field_id || ! attachment_id ) {
			return
		}

		let current_selection = $( "#" + field_id ).val().split( "," )
		const index = current_selection.indexOf( attachment_id )

		if ( index > -1 ) {
			current_selection.splice( index, 1 )
			$( "#" + field_id ).val( current_selection.join( "," ) )
			$( "#" + field_id + '-media-wrapper *[data-att-id="' + attachment_id + '"]' ).remove()
		}
	} // remove_media

	/**
	 * Register common event listeners (once) with current instance parameters.
	 */
	const init = function( params ) {
		$( document ).ready( function() {
			const core_version_slug = params["core_version"].replaceAll(".", "-").toLowerCase()

			$( "." + params.plugin_slug + "-notice .notice-dismiss" ).each( function() {
				if (
					! $._data( this, "events" )
					|| ! $._data( this, "events" )["click"]
					|| $._data( this, "events" )["click"].length < 2
				) {
					$( this ).on( "click", { params }, dismiss_admin_notice )
				}
			} )

			$( ".immonex-plugin-options.immonex-plugin-core-" + core_version_slug + " .nav-tab.nav-tab-section" ).each( function() {
				if ( ! $._data( this, "events" ) ) {
					// Remove previously set event listeners (possibly by an older core version).
					$( this ).off( "click" )
				}
				$( this ).on( "click", set_active_section_tab )
			} )

			if ( params.media_fields.length > 0 ) {
				for ( field of params.media_fields ) {
					let select_button = document.getElementById( field.id + "-select-button" )
					if ( select_button && ! $._data( select_button, "events" ) ) {
						register_media_select_cb( field, params )
					}
				}
			}

			$( ".immonex-plugin-options__thumbnail .immonex-plugin-options__delete-icon" ).each( function() {
				if ( ! $._data( this, "events" ) ) {
					$( this ).on( "click", remove_media_attachment )
				}
			} )

			$( ".immonex-plugin-options__ext-description-show button" ).each( function() {
				if ( ! $._data( this, "events" ) ) {
					$( this ).on( "click", function( event ) {
						event.preventDefault()
						$( this ).parent().parent().addClass( "immonex-plugin-options__ext-description--is-visible" )
					} )
				}
			} )

			$( ".immonex-plugin-options__ext-description-hide button" ).each( function() {
				if ( ! $._data( this, "events" ) ) {
					$( this ).on( "click", function( event ) {
						event.preventDefault()
						$( this ).parent().parent().removeClass( "immonex-plugin-options__ext-description--is-visible" )
					} )
				}
			} )
		} )
	} // init

	return {
		init,
		set_active_section_tab
	}
} )( jQuery )

immonex[iwpfpc_subns].init( iwpfpc_params )
