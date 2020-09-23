<?php
namespace BooklyServiceExtras\Backend\Modules\Appearance\ProxyProviders;

use Bookly\Backend\Modules\Appearance\Proxy;

/**
 * Class Local
 * @package BooklyServiceExtras\Backend\Modules\Appearance\ProxyProviders
 */
class Local extends Proxy\ServiceExtras
{
    /**
     * @inheritdoc
     */
    public static function renderCartExtras()
    {
        self::renderTemplate( 'cart_extras' );
    }

    /**
     * @inheritdoc
     */
    public static function renderShowCartExtras()
    {
        self::renderTemplate( 'show_cart_extras' );
    }

    /**
     * @inheritdoc
     */
    public static function renderShowStep()
    {
        self::renderTemplate( 'show_extras_step' );
    }

    /**
     * @inheritdoc
     */
    public static function renderStep( $progress_tracker )
    {
        self::renderTemplate( 'extras_step', compact( 'progress_tracker' ) );
    }

    /**
     * @inheritdoc
     */
    public static function renderStepSettings()
    {
        $show = get_option( 'bookly_service_extras_show' );
        self::renderTemplate( 'extras_step_settings', compact( 'show' ) );
    }
}