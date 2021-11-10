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
 * Script: sepa.php
 *
 */
require_once 'CRM/Core/Payment/org.payment.novalnetgateway/lib/novalnetIPN.php';
require_once 'CRM/Core/BAO/CustomField.php';
require_once 'CRM/Utils/Hook.php';
require_once 'CRM/Core/Form.php';

class CRM_Core_Payment_sepa extends CRM_Core_Payment {

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
        $config = CRM_Core_Config::singleton();
        $this->_mode = $mode;
        $this->_paymentProcessor = $paymentProcessor;
        $this->_processorName = ts('Novalnet Direct Debit SEPA');
        $nn_notify_url = trim(Civi::settings()->get('nn_notify_url'));
        $this->_vendorid = trim(Civi::settings()->get('nn_vendor'));
        $this->_tariffid = trim(Civi::settings()->get('nn_tariff'));
        $this->_authcode = trim(Civi::settings()->get('nn_authcode'));
        $this->_productid = trim(Civi::settings()->get('nn_product'));
        $this->_testmode = trim(Civi::settings()->get('nn_sepa_testmode'));
        $this->_manualchecklimit = trim(Civi::settings()->get('nn_sepa_manualamount'));
        $this->_paymentType = 'novalnet_sepa';
        $this->_testmode = (($this->_mode == 'test')?1:($this->_testmode?1:0));
        $this->_notify_url = (isset($nn_notify_url) ? $nn_notify_url : '');
        CRM_Core_Payment_novalnet::assignLogoAndDescription($this, $this->_paymentProcessor['name']);
        CRM_Core_Payment_novalnet::setSepaDetails($config, $this);
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
        if (self::$_singleton[$paymentProcessor['name']] === NULL) {
            self::$_singleton[$paymentProcessor['name']] = new CRM_Core_Payment_sepa($mode, $paymentProcessor);
        }
        return self::$_singleton[$paymentProcessor['name']];
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
     * Chekout function
     * @param  array $params assoc array of input parameters for this transaction
     * @param $component
     * @return array the result in a nice formatted array (or an error object)
     * @public
     */
    function doTransferCheckout(&$params, $component) {
        $error = '';
        $data = array();
        $_SESSION['novalnet']['payment_type'] = $this->_paymentType;
        $config = CRM_Core_Config::singleton();
        $language = CRM_Core_Payment_novalnet::getLanguage();
        $response['processor_name'] = $this->_paymentProcessor['payment_processor_type'];
        $response['orderid'] = $params['invoiceID'];
        $response['is_recu'] = (isset($params['is_recur']) && $params['is_recur'] == 1) ? '1' : 0;
        CRM_Core_Payment_novalnet::paymentNameUpdation($response);
        CRM_Core_Payment_novalnet::checkComponent($component);
        CRM_Core_Payment_novalnet::redirectUrl($component, $cancelURL, $params);
        CRM_Core_Payment_novalnet::centsConvert($params['amount'], $data);
        $data['product'] = $this->_productid;
        $data['tariff'] = $this->_tariffid;
        CRM_Core_Payment_novalnet::manualCheckLimit($data, $this->_manualchecklimit);
        $nn_sepa_due_date = Civi::settings()->get('nn_sepa_due_date');
        $duedate = (isset($nn_sepa_due_date) && empty($nn_sepa_due_date)) ? '7' : trim($nn_sepa_due_date);
        $data['sepa_due_date'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + $duedate, date("Y")));
        $account_details =  unserialize(strrev(base64_decode($_SESSION['sepa']['account_details'])));

        if (CRM_Core_Payment_novalnet::validateSepaData($account_details)) {
            CRM_Core_Payment_novalnet::paymentUpdation($response);
            CRM_Core_Error::statusBounce(t('Your account details are invalid'), $cancelURL);
        }

        if (!CRM_Core_Payment_novalnet::getProfileDetails($params, $data)) {
            CRM_Core_Payment_novalnet::paymentUpdation($response);
            CRM_Core_Error::statusBounce($data['value'], $cancelURL);
        }
        $data['key'] = CRM_Core_Payment_novalnet::getPaymentKey($this->_paymentType);
        $data['vendor'] = $this->_vendorid;
        $data['auth_code'] = $this->_authcode;
        $data['test_mode'] = (($this->_mode == 'test')?1:($this->_testmode?1:0));
        $data['sepa_unique_id'] = $account_details['uniqid'];
        $data['sepa_hash'] = $account_details['panhash'];
        $data['bank_account_holder'] = $account_details['holder'];
        $data['lang'] = $language;
        $data['language'] = $language;
		if(!empty($this->_notify_url)) {
        $data['notify_url']  = $this->_notify_url;
        }
        CRM_Core_Payment_novalnet::urlparams($params, $data,$this->_paymentType);
        CRM_Core_Payment_novalnet::getPaymentReference($data, $this->_paymentType);

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
	    $data = CRM_Utils_System::makeQueryString($data);
        $host = CRM_Core_Payment_novalnet::getPaymentUrl($this->_paymentType);
        $httppost = CRM_Utils_HttpClient::singleton();

        list($result, $response) = $httppost->post($host, $data);
        parse_str($response, $parsed);

        if (isset($parsed['status']) && $parsed['status'] == '100') {
            $final['processor_name'] = $this->_paymentProcessor['payment_processor_type'];
            parse_str($data, $param);
             $param['mode'] = ($this->_mode == 'test' ? '1' : '0');
            $param['check'] = $this->_mode;
            $param['payment_mode'] = (($this->_mode == 'test') ? 1 :( $this->_testmode ? 1 : 0));
            $final = array_merge($final, $parsed, $param);
            CRM_Core_Payment_novalnet::orderCompleteParam($component, $params, $final);
            $final['test_mode'] = $parsed['test_mode'];
            $novalnetipn = new org_novalnet_payment_novalnetipn($this->_mode, $this->_paymentProcessor);
            $novalnetipn->main($final, $this->_paymentType);
        } else {
            $parsed['processor_name'] = $this->_paymentProcessor['payment_processor_type'];
            $parsed['orderid'] = $params['invoiceID'];
            CRM_Core_Payment_novalnet::paymentUpdation($parsed);
            if (isset($params['is_recur']) && $params['is_recur'] == 1) {
                $order['orderid'] = $params['invoiceID'];
                CRM_Core_Payment_novalnet::paymentUpdationOnRecur($order);
            }
            $error = CRM_Core_Payment_novalnet::checkstatus($parsed);
            CRM_Core_Payment_novalnet::commentsOnError($parsed, $error);
            CRM_Core_Error::statusBounce($error, $cancelURL);
        }
    }

    /**
     * Transfer method functions not in use
     *
     * @param array $params  name value pair of contribution data
     *
     * @return void
     * @access public
     *
     */
    function doDirectPayment(&$params) {
        CRM_Core_Error::fatal(ts('This function is not implemented'));
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
