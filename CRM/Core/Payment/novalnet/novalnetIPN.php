<?php
#########################################################
#                                                       #
#  Novalnet IPN payment method class                    #
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
#  Script : novalnetIPN.php                             #
#                                                       #
#########################################################
$config = CRM_Core_Config::singleton();
require_once 'CRM/Core/Payment/novalnet/novalnet.php';
require_once 'CRM/Core/Config.php';
require_once 'CRM/Utils/Hook.php';
class org_novalnet_payment_novalnetipn extends CRM_Core_Payment_BaseIPN {

  private static $_singleton = null;

  function __construct($mode, &$paymentProcessor) {
    parent::__construct();

    $this->_mode = $mode;
    $this->_paymentProcessor = $paymentProcessor;
  }

  /**
   * singleton function used to manage this object
   *
   * @param string $mode the mode of operation: live or test
   *
   * @return object
   * @static
   */
  static function &singleton($mode, $component, &$paymentProcessor) {
    if (self::$_singleton === null) {
      self::$_singleton = new org_novalnet_payment_novalnetipn($mode, $paymentProcessor);
    }
    return self::$_singleton;
  }

  /**
   * This method is handles the response that will be invoked by the
   * notification or request sent by the payment processor.
   * hex string from paymentexpress is passed to this function as hex string.
   */
  function main($novalnetResponse) {

    CRM_Core_Error::debug_var('$novalnetResponse' , $novalnetResponse);
    $config = CRM_Core_Config::singleton();
    $objects = $ids = $input = array();
     // get the contribution and contact ids from the GET params
    $ids['contact']      = $novalnetResponse['cntid'];
    $ids['contribution'] = $novalnetResponse['contid'];
    $ids['comp']         = $novalnetResponse['module'];
    $input['status']     = $novalnetResponse['status'];
    $input['invoice']    = $novalnetResponse['orderid'];
    $input['amount']     = $novalnetResponse['amount'];
    $input['trxn_id']    = $novalnetResponse['tid'];
    $objects['trxn_id']  = $novalnetResponse['tid'];
    $input['is_test']    = ($novalnetResponse['check']=='live')?'0':'1';
    $input['component']  = $novalnetResponse['module'];
    $input['qfKey']      = $novalnetResponse['qfKey'];
    $input['payment_instrument_id']  ='';
    $dao = CRM_Core_DAO::executeQuery("SELECT value FROM civicrm_option_value WHERE name ='".$novalnetResponse['processor_name']."'");
 
    if ($dao->fetch()) {
        $input['payment_instrument_id']  = $dao->value;
    }
    if ($input['component'] == 'event') {
        $ids['event']         = $novalnetResponse['eid'];
        $ids['participant']   = $novalnetResponse['pid'];
    }
    else {
      $ids['membership']       = isset($novalnetResponse['mid'])?$novalnetResponse['mid'] : '';
      $ids['contributionPage'] = isset($novalnetResponse['cntpid'])? $novalnetResponse['cntpid'] : '';
      $ids['relatedContact']   = isset($novalnetResponse['rcid'])? $novalnetResponse['rcid'] : '';
    }

    $test_mode = ((isset($novalnetResponse['test_mode']) && $novalnetResponse['test_mode']== 1) || (isset($input['is_test']) && $input['is_test'] == 1)) ? ts('Test Order') : '';

    if($novalnetResponse['processor_name'] == 'Novalnet Prepayment'
        || $novalnetResponse['processor_name'] == 'Novalnet Invoice') {
          $comments =  CRM_Core_Payment_novalnet::getInvoiceComments($novalnetResponse, $test_mode);
    }
    else {
        $comments = CRM_Core_Payment_novalnet::getTransactionComments($novalnetResponse, $test_mode);
    }
    $payment            = array( 'nn_comments' => $comments );
    $template           = CRM_Core_Smarty::singleton();
    $template->assign( 'nn_payment', $payment);
    $transcation        = $novalnetResponse['tid'];
    $input['trxn_id']   =  $novalnetResponse['tid'];
    if (!$this->validateData($input, $ids, $objects,'',$this->_paymentProcessor['id'])) {
      return false;
    }

    list ($mode, $duplicateTransaction) = self::getContext($ids);
    $mode = $mode ? 'test' : 'live';
    $this->newOrderNotify($input, $ids, $objects, false, false); 
    if (($novalnetResponse['processor_name'] != 'Novalnet PayPal' && $novalnetResponse['status'] != 100)
        ||($novalnetResponse['processor_name'] == 'Novalnet PayPal' && ($novalnetResponse['status'] != 90
          &&  $novalnetResponse['status'] != 100 ))) {
            if ($input['component'] == "event") {
              $finalURL = CRM_Utils_System::url('civicrm/event/confirm', "reset=1&cc=fail&participantId={$ids['participant']}", false, null, false);
              CRM_Core_Session::setStatus(ts($novalnetResponse['status_desc']),  'error');
            }
          elseif ($input['component'] == "contribute") {
              $finalURL = CRM_Utils_System::url('civicrm/contribute/transact', "_qf_Main_display=true&cancel=1&qfKey={$novalnetResponse['qfKey']}", FALSE, NULL, FALSE);
              CRM_Core_Session::setStatus(ts($novalnetResponse['status_desc']),  'error');
          }
    }
    else {
      if ($input['component'] == "event") {
        if (($novalnetResponse['processor_name'] == 'Novalnet PayPal' && $novalnetResponse['status'] == 90)
          || $novalnetResponse['processor_name'] == 'Novalnet Invoice' || $novalnetResponse['processor_name'] == 'Novalnet Prepayment'
          || ($novalnetResponse['processor_name'] == 'Novalnet Direct Debit SEPA' && isset($novalnetResponse['mandate_present']) && $novalnetResponse['mandate_present'] == '0')) {
            $query = "UPDATE civicrm_contribution SET contribution_status_id=2 where invoice_id='" . $novalnetResponse['order_no'] . "'";
          CRM_Core_DAO::executeQuery($query);
        }
        $finalURL = CRM_Utils_System::url('civicrm/event/register', "_qf_ThankYou_display=1&qfKey={$novalnetResponse['qfKey']}", false, null, false);
      }
      elseif ($input['component'] == "contribute") {
        if (($novalnetResponse['processor_name'] == 'Novalnet PayPal' && $novalnetResponse['status'] == 90)
          || $novalnetResponse['processor_name'] == 'Novalnet Invoice' || $novalnetResponse['processor_name'] == 'Novalnet Prepayment'
          || ($novalnetResponse['processor_name'] == 'Novalnet Direct Debit SEPA' && isset($novalnetResponse['mandate_present'])
          && $novalnetResponse['mandate_present'] == '0')) {
          $query = "UPDATE civicrm_contribution SET contribution_status_id=2 where invoice_id='" . $novalnetResponse['order_no'] . "'";
          CRM_Core_DAO::executeQuery($query);
        }
        $finalURL = CRM_Utils_System::url('civicrm/contribute/transact', "_qf_ThankYou_display=1&qfKey={$novalnetResponse['qfKey']}", FALSE, NULL, FALSE);
      }
        CRM_Core_Session::setStatus($comments ,ts('Novalnet Transaction Details'),'success');
    }
    if($novalnetResponse['processor_name'] == 'Novalnet SafetyPay' ) {
      $novalnetResponse['vendor']    = $novalnetResponse['vendor_id'];
      $novalnetResponse['product']   = $novalnetResponse['product_id'];
      $novalnetResponse['tariff']    = $novalnetResponse['tariff_id'];
      $novalnetResponse['auth_code'] = $novalnetResponse['vendor_authcode'];
      $novalnetResponse['key']       = $novalnetResponse['payment_id'];
    }
    CRM_Core_Payment_novalnet::secondCall($novalnetResponse);

      $qry = "select * from civicrm_contribution where invoice_id='".$novalnetResponse['orderid'] ."'";
      $dao = CRM_Core_DAO::executeQuery($qry);
      $dao->fetch( );
      $entity_table     = 'civicrm_contribution';
      $entity_id        = $dao->id;
      $note             = $comments;
      $contact_id       = $dao->contact_id;
      $modified_date    = date('Y-m-d');
      $insert_query     = "Insert into civicrm_note (entity_table, entity_id, note, contact_id, modified_date) values ('$entity_table', $entity_id, '$note', $contact_id,'$modified_date')";
      CRM_Core_DAO::executeQuery($insert_query);


    if ($novalnetResponse['processor_name'] == 'Novalnet Direct Debit SEPA' && isset($config->nn_sepa_payment_type)
      && trim($config->nn_sepa_payment_type) == 'DIRECT_DEBIT_SEPA_SIGNED' && $novalnetResponse['mandate_present'] == '0') {

      $invoice_id   = $input['invoice'];
      $novalnet_tid = $novalnetResponse['tid'];
      $user_id      = $novalnetResponse['nc_no'];
      $amount       = $novalnetResponse['amount'];
      $nnconfig = array ('vendor'=> trim($this->_paymentProcessor['user_name']), 'authcode'=>trim($this->_paymentProcessor['password']));
      $nnconfig = serialize($nnconfig);
      $insert_query     = "Insert into novalnet_sepa_orders (invoice_id, novalnet_tid, user_id, amount, status, nnconfig) values ('$invoice_id', $novalnet_tid, '$user_id', $amount,0,'$nnconfig')";
      CRM_Core_DAO::executeQuery($insert_query);
    }

    CRM_Utils_System::redirect($finalURL);
  }

