jQuery(function($) {
    'use strict';

    let $form_forgot   = $('.bookly-js-forgot-form'),
        $form_register = $('.bookly-js-register-form'),
        $form_login    = $('.bookly-js-login-form')
    ;

    $('.bookly-js-show-register-form').on('click', function (e) {
        e.preventDefault();
        $form_login.hide();
        $form_register.show();
        $form_forgot.hide();
    });

    $('#bookly-r-country').booklySelectCountry();
    $.get('https://ipinfo.io', function() {}, 'jsonp').always(function(resp) {
        const countryCode = (resp && resp.country) ? resp.country : '';
        $('#bookly-r-country').val(countryCode.toLowerCase()).trigger('change');
    });

    $('.bookly-js-show-login-form').on('click', function (e) {
        e.preventDefault();
        $form_login.show();
        $form_register.hide();
        $form_forgot.hide();
    });

    $('.bookly-js-show-forgot-form').on('click', function (e) {
        e.preventDefault();
        $form_forgot.show();
        $form_login.hide();
        $form_register.hide();
    });

    $('.bookly-js-form-forgot-next').on('click', function (e) {
        e.preventDefault();
        var $btn  = $(this),
            $form = $(this).parents('form'),
            $code = $form.find('input[name="code"]'),
            $pwd  = $form.find('input[name="password"]'),
            $username   = $form.find('input[name="username"]'),
            $pwd_repeat = $form.find('input[name="password_repeat"]'),
            data  = { action: 'bookly_forgot_password', step: $btn.data('step'), username: $username.val(), csrf_token : BooklyL10n.csrfToken };
        switch ($(this).data('step')) {
            case 0:
                forgot_helper( data, function() {
                    $username.parent().addClass('hidden');
                    $code.parent().removeClass('hidden');
                    $btn.data('step', 1);
                });
                break;
            case 1:
                data.code = $code.val();
                forgot_helper(data, function() {
                    $code.parent().addClass('hidden');
                    $pwd.parent().removeClass('hidden');
                    $pwd_repeat.parent().removeClass('hidden');
                    $btn.data('step', 2);
                });
                break;
            case 2:
                data.code = $code.val();
                data.password = $pwd.val();
                data.password_repeat = $pwd_repeat.val();
                if (data.password === data.password_repeat && data.password !== '') {
                    forgot_helper(data, function() {
                        $('.bookly-js-show-login-form').trigger('click');
                        $btn.data('step', 0);
                        $username.parent().removeClass('hidden');
                        $pwd.parent().addClass('hidden');
                        $pwd_repeat.parent().addClass('hidden');
                        $form.trigger('reset');
                    });
                } else {
                    booklyAlert({error: [BooklyL10n.passwords_no_same]});
                }
                break;
        }
    });

    $form_login.on('submit', function (e) {
        e.preventDefault();
        let ladda = Ladda.create(this.elements.submit);
        ladda.start();
        $.ajax({
            method: 'POST',
            url: ajaxurl,
            data: {
                action: 'bookly_sms_login',
                username: $('.bookly-js-login-form [name="username"]').val(),
                password: $('.bookly-js-login-form [name="password"]').val(),
                csrf_token : BooklyL10n.csrfToken,
            },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    window.location.reload();
                } else {
                    if (response.data && response.data.message) {
                        booklyAlert({error: [response.data.message]});
                    }
                    ladda.stop();
                }
            }
        });
    });

    $form_register.on('submit', function (e) {
        e.preventDefault();
        let ladda = Ladda.create(this.elements['form-registration']);
        ladda.start();
        $.ajax({
            method: 'POST',
            url: ajaxurl,
            data: {
                action: 'bookly_sms_register',
                username: $('.bookly-js-register-form [name="username"]').val(),
                password: $('.bookly-js-register-form [name="password"]').val(),
                password_repeat: $('.bookly-js-register-form [name="password_repeat"]').val(),
                country: $('.bookly-js-register-form [name="country"]').val(),
                accept_tos: $('.bookly-js-register-form [name="accept_tos"]').prop('checked'),
                csrf_token : BooklyL10n.csrfToken,
            },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    window.location.reload();
                } else {
                    if (response.data && response.data.message) {
                        booklyAlert({error: [response.data.message]});
                    }
                    ladda.stop();
                }
            }
        });
    });

    function forgot_helper(data, callback) {
        $.ajax({
            method: 'POST',
            url: ajaxurl,
            data: data,
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    callback();
                } else {
                    if (response.data && response.data.message) {
                        booklyAlert({error: [response.data.message]});
                    }
                }
            }
        });
    }

    $('#bookly-prices').booklySmsPrices();
});