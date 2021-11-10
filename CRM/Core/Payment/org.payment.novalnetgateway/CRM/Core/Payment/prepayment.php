<?php
/**
 * Novalnet payment method module
 * This module is used for real time processing of
 * Novalnet transaction of customers.
 *
 * Author    Novalnet AG
 * Copyright (c) Novalnet
 * License   https://www.novalnet.de/payment-plugins/kostenlos/lizenz
 * 
 * If you have found this script useful a small
 * recommendation as well as a comment on merchant form
 * would be greatly appreciated.
 *
 * Script: prepayment.php
 *
 */
require_once 'CRM/Core/Payment/org.payment.novalnetgateway/lib/novalnetIPN.php';
require_once 'CRM/Core/BAO/CustomField.php';
require_once 'CRM/Utils/Hook.php';
/**
 * Prepayment class
 *
 */
class CRM_Core_Payment_prepayment extends CRM_Core_Payment {

    static private $_singleton;

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
		$this->_processorName = ts('Novalnet Prepayment');
        $this->_vendorid = trim(Civi::settings()->get('nn_vendor'));
        $this->_tariffid = trim(Civi::settings()->get('nn_tariff'));
        $this->_authcode = trim(Civi::settings()->get('nn_authcode'));
        $this->_productid = trim(Civi::settings()->get('nn_product'));
        $this->_testmode = trim(Civi::settings()->get('nn_prepayment_testmode'));
        $this->_notify_url = trim(Civi::settings()->get('nn_notify_url'));
        $this->_paymentType = 'novalnet_prepayment';
        $this->_testmode = (($this->_mode == 'test') ? 1 : ($this->_testmode ? 1 : 0));
        defined('MODE_PREPAYMENT') || define('MODE_PREPAYMENT', $this->_testmode);
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
     *
     * @return object
     * @static
     *
     */
    static function &singleton($mode, &$paymentProcessor, &$paymentForm = NULL, $force = FALSE) {
        $processorName = $paymentProcessor['name'];
        if (self::$_singleton[$processorName] === NULL) {
            self::$_singleton[$processorName] = new CRM_Core_Payment_prepayment($mode, $paymentProcessor);
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
     * Transfer method functions not in use
     *
     * @param array $params  name value pair of contribution data
     *
     * @return void
     * @access public
     *
     */
    function doDirectPayment(&$params) {
        CRM_Core_Error::fatal('This function is not implemented');
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
        $data = array();
		$config = CRM_Core_Config::singleton();
        $_SESSION['novalnet']['payment_type'] = $this->_paymentType;
        $component = strtolower($component);
        $language = CRM_Core_Payment_novalnet::getLanguage();
        $response['processor_name'] = $this->_paymentProcessor['payment_processor_type'];
        $response['orderid'] = $params['invoiceID'];
        $response['is_recu'] = (isset($params['is_recur']) && $params['is_recur'] == 1) ? '1' : 0;
        CRM_Core_Payment_novalnet::paymentNameUpdation($response);
        CRM_Core_Payment_novalnet::checkComponent($component);
        CRM_Core_Payment_novalnet::redirectUrl($component, $cancelURL, $params);

        if (!CRM_Core_Payment_novalnet::getProfileDetails($params, $data)) {
            CRM_Core_Payment_novalnet::paymentUpdation($response);
            CRM_Core_Error::statusBounce($data['value'], $cancelURL);
        }
        CRM_Core_Payment_novalnet::centsConvert($params['amount'], $data);
        $data['key']        = CRM_Core_Payment_novalnet::getPaymentKey($this->_paymentType);
        $data['vendor']     = $this->_vendorid;
        $data['auth_code']  = $this->_authcode;
        $data['tariff']     = $this->_tariffid;
        $data['product']    = $this->_productid;
        $data['test_mode']  = $this->_testmode;
        $data['lang']       = $language;
        $data['language']   = $language;
        if(!empty($this->_notify_url)) {
        $data['notify_url']  = $this->_notify_url;
        }
        $data['processor_name'] = $this->_paymentProcessor['payment_processor_type'];
        $data['invoice_type']   = 'PREPAYMENT';
        $data['invoice_ref']    = 'BNR-' . $data['product'] . '-' . $params['invoiceID'];

        CRM_Core_Payment_novalnet::urlparams($params, $data, $this->_paymentType);

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
        $dataQuery = CRM_Utils_System::makeQueryString($data);
        $host = CRM_Core_Payment_novalnet::getPaymentUrl($this->_paymentType);
        $httppost = CRM_Utils_HttpClient::singleton();

        list($result, $response) = $httppost->post($host, $dataQuery);
        parse_str($response, $parsed);
        if ((isset($parsed['status']) && $parsed['status'] == 100)) {
            $data['processor_name'] = $this->_paymentProcessor['payment_processor_type'];
            $data['mode'] = ($this->_mode == 'test' ? '1' : '0');
            $data['check'] = $this->_mode;
            $data['payment_mode'] = (($this->_mode == 'test') ? 1 :( $this->_testmode ? 1 : 0));
            $final = array_merge($parsed, $data);
            CRM_Core_Payment_novalnet::orderCompleteParam($component, $params, $final);
            $final['test_mode'] = $parsed['test_mode'];
            $novalnetipn = new org_novalnet_payment_novalnetipn($this->_mode, $this->_paymentProcessor);
            $novalnetipn->main($final, $this->_paymentType);
            return $params;
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
