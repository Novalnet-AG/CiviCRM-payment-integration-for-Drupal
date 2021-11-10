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
 * Script: cc.php
 *
 */
require_once 'CRM/Core/Payment/org.payment.novalnetgateway/lib/novalnetIPN.php';
require_once 'CRM/Core/BAO/CustomField.php';
require_once 'CRM/Utils/Hook.php';
require_once 'CRM/Core/Form.php';

class CRM_Core_Payment_cc extends CRM_Core_Payment {

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
        $this->_processorName = ts('Novalnet Credit Card');
        $this->_paymentType = 'novalnet_cc';

        $this->_vendorid = trim(Civi::settings()->get('nn_vendor'));
        $this->_tariffid = trim(Civi::settings()->get('nn_tariff'));
        $this->_authcode = trim(Civi::settings()->get('nn_authcode'));
        $this->_productid = trim(Civi::settings()->get('nn_product'));
        $this->_testmode = trim(Civi::settings()->get('nn_cc_testmode'));
        $this->_password = trim(Civi::settings()->get('nn_password'));
        $this->_notify_url = trim(Civi::settings()->get('nn_notify_url'));
        $this->_manualchecklimit = Civi::settings()->get('nn_cc_manualamount');
        $this->_nnlabel = trim(Civi::settings()->get('nn_cc_css_settings_label'));
        $this->_nninput = trim(Civi::settings()->get('nn_cc_css_settings_input'));
        $this->_csstext = Civi::settings()->get('nn_cc_css_settings_css_text');
        $this->_testmode = (($this->_mode == 'test')?1:($this->_testmode?1:0));
        defined('MODE_CC') || define('MODE_CC', $this->_testmode);
        $this->_iframe = self::novalnetDisplayIframe($this);
        CRM_Core_Payment_novalnet::assignLogoAndDescription($this, $this->_paymentProcessor['name']);
        CRM_Core_Payment_novalnet::setCreditCardDetails($config, $this);
        $this->_cancelStatus = CRM_Core_Payment_novalnet::getSubscriptionCancelReason();
    }
    /**
     * function novalnetDisplayIframe
     *
     * @param $iframedata
     * @return void
     * @static
     *
     */
    function novalnetDisplayIframe($iframedata) {

        $language = strtolower(CRM_Core_Payment_novalnet::getLanguage());
        $server_ip = CRM_Utils_System::ipAddress();
        $encodedkey = base64_encode("vendor=$iframedata->_vendorid&product=$iframedata->_productid&server_ip=$server_ip&lang=$language");
        $nniframe_source = 'https://secure.novalnet.de/cc?api=' . $encodedkey;
        $cc_hidden_field = '<input type="hidden" value="'.$this->_nnlabel.'" id="nn_label">
        <input type="hidden" value="'.$this->_nninput.'" id="nn_input">
        <input type="hidden" value="'.$this->_csstext.'" id="nn_css_text">';

        return '<iframe id="nnIframe" width="100%;" src="'.$nniframe_source.'" onload="getFormValue()" frameBorder="0" scrolling="none"></iframe>'.$cc_hidden_field.'<input type="hidden" id="nn_cc_pan_hash" name="nn_cc_pan_hash" /><input type="hidden" id="nn_cc_uniqueid" name="nn_cc_uniqueid" />';

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
            self::$_singleton[$paymentProcessor['name']] = new CRM_Core_Payment_cc($mode, $paymentProcessor);
        }
        return self::$_singleton[$paymentProcessor['name']];
    }
    /**
     * This function checks to see if we have the right config values
     *
     * @return NULL;
     * @public
     */
    function checkConfig() {
        return NULL;
    }

    /**
     * Chekout function
     * @param  array $params assoc array of input parameters for this transaction
     *
     * @return array the result in a nice formatted array (or an error object)
     * @param $component
     * @public
     */
    function doTransferCheckout(&$params, $component) {
        $error = '';$cancelURL = '';$data = array();
        $_SESSION['novalnet']['payment_type'] = $this->_paymentType;
        $_SESSION['qfKey'] = $params['qfKey'];
        $config = CRM_Core_Config::singleton();
        $language = CRM_Core_Payment_novalnet::getLanguage();
        $response['processor_name'] = $this->_paymentProcessor['payment_processor_type'];
        $response['orderid'] = $params['invoiceID'];
        $response['is_recu'] = (isset($params['is_recur']) && $params['is_recur'] == 1) ? '1' : 0;

        CRM_Core_Payment_novalnet::paymentNameUpdation($response);
        CRM_Core_Payment_novalnet::checkComponent($component);
        CRM_Core_Payment_novalnet::redirectUrl($component, $cancelURL, $params);
        CRM_Core_Payment_novalnet::centsConvert($params['amount'], $data);
        CRM_Core_Payment_novalnet:: manualCheckLimit($data, $this->_manualchecklimit);

        if (!CRM_Core_Payment_novalnet::getProfileDetails($params, $data)) {
            CRM_Core_Payment_novalnet::paymentUpdation($response);
            CRM_Core_Error::statusBounce($data['value'], $cancelURL);
        }
        $data['key']            = CRM_Core_Payment_novalnet::getPaymentKey($this->_paymentType);
        $data['vendor']         = $this->_vendorid;
        $data['auth_code']      = $this->_authcode;
        $data['product']        = $this->_productid;
        $data['tariff']         = $this->_tariffid;
        $data['test_mode']      = (($this->_mode == 'test') ? 1 : ($this->_testmode ? 1 : 0));
        $data['lang']           = $language;
        $data['language']       = $language;

        if(!empty($this->_notify_url)) {
            $data['notify_url'] = $this->_notify_url;
        }
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

        $data['pan_hash']  = $_SESSION['nn_payment_cc_pan_hash'];
        $data['unique_id'] = $_SESSION['nn_payment_cc_uniqueid'];

        $nn_cc_secure_active = Civi::settings()->get('nn_cc_secure_active');
        $nn_cc_force_secure_active = Civi::settings()->get('nn_cc_force_secure_active');
        if($data['pan_hash'] == '') {
			$cancelUrlString = "=1&cancel=1&qfKey={$params['qfKey']}";
			$cancelURL = CRM_Utils_System::url('civicrm/contribute/transact', $cancelUrlString, true, null, false);
			CRM_Core_Error::statusBounce(ts('Your credit card details are invalid'), $cancelURL);
		}
        if($nn_cc_secure_active != 1 && $nn_cc_force_secure_active != 1) {
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

        }else {
            if($nn_cc_secure_active == '1') {
                $data['cc_3d'] = '1';
            }

            $data['uniqid']         = CRM_Core_Payment_novalnet::getUniqueid();
            $data['implementation'] = 'ENC';
            $_SESSION['nn']['mode'] = ($this->_mode == 'test' ? '1' : '0');
            $_SESSION['nn']['check'] = $this->_mode;
            $_SESSION['nn']['payment_mode'] = $this->_testmode;
            $toBeEncoded = array('auth_code', 'product', 'tariff', 'amount', 'test_mode');
            
            if (isset($params['is_recur']) && $params['is_recur'] == 1) {
                $tariffToBeEncoded = array('tariff_period', 'tariff_period2', 'tariff_period2_amount');
                $toBeEncoded = array_merge($toBeEncoded, $tariffToBeEncoded);
            }
            CRM_Core_Payment_novalnet::novalnetEncode($data, $this->_password, $toBeEncoded);
            CRM_Core_Payment_novalnet::generateNovalnetHash($data,$this->_password);
            CRM_Core_Payment_novalnet::urlparams($params, $data, $this->_paymentType);
            CRM_Core_Payment_novalnet::returnUrlParams($data,$component, $this->_paymentProcessor['payment_processor_type'], $params);
            CRM_Core_Payment_novalnet::orderCompleteParam($component, $params, $data, true);
            CRM_Core_Session::storeSessionObjects();
            CRM_Core_Payment_novalnet::getSubmitForm('nn_redirect_form', $data, 'https://payport.novalnet.de/pci_payport');
        }
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
        $_SESSION['novalnet']['tid'] = $response['tid'];
        $_SESSION['novalnet']['status'] = $response['status'];
        $_SESSION['novalnet']['key'] = $response['key'];
        $response = array_merge($response, $_SESSION['novalnet']['completedata']);
        $qfKey = $response['qfKey'];
        $response['mode'] = $_SESSION['nn']['mode'];
        $response['check'] = $_SESSION['nn']['check'];
        $response['payment_mode'] = $_SESSION['nn']['payment_mode'];
        $invoiceId = CRM_Utils_Array::value('inId', $_GET);
        if ($response['module'] == 'contribute') {
            $url = CRM_Utils_System::url('civicrm/contribute/transact', "_qf_Main_display=true&cancel=1&qfKey={$qfKey}", FALSE, NULL, FALSE);
        } elseif ($response['module'] == 'event') {
            $eventid = $response['eid'];
            $url = CRM_Utils_System::url('civicrm/event/register', "id={$eventid}", FALSE, NULL, FALSE);
        }
        if (isset($response['hash2']) && $response['status'] == 100) {
            if (!CRM_Core_Payment_novalnet::novalnetCheckHash($response, $this->_password)) {
                CRM_Core_Session::setStatus(ts('While redirecting some data has been changed. The hash check failed.'), 'error');
            } else {
                CRM_Core_Payment_novalnet::novalnetDecode($response, $this->_password);
                $novalnetipn = new org_novalnet_payment_novalnetipn($this->_mode, $this->_paymentProcessor);
                $novalnetipn->main($response, 'novalnet_cc');
            }
        } else {
            $session = CRM_Core_Session::singleton();
            $session->getStatus($rese = TRUE);
            CRM_Core_Payment_novalnet::updateNovalnetTransactionDetail($response, $this->_paymentType);
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
     * @param  $params array
     *
     * @return none
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
