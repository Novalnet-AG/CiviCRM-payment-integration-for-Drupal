<?php
#########################################################
#                                                       #
#  Novalnet payment method class                        #
#  This module is used for real time processing of      #
#  Novalnet transaction of customers.                   #
#                                                       #
#  Copyright (c) Novalnet AG                            #
#                                                       #
#  Released under the GNU General Public License        #
#  This free contribution made by request.              #
#  If you have found this script usefull a small        #
#  recommendation as well as a comment on merchant form #
#  would be greatly appreciated.                        #
#                                                       #
#  Script : novalnet.php                                #
#                                                       #
#########################################################
  require_once 'civicrm-version.php';

class CRM_Core_Payment_novalnet {

  /**
   * Get the payment name
   *
   * @param  none
   * @return array
   */
  public function getPaymentMethods($payment_type) {
    $key =  array(
      'novalnet_prepayment'   => ts('Prepayment'),
      'novalnet_invoice'      => ts('Invoice'),
      'novalnet_cc'           => ts('Credit Card'),
      'novalnet_elv_de'       => ts('Direct Debit German'),
      'novalnet_elv_at'       => ts('Direct Debit Austria'),
      'novalnet_paypal'       => ts('PayPal'),
      'novalnet_ideal'        => ts('iDEAL'),
      'novalnet_instantbank'  => ts('Instant Bank Transfer'),
      'novalnet_safetypay'    => ts('SafetyPay'),
      'novalnet_sepa'         => ts('Direct Debit SEPA'),
    );
    return $key[$payment_type];
  }
  /**
   * Set the payment key
   * @param $payment_type
   * return integer
   */
  public function setPaymentKey($payment_type) {
    $key = array(
      'novalnet_elv_de'       => 2,
      'novalnet_cc'           => 6,
      'novalnet_elv_at'       => 8,
      'novalnet_prepayment'   => 27,
      'novalnet_invoice'      => 27,
      'novalnet_instantbank'  => 33,
      'novalnet_paypal'       => 34,
      'novalnet_ideal'        => 49,
      'novalnet_safetypay'    => 54,
      'novalnet_sepa'         => 37,
      'novalnet_sepa_signed'  => 55,
    );
    return $key[$payment_type];
  }
  /**
   * Set the redirection URL
   * @param none
   * return string
   */
  public function setPaymentUrl() {
    $url = array(
      'novalnet_prepayment'   => 'https://payport.novalnet.de/paygate.jsp',
      'novalnet_invoice'      => 'https://payport.novalnet.de/paygate.jsp',
      'novalnet_cc'           => 'https://payport.novalnet.de/paygate.jsp',
      'novalnet_elv_de'       => 'https://payport.novalnet.de/paygate.jsp',
      'novalnet_elv_at'       => 'https://payport.novalnet.de/paygate.jsp',
      'novalnet_sepa'         => 'https://payport.novalnet.de/paygate.jsp',
      'novalnet_paypal'       => 'https://payport.novalnet.de/paypal_payport',
      'novalnet_ideal'        => 'https://payport.novalnet.de/online_transfer_payport',
      'novalnet_instantbank'  => 'https://payport.novalnet.de/online_transfer_payport',
      'novalnet_safetypay'    => 'https://payport.novalnet.de/safetypay',
    );
  return trim($url[$this->_paymentType]);
  }
  /**
   * Set the payment description
   *
   * @param  $selected_payment
   * @return string
   */
  public function novalnetPaymentDescription($selected_payment) {
    $description = '';
    $payment_description = array(
      'novalnet_prepayment'  => ts('The bank details will be emailed to you soon after the completion of checkout process.'),
      'novalnet_invoice'     => ts('The bank details will be emailed to you soon after the completion of checkout process.'),
      'novalnet_cc'          => ts('The amount will be booked immediately from your credit card when you submit the order.'),
      'novalnet_paypal'      => ts('You will be redirected to Novalnet AG website when you place the order.'),
      'novalnet_ideal'       => ts('You will be redirected to Novalnet AG website when you place the order.'),
      'novalnet_safetypay'   => ts('You will be redirected to Novalnet AG website when you place the order.'),
      'novalnet_instantbank' => ts('You will be redirected to Novalnet AG website when you place the order.'),
      'novalnet_elv_de'      => ts('Your account will be debited upon delivery of goods.'),
      'novalnet_elv_at'      => ts('Your account will be debited upon delivery of goods.'),
      'novalnet_sepa'        => ts('Your account will be debited upon delivery of goods.'),
      'novalnet_sepa_signed' => ts('Please note that your account will be debited after receiving the signed mandate from you.'),
    );
    $description = $payment_description[$selected_payment];
    if (trim($this->_mode == 'test')) {
        $description .= '<br><div style =color:red;>' . ts('Please Note: This transaction will run on TEST MODE and the amount will not be charged') . '</div>';
    }
  return $description;
  }
  /**
   * Set the payment logo URL
   *
   * @param  $selected_payment
   * @return string
   */
  public function novalnetPaymentLogoURL($selected_payment) {
  $payment_logos   = array(
      'novalnet_prepayment'   => ts('prepayment.jpg'),
      'novalnet_invoice'      => ts('invoice.jpg'),
      'novalnet_cc'           => ts('creditcard.jpg'),
      'novalnet_paypal'       => ts('paypal.png'),
      'novalnet_ideal'        => ts('ideal.png'),
      'novalnet_instantbank'  => ts('sofort_en.png'),
      'novalnet_elv_de'       => ts('ELV.png'),
      'novalnet_elv_at'       => ts('ELV.png'),
      'novalnet_safetypay'    => ts('safetypay.png'),
      'novalnet_sepa'         => ts('sepa_en.jpg'),
    );
  return $payment_logos[$selected_payment];
  }
  /**
   * Set the logo
   *
   * @param  none
   * @return string
   */
  public function assignLogoAndDescription() {
    $config = CRM_Core_Config::singleton();

    $payment_logo_name      =  self::novalnetPaymentLogoURL($this->_paymentType);

    $payment_description    =  self::novalnetPaymentDescription($this->_paymentType);
    if ($this->_paymentType == 'novalnet_sepa' && isset($config->nn_sepa_payment_type)
      && trim($config->nn_sepa_payment_type) == 'DIRECT_DEBIT_SEPA_SIGNED') {
        $payment_description    =  self::novalnetPaymentDescription('novalnet_sepa_signed');
    }
    $paymentname            =  self::getPaymentMethods($this->_paymentType);
    $redirectionUrl         =  ts('http://www.novalnet.com');
    $novalnet_image_path    =  $config->userFrameworkResourceURL. 'CRM/Core/Payment/novalnet/logos/';

    $novalnet_logo            =  "<a href=$redirectionUrl target=_blank><img src = " . $novalnet_image_path . "novalnet.png alt='Novalnet AG' title= 'Novalnet AG'></a>";
    $payment_logo      =  "<a href=$redirectionUrl target=_blank><img src =" . $novalnet_image_path . $payment_logo_name ." alt ='$paymentname' title='$paymentname'></a>";

    if ($this->_paymentType == 'novalnet_cc' && isset($config->nn_cc_amexlogo_active) && $config->nn_cc_amexlogo_active == 1 ) {
      $payment_logo .= "<a href=$redirectionUrl target=_blank><img src =" . $novalnet_image_path . "amex_logo.png" ." alt ='$paymentname' title='$paymentname'></a>";
    }
 
    $logo = (isset($config->nn_enable_logo) && $config->nn_enable_logo )? $novalnet_logo . $payment_logo : $payment_logo ;

   return array($logo, $payment_description);
  }
  /**
   * Convert amount into cents.
   * @param $amount
   * @param $data
   * return double
   */
    public function centsConvert($amount, &$data) {
      if (preg_match('/[^\d\.]/', $amount) or !$amount) {
        $err_msg  = ts('Amount value is not valid');
      }
      $amount = sprintf('%0.2f', $amount);
      $amount = preg_replace('/^0+/', '', $amount);
      $amount = str_replace('.', '', $amount);
      $amount = str_replace(',', '', $amount);

      $data['amount'] = $amount;
  }

