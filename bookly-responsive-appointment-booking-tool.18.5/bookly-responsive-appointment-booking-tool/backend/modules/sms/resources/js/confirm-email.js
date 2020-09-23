jQuery(function ($) {
    'use strict';
    let $modal             = $('#bookly-confirm-email-modal'),
        $code              = $('#bookly-confirmation-code', $modal),
        $resend_button     = $('.bookly-js-resend-confirmation', $modal),
        $apply_button      = $('#bookly-apply-confirmation-code', $modal),
        $later_button      = $('.modal-footer button[data-dismiss="bookly-modal"]', $modal),
        $open_modal_button = $('#bookly-open-email-confirm'),
        $settings_button   = $('#bookly-open-account-settings');

    // Apply code.
    $apply_button.on('click', function (e) {
        e.preventDefault();
        let ladda = Ladda.create(this);
        ladda.start();
        $.post(
            ajaxurl,
            {
                action: 'bookly_apply_confirmation_code',
                code: $code.val(),
                csrf_token: BooklyL10n.csrfToken
            },
            function (response) {
                if (response.success) {
                    $open_modal_button.hide();
                    $settings_button.removeClass('btn-danger').addClass('btn-primary').find('i').removeClass('fa-user-slash').addClass('fa-user');
                    $modal.booklyModal('hide');
                } else {
                    booklyAlert({error: [response.data.message]});
                }
                ladda.stop();
            });
    });

    // Resend code.
    $resend_button.on('click', function (e) {
        e.preventDefault();
        $.post(
            ajaxurl,
            {
                action: 'bookly_resend_confirmation_code',
                csrf_token: BooklyL10n.csrfToken
            },
            function (response) {
                if (response.success) {
                    booklyAlert({success: [BooklyL10n.confirm_email_code_resent]})
                } else {
                    booklyAlert({error: [response.data.message]});
                }
            });
    });

    // I'll do it later button.
    $later_button.on('click', function (e) {
        $.post(
            ajaxurl,
            {
                action: 'bookly_dismiss_confirm_email',
                csrf_token: BooklyL10n.csrfToken
            });
    });

    // Open table settings modal.
    $open_modal_button.off().on('click', function () {
        $modal.booklyModal('show');
    });
    if (BooklyL10n.show_confirm_email_dialog) {
        $modal.booklyModal('show');
    }
});