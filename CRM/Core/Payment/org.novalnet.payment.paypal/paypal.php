<?php
#########################################################
#                                                       #
#  PayPal payment method class                          #
#  This module is used for real time processing of      #
#  PayPal payment of customers.                         #
#                                                       #
#  Copyright (c) Novalnet AG                            #
#                                                       #
#  Released under the GNU General Public License        #
#  This free contribution made by request.              #
#  If you have found this script usefull a small        #
#  recommendation as well as a comment on merchant form #
#  would be greatly appreciated.                        #
#                                                       #
#  Script : paypal.php                                  #
#                                                       #
#########################################################
  require_once  'CRM/Core/Payment/novalnet/novalnet.php';
  require_once 'CRM/Utils/Array.php';
  require_once 'CRM/Core/Payment/novalnet/novalnetIPN.php';
  require_once 'CRM/Core/Session.php';

class org_novalnet_payment_paypal extends CRM_Core_Payment {


  static private $_singleton = null;

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
    $this->_processorName     = ts('Novalnet PayPal');
    $this->_paymentType       = 'novalnet_paypal';
    $this->_vendorid          = trim($this->_paymentProcessor['user_name']);
    $this->_tariffid          = trim($this->_paymentProcessor['subject']);
    $this->_authcode          = trim($this->_paymentProcessor['password']);
    $this->_productid         = trim($this->_paymentProcessor['signature']);
    $this->_password          = (isset($config->nn_password))?trim($config->nn_password):'';
    $this->_paypaluser        = (isset($config->nn_paypal_user))?trim($config->nn_paypal_user):'';
    $this->_paypalsig         = (isset($config->nn_paypal_sig))?trim($config->nn_paypal_sig):'';
    $this->_paypalpwd         = (isset($config->nn_paypal_pwd))?trim($config->nn_paypal_pwd):'';
    list($logo, $description) = CRM_Core_Payment_novalnet::assignLogoAndDescription();
    $template->assign('novalnet_paypal_name', trim($this->_paymentProcessor['name']));
    $template->assign('novalnet_paypal_logo', $logo);
    $template->assign('novalnet_paypal_desc', $description);
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
          self::$_singleton[$processorName] = new org_novalnet_payment_paypal($mode, $paymentProcessor);
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
   * @param  array $params assoc array of input parameters for this transaction
   *
   * @return array the result in a nice formatted array (or an error object)
   * @public
   */
   function doTransferCheckout(&$params, $component) {
       $config                      = CRM_Core_Config::singleton();
       $component                   = strtolower($component);
       $language                    = CRM_Core_Payment_novalnet::getLanguage();
       $response['processor_name']  = $this->_paymentProcessor['payment_processor_type'];
       $response['orderid']         = $params['invoiceID'];
       CRM_Core_Payment_novalnet::paymentNameUpdation($response);
       CRM_Core_Payment_novalnet::checkComponent($component);
       CRM_Core_Payment_novalnet::redirectUrl($component, $cancelURL, $params);
       CRM_Core_Payment_novalnet::basicConfigValidation($error);
       if ($error['basic_error']!= '') {
          CRM_Core_Payment_novalnet::paymentUpdation($response);
          CRM_Core_Error::statusBounce($error, $cancelURL);
       }

       CRM_Core_Payment_novalnet::getProfileDetails($params, $data);
       $data['country'] = CRM_Core_PseudoConstant::countryIsoCode($data['country']);
       $validate        = CRM_Core_Payment_novalnet::defaultParamsValidtion($data);
       if ($validate!= 1) {
          CRM_Core_Payment_novalnet::paymentUpdation($response);
          CRM_Core_Error::statusBounce($data['value'] , $cancelURL);
       }
       CRM_Core_Payment_novalnet::centsConvert($params['amount'], $data);
       $user_variable          = $config->userFrameworkBaseURL;

       $data['mode']           = ($this->_mode =='live'?'0':'1');
       $data['key']            = CRM_Core_Payment_novalnet::setPaymentKey($this->_paymentType);
       $return_url             = CRM_Core_Payment_novalnet::getReturnUrl($component, $params, $this->_paymentProcessor['payment_processor_type']);
       $data['auth_code']        = $this->_authcode;
       $data['vendor']           = $this->_vendorid;
       $data['product']          = $this->_productid;
       $data['tariff']           = $this->_tariffid;
       $data['test_mode']        = $data['mode'];
       $data['uniqid']           = uniqid();
       $data['processor_name']   = $this->_paymentProcessor['payment_processor_type'];
       $data['user_variable_0']  = $user_variable;
       $data['lang']             = $language;
       $data['language']         = $language;
       $data['check']            = ($this->_mode == 'live')?'live':'test';
       CRM_Core_Payment_novalnet::novalnetEncode($data);
       CRM_Core_Payment_novalnet::generateNovalnetHash($data);
       $data['api_signature']    = CRM_Core_Payment_novalnet::encode($this->_paypalsig);
       $data['api_user']         = CRM_Core_Payment_novalnet::encode($this->_paypaluser);
       $data['api_pw']           = CRM_Core_Payment_novalnet::encode($this->_paypalpwd);
       CRM_Core_Payment_novalnet::urlparams($params, $data);
       CRM_Core_Payment_novalnet::returnUrlParams($return_url, $data) ;
       CRM_Core_Payment_novalnet::getPaymentReference($data, 'nn_paypal');
       CRM_Core_Payment_novalnet::orderCompleteParam($component, $params, $data) ;
       $url = CRM_Core_Payment_novalnet::setPaymentUrl();
       CRM_Core_Session::storeSessionObjects();
       print CRM_Core_Payment_novalnet::getSubmitForm('nn_redirect_form', $data, $url);
       echo '<script type="text/javascript">document.getElementById("nn_redirect_form").submit();</script>';
       exit;
  }

  public function handlePaymentNotification() {
     $response  = $_REQUEST;
     $qfKey     = $response['qfKey'] ;
     if (isset($response['hash2']) && ($response['status'] == 100 || $response['status'] == 90 )) {
      $temp_data = CRM_Core_Payment_novalnet::tempData($response);
      if (!CRM_Core_Payment_novalnet::novalnetCheckHash($temp_data, $this->_password)) {
       if ($response['module'] == 'contribute') {
           $url = CRM_Utils_System::url('civicrm/contribute/transact', "_qf_Main_display=true&cancel=1&qfKey={$qfKey}", FALSE, NULL, FALSE);
        }   elseif ($response['module'] == 'event') {
             $eventid = $response['eid'];
            $url = CRM_Utils_System::url('civicrm/event/register', "id={$eventid}", FALSE, NULL, FALSE);
            }
         CRM_Core_Session::setStatus(ts('Check Hash failed.'),  'error');

      }
          else {
            CRM_Core_Payment_novalnet::novalnetDecode($response);

                 $novalnetipn = new org_novalnet_payment_novalnetipn($this->_mode, $this->_paymentProcessor);
                 $novalnetipn->main($response);
          }
    }
     else {
    if($response['module'] == 'contribute') {
         $url = CRM_Utils_System::url('civicrm/contribute/transact', "_qf_Main_display=true&cancel=1&qfKey={$qfKey}", FALSE, NULL, FALSE);
            } elseif($response['module'] == 'event') {
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
}