  /**
   * Form validation
   * @param $configdetails
   * @param $accountdetails
   * return mixed
   */
    public function formValidation($accountdetails, $configdetails, &$error) {
    $_paymentType = $configdetails['payment_processor_type'];

    if($_paymentType == 'Novalnet Direct Debit Austria' || $_paymentType == 'Novalnet Direct Debit German') {
      if((empty($accountdetails['account_holder'])) ||  preg_match('/[#%\^<>@$=*!]/', $accountdetails['account_holder'])) {
        $error['form'] = ts('Please enter valid account details!');
        return false;
      }
      elseif((empty($accountdetails['account_number'])) ||  preg_match('/[#%\^<>@$=*!]/', $accountdetails['account_number']) || !preg_match('/^[0-9]+$/', $accountdetails['account_number'] )) {
        $error['form'] = ts('Please enter valid account details!');
        return false;
      }
      elseif(empty($accountdetails['bank_code']) ||!preg_match('/^[0-9]+$/', $accountdetails['bank_code']) ||preg_match('/[#%\^<>@$=*!]/', $accountdetails['bank_code'])) {
          $error['form'] = ts('Please enter valid account details!');
        return false;
      }
    }
    if($_paymentType == 'Novalnet Direct Debit German'  && $accountdetails['acdc_control']!=1) {
      $config = CRM_Core_Config::singleton();
      if(isset($config->nn_de_acdc) &&  $config->nn_de_acdc) {
        $error['acdc'] = ts('Please enable ACDC Check.');
        return false;
      }
    }
    return true;
  }

