/* global terawallet_export_transaction_admin */

jQuery(function ($) {
    var $wallet_screen = $('.toplevel_page_woo-wallet'),
            $title_action = $wallet_screen.find('.wrap h2:first');
    $title_action.html($title_action.html() + ' <a href="' + terawallet_export_transaction_admin.url + '" class="page-title-action">' + terawallet_export_transaction_admin.title + '</a>');
});