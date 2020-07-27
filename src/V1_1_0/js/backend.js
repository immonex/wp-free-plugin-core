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