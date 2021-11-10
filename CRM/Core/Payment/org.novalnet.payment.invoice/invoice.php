<?php
#########################################################
#                                                       #
#  Invoice payment method class                         #
#  This module is used for real time processing of      #
#  Invoice payment of customers.                        #
#                                                       #
#  Copyright (c) Novalnet AG                            #
#                                                       #
#  Released under the GNU General Public License        #
#  This free contribution made by request.              #
#  If you have found this script usefull a small        #
#  recommendation as well as a comment on merchant form #
#  would be greatly appreciated.                        #
#                                                       #
#  Script : invoice.php                                 #
#                                                       #
#########################################################
  require_once 'CRM/Core/Payment/novalnet/novalnet.php';
  require_once 'CRM/Core/BAO/CustomField.php';
  require_once 'CRM/Utils/Hook.php';
  require_once 'CRM/Core/Payment/novalnet/novalnetIPN.php';
class org_novalnet_payment_invoice extends CRM_Core_Payment {

  static private $_singleton = null;
  static public $_mode       = null;

  /**
   * Constructor
   *
   * @param string $mode the mode of operation: live or test
   *
   * @return void
   */
  function __construct($mode, &$paymentProcessor) {
    $config                   = CRM_Core_Config::singleton();
    $template                 = CRM_Core_Smarty::singleton();
    $this->_mode              = $mode;
    $this->_paymentProcessor  = $paymentProcessor;
    $this->_processorName     = ts('Novalnet Invoice');
    $this->_vendorid          = trim($this->_paymentProcessor['user_name']);
    $this->_tariffid          = trim($this->_paymentProcessor['subject']);
    $this->_authcode          = trim($this->_paymentProcessor['password']);
    $this->_productid         = trim($this->_paymentProcessor['signature']);
    $this->_duedate           = isset($config->nn_inv_duedate) ? trim($config->nn_inv_duedate) : '';
    $this->_paymentType       = 'novalnet_invoice';
    list( $logo, $description ) = CRM_Core_Payment_novalnet::assignLogoAndDescription();
    $template->assign('novalnet_invoice_name', trim($this->_paymentProcessor['name']));
    $template->assign('novalnet_invoice_logo', $logo);
    $template->assign('novalnet_invoice_desc', $description);

   }

  /**
   * Singleton function used to manage this object
   *
   * @param string $mode the mode of operation: live or test
   *
   * @return object
   * @static
   *
   */

  static function &singleton($mode, &$paymentProcessor, &$paymentForm = NULL, $force = FALSE) {
      $processorName = $paymentProcessor['name'];
      if (self::$_singleton[$processorName] === NULL ) {
          self::$_singleton[$processorName] = new org_novalnet_payment_invoice($mode, $paymentProcessor);
      }
      return self::$_singleton[$processorName];
  }

  /**
   * This function checks to see if we have the right config values
   *
   * @return string the error message if any
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
   * @param  array $params assoc array of input parameters for this transaction
   *
   * @return array the result in a nice formatted array (or an error object)
   * @public
   */
  function doTransferCheckout(&$params, $component) {
      $data = array();

    $component                  = strtolower($component);
    $response['processor_name'] = $this->_paymentProcessor['payment_processor_type'];
    $response['orderid']        = $params['invoiceID'];
    $language                   = CRM_Core_Payment_novalnet::getLanguage();
    CRM_Core_Payment_novalnet::paymentNameUpdation($response);
    CRM_Core_Payment_novalnet::checkComponent($component);
    CRM_Core_Payment_novalnet::redirectUrl($component, $cancelURL, $params);
    CRM_Core_Payment_novalnet::basicConfigValidation($error);
    if (!empty($error)) {
        CRM_Core_Payment_novalnet::paymentUpdation($response);
        CRM_Core_Error::statusBounce($error, $cancelURL);
    }
    CRM_Core_Payment_novalnet::getProfileDetails($params, $data);
    $data['country']  = CRM_Core_PseudoConstant::countryIsoCode($data['country']);
    $validate         = CRM_Core_Payment_novalnet::defaultParamsValidtion($data);
    if($validate != 1) {
        CRM_Core_Payment_novalnet::paymentUpdation($response);
        CRM_Core_Error::statusBounce($data['value'] , $cancelURL);
    }
    if (CRM_Core_Payment_novalnet::isDigits($this->_duedate) && $this->_duedate >= 0 ) {
        $data['due_date'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + $this->_duedate, date("Y")));
    }
    CRM_Core_Payment_novalnet::centsConvert($params['amount'], $data);
    $data['key']                   = CRM_Core_Payment_novalnet::setPaymentKey($this->_paymentType);
    $data['vendor']                = $this->_vendorid;
    $data['auth_code']             = $this->_authcode ;
    $data['tariff']                = $this->_tariffid;
    $data['product']               = $this->_productid;
    $data['mode']                  = ($this->_mode =='live'?'0':'1');
    $data['test_mode']             = $data['mode'] ;
    $data['processor_name']        = $this->_paymentProcessor['payment_processor_type'];
    $data['invoice_type']          = 'INVOICE';
    $data['invoice_ref']           = 'BNR-' . $data['product'] . '-' . $params['invoiceID'];
    $data['check']                 = ($this->_mode == 'live')?'live':'test';
    $data['lang']                  = $language;
    $data['language']              = $language;
    CRM_Core_Payment_novalnet::urlparams($params, $data);
    CRM_Core_Payment_novalnet::getPaymentReference($data, 'nn_inv');
    $data = CRM_Utils_System::makeQueryString($data);
    $host = CRM_Core_Payment_novalnet::setPaymentUrl();
    $httppost = CRM_Utils_HttpClient::singleton();
    list($result, $response) = $httppost->post($host, $data);
    parse_str($response, $parsed);
    if ((isset($parsed['status']) && $parsed['status'] == 100)) {
        $final['processor_name']    =  $this->_paymentProcessor['payment_processor_type'];
        parse_str($data, $param);
        $final['due_date']  = isset($param['due_date'])?$param['due_date']:'';
        $final = array_merge($final, $parsed, $param);
        $final['test_mode']  = $parsed['test_mode'];
        CRM_Core_Payment_novalnet::orderCompleteParam($component, $params, $final) ;
        $novalnetipn = new org_novalnet_payment_novalnetipn($this->_mode, $this->_paymentProcessor);
        $novalnetipn ->main($final);
    }
    else {
        $parsed['processor_name'] = $this->_paymentProcessor['payment_processor_type'];
        $parsed['orderid'] = $params['invoiceID'];
        CRM_Core_Payment_novalnet::paymentUpdation($parsed);
        CRM_Core_Payment_novalnet::checkstatus($parsed, $error);
        CRM_Core_Payment_novalnet::commentsOnError($parsed, $error);
        CRM_Core_Error::statusBounce($error, $cancelURL);
    }
  }
}



















