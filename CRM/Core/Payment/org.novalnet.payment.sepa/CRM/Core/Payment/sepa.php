<?php
#########################################################
#                                                       #
#  SEPA payment method class                            #
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
#  Script : sepa.php                                    #
#                                                       #
#########################################################
require_once 'CRM/Core/Payment/novalnet/novalnet.php';
require_once 'CRM/Core/Payment/novalnet/novalnetIPN.php';
require_once 'CRM/Core/BAO/CustomField.php';
require_once 'CRM/Utils/Hook.php';
require_once 'CRM/Core/Form.php';
class CRM_Core_Payment_sepa extends CRM_Core_Payment {


  static private $_singleton = null;
  public $_mode = null;

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
    $this->_processorName     = ts('Novalnet Direct Debit SEPA');
    $this->_vendorid          = trim($this->_paymentProcessor['user_name']);
    $this->_tariffid          = trim($this->_paymentProcessor['subject']);
    $this->_authcode          = trim($this->_paymentProcessor['password']);
    $this->_productid         = trim($this->_paymentProcessor['signature']);
    $this->_manualchecklimit  = (isset($config->nn_sepa_manualamount))? trim($config->nn_sepa_manualamount): '';
    $this->_productid2        = (isset($config->nn_sepa_productid2))? trim($config->nn_sepa_productid2) :'';
    $this->_tariffid2         = (isset($config->nn_sepa_tariffid2))? trim($config->nn_sepa_tariffid2) : '';
    $this->_paymentType       = 'novalnet_sepa';
    list($logo, $description) = CRM_Core_Payment_novalnet::assignLogoAndDescription();
    $template->assign('novalnet_sepa_name', trim($this->_paymentProcessor['name']));
    $template->assign('novalnet_sepa_logo', $logo);
    $template->assign('novalnet_sepa_desc', $description);
      

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
          self::$_singleton[$processorName] = new CRM_Core_Payment_sepa($mode, $paymentProcessor);
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

  }

 /**

   * @param  array $params assoc array of input parameters for this transaction
   *
   * @return array the result in a nice formatted array (or an error object)
   * @public
   */
  function doTransferCheckout(&$params, $component) {

      $data = array();
      $config                     = CRM_Core_Config::singleton();
      $language                   = CRM_Core_Payment_novalnet::getLanguage();
      $response['processor_name'] = $this->_paymentProcessor['payment_processor_type'];
      $response['orderid']        = $params['invoiceID'];
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
      if (isset($config->nn_sepa_payment_type) 
          && trim($config->nn_sepa_payment_type) == 'DIRECT_DEBIT_SEPA_SIGNED') {
            $data['key'] = CRM_Core_Payment_novalnet::setPaymentKey('novalnet_sepa_signed');
            $data['mandate_present'] = '0';
          if (isset($_SESSION['sepa']['mandate_ref']) && !empty($_SESSION['sepa']['mandate_ref'])) {
            $data['mandate_present']        = '1';
            $data['mandate_ref']            = $_SESSION['sepa']['mandate_ref'];
            $data['mandate_signature_date'] = $_SESSION['sepa']['mandate_date'];
          }
      }
      else {
        $data['key'] = CRM_Core_Payment_novalnet::setPaymentKey('novalnet_sepa');
        if (isset($config->nn_sepa_due_date) && !empty($config->nn_sepa_due_date) && (int)$config->nn_sepa_due_date < 7) {
          CRM_Core_Payment_novalnet::paymentUpdation($response);
          CRM_Core_Error::statusBounce(ts('SEPA Due date is not valid') , $cancelURL);
        }
      $duedate                      = (isset($config->nn_sepa_due_date) && empty($config->nn_sepa_due_date)) ? '7' : trim($config->nn_sepa_due_date);
      $data['sepa_due_date']        = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d")+$duedate, date("Y")));
    }
    if (!CRM_Core_Payment_novalnet::validateSepaData($_SESSION['sepa'])) {
        CRM_Core_Payment_novalnet::paymentUpdation($response);
          if (empty($_SESSION['sepa']['sepa_bic_confirm'])) 
            $data['sepa_error'] = ts('Please confirm IBAN & BIC');
          else
            $data['sepa_error'] = ts('Please enter valid account details!');
        CRM_Core_Error::statusBounce($data['sepa_error'], $cancelURL);
      } 
      CRM_Core_Payment_novalnet::getProfileDetails($params, $data);
      $data['country']  = CRM_Core_PseudoConstant::countryIsoCode($data['country']);
      $validate         = CRM_Core_Payment_novalnet::defaultParamsValidtion($data);
      if ($validate != 1) {
        CRM_Core_Payment_novalnet::paymentUpdation($response);
        CRM_Core_Error::statusBounce($data['value'] , $cancelURL);
      }

     

    $data['mode']                 = ($this->_mode =='live'?'0':'1');
    $data['vendor']               = $this->_vendorid;
    $data['auth_code']            = $this->_authcode;
    $data['tariff' ]              = $datas['tariff_id'];
    $data['product']              = $datas['product_id'];
    $data['test_mode' ]           = $data['mode'] ;
    $data['sepa_unique_id']       = $_SESSION['sepa']['uniqid'];
    $data['sepa_hash']            = $_SESSION['sepa']['panhash'];
    $data['bank_account_holder']  = $_SESSION['sepa']['holder'];
    $data['bank_account']         = '';
    $data['bank_code']            = '';
    $data['bic']                  = '';
    $data['iban']                 = '';
    $data['check']                = ($this->_mode == 'live')?'live':'test';
    $data['lang']                 = $language;
    $data['language']             = $language;
    unset($_SESSION['sepa']);

    CRM_Core_Payment_novalnet::urlparams($params, $data);
    CRM_Core_Payment_novalnet::getPaymentReference($data, 'nn_sepa');
    $data = CRM_Utils_System::makeQueryString($data);
    
     $host     = CRM_Core_Payment_novalnet::setPaymentUrl($this->_paymentType);
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
}
