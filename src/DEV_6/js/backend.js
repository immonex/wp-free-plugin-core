function iwpfpcSetActiveSectionTab( newSectionTab ) {
	jQuery( '.nav-tab-section' ).removeClass( 'nav-tab-active' );
	jQuery( '.tabbed-section' ).removeClass( 'is-active' );

	jQuery( '#section-nav-tab-' + newSectionTab ).addClass( 'nav-tab-active' );
	jQuery( '#tab-section-' + newSectionTab ).addClass( 'is-active' );

	let location = window.location.href
	if (location.indexOf('section_tab') !== -1) {
		location = location.replace(/\&section_tab=[0-9]+/, '&section_tab=' + newSectionTab)
	} else {
		location += '&section_tab=' + newSectionTab
	}
	window.history.replaceState(null, '', location);

	const ref = document.getElementsByName('_wp_http_referer')

	if (ref.length > 0) {
		if (ref[0].value.indexOf('section_tab') !== -1) {
			ref[0].value = ref[0].value.replace(/\&section_tab=[0-9]+/, '&section_tab=' + newSectionTab)
		} else if (ref[0].value.indexOf('#') !== -1) {
			ref[0].value = ref[0].value.replace(/\#/, '&section_tab=' + newSectionTab + '#')
		} else {
			ref[0].value += '&section_tab=' + newSectionTab
		}
	}
} // iwpfpcSetActiveSectionTab

function iwpfpcInit( iwpfpc_params ) {
	jQuery( document ).ready( function( $ ) {
		$( document ).on( 'click', '.' + iwpfpc_params.plugin_slug + '-notice .notice-dismiss', function() {
			let noticeId = $( this ).parent().data( 'notice-id' );
			if ( ! noticeId || ! iwpfpc_params.ajax_url ) return;

			var data = {
				action: 'dismiss_admin_notice',
				plugin_slug: iwpfpc_params.plugin_slug,
				notice_id: noticeId
			};

			$.post( iwpfpc_params.ajax_url, data, function() {} );
		} )
	} );
} // iwpfpcInit

iwpfpcInit( iwpfpc_params );