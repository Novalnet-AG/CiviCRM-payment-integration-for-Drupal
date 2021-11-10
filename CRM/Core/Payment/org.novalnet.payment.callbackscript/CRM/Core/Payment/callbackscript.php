<?php

  require_once 'CRM/Core/DAO.php';
  require_once 'Mail/mime.php';
  /**
   * Novalnet Callback Script for civicrm
   *
   * NOTICE
   *
   * This script is used for real time capturing of parameters passed
   * from Novalnet AG after Payment processing of customers.
   *
   * This script is only free to the use for Merchants of Novalnet AG
   *
   * If you have found this script useful a small recommendation as well
   * as a comment on merchant form would be greatly appreciated.
   *
   * Please contact sales@novalnet.de for enquiry or info
   *
   * ABSTRACT: This script is called from Novalnet, as soon as a payment
   * done for payment methods, e.g. Prepayment, Invoice.
   * An email will be sent if an error occurs
   *
   *
   * @category   Novalnet
   * @package    Novalnet
   * @version    2.0.0
   * @copyright  Copyright (c) Novalnet AG. (https://www.novalnet.de)
   * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
   * @notice     1. This script must be placed in civicrm root folder
   *                to avoid rewrite rules (mod_rewrite)
   *             2. You have to adapt the value of all the variables
   *                commented with 'adapt ...'
   *             3. Set $test/$debug to false for live system
  */
 
 global $lineBreak, $aPaymentTypes, $allowedPayment, $hParamsRequired, $basicSettings,$emailBody, $invoiceAllowed;
  $emailBody = '';
  $request = $_REQUEST;
  $invoiceAllowed = array('INVOICE_CREDIT');
  $aPaymentTypes = array(
                        'novalnet_invoice'      => array('INVOICE_CREDIT'),
                        'novalnet_prepayment'   => array('INVOICE_CREDIT'),
                        'novalnet_paypal'       => array('PAYPAL'),
                        'novalnet_banktransfer' => array('ONLINE_TRANSFER'),
                        'novalnet_cc'           => array('CREDITCARD', 'CREDITCARD_BOOKBACK'),
                        'novalnet_cc3d'         => array('CREDITCARD', 'CREDITCARD_BOOKBACK'),
                        'novalnet_elv_at'       => array('DIRECT_DEBIT_AT'),
                        'novalnet_elv_de'       => array('DIRECT_DEBIT_DE'),
                        'novalnet_sepa'       => array('DIRECT_DEBIT_SEPA'),
                        'novalnet_safetypay'       => array('SAFETYPAY'),
                        'novalnet_ideal'        => array('IDEAL')
                      );
  $allowedPayment = array('novalnet_invoice', 'novalnet_prepayment', 'novalnet_paypal', 'novalnet_banktransfer', 'novalnet_cc', 'novalnet_cc3d','novalnet_elv_at', 'novalnet_elv_de', 'novalnet_ideal');
  //Parameters Settings
  $hParamsRequired = array(
    'vendor_id'    => '',
    'tid'          => '',
    'payment_type' => '',
    'status'       => '',
    'amount'       => '',
    'tid_payment'  => ''
    );
  $config = CRM_Core_Config::singleton();

  $basicSettings = array (
    'debug'                 => isset($config->nn_callback_debug)? trim($config->nn_callback_debug):FALSE,
    'test'                  => isset($config->nn_callback_testmode)? trim($config->nn_callback_testmode):FALSE,
    'addSubsequentTidToDb'  => true,
    'orderstate'            => 1,
    'ipAllowed'             => '195.143.189.210',
    'shopInfo'              => 'Civicrm',
    'emailFromAddr'         => isset($config->nn_callback_frommail)?trim($config->nn_callback_frommail):'',
    'emailToAddr'           => isset($config->nn_callback_tomail)?trim($config->nn_callback_tomail):'',
    'emailSubject'          => 'Novalnet Callback Script Access Report',
    'emailBody'             => isset($config->nn_callback_mailbody)? trim($config->nn_callback_mailbody):'',
    'emailFromName'         => isset($config->nn_callback_fromname)? trim($config->nn_callback_fromname):'',
    'sendmail'              => isset($config->nn_callback_sendmail)? trim($config->nn_callback_sendmail):TRUE,
    'cc'                    => isset($config->nn_callback_mailcc)? trim($config->nn_callback_mailcc):'',
    'bcc'                   => isset($config->nn_callback_mailbcc)? trim($config->nn_callback_mailbcc):'',
    'emailToName'           => '',
    );
  $lineBreak  = empty($_SERVER['HTTP_HOST'])? PHP_EOL: '<br />';

  if(isset($request['IDS_request_uri'])) {
    unset($request['IDS_request_uri']);
  }
  if(isset($request['IDS_user_agent'])) {
    unset($request['IDS_user_agent']);
  }
  if(isset($request['payment_type']) && ($request['payment_type'] != 'INVOICE_CREDIT')) {
    unset($hParamsRequired['tid_payment']);
  }
  ksort($hParamsRequired);

  // ################### Main Prog. ##########################
  try {
  //Check Params
  if (checkIP($request)) {
    if (checkParams($request)) {
      //Get Order ID and Set New Order Status
      getIncrementId($request);
   }
  }

/*
  if (!$emailBody) {
    $emailBody .= 'Novalnet Callback Script called for StoreId Parameters: ' . print_r($request, true) . $lineBreak;
    $emailBody .= 'Novalnet callback succ. ' . $lineBreak;
    $emailBody .= 'Params: ' . print_r($request, true) . $lineBreak;
  }*/
  } catch(Exception $e) {
   $emailBody .= "Exception catched: $lineBreak\$e:" . $e->getMessage() . $lineBreak;
  }


  if ($emailBody) {
    if (!sendEmail($emailBody)) {
      if ($basicSettings['debug']) {
        echo "Mailing failed!".$lineBreak;
        echo "This mail text should be sent: ".$lineBreak;
        echo $emailBody;
      }
    }
  }
  exit;
  // ############## Sub Routines #####################
  /*Method to send the callback script result mail */
  function sendEmail($emailBody) {
   global $lineBreak, $aPaymentTypes, $allowedPayment, $hParamsRequired, $basicSettings,$emailBody;
    //Send Email
    $emailBodyT = str_replace('<br />', PHP_EOL, $emailBody);
    $final_text = $basicSettings['emailBody']. $lineBreak.$emailBodyT;

    header('Content-Type: text/html; charset=iso-8859-1');
    $headers = 'From: ' . $basicSettings['emailFromAddr'] . "\r\n";
    $params['subject'] = $basicSettings['emailSubject'];
    $params['from']    = $basicSettings['emailFromAddr'];
    $params['html']    = $final_text;
    $params['toEmail'] = $basicSettings['emailToAddr'];
    $params['cc']      = $basicSettings['cc'];
    $params['bcc']     = $basicSettings['bcc'];
    try {
      if ($basicSettings['debug'] && $basicSettings['sendmail']) {
        echo __FUNCTION__.' : Sending Email suceeded!'.$lineBreak;
      }
      if($basicSettings['sendmail']) {
        $sendmail = CRM_Utils_Mail::send($params);
       // $sendmail = mail($basicSettings['emailToAddr'], $basicSettings['emailSubject'], $mesg , $headers);
      } else{ return false;}

    }
    catch(Exception $e) {
      if ($basicSettings['debug']) {
        echo 'Email sending failed: '.$e->getMessage();
      }
      return false;
    }
    if ($basicSettings['debug']) {
      echo 'This text has been sent:'.$lineBreak.$final_text;
    }

  return true;
  }

  /*Method to validate the url parameters */
  function checkParams($request) {
    global $lineBreak, $aPaymentTypes, $allowedPayment, $hParamsRequired, $basicSettings,$emailBody, $invoiceAllowed;
    $error = false;
    $emailBody = '';
    if (!$request) {
      $emailBody .= 'Novalnet callback received. No params passed over!' . $lineBreak;
      return false;
    }
    if ($hParamsRequired) {
      foreach ($hParamsRequired as $k=>$v) {
        if (empty($request[$k])) {
          $error = true;
          $emailBody .= 'Required param (' . $k . ') missing!' . $lineBreak;
        }
      }
      if ($error) {
        return false;
      }
    }
    if (!search_val($request['payment_type'], $aPaymentTypes)){
      $emailBody .= 'Novalnet callback received. Payment type (' .$request['payment_type'].') is mismatched!' .     $lineBreak;
          return false;
    }

    if(!isset($request['status']) or $request['status'] <= 0) {
      $emailBody .= 'Novalnet callback received. Status [' . $request['status'] . '] is not valid: Only 100 is allowed.' . $lineBreak . $lineBreak . $lineBreak;
      return false;
    }

    if(isset($request['payment_type']) && in_array($request['payment_type'], $invoiceAllowed) && strlen($request['tid_payment'])!=17) {
      $emailBody .= 'Novalnet callback received. Invalid TID [' . $request['tid_payment'] . '] for Order.' . $lineBreak . $lineBreak;
        return false;

    }
    if(strlen($request['tid'])!=17) {
      if(in_array($request['payment_type'], $invoiceAllowed)) {
        $emailBody .= 'Novalnet callback received. New TID is not valid.' . $lineBreak . $lineBreak;
      } else {
        $emailBody .= 'Novalnet callback received. Invalid TID ['.$request['tid'].'] for Order.'. $lineBreak.$lineBreak;
      }
      return false;
    }
    return true;
  }
  /*Method to find the orderid and validate the order */
  function getIncrementId($request) {
        global $lineBreak, $aPaymentTypes, $allowedPayment, $hParamsRequired, $basicSettings,$emailBody, $invoiceAllowed;
    $orderDetails = array();

    //check amount
    $amount  = $request['amount'];
    $_amount = isset($order_total) ? $order_total * 100 : 0;

    if(!$amount || $amount < 0) {
      $emailBody .= "Novalnet callback received. The requested amount ($amount) must be greater than zero." .$lineBreak.$lineBreak;
      return false;
    }
    $org_tid = (in_array($request['payment_type'], $invoiceAllowed) ? $request['tid_payment'] : $request['tid']);

    $qry = "SELECT * from civicrm_contribution WHERE trxn_id LIKE '%".$org_tid."%'";
    $dao = CRM_Core_DAO::executeQuery($qry);

    if (!$dao->fetch()) {
      $qry = "SELECT * from civicrm_contribution WHERE invoice_id LIKE '%".$request['order_no']."%'";
      $dao = CRM_Core_DAO::executeQuery($qry);
      if (!$dao->fetch()) {
        echo 'Novalnet callback received. Transaction Mapping Failed';
        return false;
      }
      else {
       $civi_orderId      = $dao->id;
       $civi_paymentId    = $dao->payment_instrument_id;
       $civi_trxn_details = $dao->trxn_id;
      }
    }
    else {
     $civi_orderId      = $dao->id;
     $civi_paymentId    = $dao->payment_instrument_id;
     $civi_trxn_details = $dao->trxn_id;

    }
    //check amount
    $amount       = $request['amount'];
    $store_price  = $dao->total_amount;
    $roundofprice = round($store_price, 2);
    $final_price  = $roundofprice * 100;
    $_amount      = isset($final_price) ? $final_price : 0;

    $odetailsdisp = 'Order Details:<pre>Amount : '.$roundofprice.'&nbsp;'.$dao->currency.'</pre>';


    $qry_str = "SELECT * from civicrm_option_value where value ='". $civi_paymentId ."'";
    $dao= CRM_Core_DAO::executeQuery($qry_str);
    if ($dao->fetch( )) {
       $civi_payment = $dao->name;
       $civi_option_value = $dao->value;
    }
    $aPayment = array ('100','101','102','103','104','106','107', '108', '109','110');
   $allPaymentTypes = array(
                        '100'   => array('INVOICE_CREDIT'),
                        '101'   => array('INVOICE_CREDIT'),
                        '108'   => array('PAYPAL'),
                        '107'   => array('ONLINE_TRANSFER'),
                        '104'   => array('CREDITCARD', 'CREDITCARD_BOOKBACK'),
                        '102'   => array('DIRECT_DEBIT_AT'),
                        '103'   => array('DIRECT_DEBIT_DE'),
                        '109'   => array('DIRECT_DEBIT_SEPA'),
                        '110'   => array('SAFETYPAY'),
                        '106'   => array('IDEAL'));
      if(isset($request['order_no']) && !empty($request['order_no']) && $request['order_no'] != $dao->invoice_id) {
          $emailBody .= "Novalnet callback received. Order Number Not Valid$lineBreak$lineBreak";
      return false;
    }
    if (!in_array($civi_option_value, $aPayment)) {
      $emailBody .= "Novalnet callback received. No order for(".$request['order_no'].")$lineBreak$lineBreak";
      return false;
    }
    $error=0;
    foreach ($allPaymentTypes[$civi_option_value] as $key => $val) {
      if ($val == $request['payment_type'] ) {
        $error = 1;
      }
    }
    if($error !=1) {
      $emailBody .= "Novalnet callback received.Payment type [".$request['payment_type']."] is mismatched! $lineBreak$lineBreak";
        return false;
      }
  

   if ($basicSettings['debug']) { echo $odetailsdisp;}
   setOrderStatus($civi_orderId, $civi_paymentId, $request);
 }

