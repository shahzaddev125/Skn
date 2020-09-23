jQuery(function ($) {
    let $notice = $('#bookly-sms-promotion-notice'),
        type    = $notice.data('type'),
        id      = $notice.data('id');
    $notice.on('close.bs.alert', function () {
        $.post(ajaxurl, {
            action: 'bookly_dismiss_sms_promotion_notice',
            id: id,
            dismiss: $notice.data('dismiss'),
            csrf_token: BooklySmsPromotionL10n.csrfToken
        });
    });
    $notice.find('.bookly-js-remind-me-later').on('click', function () {
        $notice.data('dismiss', 'remind').alert('close');
    });
    $notice.find('.bookly-js-apply-action').on('click', function () {
        switch (type) {
            case 'registration':
                let $form  = $('.bookly-js-register-form'),
                    $input = $('#bookly-r-username', $form);
                $form.closest('.card').addClass('border-success');
                $('html, body').animate({
                    scrollTop: $input.offset().top - 20
                }, 1000);
                $input.focus();
                break;
            default:
                let $modal = $('#bookly-js-recharge-modal');
                $modal.booklyModal();
                $('.bookly-js-back', $modal).trigger('click');
                break;
        }
        $notice.alert('close');
    });
});