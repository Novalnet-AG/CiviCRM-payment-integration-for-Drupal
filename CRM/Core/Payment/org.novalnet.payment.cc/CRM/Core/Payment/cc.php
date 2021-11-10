<?php
#########################################################
#                                                       #
#  Credit Card payment method class                     #
#  This module is used for real time processing of      #
#  Credit Card data of customers.                       #
#                                                       #
#  Copyright (c) Novalnet AG                            #
#                                                       #
#  Released under the GNU General Public License        #
#  This free contribution made by request.              #
#  If you have found this script usefull a small        #
#  recommendation as well as a comment on merchant form #
#  would be greatly appreciated.                        #
#                                                       #
#  Script : cc.php                                      #
#                                                       #
#########################################################

/*
 * Payment Processor class for Novalnet Credit Card
 */
require_once 'CRM/Core/Payment/novalnet/novalnet.php';
require_once 'CRM/Core/Payment/novalnet/novalnetIPN.php';
require_once 'CRM/Core/BAO/CustomField.php';
require_once 'CRM/Utils/Hook.php';
require_once 'CRM/Core/Form.php';
class CRM_Core_Payment_cc extends CRM_Core_Payment {


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
    $template                 = CRM_Core_Smarty::singleton();
    $config                   = CRM_Core_Config::singleton();
    $this->_mode              = $mode;
    $this->_paymentProcessor  = $paymentProcessor;
    $this->_processorName     = ts('Novalnet Credit Card');
    $this->_vendorid          = trim($this->_paymentProcessor['user_name']);
    $this->_tariffid          = trim($this->_paymentProcessor['subject']);
    $this->_authcode          = trim($this->_paymentProcessor['password']);
    $this->_productid         = trim($this->_paymentProcessor['signature']);
    $this->_manualchecklimit  = (isset($config->nn_cc_manualamount))? trim($config->nn_cc_manualamount): '';
    $this->_productid2        = (isset($config->nn_cc_productid2))? trim($config->nn_cc_productid2) :'';
    $this->_tariffid2         = (isset($config->nn_cc_tariffid2))? trim($config->nn_cc_tariffid2) : '';
    $this->_paymentType       = 'novalnet_cc';
    list($logo, $description) = CRM_Core_Payment_novalnet::assignLogoAndDescription();
    $template->assign('novalnet_cc_name', trim($this->_paymentProcessor['name']));
    $template->assign('novalnet_cc_logo', $logo);
    $template->assign('novalnet_cc_desc', $description);
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
          self::$_singleton[$processorName] = new CRM_Core_Payment_cc($mode, $paymentProcessor);
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

