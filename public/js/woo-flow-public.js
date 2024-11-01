(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

    $( document ).ready(function ($) {

        var clicked_url = "";

        $("a").on("click", function () {
            clicked_url = $(this).attr("href");
        });

        $(window).bind('beforeunload', function(e){

            var nextUrl = document.activeElement.href ? document.activeElement.href : clicked_url;
            var isInSite = nextUrl.indexOf(window.location.origin);
            if (isInSite < 0) {
                trackLeavePage(nextUrl);
                if (e.currentTarget.performance.navigation.type !== 1) {
                    Cookies.remove('wf_last_visited', { path: '/' });
                    Cookies.remove('wf_session_id', { path: '/' });
                }
            } else if (nextUrl !== window.location.href) {
                trackLeavePage(nextUrl);
            }

        });

    });

    function trackLeavePage(nextUrl) {
        var session = Cookies.get('wf_session_id');
        session = session ? session : '1';
        $.ajax({
            url: woo_flow.ajax_url,
            async: false,
            type: 'POST',
            data: {
                current_url: window.location.href,
                next_url: nextUrl,
                session_id: session,
                action: 'tracking_leave_page'
            },
            success:function(data) {
                console.log(data);
            },
            error: function(errorThrown) {
                console.log(errorThrown);
            }
        });
    }

})( jQuery );
