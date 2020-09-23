<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Support;
use Bookly\Backend\Components\Dialogs\TableSettings;
use Bookly\Backend\Components\Dialogs\Recharge;
/**
 * @var Bookly\Lib\SMS $sms
 * @var int $undelivered_count
 */
?>
<div id="bookly-tbs" class="wrap">
    <div class="form-row align-items-center mb-3">
        <h4 class="col m-0"><?php esc_html_e( 'SMS Notifications', 'bookly' ) ?></h4>
        <?php Support\Buttons::render( $self::pageSlug() ) ?>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col">
                    <p class="h6 m-0 p-0"><strong><?php esc_html_e( 'Sender ID', 'bookly' ) ?>:</strong> <a href="#" id="bookly-open-tab-sender-id"><?php echo esc_html( $sms->getSenderId() ) ?>
                        <i class="fas fa-pencil-alt ml-1"></i></a></p>
                    <?php if ( $sms->getSenderIdApprovalDate() ) : ?>
                        <p class="h6 small m-0 p-0 mb-1 mr-1 text-muted text-form bookly-js-sender-id-approval-date"><?php esc_html_e( 'Approved at', 'bookly' ) ?>:
                            <strong><?php echo \Bookly\Lib\Utils\DateTime::formatDate( $sms->getSenderIdApprovalDate() ) ?></strong></p>
                    <?php else: ?>
                        <p class="h6 small m-0 p-0 mb-1 mr-1 text-muted"><?php esc_html_e( 'Change the sender\'s name to your phone number or any other name', 'bookly' ) ?></p>
                    <?php endif ?>
                </div>
                <div class="col-auto">
                    <?php Recharge\Amounts\Manual\Button::renderBalance() ?>
                    <div class="btn-group">
                        <button type="button" id="bookly-open-account-settings" class="btn <?php echo $sms->getEmailConfirmed() ? 'btn-primary' : 'btn-danger' ?> text-truncate" data-toggle="bookly-modal" href="#bookly-account-settings"
                            <?php if ( ! get_user_meta( get_current_user_id(), 'bookly_dismiss_sms_account_settings_notice', true ) ): ?>
                                data-content="<?php echo esc_attr( '<button type="button" class="close ml-2"><span>&times;</span></button>' . __( 'Click this button to access your Bookly SMS account settings', 'bookly' ) ) ?>"
                            <?php endif ?>
                        >
                            <i class="fas <?php echo $sms->getEmailConfirmed() ? 'fa-user' : 'fa-user-slash' ?>"></i><span class="d-none d-sm-inline ml-2"><?php echo $sms->getUserName() ?></span>
                        </button>
                        <?php if ( ! $sms->getEmailConfirmed() ) : ?>
                            <button id="bookly-open-email-confirm" type="button" class="btn btn-success text-nowrap ladda-button" data-spinner-color="#666666" data-style="zoom-in" data-spinner-size="40">
                                <span class="ladda-label"><i class="fas fa-exclamation-circle"></i><span class="d-none d-md-inline-block ml-2"><?php esc_html_e( 'Confirm email', 'bookly' ) ?>…</span></span>
                            </button>
                        <?php endif ?>
                        <button id="bookly-logout" type="button" class="btn btn-white border text-nowrap rounded-right ladda-button" data-spinner-color="#666666" data-style="zoom-in" data-spinner-size="40">
                            <span class="ladda-label"><i class="fas fa-sign-out-alt"></i><span class="d-none d-md-inline-block ml-2"><?php esc_html_e( 'Log out', 'bookly' ) ?></span></span>
                        </button>
                    </div>
                </div>
            </div>

            <ul class="nav nav-tabs mb-3" id="sms_tabs">
                <li class="nav-item"><a class="nav-link active" data-toggle="bookly-tab" href="#notifications"><?php esc_html_e( 'Notifications', 'bookly' ) ?></a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="bookly-tab" href="#purchases"><?php esc_html_e( 'Purchases', 'bookly' ) ?></a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="bookly-tab" href="#sms_details"><?php esc_html_e( 'SMS Details', 'bookly' );
                if ( $undelivered_count ) : ?> <span class="badge bg-danger"><?php echo $undelivered_count ?></span><?php endif ?></a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="bookly-tab" href="#price_list"><?php esc_html_e( 'Price list', 'bookly' ) ?></a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="bookly-tab" href="#sender_id"><?php esc_html_e( 'Sender ID', 'bookly' ) ?></a></li>
            </ul>

            <div class="tab-content mt-3">
                <div class="tab-pane active" id="notifications"><?php include '_notifications.php' ?></div>
                <div class="tab-pane" id="purchases"><?php include '_purchases.php' ?></div>
                <div class="tab-pane" id="sms_details"><?php include '_sms_details.php' ?></div>
                <div class="tab-pane" id="price_list"><?php include '_price.php' ?></div>
                <div class="tab-pane" id="sender_id"><?php include '_sender_id.php' ?></div>
            </div>
        </div>
    </div>

    <?php include '_settings.php' ?>
    <?php Recharge\Dialog::render() ?>
    <?php TableSettings\Dialog::render() ?>
    <?php if ( ! $sms->getEmailConfirmed() ) : ?>
        <?php include '_confirm_email.php' ?>
    <?php endif ?>
</div>