   * @param  array $params assoc array of input parameters for this transaction
   *
   * @return array the result in a nice formatted array (or an error object)
   * @public
   */
  function doTransferCheckout(&$params, $component) {
      $_SESSION['qfKey']          = $params['qfKey'];
      $config = CRM_Core_Config::singleton();
      $data = array();
      $language = CRM_Core_Payment_novalnet::getLanguage();
      $response['processor_name'] = $this->_paymentProcessor['payment_processor_type'];
      $response['orderid'] = $params['invoiceID'];
      CRM_Core_Payment_novalnet::paymentNameUpdation($response);
      CRM_Core_Payment_novalnet::checkComponent($component);
      CRM_Core_Payment_novalnet::redirectUrl($component, $cancelURL, $params);
      CRM_Core_Payment_novalnet::basicConfigValidation($error);
      if (!empty($error)) {
        CRM_Core_Payment_novalnet::paymentUpdation($response);
        CRM_Core_Error::statusBounce($error, $cancelURL);
      }
    CRM_Core_Payment_novalnet::centsConvert($params['amount'], $data);
    $datas['amount'] = $data['amount'];
    CRM_Core_Payment_novalnet:: manualCheckLimit($datas);
    if (isset($datas['error']) && $datas['error']!= '') {
        CRM_Core_Payment_novalnet::paymentUpdation($response);
        CRM_Core_Error::statusBounce($datas['error'] , $cancelURL);
    }
    $_SESSION['cc']['cc_cvc2'] = CRM_Core_Payment_novalnet::sanitizeNumber($_SESSION['cc']['cc_cvc2']);
    if (!CRM_Core_Payment_novalnet::validateCcData($_SESSION['cc'])) {
      CRM_Core_Payment_novalnet::paymentUpdation($response);
      $data['cc_error'] = ts('Please enter valid credit card details!');
      CRM_Core_Error::statusBounce($data['cc_error'], $cancelURL);
    }
    CRM_Core_Payment_novalnet::getProfileDetails($params, $data);
    $data['country'] = CRM_Core_PseudoConstant::countryIsoCode($data['country']);
    $validate = CRM_Core_Payment_novalnet::defaultParamsValidtion($data);
    if($validate != 1) {
      CRM_Core_Payment_novalnet::paymentUpdation($response);
      CRM_Core_Error::statusBounce($data['value'] , $cancelURL);
    }
    $data['key']                  = CRM_Core_Payment_novalnet::setPaymentKey($this->_paymentType) ;
    $data['mode']                 = ($this->_mode =='live'?'0':'1');
    $data['vendor']               = $this->_vendorid;
    $data['auth_code']            = $this->_authcode;
    $data['tariff' ]              = $datas['tariff_id'];
    $data['product']              = $datas['product_id'];
    $data['test_mode' ]          = $data['mode'] ;
    $data['cc_holder']            = $_SESSION['cc']['cc_holder'];
    $data['cc_no']                = '';
    $data['pan_hash']             = $_SESSION['cc']['cc_panhash'];
    $data['unique_id']            = $_SESSION['cc']['cc_uniqid'];
    $data['cc_exp_month']         = $_SESSION['cc']['cc_exp_month'];
    $data['cc_exp_year']          = $_SESSION['cc']['cc_exp_year'];
    $data['cc_cvc2']              = $_SESSION['cc']['cc_cvc2'];
    $data['check']                = ($this->_mode == 'live')?'live':'test';
    $data['lang']                 = $language;
    $data['language']             = $language;
    
    CRM_Core_Payment_novalnet::urlparams($params, $data);
    CRM_Core_Payment_novalnet::getPaymentReference($data, 'nn_cc');

    if (isset($config->nn_cc_secure_active) && $config->nn_cc_secure_active == 1) {
      $params['id'] = $this->_paymentProcessor['id'] ;
      $return_url   = CRM_Core_Payment_novalnet::getReturnUrl($component, $params, $this->_paymentProcessor['payment_processor_type']);
      $config = CRM_Core_Config::singleton();
      $return_url = $config->userFrameworkBaseURL."civicrm/novalnetcc/complete";
      unset($_SESSION['novalnet']);
      $data['processor_name']    =  $this->_paymentProcessor['payment_processor_type'];
      CRM_Core_Payment_novalnet::orderCompleteParam($component, $params, $data) ;
      CRM_Core_Payment_novalnet::urlparams($params, $data);
      CRM_Core_Payment_novalnet::returnUrlParams($return_url, $data) ;
      $url = 'https://payport.novalnet.de/global_pci_payport';
      require_once 'CRM/Core/Session.php';
      CRM_Core_Session::storeSessionObjects( );

      echo CRM_Core_Payment_novalnet::getSubmitForm('nn_redirect_form', $data, $url);
      echo '<script type="text/javascript">document.getElementById("nn_redirect_form").submit();</script>';
      exit;
    }
    $data = CRM_Utils_System::makeQueryString($data);
    $options = array(
      'headers' => array('Content-Type' => 'application/x-www-form-urlencoded'),
      'method' => 'POST',
      'data' => urldecode($data)
    );
     $host     = CRM_Core_Payment_novalnet::setPaymentUrl($this->_paymentType);
     $response = drupal_http_request($host, $options);
     parse_str($response->data, $parsed);
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
      $parsed['orderid']        = $params['invoiceID'];
      CRM_Core_Payment_novalnet::paymentUpdation($parsed);
      CRM_Core_Payment_novalnet::checkstatus($parsed, $error);
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

   public function handlePaymentNotification($response) {
    require_once 'CRM/Utils/Array.php';
    require_once 'CRM/Core/Payment/novalnet/novalnetIPN.php';
    $response['qfKey'] = $_SESSION['qfKey'];
    $qfKey = $response['qfKey'] ;
    unset($_SESSION['qfKey']);
    if (isset($response['status']) && $response['status'] == 100 ) {
        $response['product'] = $response['product_id'];
        $novalnetipn  = new org_novalnet_payment_novalnetipn($response['test'], $response['id']);
        $novalnetipn->main($response);
    }
    else {
     if($response['module'] == 'contribute') {
        $url = CRM_Utils_System::url('civicrm/contribute/transact', "_qf_Main_display=true&cancel=1&qfKey={$qfKey}", FALSE, NULL, FALSE);
       } elseif($response['module']  == 'event') {
            $eventid = $response['eid'];
            $url = CRM_Utils_System::url('civicrm/event/register', "id={$eventid}", FALSE, NULL, FALSE);
     }
       $session = CRM_Core_Session::singleton();
       $session->getStatus($rese = TRUE);
       CRM_Core_Payment_novalnet::paymentUpdation($response);
       CRM_Core_Payment_novalnet::checkstatus($response, $error);
       CRM_Core_Payment_novalnet::commentsOnError($response, $error);
       CRM_Core_Session::setStatus($error);

    }
    CRM_Utils_System::redirect($url);
  }

  function doDirectPayment(&$params) {
     CRM_Core_Error::fatal(ts('This function is not implemented'));
  }
}
