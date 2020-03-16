jQuery( document ).ready( function( $ ) {
	jQuery( document ).on( 'click', '.immonex-notice .notice-dismiss', function() {
		let noticeId = $( this ).parent().data( 'notice-id' );
		if ( ! noticeId || ! iwpfpc_params.ajax_url ) return;

		var data = {
			action: 'dismiss_admin_notice',
			notice_id: noticeId
		};

		jQuery.post( iwpfpc_params.ajax_url, data, function() {
		} );
	} )
} );
