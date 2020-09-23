
<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly This page Edited For customization Account->Appointment page button css 
use Bookly\Lib as BooklyLib;


use Bookly\Frontend\Modules\CustomerProfile\Proxy\CustomFields as CustomFieldsProxy;
use Bookly\Lib\Entities;
use Bookly\Lib\Proxy;
use Bookly\Lib\Utils\DateTime;
use Bookly\Lib\Utils\Price;

$color = get_option( 'bookly_app_color', '#f4662f' );
$compound_tokens = array();
$custom_fields = BooklyLib\Config::customFieldsActive() && get_option( 'bookly_custom_fields_enabled' ) && isset ( $attributes['custom_fields'] )
    ? explode( ',', $attributes['custom_fields'] )
    : array();
$columns = isset( $attributes['columns'] ) ? explode( ',', $attributes['columns'] ) : array();
$with_cancel = in_array( 'cancel', $columns );
$container = 'bookly-js-appointments-list-' . mt_rand( 0, PHP_INT_MAX );
?>
<?php if ( is_user_logged_in() ) : ?>
    <div class="bookly-customer-appointment-list <?php echo $container ?>">
        <h2><?php esc_html_e( 'Appointments', 'bookly' ) ?></h2>
        
        <div class="mb-extra-btn">
        <a href="https://sknelements.com/book-an-appointment/" class="appointmemnt-acount-pg fusion-bottombar-highlight mobile-acc-btn"><span class="menu-text fusion-button button-default button-large">Book a new appointment</span></a>
        
        
		<?php if ( $more ) : ?>
            <button class="bookly-btn-default bookly-js-show-past mobile-acc-btn" style="float: right; background: <?php echo $color ?>!important; width: auto" data-spinner-size="40" data-style="zoom-in">
            <span><?php esc_html_e( 'Show past appointments', 'bookly' ) ?></span>
            </button>
        <?php endif ?>
        </div>
        
        <?php if ( ! empty( $columns ) || ! empty( $custom_fields ) ) : ?>
        
            <table class="bookly-appointments-table ttt" data-columns="<?php echo esc_attr( json_encode( $columns ) ) ?>" data-custom_fields="<?php echo esc_attr( implode(',', $custom_fields ) ) ?>" data-page="0">
                <?php if ( isset( $attributes['show_column_titles'] ) && $attributes['show_column_titles'] ) : ?>
                    <thead>
                        <tr>
                            <?php foreach ( $columns as $column ) : ?>
                                <?php if ( $column != 'cancel' ) : ?>
                                    <th><?php echo $titles[ $column ] ?></th>
                                <?php endif ?>
                            <?php endforeach ?>
                            <?php foreach ( $custom_fields as $column ) : ?>
                                <th><?php if ( isset( $titles[ $column ] ) ) echo $titles[ $column ] ?></th>
                            <?php endforeach ?>
                            <?php if ( $with_cancel ) : ?>
                                <th><?php echo $titles['cancel'] ?></th>
                            <?php endif ?>
                        </tr>
                    </thead>
                <?php endif ?>
                <?php if ( empty( $appointments ) ) : ?>
                    <tr class="bookly--no-appointments"><td colspan="<?php echo count( $columns ) + count( $custom_fields ) ?>"><?php esc_html_e( 'No appointments found.', 'bookly' ) ?></td></tr>
                <?php else : ?>
                    <?php include '_rows.php' ?>
                <?php endif ?>
            </table>
           
            <?php foreach ( $appointments as $app ) : ?>
            <div class="bookly-appointments-table-mobile">
            	
                <?php if ( isset( $attributes['show_column_titles'] ) && $attributes['show_column_titles'] ) : ?>
                    <?php /*?><div class="left-side-label">
                        <ul>
                        <?php foreach ( $columns as $column ) : ?>
                            <li>
								<?php if ( $column != 'cancel' ) : ?>
                                <?php echo $titles[ $column ] ?>
                                  <?php endif ?>
                            </li>
                            <?php endforeach ?>  
                        </ull>
                    </div><?php */?>
                    
                    
                    
                    <div class="right-side-label">
                        <ul>
                            <li><span>Date:</span>
                            <span><?php echo $app['start_date'] === null ? __( 'N/A', 'bookly' ) : DateTime::formatDate( $app['start_date'] ) ?></span>
                            </li>
                            <li><span>Time:</span>
							<span><?php echo $app['start_date'] === null ? __( 'N/A', 'bookly' ) : DateTime::formatTime( $app['start_date'] ) ?></span>
                            </li>
                            
                            
                            <li><span>Service:</span>
							<span><?php echo $app['service'] ?></span>
                            <?php if ( ! empty ( $app['extras'] ) ):  ?>
                                <ul class="bookly-extras">
                                    <?php foreach ( $app['extras'] as $extra ) : ?>
                                        <li><?php echo esc_html( $extra['title'] ) ?></li>
                                    <?php endforeach ?>
                                </ul>
                            <?php endif ?>
                            </li>
                            
                            
                            <li><span>Provider:</span>
                            <span><?php echo $app['staff'] ?></span>
                            </li>
						 	<li><span>Price:</span>
                            <span><?php echo Price::format( ( $app['price'] + $extras_total_price ) * $app['number_of_persons'] ) ?></span>
                            </li>
                            
                            <li>
                            <span>Status:</span>
                            <span><?php echo Entities\CustomerAppointment::statusToString( $app['appointment_status'] ) ?></span>
                            </li>
                        </ul>
                        
                         <?php if ( $app['start_date'] > current_time( 'mysql' ) || $app['start_date'] === null ) : ?>
                    <?php if ( ( $allow_cancel < strtotime( $app['start_date'] ) || $app['start_date'] === null ) && $app['appointment_status'] != Entities\CustomerAppointment::STATUS_DONE ) : ?>
                        <?php if ( ! in_array( $app['appointment_status'], Proxy\CustomStatuses::prepareFreeStatuses( array(
                            Entities\CustomerAppointment::STATUS_CANCELLED,
                            Entities\CustomerAppointment::STATUS_REJECTED,
                        ) ) ) ) : ?>
                            <a class="bookly-btn-default" style="background-color: <?php echo $color ?>" href="<?php echo esc_attr( $url_cancel . '&token=' . $app['token'] ) ?>">
                                <span><?php esc_html_e( 'Cancel Appointment', 'bookly' ) ?></span>
                            </a>
                        <?php endif ?>
                    <?php else : ?>
                        <?php esc_html_e( 'Not allowed', 'bookly' ) ?>
                    <?php endif ?>
                <?php else : ?>
                    <?php esc_html_e( 'Expired', 'bookly' ) ?>
                <?php endif ?>
                    </div>
                    
                    
                <?php endif ?>
                
                
            </div>
            
           <?php endforeach ?> 
            
            
            
            
            <?php if ( $more ) : ?>
                <button class="bookly-btn-default bookly-js-show-past" style="float: right; background: <?php echo $color ?>!important; width: auto" data-spinner-size="40" data-style="zoom-in">
                    <span><?php esc_html_e( 'Show past appointments', 'bookly' ) ?></span>
                </button>
            <?php endif ?>
        <?php endif ?>
    </div>

    <script type="text/javascript">
        (function (win, fn) {
            var done = false, top = true,
                doc = win.document,
                root = doc.documentElement,
                modern = doc.addEventListener,
                add = modern ? 'addEventListener' : 'attachEvent',
                rem = modern ? 'removeEventListener' : 'detachEvent',
                pre = modern ? '' : 'on',
                init = function(e) {
                    if (e.type == 'readystatechange') if (doc.readyState != 'complete') return;
                    (e.type == 'load' ? win : doc)[rem](pre + e.type, init, false);
                    if (!done) { done = true; fn.call(win, e.type || e); }
                },
                poll = function() {
                    try { root.doScroll('left'); } catch(e) { setTimeout(poll, 50); return; }
                    init('poll');
                };
            if (doc.readyState == 'complete') fn.call(win, 'lazy');
            else {
                if (!modern) if (root.doScroll) {
                    try { top = !win.frameElement; } catch(e) { }
                    if (top) poll();
                }
                doc[add](pre + 'DOMContentLoaded', init, false);
                doc[add](pre + 'readystatechange', init, false);
                win[add](pre + 'load', init, false);
            }
        })(window, function() {
            window.booklyCustomerProfile(
                <?php echo json_encode( compact( 'container', 'ajaxurl' ) ) ?>
            );
        });
    </script>
<?php else : ?>
    <?php wp_login_form() ?>
<?php endif ?>


<style>
.mobile-acc-btn{display:none;}
@media only screen and (max-width: 768px) {
.mobile-acc-btn {
    position: inherit !important;
    display: block !important;
    margin: 0 auto 10px auto;
    float: none !important;
	width:80% !important;
	text-align: center;
}
a.appointmemnt-acount-pg.fusion-bottombar-highlight.appointmemnt-acount-pg_btn_sp {
    display: none;
}
button.bookly-btn-default.bookly-js-show-past {
    display: none;
}
a.appointmemnt-acount-pg.fusion-bottombar-highlight {
    display: none;
}
.page-id-10 .bookly-customer-appointment-list {
    margin-top: 0px !important;
}
}
</style>
<!--// Exit if accessed directly This page Edited For customization Account->Appointment page button css -->