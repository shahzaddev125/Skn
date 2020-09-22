<?php
namespace BooklyPro\Backend\Modules\Notifications\ProxyProviders;

use Bookly\Backend\Modules\Notifications\Proxy;

/**
 * Class Shared
 * @package BooklyPro\Backend\Modules\Notifications\ProxyProviders
 */
class Shared extends Proxy\Shared
{
    /**
     * @inheritdoc
     */
    public static function prepareNotificationCodes( array $codes, $type )
    {
        $codes['appointment']['online_meeting_url']      = __( 'online meeting URL', 'bookly' );
        $codes['appointment']['online_meeting_password'] = __( 'online meeting password', 'bookly' );
        $codes['appointment']['online_meeting_start_url'] = __( 'online meeting start URL', 'bookly' );
        $codes['appointment']['online_meeting_join_url'] = __( 'online meeting join URL', 'bookly' );

        return $codes;
    }
}