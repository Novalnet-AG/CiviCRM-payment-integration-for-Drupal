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
 * Script: invoice.php
 *
 */
require_once 'CRM/Core/Payment/org.payment.novalnetgateway/lib/novalnetIPN.php';
require_once 'CRM/Core/BAO/CustomField.php';
require_once 'CRM/Utils/Hook.php';


class CRM_Core_Payment_invoice extends CRM_Core_Payment {

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
		$this->_mode = $mode;
		$this->_paymentProcessor = $paymentProcessor;
        $this->_processorName = ts('Novalnet Invoice');
        $this->_vendorid = trim(Civi::settings()->get('nn_vendor'));
        $this->_tariffid = trim(Civi::settings()->get('nn_tariff'));
        $this->_authcode = trim(Civi::settings()->get('nn_authcode'));
        $this->_productid = trim(Civi::settings()->get('nn_product'));
        $this->_duedate = trim(Civi::settings()->get('nn_inv_duedate'));
        $this->_testmode = trim(Civi::settings()->get('nn_inv_testmode'));
        $this->_nn_invoice_guarantee = trim(Civi::settings()->get('novalnet_invoice_guarantee'));
        $this->_nn_invoice_force_amount = trim(Civi::settings()->get('novalnet_invoice_guarantee_amt'));
        $this->_nn_invoice_force_guarantee = trim(Civi::settings()->get('novalnet_invoice_force_guarantee'));
        $this->_paymentType = 'novalnet_invoice';
        $this->_testmode = (($this->_mode == 'test') ? 1 : ($this->_testmode ? 1 : 0));
        defined('MODE_INVOICE') || define('MODE_INVOICE', $this->_testmode);
        $this->_notify_url = trim(Civi::settings()->get('nn_notify_url'));
        $this->_manualchecklimit = trim(Civi::settings()->get('nn_invoice_manualamount'));
        list($logo, $description) = CRM_Core_Payment_novalnet::assignLogoAndDescription($this, $this->_paymentProcessor['name']);
        $this->_cancelStatus = CRM_Core_Payment_novalnet::getSubscriptionCancelReason();
        $config = CRM_Core_Config::singleton();
        CRM_Core_Payment_novalnet::setInvoiceDetails($config, $this->_nn_invoice_force_amount);
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
            self::$_singleton[$paymentProcessor['name']] = new CRM_Core_Payment_invoice($mode, $paymentProcessor);
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
        $_SESSION['novalnet']['payment_type'] = $this->_paymentType;
        $component = strtolower($component);
        $response['processor_name'] = $this->_paymentProcessor['payment_processor_type'];
        $response['orderid'] = $params['invoiceID'];
        $response['is_recu'] = (isset($params['is_recur']) && $params['is_recur'] == 1) ? '1' : 0;
        $language = CRM_Core_Payment_novalnet::getLanguage();
        CRM_Core_Payment_novalnet::paymentNameUpdation($response);
        CRM_Core_Payment_novalnet::checkComponent($component);
        CRM_Core_Payment_novalnet::redirectUrl($component, $cancelURL, $params);

        if (!CRM_Core_Payment_novalnet::getProfileDetails($params, $data)) {
            CRM_Core_Payment_novalnet::paymentUpdation($response);
            CRM_Core_Error::statusBounce($data['value'], $cancelURL);
        }
        if ((preg_match("/^[0-9]+$/",$this->_duedate)) && $this->_duedate >= 0 && $this->_nn_invoice_guarantee != '1') {
            $data['due_date'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + $this->_duedate, date("Y")));
        }
        CRM_Core_Payment_novalnet::centsConvert($params['amount'], $data);
        $data['key'] = CRM_Core_Payment_novalnet::getPaymentKey($this->_paymentType);
        $data['vendor'] = $this->_vendorid;
        $data['auth_code'] = $this->_authcode;
        $data['tariff'] = $this->_tariffid;
        $data['product'] = $this->_productid;
        $data['test_mode']  = $this->_testmode;
        $data['processor_name'] = $this->_paymentProcessor['payment_processor_type'];
        $data['invoice_type'] = 'INVOICE';
        $data['invoice_ref'] = 'BNR-' . $data['product'] . '-' . $params['invoiceID'];
        $data['lang'] = $language;
        $data['language'] = $language;
        if(!empty($this->_notify_url)) {
        $data['notify_url']  = $this->_notify_url;
        }
        
        $guarantee = '';
        if($this->_nn_invoice_guarantee == '1') {
			$data['birth_date'] = $_SESSION['nn_payment_invoice_birth_date'];
            $guarantee .= CRM_Core_Payment_novalnet::validateGuranteePayment($data, $params['currencyID'], $this->_paymentType);
        }
        if($this->_nn_invoice_guarantee == '1' && !empty($data['birth_date']) && empty($guarantee) && (date('Y') - date('Y', strtotime($data['birth_date']))) >= 18) {
                $data['payment_type'] = 'GUARANTEED_INVOICE';
                $data['key'] = '41';
        }
        elseif((! empty( $guarantee ) && $this->_nn_invoice_force_guarantee == '1')) {
			$data['payment_type'] = 'INVOICE_START';
            $data['key'] = '27';
		}
        elseif(($guarantee && empty($data['birth_date'])) || ($guarantee && $this->_nn_invoice_force_guarantee != '1')) {
				$cancelUrlString = "=1&cancel=1&qfKey={$params['qfKey']}";
				$cancelURL = CRM_Utils_System::url('civicrm/contribute/transact', $cancelUrlString, true, null, false);
				CRM_Core_Error::statusBounce($guarantee, $cancelURL);
        }
        CRM_Core_Payment_novalnet::manualCheckLimit($data, $this->_manualchecklimit);
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
            if($this->_nn_invoice_guarantee != '1') {
				$data['due_date'] = $parsed['due_date'];
			}
            $data['payment_mode'] = (($this->_mode == 'test') ? 1 :( $this->_testmode ? 1 : 0));
            $final = array_merge($parsed, $data);
            $final['test_mode'] = $parsed['test_mode'];
            CRM_Core_Payment_novalnet::orderCompleteParam($component, $params, $final);
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
