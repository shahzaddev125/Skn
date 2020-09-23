<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Buttons;
use Bookly\Backend\Components\Support;
use Bookly\Backend\Components\Dialogs\TableSettings;
/**
 * @var array $registration_promotion_texts
 */
?>
<div id="bookly-tbs" class="wrap">
    <div class="form-row align-items-center mb-3">
        <h4 class="col m-0"><?php esc_html_e( 'SMS Notifications', 'bookly' ) ?></h4>
        <?php Support\Buttons::render( $self::pageSlug() ) ?>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="alert alert-info">
                <p><?php esc_html_e( 'SMS Notifications (or "Bookly SMS") is a service for notifying your customers via text messages which are sent to mobile phones.', 'bookly' ) ?></p>
                <p><?php esc_html_e( 'It is necessary to register in order to start using this service.', 'bookly' ) ?></p>
                <p><?php esc_html_e( 'After registration you will need to configure notification messages and top up your balance in order to start sending SMS.', 'bookly' ) ?></p>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <form class="card-body bookly-js-login-form" style="display: none;">
                            <h5 class="cart-title"><?php esc_html_e( 'Login', 'bookly' ) ?></h5>
                            <div class="form-group">
                                <label for="bookly-username"><?php esc_html_e( 'Email', 'bookly' ) ?></label>
                                <input id="bookly-username" class="form-control" type="text" required="required" value="" name="username">
                            </div>
                            <div class="form-group">
                                <label for="bookly-password"><?php esc_html_e( 'Password', 'bookly' ) ?></label>
                                <input id="bookly-password" class="form-control" type="password" required="required" name="password">
                            </div>

                            <?php Buttons::renderSubmit( null, 'mb-2', __( 'Log In', 'bookly' ), array( 'name' => 'submit' ) ) ?>
                            <a href="#" class="bookly-js-show-register-form mx-2"><?php esc_html_e( 'Registration', 'bookly' ) ?></a><br>
                            <a href="#" class="bookly-js-show-forgot-form"><?php esc_html_e( 'Forgot password', 'bookly' ) ?></a>
                        </form>

                        <form method="post" class="card-body bookly-js-register-form">
                            <h5 class="card-title"><?php esc_html_e( 'Registration', 'bookly' ) ?></h5>
                            <?php if ( $registration_promotion_texts['form'] ): ?>
                                <div class="form-group"><?php echo $registration_promotion_texts['form'] ?></div>
                            <?php endif ?>
                            <div class="form-group">
                                <label for="bookly-r-username"><?php esc_html_e( 'Email', 'bookly' ) ?></label>
                                <input id="bookly-r-username" name="username" class="form-control" required="required" value="" type="text">
                            </div>
                            <div class="form-group">
                                <label for="bookly-r-password"><?php esc_html_e( 'Password', 'bookly' ) ?></label>
                                <input id="bookly-r-password" name="password" class="form-control" required="required" value="" type="password">
                            </div>
                            <div class="form-group">
                                <label for="bookly-r-repeat-password"><?php esc_html_e( 'Repeat password', 'bookly' ) ?></label>
                                <input id="bookly-r-repeat-password" name="password_repeat" class="form-control" required="required" value="" type="password">
                            </div>
                            <div class="form-group">
                                <label for="bookly-r-country"><?php esc_html_e( 'Country', 'bookly' ) ?></label>
                                <select id="bookly-r-country" name="country"></select>
                                <small class="text-muted"><?php esc_html_e( 'Your country is the location from where you consume Bookly SMS services and is used to provide you with the payment methods available in that country', 'bookly' ) ?></small>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input class="custom-control-input" type="checkbox" id="bookly-r-tos" name="accept_tos" required="required" value="1">
                                    <label class="custom-control-label" for="bookly-r-tos">
                                        <?php printf( __( 'I accept <a href="%1$s" target="_blank">Service Terms</a> and <a href="%2$s" target="_blank">Privacy Policy</a>', 'bookly' ), 'https://www.booking-wp-plugin.com/terms/', 'https://www.booking-wp-plugin.com/privacy/' ) ?>
                                    </label>
                                </div>
                            </div>
                            <div class="btn-group mr-2">
                                <?php Buttons::renderSubmit( null, null, __( 'Register', 'bookly' ), array( 'name' => 'form-registration' ) ) ?>
                                <?php if ( $registration_promotion_texts['button'] ) : ?>
                                    <div class="border border-left-0 rounded px-2 d-flex align-items-center">
                                        <h6 class="m-0"><?php echo $registration_promotion_texts['button'] ?></h6>
                                    </div>
                                <?php endif ?>
                            </div>
                            <a href="#" class="bookly-js-show-login-form"><?php esc_html_e( 'Log In', 'bookly' ) ?></a>
                        </form>

                        <form method="post" class="card-body bookly-js-forgot-form" style="display: none;">
                            <h5 class="card-title"><?php esc_html_e( 'Forgot password', 'bookly' ) ?></h5>
                            <div class="form-group">
                                <input name="username" class="form-control" value="" type="text" placeholder="<?php esc_attr_e( 'Email', 'bookly' ) ?>"/>
                            </div>
                            <div class="form-group hidden">
                                <input name="code" class="form-control" value="" type="text" placeholder="<?php esc_attr_e( 'Enter code from email', 'bookly' ) ?>"/>
                            </div>
                            <div class="form-group hidden">
                                <input name="password" class="form-control" value="" type="password" placeholder="<?php esc_attr_e( 'New password', 'bookly' ) ?>"/>
                            </div>
                            <div class="form-group hidden">
                                <input name="password_repeat" class="form-control" value="" type="password" placeholder="<?php esc_attr_e( 'Repeat new password', 'bookly' ) ?>"/>
                            </div>
                            <div class="form-group">
                                <button class="btn btn-success bookly-js-form-forgot-next mr-2" data-step="0"><?php esc_html_e( 'Next', 'bookly' ) ?></button>
                                <a href="#" class="bookly-js-show-login-form"><?php esc_html_e( 'Log In', 'bookly' ) ?></a>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-md-8">
                    <?php include '_price.php' ?>
                </div>
            </div>
        </div>
    </div>

    <?php TableSettings\Dialog::render() ?>
</div>