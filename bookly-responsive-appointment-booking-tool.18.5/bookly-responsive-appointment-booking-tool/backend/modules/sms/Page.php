<?php
namespace Bookly\Backend\Modules\Sms;

use Bookly\Lib;

/**
 * Class Controller
 * @package Bookly\Backend\Modules\Sms
 */
class Page extends Lib\Base\Component
{
    /**
     * Render page.
     */
    public static function render()
    {
        if ( Lib\SMS::getInstance()->loadProfile() ) {
            return self::renderMain();
        } else {
            return self::renderRegistration();
        }
    }

    /**
     * Render registration page
     */
    protected static function renderRegistration()
    {
        self::enqueueStyles( array(
            'frontend' => array( 'css/ladda.min.css', 'css/intlTelInput.css' ),
            'backend'  => array( 'bootstrap/css/bootstrap.min.css', ),
        ) );

        self::enqueueScripts( array(
            'backend'  => array(
                'bootstrap/js/bootstrap.min.js' => array( 'jquery' ),
                'js/datatables.min.js'          => array( 'jquery' ),
                'js/alert.js'                   => array( 'jquery' ),
                'js/select2.min.js'             => array( 'jquery' ),
            ),
            'frontend' => array(
                'js/spin.min.js'  => array( 'jquery' ),
                'js/ladda.min.js' => array( 'jquery' ),
            ),
            'module' => array(
                'js/sms-common.js' => array( 'jquery' ),
                'js/sms-registration.js' => array( 'bookly-sms-common.js' ),
            )
        ) );

        $promotions = get_option( 'bookly_sms_promotions', array() );
        if ( isset ( $promotions['registration'] ) ) {
            $promotion = $promotions['registration'];
            $locale = Lib\Config::getLocale();
            $registration_promotion_texts = isset ( $promotion['texts'][ $locale ] ) ?
                array( 'form' => $promotion['texts'][ $locale ]['form'], 'button' => $promotion['texts'][ $locale ]['button'] ) :
                array( 'form' => $promotion['texts']['en']['form'], 'button' => $promotion['texts']['en']['button'] );
        } else {
            $registration_promotion_texts = array( 'form' => null, 'button' => null );
        }

        // Prepare tables settings.
        $datatables = Lib\Utils\Tables::getSettings( array(
            'sms_prices',
        ) );

        wp_localize_script( 'bookly-sms-common.js', 'BooklyL10n',
            array(
                'csrfToken'          => Lib\Utils\Common::getCsrfToken(),
                'passwords_no_match' => __( 'Passwords don\'t match', 'bookly' ),
                'noResults'          => __( 'No records.', 'bookly' ),
                'processing'         => __( 'Processing...', 'bookly' ),
                'datatables'         => $datatables
            )
        );

        self::renderTemplate( 'index_registration', compact( 'datatables', 'registration_promotion_texts' ) );
    }