 /* To check element is digit */
  public function isDigits($element) {
    return(preg_match("/^[0-9]+$/", $element));
  }

  /*
   * Validate basic parameters
   * @param  $error
   * return  mixed
   */
  public function basicConfigValidation(&$error) {
    $payment_with_password = array('novalnet_paypal', 'novalnet_instantbank', 'novalnet_ideal', 'novalnet_safetypay');

    if (!self::isDigits($this->_vendorid) || $this->_authcode == '' || !self::isDigits($this->_productid)
        || !self::isDigits($this->_tariffid)) {
        $error = ts('Basic parameter not valid');
        return;
    }
    elseif (in_array($this->_paymentType, $payment_with_password) && $this->_password == '') {
        $error  = ts('Basic parameter not valid');
        return;
    }
    elseif ($this->_paymentType == 'novalnet_paypal' &&
      (empty($this->_paypaluser)  || empty($this->_paypalsig) || empty($this->_paypalpwd))) {
        $error  = ts('Basic parameter not valid');
        return;
    }
  }
  /*
   * Basic validation for iframe.
   * @param $data
   * return  array
   */
  public function iframeDataValidation(&$data) {
    if (!self::isDigits($data['vendorid']) || $data['authcode'] == '' || !self::isDigits($data['productid'])
        || !self::isDigits($data['tariffid'])) {
        $data['error'] = ts('Basic parameter not valid');
        return;
    }
    if ($data['manuallimit']) {
        if (!self::isDigits($data['manuallimit'])) {
          $data['error']= ts('Manual limit amount / Product-ID2 / Tariff-ID2 is not valid');
          return;
        }
        if(intval($data['manuallimit']) > 0 && (!self::isDigits($data['productid2']) || !self::isDigits($data['tariffid2']))) {
          $data['error']= ts('Manual limit amount / Product-ID2 / Tariff-ID2 is not valid');
          return;
        }
    }
  }
  /*
   * Encode the data of array
   * @param $data
   * @param mixed
   */
  public function novalnetEncode(&$data) {
    if (!function_exists('base64_encode') || !function_exists('pack') || !function_exists('crc32')) {
     return FALSE;
    }

    $paymentredirectlists = array('novalnet_paypal', 'novalnet_instantbank', 'novalnet_ideal');
    if (in_array($this->_paymentType, $paymentredirectlists)) {
      $toBeEncoded = array('auth_code', 'product', 'tariff', 'amount','test_mode', 'uniqid');
    }
    else {
      $toBeEncoded = array('vendor_authcode', 'product_id', 'tariff_id', 'amount','test_mode', 'uniqid');
    }
    foreach ($toBeEncoded as $_value ) {
      $fields = $data[$_value];
      if ($fields ==='') {
        return FALSE;
      }
      try {
        $crc = sprintf('%u',  crc32($fields));//%u is a must for ccrc32 returns a signed value
        $fields = $crc . "|" . $fields;
        $fields = bin2hex($fields .  $this->_password);
        $fields = strrev(base64_encode($fields));
        $data[$_value] = $fields;
      } catch (Exception $e) {
        return FALSE;
      }
    }
  return TRUE;
  }
  /**
   * Encode the data
   *
   * @param $data
   * @return string
   */
  public function encode($data) {
    $data = trim($data);
    if ($data == '') return'Error: no data';
    if (!function_exists('base64_encode') or !function_exists('pack') or !function_exists('crc32')){return'Error: func n/a';}
    try {
      $crc = sprintf('%u', crc32($data));# %u is a must for ccrc32 returns a signed value
      $data = $crc."|".$data;
      $data = bin2hex($data. $this->_password);
      $data = strrev(base64_encode($data));
    }catch (Exception $e){
    echo('Error: '.$e);
    }
  return $data;
  }
  /**
   * Generate hash vallue
   *
   * @param $h array
   * @param $key string
   * @return string
   */
  public function hash($h, $key) {
    if (!$h) return'Error: no data';
    if (!function_exists('md5')){return'Error: func n/a';}
      return md5($h['auth_code'].$h['product_id'].$h['tariff'].$h['amount'].$h['test_mode'].$h['uniqid'].strrev($key));
  }
  /*
   * Check the return hash value
   * @param $response
   * @param $key
   * return boolean
   */
  public function novalnetCheckHash($response,  $key) {
    if ($response['hash2'] == self::hash($response,  $key)) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }
  /*
   * Generate hash for before redirection
   * @param $data
   * @param boolean value
   */
  public function generateNovalnetHash(&$data) {

    $paymentredirectlists = array('novalnet_paypal', 'novalnet_instantbank', 'novalnet_ideal');
    $hashFields = array();
    if (in_array($this->_paymentType, $paymentredirectlists)) {
      $hashFields = array('auth_code', 'product', 'tariff', 'amount', 'test_mode', 'uniqid');
    }
    else {
      $hashFields = array('vendor_authcode', 'product_id', 'tariff_id', 'amount', 'test_mode', 'uniqid');
    }
    $str = NULL;
    foreach ($hashFields as $_value) {
      if ($data[$_value] == '') {
       return FALSE;
      }
      $str .= $data[$_value];
    }
    $data['hash'] = md5($str . strrev($this->_password));
  return true;
  }
  /*
   * Get return urls
   * @param $component
   * @param $params
   * @param $processorName
   * return string
   */
  public function getReturnUrl($component, $params, $processorName) {
    if ($component == 'contribute') {
      return CRM_Utils_System::baseCMSURL() . "?q=civicrm/payment/ipn?processor_name=$processorName&md=contribute&qfKey=" . $params['qfKey'] . '&inId=' . $params['invoiceID']. '&processor_name=' . $processorName;
    }
    elseif ($component == 'event') {
      return CRM_Utils_System::baseCMSURL() . "?q=civicrm/payment/ipn?processor_name=$processorName&md=event&qfKey=" . $params['qfKey'] . '&inId=' . $params['invoiceID']. '&processor_name=' . $processorName;
    }
  }
  /**
   * decode the data
   *
   * @param $data
   * @return mixed
   */
  public function novalnetDecode(&$data) {
    if (!function_exists('base64_decode') or !function_exists('pack') or !function_exists('crc32')){return'Error: func n/a';}

    $paymentredirectlists = array('Novalnet PayPal', 'Novalnet Instant Bank Transfer', 'Novalnet iDEAL');

    if (in_array($data['processor_name'],  $paymentredirectlists)) {
      $toBeEncoded = array('auth_code', 'product', 'tariff', 'test_mode', 'uniqid', 'amount');
    }
    else {
      $toBeEncoded = array('vendor_authcode', 'product_id', 'tariff_id', 'test_mode', 'uniqid', 'amount');
    }
    foreach ($toBeEncoded as $_value) {
      $fields= $data[$_value];
      if (empty($data)) {
        return FALSE;
      }
      try {

        $fields = base64_decode(strrev($fields));
        $fields = pack("H" . strlen($fields), $fields);
        $fields = substr($fields, 0, stripos($fields, $this->_password));
        $pos = strpos($fields, "|");
        if ($pos === FALSE) {
          return FALSE;
        }
        $crc = substr($fields, 0, $pos);
        $value = trim(substr($fields, $pos + 1));
        if ($crc != sprintf('%u', crc32($value))) {
          return FALSE;
        }
        $data[$_value] = $value;
        } catch (Exception $e) {
          return FALSE;
        }
    }
  }
  /*
   *  Get the form
   *  param $form_name
   *  param $form_elements
   *  param $form_action_url
   *  return string
   */
  public function getSubmitForm($form_name, $form_elements, $form_action_url) {
    $form_start = '<form name="' . $form_name . '" id="' . $form_name . '" action="' . $form_action_url . '" method="post">';
    $form_elements_html = '';
    foreach ($form_elements as $key => $value) {
        $form_elements_html .= '<input type="hidden" name="' . $key . '" value="' . $value . '" id="' . $key . '" />';
    }
    $form_elements_html .= '<input type="submit" name="submitbutton" value="' . ts('You will be redirected automatically. If not for more than 10 seconds click here') . '" id="submitbutton" />';
    $form_end='</form>';
    return ($form_start . $form_elements_html . $form_end);
  }
  /*
   *  Check the valid component
   *  param $component
   *  return  none
   */
  public function checkComponent($component) {
    if ($component != 'contribute' && $component != 'event') {
      CRM_Core_Error::fatal(ts('Component is invalid'));
    }
  }
  /*
   * Get the Profile details
   * param $params
   * return  array
   */
  public function getProfileDetails($params, &$data) {

    $data['email'] = (isset($params['email-Primary']) ? trim($params['email-Primary']) : (isset($params['email-5']) ? trim($params['email-5']) :(isset($params['email']) ? trim (($params['email'])) : '')));

    $data['first_name'] = (isset($params['first_name']) ? trim($params['first_name']) : '');

    $data['last_name'] = (isset($params['last_name']) ? trim($params['last_name']) : '');

    $data['city'] = (isset($params['city']) ? trim($params['city']) : (isset($params['city-1']) ? trim($params['city-1']) :(isset($params['city-primary']) ? trim (($params['city-primary'])) : '')));

    $data['street'] = (isset($params['street_address-Primary']) ? trim($params['street_address-Primary']) : (isset($params['street_address-1']) ? trim($params['street_address-1']) :(isset($params['street_address']) ? trim (($params['street_address'])) : '')));

    $data['zip'] = (isset($params['postal_code-Primary']) ? trim($params['postal_code-Primary']) : (isset($params['postal_code-1']) ? trim($params['postal_code-1']) :(isset($params['postal_code']) ? trim (($params['postal_code'])) : '')));

    $data['country'] = (isset($params['country-Primary']) ? trim($params['country-Primary']) : (isset($params['country-1']) ? trim($params['country-1']) :(isset($params['country']) ? trim (($params['country'])) : '')));
  }
  /*
   * Validate Customer default params
   * param $data
   * return  array
   */
  public function defaultParamsValidtion(&$data) {

    if (!preg_match("/([\w\-]+\@[\w\-]+\.[\w\-]+)/", $data['email']) || (empty($data['first_name']) && empty($data['last_name']))) {
      $data['value'] = ts('Customer name/email fields are not valid');
    return false;
    }
    if (empty($data['first_name']) || empty($data['last_name']))  {
      $name = trim($data['first_name']) . trim($data['last_name']);
      list($data['first_name'], $data['last_name']) = preg_match('/\s/', $name) ? explode(' ', $name, 2) : array($name,$name);
    }
    foreach($data as $key => $value) {
      if($value == '')  {
        $data['value'] = ts('Address details are required to continue this transaction.');
      return false;
      }
    }
    return true;
   }

