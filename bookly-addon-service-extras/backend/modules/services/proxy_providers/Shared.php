<?php
namespace BooklyServiceExtras\Backend\Modules\Services\ProxyProviders;

use Bookly\Lib as BooklyLib;
use BooklyServiceExtras\Lib;
use Bookly\Backend\Modules\Services\Proxy;
use BooklyServiceExtras\Backend\Modules\Services\Forms;
use BooklyServiceExtras\Lib\Entities\ServiceExtra;

/**
 * Class Shared
 * @package BooklyServiceExtras\Backend\Modules\Services\ProxyProviders
 */
class Shared extends Proxy\Shared
{
    /**
     * @inheritdoc
     */
    public static function enqueueAssetsForServices()
    {
        $list = Lib\Utils\Common::getExtrasList();

        self::enqueueStyles( array(
            'bookly' => array(
                'backend/resources/bootstrap/css/bootstrap.min.css',
                'backend/resources/css/typeahead.css',
            ),
        ) );

        self::enqueueScripts( array(
            'module' => array( 'js/extras.js' => array( 'jquery' ), ),
            'bookly' => array(
                'backend/resources/js/typeahead.bundle.min.js' => array( 'jquery' ),
            ),
        ) );

        wp_localize_script( 'bookly-extras.js', 'ExtrasL10n', array(
            'list' => $list,
        ) );
    }

    /**
     * @inheritdoc
     */
    public static function prepareAfterServiceList( $html )
    {
        return $html . self::renderTemplate( 'extras_blank', array(), false  );
    }

    /**
     * @inheritdoc
     */
    public static function prepareUpdateServiceResponse( array $response, BooklyLib\Entities\Service $service, array $_post )
    {
        $response['new_extras_list'] = Lib\Utils\Common::getExtrasList();

        return $response;
    }

    /**
     * @inheritdoc
     */
    public static function updateService( array $alert, BooklyLib\Entities\Service $service, array $_post )
    {
        if ( isset( $_post['extras'] ) ) {
            $extras         = $_post['extras'];
            $current_ids    = array_map( function ( ServiceExtra $se ) { return $se->getId(); }, ServiceExtra::query()->where( 'service_id', $service->getId() )->find() );
            $ids_to_delete  = array_diff( $current_ids, array_keys( $extras ) );
            if ( ! empty ( $ids_to_delete ) ) {
                // Remove redundant extras.
                ServiceExtra::query()->delete()->whereIn( 'id', $ids_to_delete )->execute();
            }
            foreach ( $extras as $id => $data ) {
                $form               = new Forms\ServiceExtra();
                $data['service_id'] = $service->getId();
                $form->bind( $data );
                $form->save();
            }
        } else {
            ServiceExtra::query()->delete()->where( 'service_id', $service->getId() )->execute();
        }

        return $alert;
    }

    /**
     * @inheritDoc
     */
    public static function duplicateService( $source_id, $target_id )
    {
        foreach ( Lib\Entities\ServiceExtra::query()->where( 'service_id', $source_id )->fetchArray() as $extra ) {
            $new_extra = new Lib\Entities\ServiceExtra( $extra );
            $new_extra->setId( null )->setServiceId( $target_id )->save();
        }
    }
}