/*Method to update the order balance, order status and novalnet order comments */
  function setOrderStatus ($civi_orderId, $civi_paymentId, $request) {
    global $lineBreak, $basicSettings, $emailBody, $hParamsRequired, $aPaymentTypes, $allowedPayment, $invoiceAllowed;
    $qry = "SELECT id, trxn_id, contribution_status_id,contact_id, total_amount from civicrm_contribution WHERE  id = $civi_orderId";
    $dao = CRM_Core_DAO::executeQuery($qry);
    $dao->fetch();
    $org_tid = (in_array($request['payment_type'], $invoiceAllowed) ? $request['tid_payment'] : $request['tid']);
    if ($civi_orderId) {
      if ($request['status'] == 100) {
        if ($dao->contribution_status_id != 1) {
          if (!in_array($request['payment_type'], $invoiceAllowed)) {
            $emailBody .= $callback_comment = "$lineBreak Novalnet callback received. Novalnet Callback Script executed successfully on ".date("Y-m-d H:i:s");
          } else {
            $amount = sprintf('%0.2f', ($request['amount']/100));
            $emailBody .= $callback_comment = " $lineBreak Novalnet callback received. Novalnet Callback Script executed successfully for amount:".$amount. ". The subsequent TID: (".$request["tid"].") on ".date("Y-m-d H:i:s");
          }
          if (trim($dao->trxn_id) == '') {
            $comments = $org_tid;
            if (isset($request["test_mode"]) && $request["test_mode"] == 1 )
              $comments .= '<br>'.ts("Test Order");
          }
          else {
            $comments =  $callback_comment;
          }
          if ($dao->id) {
            $notedata = CRM_Core_DAO::executeQuery("SELECT id FROM civicrm_note WHERE entity_id=$dao->id and entity_table ='civicrm_contribution'");
            $notedata->fetch();
            if ($notedata->id) {
              $update = CRM_Core_DAO::executeQuery("UPDATE civicrm_note SET note=CONCAT(note, '$comments') WHERE id=$notedata->id and entity_id=$dao->id and entity_table ='civicrm_contribution'");
            }
            else {
              $entity_table     = 'civicrm_contribution';
              $entity_id        = $dao->id;
              $contact_id       = $dao->contact_id;
              $modified_date    = date('Y-m-d');
              $update = CRM_Core_DAO::executeQuery("Insert into civicrm_note (entity_table, entity_id, note, contact_id, modified_date) values ('$entity_table', $entity_id, '$comments', $contact_id,'$modified_date')");
            }
          }
          if ($request['payment_type'] == 'INVOICE_CREDIT') {
            $callback_amount      = $_REQUEST['amount'];
            $reference_tid        = $_REQUEST['tid'];
            $callback_datetime    = date('Y-m-d');
            $update = CRM_Core_DAO::executeQuery("Insert into novalnet_callback (order_id, callback_amount, reference_tid, callback_datetime, callback_tid) values ($civi_orderId, $callback_amount, $reference_tid, '$callback_datetime',$org_tid)");
            $paidamount = CRM_Core_DAO::executeQuery("SELECT SUM(callback_amount) as paidamount FROM novalnet_callback  WHERE order_id=$civi_orderId");
            $paidamount->fetch();
          }
          $ordertotal = $dao->total_amount*100;

          if (($request['payment_type'] != 'INVOICE_CREDIT') || ($request['payment_type'] == 'INVOICE_CREDIT' && $paidamount->paidamount >=$ordertotal)) {
            $status_update = CRM_Core_DAO::executeQuery("UPDATE civicrm_contribution SET contribution_status_id=1 where id= $civi_orderId");
          }
        }
        else {
          $emailBody .= "Novalnet callback received. Callback Script executed already. Refer Order :".$civi_orderId;
          return false;
      }
    } else {
      if ($dao->contribution_status_id != 1) {
        if (!in_array($request['payment_type'], $invoiceAllowed)) {
            $callback_comment = "$lineBreak Novalnet callback received. Novalnet Callback Script executed successfully on ".date("Y-m-d H:i:s");
          } else {
            $amount = sprintf('%0.2f', ($request['amount']/100));
            $callback_comment = " $lineBreak Novalnet callback received. Novalnet Callback Script executed successfully for amount:".$amount. ". The subsequent TID: (".$request["tid"].") on ".date("Y-m-d H:i:s");
          }
          if (trim($dao->trxn_id) == '') {
            $comments = $org_tid;
            if (isset($request["test_mode"]) && $request["test_mode"] == 1 )
              $comments .= '<br>'.ts("Test Order");
          }
          else {
            $comments = $callback_comment;
          }
          if ($dao->id) {
            $notedata = CRM_Core_DAO::executeQuery("SELECT id FROM civicrm_note entity_id=$dao->id and entity_table ='civicrm_contribution'");
            if ($notedata->id) {
              $update = CRM_Core_DAO::executeQuery("UPDATE civicrm_note SET note=CONCAT(note, '$comments') WHERE id=$notedata->id and entity_id=$dao->id and entity_table ='civicrm_contribution'");
            }
            else {
              $entity_table     = 'civicrm_contribution';
              $entity_id        = $dao->id;
              $contact_id       = $dao->contact_id;
              $modified_date    = date('Y-m-d');
              $update = CRM_Core_DAO::executeQuery("Insert into civicrm_note (entity_table, entity_id, note, contact_id, modified_date) values ('$entity_table', $entity_id, '$comments', $contact_id,'$modified_date')");
            }
          }
        } else {
          echo "Novalnet callback received. Callback Script executed already. Refer Order :".$civi_orderId;
          exit;
        }
      }
    }
    else {
      $emailBody .= "Novalnet Callback received. No order for Increment-ID $civi_orderId found.";
      return false;
    }
    return true;
  }

  /*Method to validate the the IP address */
  function checkIP($request) {
    global $lineBreak, $basicSettings, $emailBody, $allowedPayment;

    $callerIp  = $_SERVER['REMOTE_ADDR'];
    if ($basicSettings['ipAllowed'] != $callerIp && !$basicSettings['test']) {
      $emailBody .= 'Novalnet callback received. Unauthorised access from the IP [' . $callerIp . ']' . $lineBreak . $lineBreak;
      $emailBody .= 'Request Params: ' . print_r($request, true);
      return false;
    }
  return true;
  }
  /**
 * Search array value
 */
function search_val($nn_value, $array) {
  if (!is_array($array))
    return false;
  foreach($array as $key => $value) {
    if(in_array($nn_value, $value))
      return true;
  }
}

?>
