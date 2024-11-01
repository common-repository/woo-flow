(function ($) {
    'use strict';

    /**
     * All of the code for your admin-facing JavaScript source
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

    function checkAllItemsChecked() {
        var allChecked = $('.select-list input:checked').length === $('.select-list input').length;
        $('#allPages').prop('checked', allChecked);
    }

    function settingSelectCheckAll() {

        $('#allPages').on('change', function () {
            $('.select-list input[type="checkbox"]').prop('checked', this.checked);
        });

        $('.select-list input[type="checkbox"]').on('change', function () {
            checkAllItemsChecked();
        });

    }

    settingSelectCheckAll();
    checkAllItemsChecked();

    $('.custom_date').datepicker({
        dateFormat: 'yy-mm-dd'
    });

    var clickedResetButton;
    $("#dialog-confirm").dialog({
        resizable: false,
        autoOpen: false,
        height: "auto",
        width: 320,
        modal: false,
        buttons: {
            "Delete": function () {
                $('#'+ clickedResetButton).trigger('click');
                $(this).dialog("close");
            },
            Cancel: function () {
                $(this).dialog("close");
            }
        }
    }).parents(".ui-dialog").find(".ui-dialog-titlebar").remove();

    $(document).on('click', '.btn-wf-reset-data', function () {
        clickedResetButton = $(this).attr('name');
        $('#dialog-confirm').dialog('open');
    });

    $(document).on('click', '.wf-dissmiss', function () {
        $.ajax({
            url: woo_flow.ajax_url,
            type: 'POST',
            data: {action: 'wf_dissmiss_onemonth_message'},
            success:function(data) {
                console.log(data);
            },
            error: function(errorThrown) {
                console.log(errorThrown);
            }
        });
        $(this).closest('div.notice').hide('normal', function(){
            $(this).remove();
        });
    });

})(jQuery);
