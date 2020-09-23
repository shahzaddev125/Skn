<?php
namespace Bookly\Backend\Components\Dialogs\Recharge\Amounts\Auto;

use Bookly\Lib;

/**
 * Class Ajax
 * @package Bookly\Backend\Components\Dialogs\Recharge\Amounts\Auto
 */
class Ajax extends Lib\Base\Ajax
{
    /**
     * Disable Auto-Recharge balance
     */
    public static function disableAutoRecharge()
    {
         $declined = Lib\SMS::getInstance()->disableAutoRecharge();
        if ( $declined !== false ) {
            wp_send_json_success( array( 'message' => __( 'Auto-Recharge disabled', 'bookly' ) ) );
        } else {
            wp_send_json_error( array( 'message' => sprintf( __( 'Can\'t disable Auto-Recharge, please contact us at %s', 'bookly' ), '<a href="mailto:support@bookly.info">support@bookly.info</a>' ) ) );
        }
    }
}