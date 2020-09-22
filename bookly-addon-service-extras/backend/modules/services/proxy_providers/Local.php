<?php
namespace BooklyServiceExtras\Backend\Modules\Services\ProxyProviders;

use Bookly\Lib as BooklyLib;
use Bookly\Backend\Modules\Services\Proxy;

/**
 * Class Local
 * @package BooklyServiceExtras\Backend\Modules\Services\ProxyProviders
 */
class Local extends Proxy\ServiceExtras
{
    /**
     * @inheritdoc
     */
    public static function renderTab()
    {
        self::renderTemplate( 'extras_tab' );
    }

    /**
     * @inheritdoc
     */
    public static function getTabHtml( $service_id )
    {
        $extras        = BooklyLib\Proxy\ServiceExtras::findByServiceId( $service_id );
        $time_interval = get_option( 'bookly_gen_time_slot_length' );

        return self::renderTemplate( 'extras', compact( 'service_id', 'extras', 'time_interval' ), false );
    }
}