  /*
   *  Set product id/tariff id
   *  param $data
   *  return  array
   */
  public function manualCheckLimit (&$data) {
    $data['product_id'] = $this->_productid;
    $data['tariff_id'] = $this->_tariffid;
    if ($this->_manualchecklimit) {
      if (!self::isDigits($this->_manualchecklimit)) {
        $data['error']= ts('Manual limit amount / Product-ID2 / Tariff-ID2 is not valid');
      return;
      }
      if(intval($this->_manualchecklimit) > 0 && (!self::isDigits($this->_productid2) || !self::isDigits($this->_tariffid2))) {
        $data['error']= ts('Manual limit amount / Product-ID2 / Tariff-ID2 is not valid');
      return;
      }
      if (intval($this->_manualchecklimit) > 0 && $data['amount'] >= intval($this->_manualchecklimit) ) {
        $data['product_id'] = $this->_productid2;
        $data['tariff_id']  = $this->_tariffid2;
      }
    }
  }

  /*
   * Set complete params for redirection method
   * param $component
   * param $params
   * return  array
   */
  static public function orderCompleteParam($component, $params, &$add_data) {
    if(isset($params['id'])) {
      $temp_data['id']         = $params['id'];
    }
    $temp_data['module']     = $component;
    $temp_data['orderid']    = $params['invoiceID'];
    $temp_data['cntid']      = $params['contactID'];
    $temp_data['contid']     = $params['contributionID'];
    $temp_data['cntpid']     = isset($params['contributionPageID'])?$params['contributionPageID']:'';
    $temp_data['test']       = $add_data['mode'];
    $temp_data['org_amount'] = $params['amount'];
    $temp_data['qfKey']      = $params['qfKey'];
    if ($component == 'event') {
      $temp_data['eid']       =  $params['eventID'];
      $temp_data['pid']      = $params['participantID'];
    }
    else {
      $membershipID = CRM_Utils_Array::value('membershipID', $params);
      if ($membershipID) {
        $temp_data['mid'] =  $membershipID;
      }
      $relatedContactID = CRM_Utils_Array::value('related_contact', $params);
      if ($relatedContactID) {
        $temp_data['rcid'] =  $relatedContactID;
        $onBehalfDupeAlert = CRM_Utils_Array::value('onbehalf_dupe_alert', $params);
        if ($onBehalfDupeAlert) {
          $temp_data['oda'] = $onBehalfDupeAlert;
        }
      }
    }
    $add_data = array_merge($add_data, $temp_data);
    $_SESSION['novalnet']['completedata'] = $temp_data;
  }

