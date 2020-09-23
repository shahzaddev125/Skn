jQuery(function($) {
    'use strict';

    $('#bookly-logout').on('click', function () {
        let ladda = Ladda.create(this);
        ladda.start();
        $.ajax({
            method: 'POST',
            url: ajaxurl,
            data: {
                action: 'bookly_sms_logout',
                csrf_token : BooklyL10n.csrfToken,
            },
            dataType: 'json',
            success: function () {
                window.location.reload();
            }
        });
    });

    /**
     * Account settings
     */
    const $openSettingsBtn = $('#bookly-open-account-settings');
    const $invoiceCountry  = $('.bookly-js-invoice-country .bookly-js-label');
    const $country         = $('#bookly-country');

    let invoiceDataValid = true;

    if ($openSettingsBtn.data('content')) {
        $openSettingsBtn
            .on('click', function () {
                $openSettingsBtn.booklyPopover('hide');
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'bookly_dismiss_sms_account_settings_notice',
                        csrf_token: BooklyL10n.csrfToken
                    }
                });
            })
            .booklyPopover({
                html: true,
                sanitize: false,
                trigger: 'manual',
                container: '#bookly-tbs',
                template: '<div class="bookly-popover" role="tooltip"><div class="arrow"></div><div class="popover-body"></div></div>',
                placement: function (tip) {
                    $(tip)
                        .find('.popover-body button').on('click', function () {
                        $openSettingsBtn.booklyPopover('hide');
                        $.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'bookly_dismiss_sms_account_settings_notice',
                                csrf_token: BooklyL10n.csrfToken
                            }
                        });
                    });

                    return 'bottom';
                }
            })
            .booklyPopover('show');
    }

    /**
     * Settings: Country tab
     */
    $country
        .booklySelectCountry({dropdownParent: '#bookly-account-settings'})
        .val(BooklyL10n.country).trigger('change')
    ;

    $('#bookly-update-country').on('click', function () {
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
                    $invoiceCountry.html($country.select2('data')[0].text);
                    if (response.auto_recharge === 'disabled') {
                        $(document.body).trigger('bookly.auto-recharge.toggle', [false]);
                    }
                } else {
                    if (response.data && response.data.message) {
                        booklyAlert({error: [response.data.message]});
                    }
                }
            }
        }).always(ladda.stop);
    });

    /**
     * Settings: Invoice tab
     */
    if ($country.length && $country.select2('data').length > 0) {
        $invoiceCountry.html($country.select2('data')[0].text);
    }

    $('.bookly-js-invoice-country a').on('click', function (e) {
        e.preventDefault();
        $('#bookly-account-settings .nav-link[href=#bookly-country-tab]').trigger('click');
    });

    $('#bookly-save-invoice').on('click', function (e) {
        e.preventDefault();
        const $form = $(this).closest('form');
        invoiceDataValid = true;
        $('input[required]', $form).each(function () {
            if ($(this).val() === '') {
                $(this).addClass('is-invalid');
                invoiceDataValid = false;
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        if (invoiceDataValid) {
            const ladda = Ladda.create(this);
            ladda.start();
            const data = $form.serializeArray();
            data.push({name: 'action', value: 'bookly_save_invoice_data'});
            data.push({name: 'csrf_token', value: BooklyL10n.csrfToken});
            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: data,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        booklyAlert({success: [BooklyL10n.settingsSaved]});
                    } else {
                        booklyAlert({error: [response.data.message]});
                    }
                }
            }).always(ladda.stop);
        } else {
            return false;
        }
    }).closest('form').find('input[required]').each(function () {
        if (this.value === '') {
            invoiceDataValid = false;
        }
    });

    /**
     * Settings: Notifications tab
     */
    $('#bookly-account-notifications-tab :checkbox').on('change', function () {
        let $checkbox = $(this);
        $checkbox.prop('disabled', true).addClass('bookly-checkbox-loading');
        $.get(
            ajaxurl,
            {
                action: 'bookly_admin_notify',
                csrf_token : BooklyL10n.csrfToken,
                option_name: $checkbox.attr('name'),
                value: $checkbox.is(':checked') ? 1 : 0
            },
            function () {},
            'json'
        ).always(function () {
            $checkbox.prop('disabled', false).removeClass('bookly-checkbox-loading');
        });
    });

    /**
     * Settings: Change password tab
     */
    $('#bookly-change-password').on('click', function (e) {
        e.preventDefault();
        const $form = $(this).closest('form');
        const $oldPassword = $form.find('#old_password');
        const $newPassword = $form.find('#new_password');
        const $repeatPassword = $form.find('#new_password_repeat');
        $oldPassword.toggleClass('is-invalid', $oldPassword.val() === '');
        $newPassword.toggleClass('is-invalid', $newPassword.val() === '');
        if ($oldPassword.val() !== '' && $newPassword.val() !== '') {
            if ($newPassword.val() === $repeatPassword.val()) {
                $newPassword.removeClass('is-invalid');
                $repeatPassword.removeClass('is-invalid');
                const ladda = Ladda.create(this);
                ladda.start();
                const data = $form.serializeArray();
                data.push({name: 'action', value: 'bookly_change_password'});
                data.push({name: 'csrf_token', value: BooklyL10n.csrfToken});
                $.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    data: data,
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            booklyAlert({success: [BooklyL10n.settingsSaved]});
                            $form.trigger('reset');
                        } else {
                            if (response.data && response.data.message) {
                                booklyAlert({error: [response.data.message]});
                            }
                        }
                    }
                }).always(ladda.stop);
            } else {
                booklyAlert({error: [BooklyL10n.passwords_no_match]});
                $newPassword.addClass('is-invalid');
                $repeatPassword.addClass('is-invalid');
            }
        }
    });

    /**
     * Notifications Tab
     */
    var $phone_input = $('#admin_phone');
    if (BooklyL10n.intlTelInput.enabled) {
        $phone_input.intlTelInput({
            preferredCountries: [BooklyL10n.intlTelInput.country],
            initialCountry: BooklyL10n.intlTelInput.country,
            geoIpLookup: function (callback) {
                $.get('https://ipinfo.io', function() {}, 'jsonp').always(function(resp) {
                    var countryCode = (resp && resp.country) ? resp.country : '';
                    callback(countryCode);
                });
            },
            utilsScript: BooklyL10n.intlTelInput.utils
        });
    }
    $('#bookly-js-submit-notifications').on('click', function (e) {
        e.preventDefault();
        var ladda = Ladda.create(this);
        ladda.start();
        var $form = $(this).parents('form');
        $form.bookly_sms_administrator_phone = getPhoneNumber();
        $form.submit();
    });
    $('#send_test_sms').on('click', function (e) {
        e.preventDefault();
        $.ajax({
            url: ajaxurl,
            data: {
                action: 'bookly_send_test_sms',
                csrf_token : BooklyL10n.csrfToken,
                phone_number: getPhoneNumber() },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    booklyAlert({success: [response.message]});
                } else {
                    booklyAlert({error: [response.message]});
                }
            }
        });
    });

    $('[data-action=save-administrator-phone]')
        .on('click', function (e) {
            e.preventDefault();
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'bookly_save_administrator_phone',
                    bookly_sms_administrator_phone: getPhoneNumber(),
                    csrf_token: BooklyL10n.csrfToken
                },
                success: function (response) {
                    if (response.success) {
                        booklyAlert({success: [BooklyL10n.settingsSaved]});
                    }
                }
            });
        });

    function getPhoneNumber() {
        var phone_number;
        try {
            phone_number = BooklyL10n.intlTelInput.enabled ? $phone_input.intlTelInput('getNumber') : $phone_input.val();
            if (phone_number == '') {
                phone_number = $phone_input.val();
            }
        } catch (error) {  // In case when intlTelInput can't return phone number.
            phone_number = $phone_input.val();
        }

        return phone_number;
    }

    /**
     * Date range pickers options.
     */
    var picker_ranges = {};
    picker_ranges[BooklyL10n.dateRange.yesterday] = [moment().subtract(1, 'days'), moment().subtract(1, 'days')];
    picker_ranges[BooklyL10n.dateRange.today]     = [moment(), moment()];
    picker_ranges[BooklyL10n.dateRange.last_7]    = [moment().subtract(7, 'days'), moment()];
    picker_ranges[BooklyL10n.dateRange.last_30]   = [moment().subtract(30, 'days'), moment()];
    picker_ranges[BooklyL10n.dateRange.thisMonth] = [moment().startOf('month'), moment().endOf('month')];
    picker_ranges[BooklyL10n.dateRange.lastMonth] = [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')];
    var locale = $.extend({},BooklyL10n.dateRange, BooklyL10n.datePicker);

    /**
     * Purchases Tab.
     */
    $('[href="#purchases"]').one('click', function() {
        var $date_range = $('#purchases_date_range');
        $date_range.daterangepicker(
            {
                parentEl : $date_range.parent(),
                startDate: moment().subtract(30, 'days'), // by default select "Last 30 days"
                ranges   : picker_ranges,
                locale   : locale,
                showDropdowns: true,
                linkedCalendars: false,
            },
            function (start, end) {
                var format = 'YYYY-MM-DD';
                $date_range
                    .data('date', start.format(format) + ' - ' + end.format(format))
                    .find('span')
                    .html(start.format(BooklyL10n.dateRange.format) + ' - ' + end.format(BooklyL10n.dateRange.format));
            }
        );

        /**
         * Init Columns.
         */
        let columns = [];

        $.each(BooklyL10n.datatables.sms_purchases.settings.columns, function (column, show) {
            if (show) {
                if (column === 'amount') {
                    columns.push({
                        data: column,
                        render: function (data, type, row, meta) {
                            const disabled = ['Pending','Rejected','Cancelled reversal'].includes(row.status);
                            return data >= 0
                                ? '<span class="text-' + (disabled ? 'muted' : 'success') + '">+ $' + data + '</span>'
                                : '<span class="text-' + (disabled ? 'muted' : 'danger') + '">- $' + data.substring(1) + '</span>';
                        }
                    });
                } else {
                    columns.push({data: column});
                }
            }
        });
        columns.push({
            className: "text-right",
            render   : function (data, type, row, meta) {
                if ((row.type === 'PayPal' || row.type === 'Card') && row.status === 'Paid') {
                    return '<button type="button" class="btn btn-default" data-action="download-invoice"><i class="far fa-fw fa-file-pdf mr-1"></i> ' + BooklyL10n.invoice.button + '</button>';
                }
                return '';
            }
        });

        var dt = $('#bookly-purchases').DataTable({
            ordering: false,
            paging: false,
            info: false,
            searching: false,
            processing: true,
            responsive: true,
            ajax: {
                url : ajaxurl,
                data: function (d) {
                    return {
                        action: 'bookly_get_purchases_list',
                        csrf_token: BooklyL10n.csrfToken,
                        range:  $date_range.data('date')
                    };
                },
                dataSrc: 'list'
            },
            columns: columns,
            language: {
                zeroRecords: BooklyL10n.zeroRecords,
                processing:  BooklyL10n.processing
            }
        });

        $date_range.on('apply.daterangepicker', function () { dt.ajax.reload(); });
    });

    $('#bookly-purchases')
        .on('click', '[data-action=download-invoice]', function () {
            if (invoiceDataValid) {
                const data = $('#bookly-purchases').DataTable().row($(this).closest('td')).data();
                window.location = BooklyL10n.invoice.link + '/' + data.id;
            } else {
                booklyAlert({error: [BooklyL10n.invoice.alert]});
            }
        });

    /**
     * SMS Details Tab.
     */
    $('[href="#sms_details"]').one('click', function() {
        var $date_range = $('#sms_date_range');
        $date_range.daterangepicker(
            {
                parentEl : $date_range.parent(),
                startDate: moment().subtract(30, 'days'), // by default select "Last 30 days"
                ranges   : picker_ranges,
                locale   : locale,
                showDropdowns: true,
                linkedCalendars: false,
            },
            function (start, end) {
                var format = 'YYYY-MM-DD';
                $date_range
                    .data('date', start.format(format) + ' - ' + end.format(format))
                    .find('span')
                    .html(start.format(BooklyL10n.dateRange.format) + ' - ' + end.format(BooklyL10n.dateRange.format));
            }
        );

        /**
         * Init Columns.
         */
        let columns = [];

        $.each(BooklyL10n.datatables.sms_details.settings.columns, function (column, show) {
            if (show) {
                columns.push({data: column});
            }
        });
        if (columns.length) {
            var dt = $('#bookly-sms').DataTable({
                ordering  : false,
                paging    : false,
                info      : false,
                searching : false,
                processing: true,
                responsive: true,
                ajax      : {
                    url    : ajaxurl,
                    data   : function (d) {
                        return {
                            action    : 'bookly_get_sms_list',
                            csrf_token: BooklyL10n.csrfToken,
                            range     : $date_range.data('date')
                        };
                    },
                    dataSrc: 'list'
                },
                columns   : columns,
                language  : {
                    zeroRecords: BooklyL10n.zeroRecords,
                    processing : BooklyL10n.processing
                }
            });
        }

        $date_range.on('apply.daterangepicker', function () { dt.ajax.reload(); });
        $(this).on('click', function () { dt.ajax.reload(); });
    });

    /**
     * Prices Tab.
     */
    $("[href='#price_list']").one('click', function() {
        $('#bookly-prices').booklySmsPrices();
    });

    /**
     * Sender ID Tab.
     */
    $("[href='#sender_id']").one('click', function() {
        var $request_sender_id = $('#bookly-request-sender_id'),
            $reset_sender_id   = $('#bookly-reset-sender_id'),
            $cancel_sender_id  = $('#bookly-cancel-sender_id'),
            $sender_id         = $('#bookly-sender-id-input');

        /**
         * Init Columns.
         */
        let columns = [];

        $.each(BooklyL10n.datatables.sms_sender.settings.columns, function (column, show) {
            if (show) {
                columns.push({data: column});
            }
        });
        if (columns.length) {
            var dt = $('#bookly-sender-ids').DataTable({
                ordering  : false,
                paging    : false,
                info      : false,
                searching : false,
                processing: true,
                responsive: true,
                ajax      : {
                    url    : ajaxurl,
                    data   : {action: 'bookly_get_sender_ids_list', csrf_token: BooklyL10n.csrfToken},
                    dataSrc: function (json) {
                        if (json.pending) {
                            $sender_id.val(json.pending);
                            $request_sender_id.hide();
                            $sender_id.prop('disabled', true);
                            $cancel_sender_id.show();
                        }

                        return json.list;
                    }
                },
                columns   : columns,
                language  : {
                    zeroRecords: BooklyL10n.zeroRecords2,
                    processing : BooklyL10n.processing
                }
            });
        }

        $request_sender_id.on('click', function () {
            var ladda = Ladda.create(this);
            ladda.start();
            $.ajax({
                url  : ajaxurl,
                data : {action: 'bookly_request_sender_id', csrf_token : BooklyL10n.csrfToken, 'sender_id': $sender_id.val()},
                dataType : 'json',
                success: function (response) {
                    if (response.success) {
                        booklyAlert({success: [BooklyL10n.sender_id.sent]});
                        $request_sender_id.hide();
                        $sender_id.prop('disabled',true);
                        $cancel_sender_id.show();
                        dt.ajax.reload();
                    } else {
                        booklyAlert({error: [response.data.message]});
                    }
                }
            }).always(function () {
                ladda.stop();
            });
        });

        $reset_sender_id.on('click', function (e) {
            e.preventDefault();
            if (confirm(BooklyL10n.areYouSure)) {
                $.ajax({
                    url: ajaxurl,
                    data: {action: 'bookly_reset_sender_id', csrf_token : BooklyL10n.csrfToken},
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            booklyAlert({success: [BooklyL10n.sender_id.set_default]});
                            $('.bookly-js-sender-id').html('Bookly');
                            $('.bookly-js-approval-date').remove();
                            $sender_id.prop('disabled', false).val('');
                            $request_sender_id.show();
                            $cancel_sender_id.hide();
                            dt.ajax.reload();
                        } else {
                            booklyAlert({error: [response.data.message]});
                        }
                    }
                });
            }
        });

        $cancel_sender_id.on('click',function () {
            if (confirm(BooklyL10n.areYouSure)) {
                var ladda = Ladda.create(this);
                ladda.start();
                $.ajax({
                    method: 'POST',
                    url: ajaxurl,
                    data: {action: 'bookly_cancel_sender_id', csrf_token : BooklyL10n.csrfToken},
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            $sender_id.prop('disabled', false).val('');
                            $request_sender_id.show();
                            $cancel_sender_id.hide();
                            dt.ajax.reload();
                        } else {
                            if (response.data && response.data.message) {
                                booklyAlert({error: [response.data.message]});
                            }
                        }
                    }
                }).always(function () {
                    ladda.stop();
                });
            }
        });
        $(this).on('click', function () { dt.ajax.reload(); });
    });

    $('#bookly-open-tab-sender-id').on('click', function (e) {
        e.preventDefault();
        $('#sms_tabs li a[href="#sender_id"]').trigger('click');
    });

    $('[href="#' + BooklyL10n.current_tab + '"]').click();
});