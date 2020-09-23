jQuery(function($) {
    'use strict';

    const
        $dialog  = $('#bookly-setup-country'),
        $country = $('#bookly-s-country'),
        $setBtn  = $('#bookly-set-country'),
        settings = {
            $country: $('#bookly-country'),
            $invoiceCountry: $('.bookly-js-invoice-country .bookly-js-label')
        }
    ;

    $country.booklySelectCountry({dropdownParent: $dialog});
    $.get('https://ipinfo.io', function() {}, 'jsonp').always(function(resp) {
        const countryCode = (resp && resp.country) ? resp.country : '';
        $country.val(countryCode.toLowerCase()).trigger('change');
    });

    /**
     * Setup country dialog
     */
    $dialog.booklyModal('show');
    $setBtn.on('click', function () {
        const ladda = Ladda.create(this);
        ladda.start();
        const country = $country.val();
        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action: 'bookly_change_country',
                csrf_token: BooklyL10n.csrfToken,
                country: country
            },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    booklyAlert({success: [BooklyL10n.settingsSaved]});
                    BooklyL10n.country = country;
                    if (BooklyRechargeDialogL10n) {
                        BooklyRechargeDialogL10n.country = country;
                    }
                    settings.$country.val(country).trigger('change');
                    settings.$invoiceCountry.html($country.select2('data')[0].text);
                    if (response.auto_recharge === 'disabled') {
                        $(document.body).trigger('bookly.auto-recharge.toggle', [false]);
                    }
                    $dialog.booklyModal('hide');
                } else {
                    if (response.data && response.data.message) {
                        booklyAlert({error: [response.data.message]});
                    }
                }
            }
        }).always(ladda.stop);
    });
});