  /*
   * Set url for redirecting to the corresponding page (event/contribution)
   * param array $params
   * param array $component
   * return string
   */
  public function redirectUrl($component, &$cancelURL, $params) {
    $cancelUrlString = "=1&cancel=1&qfKey={$params['qfKey']}";
    if ($component == 'contribute') {
      $cancelURL = CRM_Utils_System::url('civicrm/contribute/transact', $cancelUrlString, TRUE, NULL, FALSE);
    }
    elseif ($component == 'event') {
      $eventid          = $params['eventID'];
      $cancelUrlString  = "id={$eventid}";
      $cancelURL        = CRM_Utils_System::url('civicrm/event/register', $cancelUrlString, TRUE, NULL, FALSE);
    }
  }

  /*
   * Set postback call
   * param array $response
   */
  public function secondCall($response) {
    $postback     = array();

    $referencePayments      = array('Novalnet Invoice', 'Novalnet Prepayment');
    $postback['status']     = 100;
    $postback['tid']        = $response['tid'];
    $postback['order_no']   = $response['order_no'];
    $postback['vendor']     = $response['vendor'];
    $postback['auth_code']  = $response['auth_code'];
    $postback['product']    = $response['product'];
    $postback['tariff']     = $response['tariff'];
    $postback['key']        = $response['key'];

    if (isset($response['processor_name']) && in_array($response['processor_name'], $referencePayments)) {
      $postback['invoice_ref'] = 'BNR-' . $response['product'] . '-' . $response['order_no'];
    }
    $postback = array_map('trim', $postback);

    if (!array_search('', $postback)) {
      $query  = http_build_query($postback, '', '&');
      $host   = 'https://payport.novalnet.de/paygate.jsp';
      $options = array(
        'headers' => array('Content-Type' => 'application/x-www-form-urlencoded'),
        'method' => 'POST',
        'data' => urldecode($query)
      );
      drupal_http_request($host, $options);
    }
    if(isset($_SESSION['cc'])) unset($_SESSION['cc']);
    if(isset($_SESSION['sepa'])) unset($_SESSION['sepa']);
    if(isset($_SESSION['elvat'])) unset($_SESSION['elvat']);
    if(isset($_SESSION['elvde'])) unset($_SESSION['elvde']);
  }

