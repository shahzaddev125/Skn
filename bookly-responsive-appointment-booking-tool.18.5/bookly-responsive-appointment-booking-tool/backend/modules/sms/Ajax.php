<?php
namespace Bookly\Backend\Modules\Sms;

use Bookly\Lib;

/**
 * Class Ajax
 * @package Bookly\Backend\Modules\Sms
 */
class Ajax extends Lib\Base\Ajax
{
    /**
     * @inheritdoc
     */
    protected static function permissions()
    {
        return array(
            'sendQueue'        => array( 'supervisor', 'staff' ),
            'clearAttachments' => array( 'supervisor', 'staff' ),
        );
    }

    /**
     * Get purchases list.
     */
    public static function getPurchasesList()
    {
        $dates = explode( ' - ', self::parameter( 'range' ), 2 );
        $start = Lib\Utils\DateTime::applyTimeZoneOffset( $dates[0], 0 );
        $end   = Lib\Utils\DateTime::applyTimeZoneOffset( date( 'Y-m-d', strtotime( '+1 day', strtotime( $dates[1] ) ) ), 0 );

        wp_send_json( Lib\SMS::getInstance()->getPurchasesList( $start, $end ) );
    }

    /**
     * Get SMS list.
     */
    public static function getSmsList()
    {
        $dates = explode( ' - ', self::parameter( 'range' ), 2 );
        $start = Lib\Utils\DateTime::applyTimeZoneOffset( $dates[0], 0 );
        $end   = Lib\Utils\DateTime::applyTimeZoneOffset( date( 'Y-m-d', strtotime( '+1 day', strtotime( $dates[1] ) ) ), 0 );

        wp_send_json( Lib\SMS::getInstance()->getSmsList( $start, $end ) );
    }

    /**
     * Get price-list.
     */
    public static function getPriceList()
    {
        wp_send_json( Lib\SMS::getInstance()->getPriceList() );
    }

    /**
     * Change country.
     */
    public static function changeCountry()
    {
        $country = self::parameter( 'country' );

        $result = Lib\SMS::getInstance()->changeCountry( $country );
        if ( $result === false ) {
            wp_send_json_error( array( 'message' => current( Lib\SMS::getInstance()->getErrors() ) ) );
        } else {
            wp_send_json( $result );
        }
    }

    /**
     * Change password.
     */
    public static function changePassword()
    {
        $old_password = self::parameter( 'old_password' );
        $new_password = self::parameter( 'new_password' );

        $result = Lib\SMS::getInstance()->changePassword( $new_password, $old_password );
        if ( $result === false ) {
            wp_send_json_error( array( 'message' => current( Lib\SMS::getInstance()->getErrors() ) ) );
        } else {
            wp_send_json_success();
        }
    }

    /**
     * Send test SMS.
     */
    public static function sendTestSms()
    {
        $sms = Lib\SMS::getInstance();
        $response = array( 'success' => $sms->sendSms(
            self::parameter( 'phone_number' ),
            'Bookly test SMS.',
            'Bookly test SMS.',
            Lib\Entities\Notification::$type_ids['test_message']
        ) );

        if ( $response['success'] ) {
            $response['message'] = __( 'SMS has been sent successfully.', 'bookly' );
        } else {
            $response['message'] = implode( ' ', $sms->getErrors() );
        }

        wp_send_json( $response );
    }

    /**
     * Forgot password.
     */
    public static function forgotPassword()
    {
        $sms      = Lib\SMS::getInstance();
        $step     = self::parameter( 'step' );
        $code     = self::parameter( 'code' );
        $username = self::parameter( 'username' );
        $password = self::parameter( 'password' );
        $result   = $sms->forgotPassword( $username, $step, $code, $password );
        if ( $result === false ) {
            $errors = $sms->getErrors();
            wp_send_json_error( array( 'code' => key( $errors ), 'message' => current( $errors ) ) );
        } else {
            wp_send_json_success();
        }
    }

