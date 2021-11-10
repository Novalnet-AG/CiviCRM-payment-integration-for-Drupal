<?php
#########################################################
#                                                       #
#  Direct Debit Austria payment method class            #
#  This module is used for real time processing of      #
#  Debit Card data of customers.                        #
#                                                       #
#  Copyright (c) Novalnet AG                            #
#                                                       #
#  Released under the GNU General Public License        #
#  This free contribution made by request.              #
#  If you have found this script usefull a small        #
#  recommendation as well as a comment on merchant form #
#  would be greatly appreciated.                        #
#                                                       #
#  Script : elvat.php                                   #
#                                                       #
#########################################################
/*
 * Payment Processor class for Novalnet Direct Debit Austria
 */
require_once 'CRM/Core/Payment/novalnet/novalnet.php';
require_once 'CRM/Core/Payment/novalnet/novalnetIPN.php';
require_once 'CRM/Utils/Hook.php';
require_once 'CRM/Core/Form.php';
require_once 'CRM/Core/Session.php';
class CRM_Core_Payment_elvat extends CRM_Core_Payment {

  static private $_singleton = null;

  static public $_mode = null;

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
    $this->_processorName     = ts('Novalnet Direct Debit Austria');
    $this->_vendorid          = trim($this->_paymentProcessor['user_name']);
    $this->_tariffid          = trim($this->_paymentProcessor['subject']);
    $this->_authcode          = trim($this->_paymentProcessor['password']);
    $this->_productid         = trim($this->_paymentProcessor['signature']);
    $this->_manualchecklimit  = (isset($config->nn_at_manualamount))?trim($config->nn_at_manualamount):'';
    $this->_productid2        = (isset($config->nn_at_productid2))?trim($config->nn_at_productid2):'';
    $this->_tariffid2         = (isset($config->nn_at_tariffid2))?trim($config->nn_at_tariffid2):'';
    $this->_paymentType       = 'novalnet_elv_at';
    list($logo, $description) = CRM_Core_Payment_novalnet::assignLogoAndDescription();
    $template->assign('novalnet_elvat_name', trim($this->_paymentProcessor['name']));
    $template->assign('novalnet_elvat_logo', $logo);
    $template->assign('novalnet_elvat_desc', $description);
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
          self::$_singleton[$processorName] = new  CRM_Core_Payment_elvat($mode, $paymentProcessor);
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
  CRM_Core_Error::fatal(ts('This function is not implemented'));
  }

  /**

   * @param  array $params assoc array of input parameters for this transaction
   *
   * @return array the result in a nice formatted array (or an error object)
   * @public
   */
  function doTransferCheckout(&$params, $component) {

    $data                       = array();
    $language                   = CRM_Core_Payment_novalnet::getLanguage();
    $response['processor_name'] = $this->_paymentProcessor['payment_processor_type'];
    $response['orderid']        = $params['invoiceID'];
    CRM_Core_Payment_novalnet::paymentNameUpdation($response);
    CRM_Core_Payment_novalnet::checkComponent($component);
    CRM_Core_Payment_novalnet::redirectUrl($component, $cancelURL, $params);
    CRM_Core_Payment_novalnet::basicConfigValidation($error);
    if($error != '') {
       CRM_Core_Payment_novalnet::paymentUpdation($response);
       CRM_Core_Error::statusBounce($error, $cancelURL);
    }

    CRM_Core_Payment_novalnet::centsConvert($params['amount'], $data);
    $datas['amount'] = $data['amount'];
    CRM_Core_Payment_novalnet:: manualCheckLimit($datas);
     if(isset($datas['error']) && $datas['error']!= '') {
        CRM_Core_Payment_novalnet::paymentUpdation($response);
          CRM_Core_Error::statusBounce($datas['error'], $cancelURL);
     }
    $_SESSION['elvat']['account_number'] = CRM_Core_Payment_novalnet::sanitizeNumber($_SESSION['elvat']['account_number']);
    $_SESSION['elvat']['bank_code']      = CRM_Core_Payment_novalnet::sanitizeNumber($_SESSION['elvat']['bank_code']);
    CRM_Core_Payment_novalnet::formValidation( $_SESSION['elvat'] ,$this->_paymentProcessor,$error);
      if(isset($error['form']) && $error['form']!='') {
     CRM_Core_Payment_novalnet::paymentUpdation($response);
       CRM_Core_Error::statusBounce($error['form'], $cancelURL);
     }
    CRM_Core_Payment_novalnet::getProfileDetails($params, $data);
    $data['country'] = CRM_Core_PseudoConstant::countryIsoCode($data['country']);
    $validate = CRM_Core_Payment_novalnet::defaultParamsValidtion($data);
    if($validate!= 1) {
      CRM_Core_Payment_novalnet::paymentUpdation($response);
      CRM_Core_Error::statusBounce($data['value'] , $cancelURL);
    }
    $data['key']                  = CRM_Core_Payment_novalnet::setPaymentKey($this->_paymentType);
    $data['mode']                 = ($this->_mode =='live'?'0':'1');
    $data['vendor']               = $this->_vendorid;
    $data['auth_code']            = $this->_authcode;
    $data['tariff' ]              = $datas['tariff_id'];
    $data['product']              = $datas['product_id'];
    $data['key' ]                 = $data['key'] ;
    $data[ 'test_mode' ]          = $data['mode'] ;
    $data['bank_account_holder']  = $_SESSION['elvat']['account_holder'];
    $data['bank_account']         = $_SESSION['elvat']['account_number'];
    $data['bank_code' ]           = $_SESSION ['elvat']['bank_code'];
    $data['check']                = ($this->_mode == 'live')?'live':'test';
    $data['lang']                 = $language;
    $data['language']             = $language;
   
    CRM_Core_Payment_novalnet::urlparams($params, $data);
    CRM_Core_Payment_novalnet::getPaymentReference($data, 'nn_at');
    $data = CRM_Utils_System::makeQueryString($data);
   
    $host = CRM_Core_Payment_novalnet::setPaymentUrl($this->_paymentType);
   $httppost = CRM_Utils_HttpClient::singleton();
    list($result, $response) = $httppost->post($host, $data);
    parse_str($response, $parsed);
    if (isset($parsed['status']) && $parsed['status'] == '100') {
      $final['processor_name']    =  $this->_paymentProcessor['payment_processor_type'];
      parse_str($data, $param);
      $final = array_merge($final, $parsed, $param);
      CRM_Core_Payment_novalnet::orderCompleteParam($component, $params, $final) ;
      $final['test_mode']  = $parsed['test_mode'];
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
