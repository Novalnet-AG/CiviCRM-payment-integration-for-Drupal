<?php
/**
 * Novalnet payment method module
 * This module is used for real time processing of
 * Novalnet transaction of customers.
 *
 * Copyright (c) Novalnet AG
 *
 * Released under the GNU General Public License
 * This free contribution made by request.
 * If you have found this script useful a small
 * recommendation as well as a comment on merchant form
 * would be greatly appreciated.
 *
 * Script: paypal.php
 *
 */
require_once 'CRM/Core/Payment/org.payment.novalnetgateway/lib/novalnetIPN.php';
require_once 'CRM/Utils/Array.php';
require_once 'CRM/Core/Session.php';

class CRM_Core_Payment_paypal extends CRM_Core_Payment {

    static private $_singleton = null;

    /**
     * Constructor
     *
     * @param $mode string
     * @param $paymentProcessor array
     *
     * @return void
     */
    function __construct($mode, &$paymentProcessor) {
        $template = CRM_Core_Smarty::singleton();
        $config = CRM_Core_Config::singleton();
        $this->_mode = $mode;
        $this->_paymentProcessor = $paymentProcessor;
		$this->_processorName = ts('Novalnet PayPal');
        $this->_paymentType = 'novalnet_paypal';
        $this->_vendorid = trim(Civi::settings()->get('nn_vendor'));
        $this->_tariffid = trim(Civi::settings()->get('nn_tariff'));
        $this->_authcode = trim(Civi::settings()->get('nn_authcode'));
        $this->_productid = trim(Civi::settings()->get('nn_product'));
        $this->_testmode = trim(Civi::settings()->get('nn_paypal_testmode'));
        $this->_password = trim(Civi::settings()->get('nn_password'));
        $this->_paypaluser = trim(Civi::settings()->get('nn_paypal_user'));
        $this->_paypalsig = trim(Civi::settings()->get('nn_paypal_sig'));
        $this->_paypalpwd = trim(Civi::settings()->get('nn_paypal_pwd'));
        $this->_testmode = (($this->_mode == 'test') ? 1 : ($this->_testmode ? 1 : 0));
        $this->_notify_url = trim(Civi::settings()->get('nn_notify_url'));
        list($logo, $description) = CRM_Core_Payment_novalnet::assignLogoAndDescription($this, $this->_paymentProcessor['name']);
		 $this->_cancelStatus = CRM_Core_Payment_novalnet::getSubscriptionCancelReason();

    }

    /**
     * Singleton function used to manage this object
     *
     * @param string $mode the mode of operation: live or test
     * @param $paymentProcessor
     * @param $paymentForm
     * @param $force
     * @return object
     * @static
     *
     */
    static function &singleton($mode, &$paymentProcessor, &$paymentForm = NULL, $force = FALSE) {
        $processorName = $paymentProcessor['name'];
        if (self::$_singleton[$processorName] === NULL) {
            self::$_singleton[$processorName] = new CRM_Core_Payment_paypal($mode, $paymentProcessor);
        }
        return self::$_singleton[$processorName];
    }

    /**
     * This function checks to see if we have the right config values
     *
     * @return NULL
     * @public
     */
    function checkConfig() {
        return NULL;
    }

    /**
     * Transfer method not in use
     *
     * @param array $params  name value pair of contribution data
     *
     * @return void
     * @access public
     *
     *
     */
    function doDirectPayment(&$params) {
        CRM_Core_Error::fatal(ts('This function is not implemented'));
    }