 /**
   * method to validate the card details of user.
   * @param $sepaData
   */
  public function validateSepaData($sepaData) {
 
    if (empty($sepaData['sepa_bic_confirm']) || empty($sepaData['panhash'])
      || empty($sepaData['uniqid']) || preg_match('/[#%\^<>@$=*!]/', $sepaData['holder'])) {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * remove the white space.
   * @param $ccNumber
   * return double
   */
  public function sanitizeNumber($ccNumber) {
    return preg_replace('/[\-\s]+/', '', $ccNumber);
  }

  /**
   * Validate credit card details of user.
   * @param $ccData
   * return boolean
   */
  public function validateCcData($ccData) {

    if (isset($ccData) && array_search('', $ccData)) {
        return FALSE;
    }
    if (preg_match('/[#%\^<>@$=*!]/', $ccData['cc_holder'])) {
        return FALSE;
    }
    if (!self::validateCcExpiry($ccData['cc_exp_month'], $ccData['cc_exp_year']) || !preg_match('/^[0-9]+$/', $ccData['cc_cvc2'])) {
       return FALSE;
    }
  return TRUE;
  }

  /**
   * Validate the credit number of user.
   * @param $ccNumber
   * return boolean
   */
  public function validateCcNumber($ccNumber) {
    if (!is_numeric($ccNumber) || preg_match('/[#%\^<>@$=*!]/', $ccNumber))
      return FALSE;
  return TRUE;
  }
  /**
   * validate card expirty month and year.
   *
   * @param $month
   * @param $year
   * return boolean
   */
  public function validateCcExpiry($month, $year) {
    if (empty($year) || empty($month)) {
      return FALSE;
    }
    if (($year == date('Y') && $month < date('m')) || ($year < date('Y'))) {
      return FALSE;
    }
  return TRUE;
  }

  /**
   * Check status of payment.
   * @param array $_response
   * @return string
   */
  public function checkstatus($_response, &$error) {
    if (isset($_response['status']) && $_response['status'] == 100) {
      return TRUE;
    }
    else {
      $error = self::getstatus_error($_response);
    return $error;
    }
  }
  /**
   * payment method error display.
   * @param array $_response
   * @return string
   */
  public function getstatus_error($_response) {
    if (isset($_response['status_desc'])) {
      $nn_status_error = html_entity_decode($_response['status_desc'], ENT_QUOTES);
    }
    elseif (isset($_response['status_text'])) {
      $nn_status_error = html_entity_decode($_response['status_text'], ENT_QUOTES);
    }
    else {
      $nn_status_error = ts('There was an error and your payment could not be completed.');
    }
    return $nn_status_error;
  }
  /**
   * payment method language.
   * @param array $_lang
   * @return string
   */
  function getLanguage() {
    global  $tsLocale;
    $language = strtoupper(strstr($tsLocale,'_',True));
    return (($language == 'DE' || $language == 'EN') ? $language : 'EN');
  }

  /**
   * Invoice/Prepayment payment Comments.
   * @param array $novalnetResponse
   * @return $comments
   */
  public function getInvoiceComments($novalnetResponse, $test_mode) {
    $comments='';
    $space = ' ';
    $break = '<br>';
    $comments .= $break. $test_mode . $break;

    $comments .= ts($novalnetResponse['processor_name']). $break;
    $comments .= ts('Novalnet Transaction ID :'). $space . $novalnetResponse['tid']. $break . $break ;

    $comments .= ts('Please transfer the amount to the following information to our payment service Novalnet AG') . $break;
    if ($novalnetResponse['processor_name'] == 'Novalnet Invoice' && $novalnetResponse['due_date'] !='' ) {
      $comments .=  ts('Due date :') . $space .CRM_Utils_Date::customFormat($novalnetResponse['due_date']). $break;
    }
    $comments .= ts('Account holder :') . $space . 'NOVALNET AG' . $break;
    $comments .= ts('Account number :') . $space . $novalnetResponse['invoice_account'] . $break;
    $comments .= ts('Bankcode :') . $space . $novalnetResponse['invoice_bankcode'] . $break;
    $comments .= ts('Bank :') . $space . $novalnetResponse['invoice_bankname'] . $space . $novalnetResponse['invoice_bankplace'] . $break;
    $comments .= ts('Amount :') . $space . CRM_Utils_Money::format($novalnetResponse['org_amount']) . $break;
    $comments .= ts('Reference :') . $space . 'TID' . $space . $novalnetResponse['tid'] . $break . $break;
    $comments .= ts('Only for international transfers:') . $break;
    $comments .= ts('IBAN :') . $space . $novalnetResponse['invoice_iban'] . $break;
    $comments .= ts('SWIFT / BIC :') . $space . $novalnetResponse['invoice_bic'] . $break;

  return $comments;
  }

  /*
   * payment methods order details.
   * @param array $novalnetResponse
   * @return $comments
   */
  public function getTransactionComments($novalnetResponse, $test_mode) {
    $config                     = CRM_Core_Config::singleton();
    $comments='';
    $break = '<br>';
    $comments .= $break;
    $comments .= $test_mode . $break;
    $comments .= ts($novalnetResponse['processor_name']). $break . ts('Novalnet Transaction ID :') . $novalnetResponse['tid'];
    if (isset($config->nn_sepa_payment_type) && trim($config->nn_sepa_payment_type) == 'DIRECT_DEBIT_SEPA_SIGNED') {
    if ($novalnetResponse['processor_name'] == 'Novalnet Direct Debit SEPA' && isset($config->nn_sepa_payment_type) && trim($config->nn_sepa_payment_type) == 'DIRECT_DEBIT_SEPA_SIGNED' && $novalnetResponse['mandate_present'] ==0) {
        $data['tid'] = trim($novalnetResponse['tid']);
        $vendor      = trim($novalnetResponse['vendor']);
        $comments    .= "<br>" . ts('Download your mandate') .":";

       if (isset($novalnetResponse['mandate_url']) && trim($novalnetResponse['mandate_url']) !='' ) {
          if (strpos($novalnetResponse['mandate_url'], "?") !== false) {
            $url          = explode('?', $novalnetResponse['mandate_url']);
            $mandate_ref  = (isset($novalnetResponse['mandate_ref']) && trim($novalnetResponse['mandate_ref']) !='') ? ('&mandate_ref=' . $novalnetResponse['mandate_ref']) :('&mandate_ref=NN' . $vendor. '-' . $novalnetResponse['tid']);
            $mandat_url = $url[0] . '?vendor_id='. $vendor . '&tid=' . $novalnetResponse['tid'] . $mandate_ref;
            $comments      .= '<a href= "' . $mandat_url .'" target="_blank">'. ts('Click here') .'</a>';
         }
        }
        else {
          $mandate_ref  = (isset($novalnetResponse['mandate_ref']) && trim($novalnetResponse['mandate_ref']) !='') ? ('&mandate_ref=' . $novalnetResponse['mandate_ref']) : ('&mandate_ref=NN' . $vendor. '-' . $novalnetResponse['tid']);
          $url = 'https://payport.novalnet.de/sepa/mandate_pdf_generate.jsp?vendor_id=' . $vendor . '&tid=' . $novalnetResponse['tid'] . $mandate_ref;
          $comments .= '<a href = "'.$url .'" target="_blank">'. ts('Click here') . '</a>';
      }
    }
  }
  return $comments;
}

  /*
   * payment methods basic params.
   * @param array $params
   * @return $data
   */
  public function urlparams($params, &$data) {
    global $user;

    $versionDetails = civicrmVersion();
    $config = CRM_Core_Config::singleton();

    $customer_no                = (isset($user->uid) && $user->uid!=0) ? $user->uid : 'guest';
    $data['currency']           = $params['currencyID'];
    $data['first_name']         = $data['first_name'];
    $data['last_name']          = $data['last_name'];
    $data['gender']             = 'u';
    $data['email']              = $data['email'];
    $data['street']             = $data['street'];
    $data['search_in_street']   = 1;
    $data['city']               = $data['city'];
    $data['zip']                = $data['zip'];
    $data['country']            = $data['country'];
    $data['country_code']       = $data['country'];
    $data['remote_ip']          = CRM_Utils_System::ipAddress();
    $data['order_no' ]          = $params['invoiceID'];
    $data['session']            = session_id();
    $data['customer_no']        = $customer_no;
    $data['use_utf8']           = 1;
    $data['birth_date']         = '';
    $data['fax']                = '';
    $data['system_name']        = 'Drupal - civicrm';
    $data['system_version']     = VERSION . '-' . $versionDetails['version'] .'-NN2.0.0';
    $data['system_ip']          = $_SERVER['SERVER_ADDR'];
    $data['system_url']         = url('', array('absolute' => TRUE));

    if ( isset($config->nn_referrer_id) && trim($config->nn_referrer_id)) {
        $data['referrer_id'] = trim(strip_tags(html_entity_decode($config->nn_referrer_id)));
    }
 
  }

  /*
   * payment methods returl url .
   * @param array $returl_url
   * @return $data
   */
  public function returnUrlParams($return_url, &$data) {
    $data['return_url']              = $return_url;
    $data['return_method']           = 'POST';
    $data['error_return_url']        = $return_url;
    $data['error_return_method']     = 'POST';
  }
  /*
   * payment methods contains hash2 value.
   * @param array $response
   * @return $temp_data
   */
  public function tempData($response) {
    $redirectmethod = array('Novalnet PayPal', 'Novalnet Instant Bank Transfer', 'Novalnet iDEAL');

    $temp_data = array(
      'test_mode'            => $response['test_mode'],
      'uniqid'               => $response['uniqid'],
      'amount'               => $response['amount'],
      'hash2'                => $response['hash2'],
      'hash'                 => $response['hash'],
    );
    if (in_array($response['processor_name'], $redirectmethod )) {
      $temp_data['auth_code']  = $response['auth_code'];
      $temp_data['product_id'] = $response['product'];
      $temp_data['tariff']     = $response['tariff'];
    }
    else {
      $temp_data['auth_code']  = $response['vendor_authcode'];
      $temp_data['product_id'] = $response['product_id'];
      $temp_data['tariff']     = $response['tariff_id'];
    }
  return $temp_data;
  }
   /*
   * payment methods to update the paymentinstrumentid.
   * @param array $response
   * @return null
   */
  public function paymentNameUpdation($response) {
    $dao = CRM_Core_DAO::executeQuery("SELECT value FROM civicrm_option_value WHERE name ='".$response['processor_name']."'");
    if ($dao->fetch()) {
      $instrumentid = $dao->value;
      $query = "UPDATE civicrm_contribution SET payment_instrument_id='" . $instrumentid . "' where invoice_id='" . $response['orderid'] . "'";
      CRM_Core_DAO::executeQuery($query);
    }

  }
  /*
   * payment methods to update the paymentinstrumentid.
   * @param array $response
   * @return null
   */
  public function paymentUpdation($response) {

    $que_pay = "UPDATE civicrm_contribution SET contribution_status_id=3 where invoice_id='" .$response['orderid']. "'";
    CRM_Core_DAO::executeQuery($que_pay);
  }

  public function commentsOnError($novalnetResponse, $error) {
    $notes = ts('Novalnet Transaction Details') . '<br>' . $error;
    if(isset($novalnetResponse['tid']))
      $notes .= '<br>'. ts('Novalnet Transaction ID :') . $novalnetResponse['tid'];
    $qry = "select * from civicrm_contribution where invoice_id='".$novalnetResponse['orderid'] ."'";
    $dao = CRM_Core_DAO::executeQuery($qry);
    $dao->fetch( );
    $entity_table     = 'civicrm_contribution';
    $entity_id        = $dao->id;
    $note             = $notes;
    $contact_id       = $dao->contact_id;
    $modified_date    = date('Y-m-d');
    $insert_query     = "Insert into civicrm_note (entity_table, entity_id, note, contact_id, modified_date) values ('$entity_table', $entity_id, '$note', $contact_id,'$modified_date')";
      CRM_Core_DAO::executeQuery($insert_query);
  }
  /*
   * Get Reference for the novalnet orders
   * @param array $response
   * @return null
   */
  public function getPaymentReference(&$data, $paymentkey) {
      $config                     = (array)CRM_Core_Config::singleton();

      $reference_one = (isset($config[$paymentkey.'_ref_one'])? trim($config[$paymentkey.'_ref_one']):'');
      $reference_two = (isset($config[$paymentkey.'_ref_two'])? trim($config[$paymentkey.'_ref_two']):'');
      if(!empty($reference_one)) {
          $data['input1']     = 'reference1';
          $data['inputval1']  = strip_tags($reference_one);
      }
      if(!empty($reference_two)) {
          $data['input2']     = 'reference2';
          $data['inputval2']  = strip_tags($reference_two);
      }
  }
}
?>