    /**
     * Get Sender IDs list.
     */
    public static function getSenderIdsList()
    {
        wp_send_json( Lib\SMS::getInstance()->getSenderIdsList() );
    }

    /**
     * Request new Sender ID.
     */
    public static function requestSenderId()
    {
        $sms    = Lib\SMS::getInstance();
        $result = $sms->requestSenderId( self::parameter( 'sender_id' ) );
        if ( $result === false ) {
            wp_send_json_error( array( 'message' => current( $sms->getErrors() ) ) );
        } else {
            wp_send_json_success( array( 'request_id' => $result['request_id'] ) );
        }
    }

    /**
     * Cancel request for Sender ID.
     */
    public static function cancelSenderId()
    {
        $sms    = Lib\SMS::getInstance();
        $result = $sms->cancelSenderId();
        if ( $result === false ) {
            wp_send_json_error( array( 'message' => current( $sms->getErrors() ) ) );
        } else {
            wp_send_json_success();
        }
    }

    /**
     * Reset Sender ID to default (Bookly).
     */
    public static function resetSenderId()
    {
        $sms    = Lib\SMS::getInstance();
        $result = $sms->resetSenderId();
        if ( $result === false ) {
            wp_send_json_error( array( 'message' => current( $sms->getErrors() ) ) );
        } else {
            wp_send_json_success();
        }
    }

    /**
     * Send client info for invoice.
     */
    public static function saveInvoiceData()
    {
        $sms    = Lib\SMS::getInstance();
        $result = $sms->sendInvoiceData( (array) self::parameter( 'invoice' ) );
        if ( $result === false ) {
            wp_send_json_error( array( 'message' => current( $sms->getErrors() ) ) );
        } else {
            wp_send_json_success();
        }
    }

    /**
     * Login.
     */
    public static function smsLogin()
    {
        $sms    = new Lib\SMS();
        $result = $sms->login( self::parameter( 'username' ), self::parameter( 'password' ) );
        if ( $result ) {
            wp_send_json_success();
        }

        wp_send_json_error( array( 'message' => current( $sms->getErrors() ) ) );
    }

    /**
     * Registration.
     */
    public static function smsRegister()
    {
        $sms = new Lib\SMS();

        if ( self::parameter( 'accept_tos', false ) ) {
            $response = $sms->register(
                self::parameter( 'username' ),
                self::parameter( 'password' ),
                self::parameter( 'password_repeat' ),
                self::parameter( 'country' )
            );
            if ( $response ) {
                update_option( 'bookly_sms_token', $response['token'] );

                wp_send_json_success();
            }
        } else {
            wp_send_json_error( array( 'message' => __( 'Please accept terms and conditions.', 'bookly' ) ) );
        }

        wp_send_json_error( array( 'message' => current( $sms->getErrors() ) ) );
    }

    /**
     * Logout.
     */
    public static function smsLogout()
    {
        $sms = new Lib\SMS();
        $sms->logout();

        foreach ( get_users( 'role=administrator' ) as $user ) {
            delete_user_meta( $user->ID, 'bookly_dismiss_sms_account_settings_notice' );
        }

        wp_send_json_success();
    }

    /**
     * Enable or Disable administrators email reports.
     */
    public static function adminNotify()
    {
        if ( in_array( self::parameter( 'option_name' ), array( 'bookly_sms_notify_low_balance', 'bookly_sms_notify_weekly_summary' ) ) ) {
            update_option( self::parameter( 'option_name' ), self::parameter( 'value' ) );
        }
        wp_send_json_success();
    }

    /**
     * Delete notification.
     */
    public static function deleteNotification()
    {
        Lib\Entities\Notification::query()
            ->delete()
            ->where( 'id', self::parameter( 'id' ) )
            ->execute();

        wp_send_json_success();
    }