  /**
   * The function gets called when a new order takes place.
   *
   * @param array $privateData contains the CiviCRM related data
   * @param string $component the CiviCRM component
   * @param array $borikaData contains the Merchant related data
   *
   * @return void
   *
   */
  function newOrderNotify(&$input, &$ids, &$objects, $recur = false, $first = false) {
    CRM_Core_Error::debug_var('$input' , $input);
    $contribution = & $objects['contribution'];
    if (strtoupper($contribution->invoice_id) != strtoupper($input['invoice'])) {
      CRM_Core_Error::debug_log_message("Invoice values dont match between database and IPN request");
      echo "Failure: Invoice values dont match between database and IPN request<p>";
      return false;
    }
    $input['amount']     = $input['amount']/100;
    $input['net_amount'] = $input['amount'];
    if ($contribution->total_amount != $input['amount']) {
      CRM_Core_Error::debug_log_message("Amount values dont match between database and IPN request");
      echo "Failure: Amount values dont match between database and IPN request<p>";
      return false;
    }
    $transaction = new CRM_Core_Transaction( );
    $participant = & $objects['participant'];
    $membership = & $objects['membership'];
    $status = $input['status'];
    // check if contribution is already completed, if so we ignore this ipn
    if ($contribution->contribution_status_id == 1) {
      $transaction->commit();
      CRM_Core_Error::debug_log_message("returning since contribution has already been handled");
      echo "Success: Contribution has already been handled<p>";
      return true;
    }
    else {
      if (CRM_Utils_Array::value('event', $ids)) {
        $contribution->trxn_id = $input['trxn_id'];
      }
      elseif (CRM_Utils_Array::value('membership', $ids)) {
        $contribution->trxn_id = $input['trxn_id'];
      }
    }

    $this->completeTransaction($input, $ids, $objects, $transaction);
//print_r($input);exit;
    return true;
  }

