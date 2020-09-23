<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Buttons;
use Bookly\Backend\Components\Controls\Inputs;
use Bookly\Backend\Components\Dialogs\Recharge;
use Bookly\Lib;
/**
 * @var Lib\SMS $sms
 */
$invoice = $sms->getInvoiceData();
?>
<div class="bookly-modal bookly-fade" id="bookly-account-settings" tabindex=-1 role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php esc_html_e( 'Account settings', 'bookly' ) ?></h5>
                <button type="button" class="close" data-dismiss="bookly-modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-3">
                        <div class="nav flex-column nav-pills">
                            <a class="nav-link active" data-toggle="bookly-pill" href="#bookly-auto-recharge-tab"><?php esc_html_e( 'Auto-Recharge', 'bookly' ) ?></a>
                            <a class="nav-link mt-2" data-toggle="bookly-pill" href="#bookly-invoice-tab"><?php esc_html_e( 'Invoice', 'bookly' ) ?></a>
                            <a class="nav-link mt-2" data-toggle="bookly-pill" href="#bookly-account-notifications-tab"><?php esc_html_e( 'Notifications', 'bookly' ) ?></a>
                            <a class="nav-link mt-2" data-toggle="bookly-pill" href="#bookly-country-tab"><?php esc_html_e( 'Country', 'bookly' ) ?></a>
                            <a class="nav-link mt-2" data-toggle="bookly-pill" href="#bookly-change-password-tab"><?php esc_html_e( 'Change password', 'bookly' ) ?></a>
                        </div>
                    </div>
                    <div class="col-9">
                        <div class="tab-content">
                            <div class="tab-pane bookly-fade show active" id="bookly-auto-recharge-tab">
                                <div class="form-row align-items-center">
                                    <div class="col-8">
                                        <?php Recharge\Amounts\Auto\Button::renderSelector() ?>
                                    </div>
                                </div>
                                <small class="text-muted"><?php printf( __( 'We will only charge you when your balance falls bellow %s', 'bookly' ), '<b>$10</b>' ) ?></small>
                            </div>

                            <div class="tab-pane bookly-fade" id="bookly-invoice-tab">
                                <form>
                                    <div class="form-group">
                                        <label for="bookly_sms_invoice_company_name"><?php esc_html_e( 'Company name', 'bookly' ) ?>*</label>
                                        <input name="invoice[company_name]" type="text" class="form-control" id="bookly_sms_invoice_company_name" required value="<?php echo esc_attr( $invoice['company_name'] ) ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="bookly_sms_invoice_company_address"><?php esc_html_e( 'Company address', 'bookly' ) ?>*</label>
                                        <input name="invoice[company_address]" type="text" class="form-control" id="bookly_sms_invoice_company_address" required value="<?php echo esc_attr( $invoice['company_address'] ) ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="bookly_sms_invoice_company_address_l2"><?php esc_html_e( 'Company address line 2', 'bookly' ) ?></label>
                                        <input name="invoice[company_address_l2]" type="text" class="form-control" id="bookly_sms_invoice_company_address_l2" value="<?php echo esc_attr( $invoice['company_address_l2'] ) ?>">
                                    </div>
                                    <div class="form-group bookly-js-invoice-country">
                                        <div class="bookly-js-label">N/A</div>
                                        <small class="form-text text-muted mb-2"><?php _e( 'You can change the country <a href="#">here</a>', 'bookly' ) ?></small>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label for="bookly_sms_invoice_company_code"><?php esc_html_e( 'Company number', 'bookly' ) ?></label>
                                            <input name="invoice[company_code]" type="text" class="form-control" id="bookly_sms_invoice_company_code" value="<?php echo esc_attr( $invoice['company_code'] ) ?>">
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label for="bookly_sms_invoice_company_vat"><?php esc_html_e( 'VAT / Tax number', 'bookly' ) ?></label>
                                            <input name="invoice[company_vat]" type="text" class="form-control" id="bookly_sms_invoice_company_vat" value="<?php echo esc_attr( $invoice['company_vat'] ) ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="bookly_sms_invoice_company_add_text"><?php esc_html_e( 'Additional text to include in invoice', 'bookly' ) ?></label>
                                        <textarea name="invoice[company_add_text]" class="form-control" rows="3" id="bookly_sms_invoice_company_add_text"><?php echo esc_textarea( $invoice['company_add_text'] ) ?></textarea>
                                    </div>
                                    <div class="form-group border-top pt-2">
                                        <input name="invoice[send]" value="0" class="hidden" />
                                        <?php Inputs::renderCheckBox( 'Send invoice', 1, $invoice['send'], array( 'name' => 'invoice[send]' ) ) ?>
                                        <small class="text-muted"><?php printf( __( 'The invoice will be sent to <a href="mailto:%1$s">%1$s</a>', 'bookly' ), Bookly\Lib\SMS::getInstance()->getUserName() ) ?></small>
                                    </div>
                                    <div class="form-group">
                                        <?php Inputs::renderCheckBox( __( 'Copy invoice to another email(s)', 'bookly' ), 1, $invoice['send_copy'], array( 'name' => 'invoice[send_copy]' ) ) ?>
                                        <input name="invoice[cc]" type="text" class="form-control mt-2" value="<?php echo esc_attr( $invoice['cc'] ) ?>">
                                        <small class="form-text text-muted"><?php esc_html_e( 'Enter one or more email addresses separated by commas.', 'bookly' ) ?></small>
                                    </div>
                                    <?php Buttons::renderSubmit( 'bookly-save-invoice', null, __( 'Save invoice settings', 'bookly' ) ) ?>
                                </form>
                            </div>

                            <div class="tab-pane bookly-fade" id="bookly-account-notifications-tab">
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input class="custom-control-input" type="checkbox" id="bookly_sms_notify_low_balance" name="bookly_sms_notify_low_balance" <?php checked( get_option( 'bookly_sms_notify_low_balance' ) ) ?>>
                                        <label class="custom-control-label" for="bookly_sms_notify_low_balance"><span><?php esc_html_e( 'Send email notification to administrators at low balance', 'bookly' ) ?></span></label>
                                    </div>
                                </div>
                                <div class="custom-control custom-checkbox">
                                    <input class="custom-control-input" type="checkbox" id="bookly_sms_notify_weekly_summary" name="bookly_sms_notify_weekly_summary" <?php checked( get_option( 'bookly_sms_notify_weekly_summary' ) ) ?>>
                                    <label class="custom-control-label" for="bookly_sms_notify_weekly_summary"><span><?php esc_html_e( 'Send weekly summary to administrators', 'bookly' ) ?></span></label>
                                </div>
                            </div>

                            <div class="tab-pane bookly-fade" id="bookly-country-tab">
                                <div class="form-group">
                                    <select id="bookly-country"></select>
                                    <small class="text-muted"><?php esc_html_e( 'Your country is the location from where you consume Bookly SMS services and is used to provide you with the payment methods available in that country.', 'bookly' ) ?></small>
                                </div>
                                <?php Buttons::renderSubmit( 'bookly-update-country', null, __( 'Update country', 'bookly' ) ) ?>
                            </div>

                            <div class="tab-pane bookly-fade" id="bookly-change-password-tab">
                                <form>
                                    <div class="form-group">
                                        <label for="old_password"><?php esc_html_e( 'Old password', 'bookly' ) ?></label>
                                        <input type="password" class="form-control" id="old_password" name="old_password" placeholder="<?php esc_attr_e( 'Old password', 'bookly' ) ?>" required />
                                    </div>
                                    <div class="form-group">
                                        <label for="new_password"><?php esc_html_e( 'New password', 'bookly' ) ?></label>
                                        <input type="password" class="form-control" id="new_password" name="new_password" placeholder="<?php esc_attr_e( 'New password', 'bookly' ) ?>" required />
                                    </div>
                                    <div class="form-group">
                                        <label for="new_password_repeat"><?php esc_html_e( 'Repeat new password', 'bookly' ) ?></label>
                                        <input type="password" class="form-control" id="new_password_repeat" placeholder="<?php esc_attr_e( 'Repeat new password', 'bookly' ) ?>" required />
                                    </div>
                                    <?php Buttons::renderSubmit( 'bookly-change-password', null, __( 'Change password', 'bookly' ) ) ?>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <?php Buttons::renderCancel( __( 'Close', 'bookly' ) ) ?>
            </div>
        </div>
    </div>
</div>

<?php Recharge\Amounts\Auto\Button::renderConfirmModal() ?>
<?php if ( ! $sms->getCountry() ): ?>
    <?php include '_setup_country.php' ?>
<?php endif ?>