    /**
     * Get data for notification list.
     */
    public static function getNotifications()
    {
        $types = Lib\Entities\Notification::getTypes( self::parameter( 'gateway' ) );

        $notifications = Lib\Entities\Notification::query()
            ->select( 'id, name, active, type' )
            ->where( 'gateway', self::parameter( 'gateway' ) )
            ->whereIn( 'type', $types )
            ->fetchArray();

        foreach ( $notifications as &$notification ) {
            $notification['order'] = array_search( $notification['type'], $types );
            $notification['icon']  = Lib\Entities\Notification::getIcon( $notification['type'] );
            $notification['title'] = Lib\Entities\Notification::getTitle( $notification['type'] );
        }

        wp_send_json_success( $notifications );
    }

    /**
     * Activate/Suspend notification.
     */
    public static function setNotificationState()
    {
        Lib\Entities\Notification::query()
            ->update()
            ->set( 'active', (int) self::parameter( 'active' ) )
            ->where( 'id', self::parameter( 'id' ) )
            ->execute();

        wp_send_json_success();
    }

    /**
     * Remove notification(s).
     */
    public static function deleteNotifications()
    {
        $notifications = array_map( 'intval', self::parameter( 'notifications', array() ) );
        Lib\Entities\Notification::query()->delete()->whereIn( 'id', $notifications )->execute();
        wp_send_json_success();
    }

    public static function saveAdministratorPhone()
    {
        update_option( 'bookly_sms_administrator_phone', self::parameter( 'bookly_sms_administrator_phone' ) );
        wp_send_json_success();
    }

    /**
     * Send queue
     */
    public static function sendQueue()
    {
        $queue = self::parameter( 'queue', array() );
        $sms   = Lib\SMS::getInstance();
        foreach ( $queue as $notification ) {
            if ( $notification['gateway'] == 'sms' ) {
                $sms->sendSms( $notification['address'], $notification['message'], $notification['impersonal'], $notification['type_id'] );
            } else {
                wp_mail( $notification['address'], $notification['subject'], $notification['message'], $notification['headers'], isset( $notification['attachments'] ) ? $notification['attachments'] : array() );
            }
        }
        self::_deleteAttachmentFiles( self::parameter( 'queue_full', array() ) );

        wp_send_json_success();
    }

    /**
     * Delete attachments files
     */
    public static function clearAttachments()
    {
        self::_deleteAttachmentFiles( self::parameter( 'queue', array() ) );

        wp_send_json_success();
    }

    /**
     * Delete attachment files
     *
     * @param $queue
     */
    private static function _deleteAttachmentFiles( $queue )
    {
        foreach ( $queue as $notification ) {
            if ( isset( $notification['attachments'] ) ) {
                foreach ( $notification['attachments'] as $file ) {
                    if ( file_exists( $file ) ) {
                        unlink( $file );
                    }
                }
            }
        }
    }

    /**
     * Apply confirmation code.
     */
    public static function applyConfirmationCode()
    {
        $code = self::parameter( 'code' );

        $result = Lib\SMS::getInstance()->confirmEmail( $code );
        if ( $result === false ) {
            wp_send_json_error( array( 'message' => current( Lib\SMS::getInstance()->getErrors() ) ) );
        } else {
            wp_send_json( $result );
        }
    }

    /**
     * Resend confirmation code.
     */
    public static function resendConfirmationCode()
    {
        $result = Lib\SMS::getInstance()->resendConfirmation();
        if ( $result === false ) {
            wp_send_json_error( array( 'message' => current( Lib\SMS::getInstance()->getErrors() ) ) );
        } else {
            wp_send_json( $result );
        }
    }


    /**
     * Dismiss confirm email modal.
     */
    public static function dismissConfirmEmail()
    {
        update_user_meta( get_current_user_id(), 'bookly_dismiss_sms_confirm_email', 1 );

        wp_send_json_success();
    }

    /**
     * Dismiss notice that shows user where SMS account settings are located
     */
    public static function dismissSmsAccountSettingsNotice()
    {
        update_user_meta( get_current_user_id(), Lib\Plugin::getPrefix() . 'dismiss_sms_account_settings_notice', 1 );

        wp_send_json_success();
    }
}