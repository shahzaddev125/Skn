<?php
namespace Bookly\Backend\Components\Dialogs\Recharge;

use Bookly\Lib;

/**
 * Class Ajax
 * @package Bookly\Backend\Components\Dialogs\Recharge
 */
class Ajax extends Lib\Base\Ajax
{
    /**
     * Initial for enabling Auto-Recharge balance with PayPal
     */
    public static function initAutoRechargePaypal()
    {
        $sms = Lib\SMS::getInstance();
        $url = $sms->getPreApprovalUrl( self::parameter( 'recharge' ), self::parameter( 'url' ) );
        if ( $url !== false ) {
            wp_send_json_success( array( 'paypal_preapproval' => $url ) );
        } else {
            $errors = $sms->getErrors();
            $message = __( 'Auto-Recharge has failed, please replenish your balance directly.', 'bookly' );
            if ( array_key_exists( 'ERROR_PROMOTION_NOT_AVAILABLE', $errors ) ) {
                $message = $errors['ERROR_PROMOTION_NOT_AVAILABLE'];
            }
            wp_send_json_error( compact( 'message' ) );
        }
    }

    /**
     * Create Stripe Checkout session
     */
    public static function createStripeCheckoutSession()
    {
        $sms = Lib\SMS::getInstance();
        $result = $sms->createStripeCheckoutSession(
            self::parameter( 'recharge' ),
            self::parameter( 'mode' ),
            self::parameter( 'url' )
        );

        if ( $result === false ) {
            $errors = $sms->getErrors();
            if ( array_key_exists( 'ERROR_RECHARGE_NOT_AVAILABLE', $errors ) ) {
                wp_send_json_error( array( 'message' => $errors['ERROR_RECHARGE_NOT_AVAILABLE'] ) );
            } else {
                wp_send_json_error( array( 'message' => __( 'Card payment has failed, please use another payment option', 'bookly' ) ) );
            }
        } else {
            wp_send_json( $result );
        }
    }

    /**
     * Create PayPal order
     */
    public static function createPaypalOrder()
    {
        $sms = Lib\SMS::getInstance();
        $order_url = $sms->createPaypalOrder(
            self::parameter( 'recharge' ),
            self::parameter( 'url' )
        );

        if ( $order_url === false ) {
            $errors = $sms->getErrors();
            if ( array_key_exists( 'ERROR_RECHARGE_NOT_AVAILABLE', $errors ) ) {
                wp_send_json_error( array( 'message' => $errors['ERROR_RECHARGE_NOT_AVAILABLE'] ) );
            } else {
                wp_send_json_error( array( 'message' => __( 'Payment has failed, please use another payment option', 'bookly' ) ) );
            }
        } else {
            wp_send_json_success( compact( 'order_url' ) );
        }
    }
}