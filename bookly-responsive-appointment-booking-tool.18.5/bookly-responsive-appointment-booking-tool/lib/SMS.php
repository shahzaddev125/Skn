<?php
namespace Bookly\Lib;

use Bookly\Backend\Modules;

/**
 * Class SMS
 * @package Bookly\Lib
 */
class SMS extends Base\Cache
{
    const API_URL = 'https://sms.booking-wp-plugin.com';

    const AUTHENTICATE                   = '/1.1/logins';                                 //POST
    const CANCEL_SENDER_ID               = '/1.0/users/%token%/sender-ids/cancel';        //GET
    const CHANGE_COUNTRY                 = '/1.0/users/%token%/country';                  //PATCH
    const CHANGE_PASSWORD                = '/1.0/users/%token%';                          //PATCH
    const CONFIRM_EMAIL                  = '/1.3/users/%token%/confirm';                  //POST
    const CREATE_PAYPAL_ORDER            = '/1.0/users/%token%/paypal/order';             //POST
    const CREATE_PREAPPROVAL             = '/1.1/users/%token%/paypal/pre-approvals';     //POST
    const CREATE_STRIPE_CHECKOUT_SESSION = '/1.0/users/%token%/stripe/checkout/sessions'; //POST
    const DISABLE_AUTO_RECHARGE          = '/1.0/users/%token%/auto-recharge';            //DELETE
    const GET_INVOICE                    = '/1.2/users/%token%/invoice';                  //GET
    const GET_PRICES                     = '/1.0/prices';                                 //GET
    const GET_PROFILE_INFO               = '/1.1/users/%token%';                          //GET
    const GET_PROMOTIONS                 = '/1.0/promotions';                             //GET
    const GET_PURCHASES_LIST             = '/1.1/users/%token%/purchases';                //GET
    const GET_SENDER_IDS_LIST            = '/1.0/users/%token%/sender-ids';               //GET
    const GET_SMS_LIST                   = '/1.0/users/%token%/sms';                      //GET
    const GET_SMS_SUMMARY                = '/1.0/users/%token%/sms/summary';              //GET
    const LOG_OUT                        = '/1.0/users/%token%/logout';                   //GET
    const RECOVER_PASSWORD               = '/1.0/recoveries';                             //POST
    const REGISTER                       = '/1.4/users';                                  //POST
    const REQUEST_SENDER_ID              = '/1.0/users/%token%/sender-ids';               //POST
    const RESEND_CONFIRMATION            = '/1.3/users/%token%/resend-confirmation';      //GET
    const RESET_SENDER_ID                = '/1.0/users/%token%/sender-ids/reset';         //GET
    const SEND_SMS                       = '/1.1/users/%token%/sms';                      //POST
    const SET_INVOICE_DATA               = '/1.1/users/%token%/invoice';                  //POST

