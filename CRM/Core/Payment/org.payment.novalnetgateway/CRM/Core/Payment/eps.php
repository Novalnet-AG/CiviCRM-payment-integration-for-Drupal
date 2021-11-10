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
 * Script: eps.php
 *
 */
require_once 'CRM/Core/Payment/org.payment.novalnetgateway/lib/novalnetIPN.php';
require_once 'CRM/Utils/Array.php';
require_once 'CRM/Core/Session.php';

/**
 * Payment Processor class for Novalnet EPS
 */
class CRM_Core_Payment_eps extends CRM_Core_Payment {

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
        $template = CRM_Core_Smarty::singleton();
        $this->_mode = $mode;
        $this->_paymentProcessor = $paymentProcessor;
        $this->_paymentType = 'novalnet_eps';
		$this->_processorName = ts('Novalnet eps');
        $this->_vendorid = trim(Civi::settings()->get('nn_vendor'));
        $this->_tariffid = trim(Civi::settings()->get('nn_tariff'));
        $this->_authcode = trim(Civi::settings()->get('nn_authcode'));
        $this->_productid = trim(Civi::settings()->get('nn_product'));
        $this->_testmode = trim(Civi::settings()->get('nn_eps_testmode'));
        $this->_password = trim(Civi::settings()->get('nn_password'));
        $this->_notify_url = trim(Civi::settings()->get('nn_notify_url'));
        $this->_testmode = (($this->_mode == 'test') ? 1 : ($this->_testmode ? 1 : 0));
        defined('MODE_EPS') || define('MODE_EPS', $this->_testmode);
        list($logo, $description) = CRM_Core_Payment_novalnet::assignLogoAndDescription($this, $this->_paymentProcessor['name']);
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
            self::$_singleton[$paymentProcessor['name']] = new CRM_Core_Payment_eps($mode, $paymentProcessor);
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
     *  Chekout function
     *
     * @param array $params  name value pair of contribution data
     * @param $component
     *
     * @return void
     * @access public
     *
     */
    function doTransferCheckout(&$params, $component) {
        $_SESSION['novalnet']['payment_type'] = $this->_paymentType;
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
        $config = CRM_Core_Config::singleton();
        $user_variable = $config->userFrameworkBaseURL;
        $data['key'] = CRM_Core_Payment_novalnet::getPaymentKey($this->_paymentType);
        $data['vendor'] = $this->_vendorid;
        $data['auth_code'] = $this->_authcode;
        $data['product'] = $this->_productid;
        $data['tariff'] = $this->_tariffid;
        $data['test_mode'] = $this->_testmode;
        $data['uniqid'] = CRM_Core_Payment_novalnet::getUniqueid();
        $data['processor_name'] = $this->_paymentProcessor['payment_processor_type'];
        $data['user_variable_0'] = $user_variable;
        $data['lang'] = $language;
        $data['language'] = $language;
        if(!empty($this->_notify_url)) {
        $data['notify_url']  = $this->_notify_url;
        }
        $_SESSION['nn']['mode'] = ($this->_mode == 'test' ? '1' : '0');
        $_SESSION['nn']['check'] = $this->_mode;
        $_SESSION['nn']['payment_mode'] = $this->_testmode;
        $toBeEncoded = array('auth_code', 'product', 'tariff', 'amount', 'test_mode');
        CRM_Core_Payment_novalnet::novalnetEncode($data, $this->_password, $toBeEncoded);
        CRM_Core_Payment_novalnet::generateNovalnetHash($data,$this->_password);
        CRM_Core_Payment_novalnet::urlparams($params, $data, $this->_paymentType);
        CRM_Core_Payment_novalnet::returnUrlParams($data,$component, $this->_paymentProcessor['payment_processor_type'], $params);
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
        $response['module'] = $response['md'];
        $response['mode'] = $_SESSION['nn']['mode'];
        $response['check'] = $_SESSION['nn']['check'];
        $response['payment_mode'] = $_SESSION['nn']['payment_mode'];
        $response['orderid'] = $invoiceId = CRM_Utils_Array::value('inId', $_GET);
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
                $novalnetipn->main($response, 'novalnet_eps');
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
}