    /**
     * Chekout function
     * @param  array $params assoc array of input parameters for this transaction
     * @param  $component
     *
     * @return array the result in a nice formatted array (or an error object)
     * @public
     */
    function doTransferCheckout(&$params, $component) {
        $_SESSION['novalnet']['payment_type'] = $this->_paymentType;
        $config = CRM_Core_Config::singleton();
        $component = strtolower($component);
        $language = CRM_Core_Payment_novalnet::getLanguage();
        $response['processor_name'] = $this->_paymentProcessor['payment_processor_type'];
        $response['orderid'] = $params['invoiceID'];
        CRM_Core_Payment_novalnet::paymentNameUpdation($response);
        CRM_Core_Payment_novalnet::checkComponent($component);
        CRM_Core_Payment_novalnet::redirectUrl($component, $cancelURL, $params);

        if (!CRM_Core_Payment_novalnet::getProfileDetails($params, $data)) {
            CRM_Core_Payment_novalnet::paymentUpdation($response);
            CRM_Core_Error::statusBounce($data['value'], $cancelURL);
        }
        CRM_Core_Payment_novalnet::centsConvert($params['amount'], $data);
        $user_variable = $config->userFrameworkBaseURL;

        $data['key'] = CRM_Core_Payment_novalnet::getPaymentKey($this->_paymentType);
        $data['auth_code'] = $this->_authcode;
        $data['vendor'] = $this->_vendorid;
        $data['product'] = $this->_productid;
        $data['tariff'] = $this->_tariffid;
        $data['test_mode'] = $this->_testmode;
        $data['uniqid'] = uniqid();
        $data['processor_name'] = $this->_paymentProcessor['payment_processor_type'];
        $data['user_variable_0'] = $user_variable;
        $data['lang'] = $language;
        $data['language'] = $language;
        $data['api_signature'] = $this->_paypalsig;
        $data['api_user'] = $this->_paypaluser;
        $data['api_pw'] = $this->_paypalpwd;
        if(!empty($this->_notify_url)) {
        $data['notify_url']  = $this->_notify_url;
        }
        $_SESSION['nn']['mode'] = ($this->_mode == 'test' ? '1' : '0');
        $_SESSION['nn']['check'] = $this->_mode;
        $_SESSION['nn']['payment_mode'] = $this->_testmode;
        if (isset($params['is_recur']) && $params['is_recur'] == 1) {
            if (!isset($params['installments']) || (int) $params['installments'] != 1) {
                $frequency_interval = CRM_Core_Payment_novalnet::getFrequencyInterval($params);
                $error = CRM_Core_Payment_novalnet::validateOnRecurring($frequency_interval);
                if ($error) {
                    CRM_Core_Payment_novalnet::paymentUpdation($response);
                    CRM_Core_Error::statusBounce($error, $cancelURL);
                }
                $nn_subscription_tariff_id = Civi::settings()->get('nn_subscription_tariff_id');
                $data['tariff_period'] = $frequency_interval;
                $data['tariff_period2'] = $frequency_interval;
                $data['tariff_period2_amount'] = $data['amount'];
                $data['tariff'] = trim($nn_subscription_tariff_id);
            }
        }
        $toBeEncoded = array('auth_code', 'product', 'tariff', 'amount', 'test_mode', 'uniqid','api_signature','api_user','api_pw');
        CRM_Core_Payment_novalnet::novalnetEncode($data, $this->_password, $toBeEncoded);
        CRM_Core_Payment_novalnet::generateNovalnetHash($data, $this->_password);

        CRM_Core_Payment_novalnet::urlparams($params, $data, $this->_paymentType);
         CRM_Core_Payment_novalnet::returnUrlParams($data,$component, $this->_paymentProcessor['payment_processor_type']);
        CRM_Core_Payment_novalnet::getPaymentReference($data, $this->_paymentType);
        CRM_Core_Payment_novalnet::orderCompleteParam($component, $params, $data, true);
        $url = CRM_Core_Payment_novalnet::getPaymentUrl($this->_paymentType);

        CRM_Core_Session::storeSessionObjects();
        CRM_Core_Payment_novalnet::getSubmitForm('nn_redirect_form', $data, $url);
    }

    /**
     * To complete the order
     *
     * @param none
     *
     * return none
     */
    public function handlePaymentNotification() {
        $response = $_REQUEST;
        $response = array_merge($response, $_SESSION['novalnet']['completedata']);
        $qfKey = $response['qfKey'];
        $response['mode'] = $_SESSION['nn']['mode'];
        $response['check'] = $_SESSION['nn']['check'];
        $response['payment_mode'] = $_SESSION['nn']['payment_mode'];
        if ($response['module'] == 'contribute') {
			$url = CRM_Utils_System::url('civicrm/contribute/transact', "_qf_Main_display=true&cancel=1&qfKey={$qfKey}", FALSE, NULL, FALSE);
		} elseif ($response['module'] == 'event') {
			$eventid = $response['eid'];
			$url = CRM_Utils_System::url('civicrm/event/register', "id={$eventid}", FALSE, NULL, FALSE);
		}
        if (isset($response['hash2']) && ($response['status'] == 100 || $response['status'] == 90 )) {

            if (!CRM_Core_Payment_novalnet::novalnetCheckHash($response, $this->_password)) {
                CRM_Core_Session::setStatus(ts('While redirecting some data has been changed. The hash check failed.'), 'error');
            } else {
                CRM_Core_Payment_novalnet::novalnetDecode($response, $this->_password);

                $novalnetipn = new org_novalnet_payment_novalnetipn($this->_mode, $this->_paymentProcessor);
                $novalnetipn->main($response, 'novalnet_paypal');
            }
        } else {
            $session = CRM_Core_Session::singleton();
            $session->getStatus($rese = TRUE);
            CRM_Core_Payment_novalnet::paymentUpdation($response);
            $error = CRM_Core_Payment_novalnet::checkstatus($response);
            CRM_Core_Payment_novalnet::commentsOnError($response, $error);
            CRM_Core_Session::setStatus($error);
        }
        CRM_Utils_System::redirect($url);
    }

     /**
     * cancel the subscription/recurring payment
     *
     * @param  $message string
     * @param  $cancelParams array
     *
     * @return boolean
     *
     */
    function cancelSubscription($message, $cancelParams) {
		return CRM_Core_Payment_novalnet::subscriptionCancel($message, $cancelParams);
    }

    /**
     * change subscription/recurring amount
     *
     * @param  $message string
     * @param  $cancelParams array
     *
     * @return boolean
     *
     */
    function changeSubscriptionAmount(&$message, $cancelParams) {
		return CRM_Core_Payment_novalnet::subscriptionAmountChange($message, $cancelParams);
    }

}