    /** @var string */
    private $username;
    /** @var string */
    private $token;
    /** @var float */
    private $balance;
    /** @var string */
    private $country;
    /** @var array */
    private $sender_id;
    /** @var array */
    private $auto_recharge;
    /** @var array */
    private $invoice;
    /** @var array */
    private $recharge = array();
    /** @var bool */
    private $email_confirmed = false;
    /** @var bool */
    private $profile_is_loaded = null;
    /** @var array */
    private $errors = array();

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->token = get_option( 'bookly_sms_token' );
    }

    /**
     * @return SMS
     */
    public static function getInstance()
    {
        if ( ! self::hasInCache( __FUNCTION__ ) ) {
            self::putInCache( __FUNCTION__, new static() );
        }

        return self::getFromCache( __FUNCTION__ );
    }

    /**
     * Register new account.
     *
     * @param string $username
     * @param string $password
     * @param string $password_repeat
     * @param string $country
     * @return bool
     */
    public function register( $username, $password, $password_repeat, $country )
    {
        $data = array( '_username' => $username, '_password' => $password, 'country' => $country );

        if ( $password !== $password_repeat && ! empty( $password ) ) {
            $this->errors[] = __( 'Passwords must be the same.', 'bookly' );

            return false;
        }

        return $this->sendPostRequest( self::REGISTER, $data );
    }

    /**
     * Log in.
     *
     * @param string $username
     * @param string $password
     * @return bool
     */
    public function login( $username, $password )
    {
        $data = array( '_username' => $username, '_password' => $password );

        $response = $this->sendPostRequest( self::AUTHENTICATE, $data );
        if ( $response ) {
            update_option( 'bookly_sms_token', $response['token'] );
            $this->token = $response['token'];

            return true;
        }

        return false;
    }

    /**
     * Confirm email.
     *
     * @param string $code
     * @return array|false
     */
    public function confirmEmail( $code )
    {
        $response = $this->sendPostRequest( self::CONFIRM_EMAIL, compact( 'code' ) );
        if ( $response ) {

            return $response;
        }

        return false;
    }

    /**
     * Resend confirmation email for sms account.
     *
     * @return bool
     */
    public function resendConfirmation()
    {
        $response = $this->sendGetRequest( self::RESEND_CONFIRMATION );
        if ( $response ) {

            return $response;
        }

        return false;
    }

    /**
     * Change country.
     *
     * @param string $country
     * @return array|false
     */
    public function changeCountry( $country )
    {
        $data = array( 'country' => $country );

        $response = $this->sendPatchRequest( self::CHANGE_COUNTRY, $data );
        if ( $response ) {

            return $response;
        }

        return false;
    }

    /**
     * Change password.
     *
     * @param string $new_password
     * @param string $old_password
     * @return bool
     */
    public function changePassword( $new_password, $old_password )
    {
        $data = array( '_old_password' => $old_password, '_new_password' => $new_password );

        $response = $this->sendPatchRequest( self::CHANGE_PASSWORD, $data );
        if ( $response ) {

            return true;
        }

        return false;
    }

    /**
     * Log out.
     */
    public function logout()
    {
        update_option( 'bookly_sms_token', '' );
        $this->setUndeliveredSmsCount( 0 );

        if ( $this->token ) {
            $this->sendGetRequest( self::LOG_OUT );
        }
        $this->token = null;
    }

    /**
     * Get PayPal PreApproval url, (for enabling auto recharge)
     *
     * @param string $recharge_id
     * @param string $url
     * @return bool|mixed
     */
    public function getPreApprovalUrl( $recharge_id, $url )
    {
        if ( $this->token ) {
            $response = $this->sendPostRequest(
                self::CREATE_PREAPPROVAL,
                array(
                    'recharge'      => $recharge_id,
                    'enabled_url'   => $url . '#auto-recharge=enabled',
                    'cancelled_url' => $url . '#auto-recharge=cancelled',
                )
            );
            if ( $response ) {
                return $response['redirect_url'];
            }
        }

        return false;
    }

    /**
     * Decline PayPal Preapproval. (disable auto recharge)
     *
     * @return bool
     */
    public function disableAutoRecharge()
    {
        if ( $this->token ) {
            $response = $this->sendDeleteRequest( self::DISABLE_AUTO_RECHARGE, array() );
            if ( $response ) {

                return true;
            }
        }

        return false;
    }

    /**
     * Send SMS.
     *
     * @param string $phone_number
     * @param string $message
     * @param string $impersonal_message
     * @param int    $type_id
     * @return bool
     */
    public function sendSms( $phone_number, $message, $impersonal_message, $type_id = null )
    {
        if ( $this->token ) {
            $data = array(
                'message'            => $message,
                'impersonal_message' => $impersonal_message,
                'phone'              => $this->normalizePhoneNumber( $phone_number ),
                'type'               => $type_id,
            );
            if ( $data['phone'] != '' ) {
                $response = $this->sendPostRequest( self::SEND_SMS, $data );
                if ( $response ) {
                    if ( array_key_exists( 'notify_low_balance', $response ) && get_option( 'bookly_sms_notify_low_balance' ) ) {
                        if ( $response['notify_low_balance'] ) {
                            $this->_sendLowBalanceNotification();
                        }
                    }
                    if ( array_key_exists( 'gateway_status' , $response ) ) {
                        if ( in_array( $response['gateway_status'], array( 1, 10, 11, 12, 13 ) ) ) {  /* @see SMS::getSmsList */

                            return true;
                        } elseif ( $response['gateway_status'] == 3 ) {
                            $this->errors[] = __( 'Your don\'t have enough Bookly SMS credits to send this message. Please add funds to your balance and try again.', 'bookly' );
                        } else {
                            $this->errors[] = __( 'Failed to send SMS.', 'bookly' );
                        }
                    }
                }
            } else {
                $this->errors[] = __( 'Phone number is empty.', 'bookly' );
            }
        }

        return false;
    }

    /**
     * Set invoice data ( client info )
     *
     * @param array $settings with keys [ send, company_name, company_address, company_address_l2, company_vat, company_code, send_copy, cc, company_add_text ]
     * @return bool
     */
    public function sendInvoiceData( array $settings )
    {
        if ( $this->token ) {
            $response = $this->sendPostRequest( self::SET_INVOICE_DATA, array( 'invoice' => $settings ) );
            if ( $response ) {

                return true;
            }
        }

        return false;
    }

    /**
     * Get link for downloading invoice file.
     *
     * @return string
     */
    public function getInvoiceLink()
    {
        $data = array();

        return $this->_prepareUrl( self::GET_INVOICE, $data );
    }

    /**
     * Return phone_number in international format without +
     *
     * @param $phone_number
     * @return string
     */
    public function normalizePhoneNumber( $phone_number )
    {
        // Remove everything except numbers and "+".
        $phone_number = preg_replace( '/[^\d\+]/', '', $phone_number );

        if ( strpos( $phone_number, '+' ) === 0 ) {
            // ok.
        } elseif ( strpos( $phone_number, '00' ) === 0 ) {
            $phone_number = ltrim( $phone_number, '0' );
        } else {
            // Default country code can contain not permitted characters. Remove everything except numbers.
            $phone_number = ltrim( preg_replace( '/\D/', '', get_option( 'bookly_cst_default_country_code', '' ) ), '0' )  . ltrim( $phone_number, '0' );
        }

        // Finally remove "+" if there were any among digits.
        return str_replace( '+', '', $phone_number );
    }

    /**
     * Load user profile info.
     *
     * @return bool
     */
    public function loadProfile()
    {
        if ( $this->token ) {
            if ( $this->profile_is_loaded === null ) {
                $response = $this->sendGetRequest( self::GET_PROFILE_INFO );
                if ( $response ) {
                    $this->username        = $response['username'];
                    $this->balance         = $response['balance'];
                    $this->country         = $response['country'];
                    $this->sender_id       = $response['sender_id'];
                    $this->auto_recharge   = $response['auto_recharge'];
                    $this->invoice         = $response['invoice'];
                    $this->recharge        = $response['recharge'];
                    $this->email_confirmed = $response['email_confirmed'];
                    $this->setUndeliveredSmsCount( $response['sms']['undelivered_count'] );

                    update_option( 'bookly_sms_promotions', $response['promotions'] );

                    $this->profile_is_loaded = true;
                } else {
                    $this->profile_is_loaded = false;
                }
            }

            return $this->profile_is_loaded;
        }

        $this->setUndeliveredSmsCount( 0 );

        return false;
    }

    /**
     * Load promotions info.
     *
     * @return array
     */
    public function loadPromotions()
    {
        $response = $this->sendGetRequest( self::GET_PROMOTIONS, array( 'token' => $this->token ) );
        if ( $response ) {
            update_option( 'bookly_sms_promotions', $response['promotions'] );

            return $response['promotions'];
        }

        return array();
    }

    /**
     * Client data for invoice.
     *
     * @return array
     */
    public function getInvoiceData()
    {
        return (array) $this->invoice;
    }

    /**
     * User forgot password for sms
     *
     * @param null $username
     * @param null $step
     * @param null $code
     * @param null $password
     * @return array|false
     */
    public function forgotPassword( $username = null, $step = null, $code = null, $password = null )
    {
        $data = array( '_username' => $username, 'step' => $step );
        switch ( $step ) {
            case 0:
                break;
            case 1:
                $data['code'] = $code;
                break;
            case 2:
                $data['code'] = $code;
                $data['password'] = $password;
                break;
        }
        $response = $this->sendPostRequest( self::RECOVER_PASSWORD, $data );

        if ( $response ) {

            return $response;
        }

        return false;
    }

    /**
     * Get purchases list.
     *
     * @param null $start_date
     * @param null $end_date
     * @return array
     */
    public function getPurchasesList( $start_date = null, $end_date = null )
    {
        if ( $this->token ) {
            $response = $this->sendGetRequest(
                self::GET_PURCHASES_LIST,
                compact( 'start_date', 'end_date' )
            );
            if ( $response ) {
                array_walk( $response['list'], function( &$item ) {
                    $date_time    = Utils\DateTime::UTCToWPTimeZone( $item['datetime'] );
                    $item['date'] = Utils\DateTime::formatDate( $date_time );
                    $item['time'] = Utils\DateTime::formatTime( $date_time );
                } );

                return $response;
            }
        }

        return array( 'success' => false, 'list' => array() );
    }

    /**
     * Get purchases list.
     *
     * @param null $start_date
     * @param null $end_date
     * @return array|false
     */
    public function getSummary( $start_date = null, $end_date = null )
    {
        if ( $this->token ) {
            $response = $this->sendGetRequest(
                self::GET_SMS_SUMMARY,
                compact( 'start_date', 'end_date' )
            );
            if ( $response ) {

                return $response['summary'];
            }
        }

        return false;
    }

    /**
     * Get SMS list.
     *
     * @param null $start_date
     * @param null $end_date
     * @return array
     */
    public function getSmsList( $start_date = null, $end_date = null )
    {
        if ( $this->token ) {
            $response = $this->sendGetRequest(
                self::GET_SMS_LIST,
                compact( 'start_date', 'end_date' )
            );
            if ( $response ) {
                array_walk( $response['list'], function( &$item ) {
                    $date_time = Utils\DateTime::UTCToWPTimeZone( $item['datetime'] );
                    $item['date']    = Utils\DateTime::formatDate( $date_time );
                    $item['time']    = Utils\DateTime::formatTime( $date_time );
                    $item['message'] = nl2br( preg_replace( '/([^\s]{50})+/U', '$1 ', htmlspecialchars( $item['message'] ) ) );
                    $item['phone']   = '+' . $item['phone'];
                    $item['charge']  = rtrim( $item['charge'], '0' );
                    $item['info']    = nl2br( htmlspecialchars( $item['info'] ) );
                    switch ( $item['status'] ) {
                        case 1:
                        case 10:
                            $item['status'] = __( 'Queued', 'bookly' );
                            $item['charge'] = '$' . $item['charge'];
                            break;
                        case 2:
                        case 16:
                            $item['status'] = __( 'Error', 'bookly' );
                            $item['charge'] = '';
                            break;
                        case 3:
                            $item['status'] = __( 'Out of credit', 'bookly' );
                            $item['charge'] = '';
                            break;
                        case 4:
                            $item['status'] = __( 'Country out of service', 'bookly' );
                            $item['charge'] = '';
                            break;
                        case 11:
                            $item['status'] = __( 'Sending', 'bookly' );
                            $item['charge'] = '$' . $item['charge'];
                            break;
                        case 12:
                            $item['status'] = __( 'Sent', 'bookly' );
                            $item['charge'] = '$' . $item['charge'];
                            break;
                        case 13:
                            $item['status'] = __( 'Delivered', 'bookly' );
                            $item['charge'] = '$' . $item['charge'];
                            break;
                        case 14:
                            $item['status'] = __( 'Failed', 'bookly' );
                            if ( $item['charge'] != '' ) {
                                $item['charge'] = '$' . $item['charge'];
                            }
                            break;
                        case 15:
                            $item['status'] = __( 'Undelivered', 'bookly' );
                            $item['charge'] = '$' . $item['charge'];
                            break;
                        default:
                            $item['status'] = __( 'Error', 'bookly' );
                            $item['charge'] = '';
                    }
                } );

                $this->setUndeliveredSmsCount( 0 );

                return $response;
            }
        }

        return array( 'success' => false, 'list' => array() );
    }

    /**
     * Get Price list.
     *
     * @return array
     */
    public function getPriceList()
    {
        $response = $this->sendGetRequest( self::GET_PRICES );
        if ( $response ) {
            return $response;
        }

        return array( 'success' => false, 'list' => array() );
    }

    /**
     * Get list of all requests for SENDER IDs.
     *
     * @return array
     */
    public function getSenderIdsList()
    {
        $response = $this->sendGetRequest( self::GET_SENDER_IDS_LIST );
        if ( $response ) {
            $response['pending'] = null;
            foreach ( $response['list'] as &$item ) {
                $item['date'] = Utils\DateTime::formatDate( Utils\DateTime::UTCToWPTimeZone( $item['date'] ) );
                if ( $item['name'] == '' ) {
                    $item['name'] = '<i>' . __( 'Default', 'bookly' ) . '</i>';
                }
                $item['status_date'] = $item['status_date'] ? Utils\DateTime::formatDate( Utils\DateTime::UTCToWPTimeZone( $item['status_date'] ) ) : '';
                switch ( $item['status'] ) {
                    case 0:
                        $item['status'] = __( 'Pending', 'bookly' );
                        $response['pending'] = $item['name'];
                        break;
                    case 1:
                        $item['status'] = __( 'Approved', 'bookly' );
                        break;
                    case 2:
                        $item['status'] = __( 'Declined', 'bookly' );
                        break;
                    case 3:
                        $item['status'] = __( 'Cancelled', 'bookly' );
                        break;
                }
            }

            return $response;
        }

        return array( 'success' => false, 'list' => array(), 'pending' => null );
    }

    /**
     * Request new SENDER ID.
     *
     * @param string $sender_id
     * @return array|false
     */
    public function requestSenderId( $sender_id )
    {
        if ( $this->token ) {
            $response = $this->sendPostRequest( self::REQUEST_SENDER_ID, array( 'name' => $sender_id ) );
            if ( $response ) {

                return $response;
            }
        }

        return false;
    }

    /**
     * Cancel request for SENDER ID.
     *
     * @return bool
     */
    public function cancelSenderId()
    {
        if ( $this->token ) {
            $response = $this->sendGetRequest( self::CANCEL_SENDER_ID );
            if ( $response ) {

                return true;
            }
        }

        return false;
    }

    /**
     * Reset SENDER ID to default (Bookly).
     *
     * @return bool
     */
    public function resetSenderId()
    {
        if ( $this->token ) {
            $response = $this->sendGetRequest( self::RESET_SENDER_ID );
            if ( $response ) {

                return true;
            }
        }

        return false;
    }

    /**
     * Create Stripe Checkout session
     *
     * @param int $recharge
     * @param string $mode
     * @param string $url
     * @return array|false
     */
    public function createStripeCheckoutSession( $recharge, $mode, $url )
    {
        if ( $this->token ) {
            $response = $this->sendPostRequest( self::CREATE_STRIPE_CHECKOUT_SESSION, array(
                'mode'        => $mode,
                'recharge'    => $recharge,
                'success_url' => $url . ( $mode == 'setup' ? '#auto-recharge=enabled' : '#payment=accepted' ),
                'cancel_url'  => $url . ( $mode == 'setup' ? '#auto-recharge=cancelled' : '#payment=cancelled' ),
            ) );
            if ( $response ) {

                return $response;
            }
        }

        return false;
    }

    /**
     * Create PayPal order
     *
     * @param int $recharge
     * @param string $mode
     * @param string $url
     * @return array|false
     */
    public function createPaypalOrder( $recharge, $url )
    {
        if ( $this->token ) {
            $response = $this->sendPostRequest( self::CREATE_PAYPAL_ORDER, array(
                'recharge'    => $recharge,
                'success_url' => $url . '#payment=accepted',
                'cancel_url'  => $url . '#payment=cancelled',
            ) );
            if ( $response ) {

                return $response['order_url'];
            }
        }

        return false;
    }

    /**
     * Send GET request.
     *
     * @param string $path
     * @param array  $data
     * @return array|false
     */
    private function sendGetRequest( $path, array $data = array() )
    {
        $url = $this->_prepareUrl( $path, $data );

        return $this->_handleResponse( $this->_sendRequest( 'GET', $url, $data ) );
    }

    /**
     * Send POST request.
     *
     * @param string $path
     * @param array  $data
     * @return array|false
     */
    private function sendPostRequest( $path, array $data )
    {
        $url = $this->_prepareUrl( $path, $data );

        return $this->_handleResponse( $this->_sendRequest( 'POST', $url, $data ) );
    }

    /**
     * Send PATCH request.
     *
     * @param string $path
     * @param array  $data
     * @return array|false
     */
    private function sendPatchRequest( $path, array $data )
    {
        $url = $this->_prepareUrl( $path, $data );

        return $this->_handleResponse( $this->_sendRequest( 'PATCH', $url, $data ) );
    }

    /**
     * Send DELETE request.
     *
     * @param string $path
     * @param array  $data
     * @return array|false
     */
    private function sendDeleteRequest( $path, array $data )
    {
        $url = $this->_prepareUrl( $path, $data );

        return $this->_handleResponse( $this->_sendRequest( 'DELETE', $url, $data ) );
    }

    /**
     * Get username.
     *
     * @return string
     */
    public function getUserName()
    {
        return $this->username;
    }

    /**
     * Get email_confirmed.
     *
     * @return string
     */
    public function getEmailConfirmed()
    {
        return $this->email_confirmed;
    }

    /**
     * Get balance.
     *
     * @return float
     */
    public function getBalance()
    {
        return $this->balance;
    }

    /**
     * Get country.
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Get sender ID.
     *
     * @return string
     */
    public function getSenderId()
    {
        return $this->sender_id['value'];
    }

    /**
     * Get sender ID approval date.
     *
     * @return string
     */
    public function getSenderIdApprovalDate()
    {
        return $this->sender_id['approved_at'];
    }

    /**
     * Whether auto-recharge enabled or not.
     *
     * @return bool
     */
    public function autoRechargeEnabled()
    {
        return $this->auto_recharge['enabled'];
    }

    /**
     * Get auto-recharge amount.
     *
     * @return float
     */
    public function getAutoRechargeAmount()
    {
        return $this->auto_recharge['amount'];
    }

    /**
     * Get auto-recharge bonus.
     *
     * @return float
     */
    public function getAutoRechargeBonus()
    {
        return $this->auto_recharge['bonus'];
    }

    /**
     * Get recharge data
     *
     * @return array
     */
    public function getRecharge()
    {
        return $this->recharge;
    }

    /**
     * Get errors.
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Clear errors.
     */
    public function clearErrors()
    {
        $this->errors = array();
    }

    /**
     * Set number undelivered sms.
     *
     * @param int $count
     */
    public function setUndeliveredSmsCount( $count )
    {
        update_option( 'bookly_sms_undelivered_count', (int) $count );
    }

    /**
     * Get number undelivered sms.
     *
     * @return int
     */
    public static function getUndeliveredSmsCount()
    {
        return (int) get_option( 'bookly_sms_undelivered_count', 0 );
    }

    /**
     * Get promotion for displaying in a notice
     *
     * @param null &$type
     * @param bool $force_load
     * @return string|null
     */
    public static function getPromotionForNotice(&$type = null, $force_load = false)
    {
        $promotions = $force_load ? self::getInstance()->loadPromotions() : get_option( 'bookly_sms_promotions', array() );
        $dismissed = get_user_meta( get_current_user_id(), 'bookly_dismiss_sms_promotion_notices', true ) ?: array();
        foreach( $promotions as $type => $promotion ) {
            if ( ! isset ( $dismissed[ $promotion['id'] ] ) || time() > $dismissed[ $promotion['id'] ] ) {
                return $promotion;
            }
        }

        return null;
    }

    /**
     * Prepare URL.
     *
     * @param string $path
     * @param array  $data
     * @return string
     */
    private function _prepareUrl( $path, array &$data )
    {
        $url = self::API_URL . str_replace( '%token%', $this->token, $path );
        foreach ( $data as $key => $value ) {
            if ( strpos( $key, '%' ) === 0 && substr( $key, - 1 ) == '%' ) {
                $url = str_replace( $key, $value, $url );
                unset ( $data[ $key ] );
            }
        }

        return $url;
    }

    /**
     * Send HTTP request.
     *
     * @param string $method
     * @param string $url
     * @param array  $data
     * @return string|null
     */
    private function _sendRequest( $method, $url, $data )
    {
        $args = array(
            'method'  => $method,
            'timeout' => 30,
        );

        if ( ! isset( $data['site_url'] ) ) {
            $data['site_url'] = site_url();
        }

        if ( $method == 'GET' ) {
            // WP 4.4.11 doesn't take into account the $data for the GET request
            // Manually move data in query string
            $query_data = array();
            foreach ( $data as $key => $value ) {
                $query_data[ $key ] = urlencode( $value );
            }
            $url = add_query_arg( $query_data, $url );
        } else {
            $args['body'] = $data;
        }

        $response = wp_remote_request( $url, $args );
        if ( $response instanceof \WP_Error ) {
            /** @var \WP_Error $response */
            $this->errors[] = $response->get_error_messages();

            return null;
        }

        return $response['body'];
    }

    /**
     * Check response for errors.
     *
     * @param mixed $response
     * @return array|false
     */
    private function _handleResponse( $response )
    {
        $response = json_decode( $response, true );

        if ( $response !== null && array_key_exists( 'success', $response ) ) {
            if ( $response['success'] == true ) {

                return $response;
            }
            if ( strncmp( $response['message'], 'ERROR_', 6 ) === 0 ) {
                $this->errors[ $response['message'] ] = $this->_translateError( $response['message'] );
            } else {
                $this->errors[] = $this->_translateError( $response['message'] );
            }
        } else {
            $this->errors[] = __( 'Error connecting to server.', 'bookly' );
        }

        return false;
    }

    /**
     * Send notification to administrators about low balance.
     */
    private function _sendLowBalanceNotification()
    {
        $sms_page_url = admin_url( 'admin.php?' . build_query( array( 'page' => \Bookly\Backend\Modules\Sms\Ajax::pageSlug() ) ) );
        $message = sprintf( __( "Dear Bookly SMS customer.\nWe would like to notify you that your Bookly SMS balance fell lower than 5 USD. To use our service without interruptions please recharge your balance by visiting Bookly SMS page <a href='%s'>here</a>.\n\nIf you want to stop receiving these notifications, please update your settings <a href='%s'>here</a>.", 'bookly' ), $sms_page_url . '#recharge', $sms_page_url . '#notifications-settings' );

        wp_mail(
            Utils\Common::getAdminEmails(),
            __( 'Bookly SMS - Low Balance', 'bookly' ),
            get_option( 'bookly_email_send_as' ) == 'html' ? wpautop( $message ) : $message,
            Utils\Common::getEmailHeaders()
        );
    }

    /**
     * Translate error message.
     *
     * @param string $error_code
     * @return string
     */
    private function _translateError( $error_code )
    {
        $error_codes = array(
            'ERROR_EMPTY_PASSWORD'                   => __( 'Empty password.', 'bookly' ),
            'ERROR_INCORRECT_PASSWORD'               => __( 'Incorrect password.', 'bookly' ),
            'ERROR_INCORRECT_RECOVERY_CODE'          => __( 'Incorrect recovery code.', 'bookly' ),
            'ERROR_INCORRECT_USERNAME_OR_PASSWORD'   => __( 'Incorrect email or password.', 'bookly' ),
            'ERROR_INVALID_SENDER_ID'                => __( 'Incorrect sender ID', 'bookly' ),
            'ERROR_INVALID_USERNAME'                 => __( 'Invalid email.', 'bookly' ),
            'ERROR_PENDING_SENDER_ID_ALREADY_EXISTS' => __( 'Pending sender ID already exists.', 'bookly' ),
            'ERROR_RECHARGE_NOT_AVAILABLE'           => __( 'Recharge not available.', 'bookly' ),
            'ERROR_RECOVERY_CODE_EXPIRED'            => __( 'Recovery code expired.', 'bookly' ),
            'ERROR_SENDING_EMAIL'                    => __( 'Error sending email.', 'bookly' ),
            'ERROR_USER_NOT_FOUND'                   => __( 'User not found.', 'bookly' ),
            'ERROR_USERNAME_ALREADY_EXISTS'          => __( 'Email already in use.', 'bookly' ),
        );
        if ( array_key_exists( $error_code, $error_codes ) ) {
            $message = $error_codes[ $error_code ];
        } else {
            // Build message from error code.
            if ( strncmp( $error_code, 'ERROR_', 6 ) === 0 ) {
                $error_code = substr( $error_code, 6 );
            }
            $message = __( ucfirst( strtolower ( str_replace( '_', ' ', $error_code ) ) ), 'bookly' );
        }

        return $message;
    }
}