  /**
   * The function returns the component(Event/Contribute..)and whether it is Test or not
   *
   * @param array $privateData contains the name-value pairs of transaction related data
   *
   * @return array context of this call (test, component, payment processor id)
   * @static
   */
  static function getContext($privateData) {
    $isTest           = null;
    $component        = $privateData['comp'];
    $contributionID   = $privateData['contribution'];
    $contribution     = & new CRM_Contribute_DAO_Contribution();
    $contribution->id = $contributionID;

    if (!$contribution->find(true)) {
      CRM_Core_Error::debug_log_message("Could not find contribution record: $contributionID");
      echo "Failure: Could not find contribution record for $contributionID<p>";
      exit();
    }
    $isTest = $contribution->is_test;

    $duplicateTransaction = 0;
    if ($contribution->contribution_status_id == 1) {
      //contribution already handled. (some processors do two notifications so this could be valid)
      $duplicateTransaction = 1;
    }
    if ($component == 'contribute') {
      if (!$contribution->contribution_page_id) {
        CRM_Core_Error::debug_log_message("Could not find contribution page for contribution record: $contributionID");
        echo "Failure: Could not find contribution page for contribution record: $contributionID<p>";
        exit();
      }
    }
    else {
      $eventID = $privateData['event'];
      if (!$eventID) {
        CRM_Core_Error::debug_log_message("Could not find event ID");
        echo "Failure: Could not find eventID<p>";
        exit();
      }
      //require_once 'CRM/Event/DAO/Event.php';
      $event = & new CRM_Event_DAO_Event();
      $event->id = $eventID;

      if (!$event->find(true)) {
        CRM_Core_Error::debug_log_message("Could not find event: $eventID");
        echo "Failure: Could not find event: $eventID<p>";
        exit();
      }
    }
    return array(
      $isTest,
      $duplicateTransaction
    );
  }
}

?>