    /**
     * Render main page (logged in)
     */
    protected static function renderMain()
    {
        $sms = Lib\SMS::getInstance();

        self::enqueueStyles( array(
            'frontend' => array( 'css/ladda.min.css', 'css/intlTelInput.css' ),
            'backend'  => array( 'bootstrap/css/bootstrap.min.css', ),
        ) );

        self::enqueueScripts( array(
            'backend'  => array(
                'bootstrap/js/bootstrap.min.js' => array( 'jquery' ),
                'js/datatables.min.js'          => array( 'jquery' ),
                'js/moment.min.js',
                'js/daterangepicker.js'         => array( 'jquery' ),
                'js/alert.js'                   => array( 'jquery' ),
                'js/select2.min.js'             => array( 'jquery' ),
            ),
            'frontend' => array_merge(
                array(
                    'js/spin.min.js'  => array( 'jquery' ),
                    'js/ladda.min.js' => array( 'jquery' ),
                ),
                get_option( 'bookly_cst_phone_default_country' ) == 'disabled'
                    ? array()
                    : array( 'js/intlTelInput.min.js' => array( 'jquery' ) )
            ),
            'module' => array(
                'js/sms-common.js' => array( 'jquery' ),
                'js/sms.js' => array( 'bookly-sms-common.js', ),
                'js/notifications-list.js' => array( 'jquery', ),
            )
        ) );

        if ( ! $sms->getCountry() ) {
            self::enqueueScripts( array(
                'module' => array( 'js/setup-country.js' => array( 'bookly-sms-common.js', ) )
            ) );
        }
        if ( ! $sms->getEmailConfirmed() ) {
            self::enqueueScripts( array(
                'module' => array( 'js/confirm-email.js' => array( 'jquery', ) )
            ) );
        }

        $current_tab = self::hasParameter( 'tab' ) ? self::parameter( 'tab' ) : 'notifications';

        // Prepare tables settings.
        $datatables = Lib\Utils\Tables::getSettings( array(
            'sms_notifications',
            'sms_purchases',
            'sms_details',
            'sms_prices',
            'sms_sender',
        ) );

        $l10n = array(
            'csrfToken'    => Lib\Utils\Common::getCsrfToken(),
            'areYouSure'   => __( 'Are you sure?', 'bookly' ),
            'country'      => $sms->getCountry(),
            'current_tab'  => $current_tab,
            'intlTelInput' => array(
                'country' => get_option( 'bookly_cst_phone_default_country' ),
                'utils'   => is_rtl() ? '' : plugins_url( 'intlTelInput.utils.js', Lib\Plugin::getDirectory() . '/frontend/resources/js/intlTelInput.utils.js' ),
                'enabled' => get_option( 'bookly_cst_phone_default_country' ) != 'disabled',
            ),
            'datePicker' => Lib\Utils\DateTime::datePickerOptions(),
            'dateRange'  => Lib\Utils\DateTime::dateRangeOptions( array( 'lastMonth' => __( 'Last month', 'bookly' ), ) ),
            'sender_id'  => array(
                'sent'        => __( 'Sender ID request is sent.', 'bookly' ),
                'set_default' => __( 'Sender ID is reset to default.', 'bookly' ),
            ),
            'zeroRecords' => __( 'No records for selected period.', 'bookly' ),
            'noResults'   => __( 'No records.', 'bookly' ),
            'processing'  => __( 'Processing...', 'bookly' ),
            'invoice'     => array(
                'button' => __( 'Invoice', 'bookly' ),
                'alert'  => __( 'To generate an invoice you should fill in company information in Bookly SMS Account settings -> Invoice', 'bookly' ),
                'link'   => $sms->getInvoiceLink(),
            ),
            'state'         => array( __( 'Disabled', 'bookly' ), __( 'Enabled', 'bookly' ) ),
            'action'        => array( __( 'enable', 'bookly' ), __( 'disable', 'bookly' ) ),
            'edit'          => __( 'Edit', 'bookly' ),
            'settingsSaved' => __( 'Settings saved.', 'bookly' ),
            'gateway'       => 'sms',
            'datatables'    => $datatables
        );
        if ( ! $sms->getEmailConfirmed() ) {
            $l10n += array(
                'confirm_email_code_resent' => __( 'An email containing the confirmation code has been sent to your email address.', 'bookly' ),
                'show_confirm_email_dialog' => ! get_user_meta( get_current_user_id(), 'bookly_dismiss_sms_confirm_email', true ),
            );
        }

        wp_localize_script( 'bookly-daterangepicker.js', 'BooklyL10n', $l10n );

        // Number of undelivered sms.
        $undelivered_count = Lib\SMS::getUndeliveredSmsCount();

        self::renderTemplate( 'index', compact( 'sms', 'undelivered_count', 'datatables' ) );
    }

    /**
     * Show 'SMS Notifications' submenu with counter inside Bookly main menu.
     */
    public static function addBooklyMenuItem()
    {
        $sms = __( 'SMS Notifications', 'bookly' );

        $promotion = Lib\SMS::getPromotionForNotice();
        if ( $promotion ) {
            $title = sprintf( '%s <span class="update-plugins"><span class="update-count">$</span></span>', $sms );
        } else {
            $count = Lib\SMS::getUndeliveredSmsCount();
            $title = $count ? sprintf( '%s <span class="update-plugins"><span class="update-count">%d</span></span>', $sms, $count ) : $sms;
        }

        add_submenu_page(
            'bookly-menu',
            $sms,
            $title,
            Lib\Utils\Common::getRequiredCapability(),
            self::pageSlug(),
            function () { Page::render(); }
        );
    }
}