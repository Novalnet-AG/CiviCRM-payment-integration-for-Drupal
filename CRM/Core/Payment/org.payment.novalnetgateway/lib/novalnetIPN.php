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
 * Script: novalnetIPN.php
 *
 */
require_once 'CRM/Core/Payment/org.payment.novalnetgateway/lib/novalnet.php';
require_once 'CRM/Core/Config.php';
require_once 'CRM/Utils/Hook.php';

class org_novalnet_payment_novalnetipn extends CRM_Core_Payment_BaseIPN {

    private static $_singleton = null;

    /**
     * Constructor
     */
    function __construct($mode, $paymentProcessor) {
        parent::__construct();

        $this->_mode = $mode;
        $this->_paymentProcessor = $paymentProcessor;
    }

    /**
     * singleton function used to manage this object
     *
     * @param $mode string
     * @param $component array
     * @param $paymentProcessor array
     * @return object
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
     *
     * @param $novalnetResponse array
     * @param $paymentType string
     *
     * @return none
     */
    function main($novalnetResponse, $paymentType = NULL) {
        CRM_Core_Error::debug_var('$novalnetResponse', $novalnetResponse);
        $config = CRM_Core_Config::singleton();
        $objects = $ids = $input = array();
        // get the contribution and contact ids from the GET params
        $ids['contact'] = $novalnetResponse['cntid'];
        $ids['contribution'] = $novalnetResponse['contid'];
        $ids['comp'] = $novalnetResponse['module'];
        $input['status'] = $novalnetResponse['status'];
        $input['invoice'] = $novalnetResponse['orderid'];
        $input['amount'] = $novalnetResponse['amount'];
        $input['trxn_id'] = $novalnetResponse['tid'];
        $objects['trxn_id'] = $novalnetResponse['tid'];
        $input['is_test'] = ($novalnetResponse['check'] == 'live') ? '0' : '1';
        $input['component'] = $novalnetResponse['module'];
        $input['qfKey'] = $novalnetResponse['qfKey'];
        $input['payment_instrument_id'] = '';

        $input['payment_instrument_id'] = CRM_Core_Payment_novalnet::getProcessorId($novalnetResponse['processor_name']);

        if ($input['component'] == 'event') {
            $ids['event'] = $novalnetResponse['eid'];
            $ids['participant'] = $novalnetResponse['pid'];
        } else {
            $ids['membership'] = isset($novalnetResponse['mid']) ? $novalnetResponse['mid'] : '';
            $ids['contributionPage'] = isset($novalnetResponse['cntpid']) ? $novalnetResponse['cntpid'] : '';
            $ids['relatedContact'] = isset($novalnetResponse['rcid']) ? $novalnetResponse['rcid'] : '';
        }

        $test_mode = ((isset($novalnetResponse['test_mode']) && $novalnetResponse['test_mode'] == 1) || (isset($novalnetResponse['payment_mode']) && $novalnetResponse['payment_mode'] == 1)) ? ts('Test order') : '';
        
        $comments = CRM_Core_Payment_novalnet::getTransactionComments($novalnetResponse, $test_mode, $paymentType);

        $payment = array('nn_comments' => $comments);
        $template = CRM_Core_Smarty::singleton();
        $template->assign('nn_payment', $payment);
        $transcation = $novalnetResponse['tid'];
        $input['trxn_id'] = $novalnetResponse['tid'];
        if (!$this->validateData($input, $ids, $objects, '', $this->_paymentProcessor['id'])) {
            return false;
        }
        if (isset($ids['membership']) && is_array($ids['membership'])) $ids['membership'] = $ids['membership']['0'];
        list ($mode, $duplicateTransaction) = self::getContext($ids);
        $mode = $mode ? 'test' : 'live';
        $recur = (isset($novalnetResponse['is_recur']) && $novalnetResponse['is_recur'] == 1) ? true : (isset($novalnetResponse['subs_id'])) ? true : false;
        if ($recur === false)
            $recur = (isset($novalnetResponse['auto_renew']) && $novalnetResponse['auto_renew'] == 1) ? true : false;

        $this->newOrderNotify($input, $ids, $objects, $recur, false);

        
		$amount = (in_array($paymentType, array('novalnet_invoice', 'novalnet_prepayment')) || $novalnetResponse['tid_status'] != 100) ?  0 : $novalnetResponse['amount'];
        if (($paymentType != 'novalnet_paypal' && $novalnetResponse['status'] != 100) || ($paymentType == 'novalnet_paypal' && ($novalnetResponse['status'] != 90 && $novalnetResponse['status'] != 100 ))) {
			if ($input['component'] == "event") {
                $finalURL = CRM_Utils_System::url('civicrm/event/confirm', "reset=1&cc=fail&participantId={$ids['participant']}", false, null, false);
            } elseif ($input['component'] == "contribute") {
                $finalURL = CRM_Utils_System::url('civicrm/contribute/transact', "_qf_Main_display=true&cancel=1&qfKey={$novalnetResponse['qfKey']}", FALSE, NULL, FALSE);
            }
            CRM_Core_Session::setStatus(ts($novalnetResponse['status_desc']), 'error');
        } else {
            if (in_array($paymentType, array('novalnet_invoice', 'novalnet_sepa', 'novalnet_cc', 'novalnet_paypal'))) {
                if ($paymentType == 'novalnet_paypal' && $novalnetResponse['tid_status'] == '90'){
					$statusKey = 'novalnet_paypal_pending_status';
				}elseif(in_array($novalnetResponse['tid_status'], array('85', '91' , '98', '99'))) {
					$statusKey = 'novalnet_onhold_order_complete';
				}else{
					$statusKey = ($novalnetResponse['tid_status'] == '75' && in_array($paymentType, array('novalnet_invoice', 'novalnet_sepa'))) ?  $paymentType . '_guarantee_status' : $paymentType . '_cont_status';
				}
				if (!empty($novalnetResponse['payment_type']) && $novalnetResponse['payment_type'] == 'GUARANTEED_INVOICE' && $novalnetResponse['tid_status'] == '100') {
					$statusKey = 'novalnet_invoice_cont_cb_status';
				}
				$updateData = array('contribution_status_id' => Civi::settings()->get($statusKey));

                CRM_Core_Payment_novalnet::contributionUpdate($updateData, $novalnetResponse['order_no']);
            } else {
                $statusKey =  $paymentType . '_cont_status';
                $updateData = array('contribution_status_id' => Civi::settings()->get($statusKey));
                CRM_Core_Payment_novalnet::contributionUpdate($updateData, $novalnetResponse['order_no']);
            }
            if ($input['component'] == "event") {
                $finalURL = CRM_Utils_System::url('civicrm/event/register', "_qf_ThankYou_display=1&qfKey={$novalnetResponse['qfKey']}", false, null, false);
            } elseif ($input['component'] == "contribute") {
                $finalURL = CRM_Utils_System::url('civicrm/contribute/transact', "_qf_ThankYou_display=1&qfKey={$novalnetResponse['qfKey']}", FALSE, NULL, FALSE);
            }
            CRM_Core_Session::setStatus($comments, ts('Novalnet transaction details'), 'success');
        }

        CRM_Core_Payment_novalnet::updateCallbackTable($novalnetResponse, $amount, $paymentType);
        CRM_Core_Payment_novalnet::updateNovalnetTransactionDetail($novalnetResponse, $paymentType);
        if ($recur) {
            if ($novalnetResponse['installments'] != 1) {
                $subs_id = $novalnetResponse['subs_id'];
                $nextcycle_date = !empty($novalnetResponse['next_subs_cycle']) ? $novalnetResponse['next_subs_cycle'] : $novalnetResponse['paid_until'];
            } else {
                $subs_id = $novalnetResponse['tid'];
                $nextcycle_date = CRM_Utils_Date::isoToMysql(date('Y-m-d'));
            }
            $updateData = array(
                'trxn_id' => $novalnetResponse['tid'],
                'next_sched_contribution_date' => CRM_Utils_Date::isoToMysql($nextcycle_date),
                'processor_id' => $subs_id,
                'auto_renew' => 1,
                'payment_instrument_id' => $input['payment_instrument_id'],
            );
            CRM_Core_Payment_novalnet::contributionrecurUpdate($updateData, $novalnetResponse['order_no']);
            $sub_data = array(
                'tid' => trim($novalnetResponse['tid']),
                'product' => $novalnetResponse['product'],
                'tariff' => $novalnetResponse['tariff'],
                'vendor' => $novalnetResponse['vendor'],
                'authcode' => $novalnetResponse['auth_code'],
                'key' => $novalnetResponse['key'],
            );

            if ($paymentType != 'novalnet_invoice' && $paymentType != 'novalnet_prepayment') {
                if ($novalnetResponse['installments'] == 1) {
                    $updateData = array('contribution_status_id' => 1);
                    CRM_Core_Payment_novalnet::contributionrecurUpdate($updateData, $novalnetResponse['order_no']);
                } else {
                    $updateData = array('contribution_status_id' => 5);
                    CRM_Core_Payment_novalnet::contributionrecurUpdate($updateData, $novalnetResponse['order_no']);
                }
            } else {
                $updateData = array('contribution_status_id' => 2);
                CRM_Core_Payment_novalnet::contributionrecurUpdate($updateData, $novalnetResponse['order_no']);
            }
            $order_id = $novalnetResponse['orderid'];
            $frequency_unit = $novalnetResponse['frequency_unit'];
            $frequency_interval = $novalnetResponse['frequency_interval'];
            $installments = '1';
            $nnconfig = serialize($sub_data);
            $sub_query = "INSERT INTO novalnet_subscription_details (novalnet_tid, nnconfig, invoice_id, frequency_unit, frequency_interval, installments, paid_upto) values (" . $sub_data['tid'] . ", '$nnconfig', '$order_id', '$frequency_unit', $frequency_interval, '$installments', '$nextcycle_date')";
            CRM_Core_DAO::executeQuery($sub_query);
        }
        $contribution = new CRM_Contribute_BAO_Contribution();
        $contribution->invoice_id = $novalnetResponse['orderid'];
        if ($contribution->find(TRUE)) {
            $noteParams = array(
                'entity_table' => 'civicrm_contribution',
                'entity_id' => $contribution->id,
                'note' => $comments,
                'contact_id' => $contribution->contact_id,
                'modified_date' => CRM_Utils_Date::isoToMysql(date('Y-m-d'))
            );
            CRM_Core_BAO_Note::add($noteParams);
        }
        CRM_Utils_System::redirect($finalURL);
    }

    /**
     * The function gets called when a new order takes place.
     *
     * @param $input array
     * @param $ids array
     * @param $objects array
     * @param $recur boolean
     * @param $first boolean
     *
     * @return void
     *
     */
    function newOrderNotify(&$input, &$ids, &$objects, $recur = false, $first = false) {
        CRM_Core_Error::debug_var('$input', $input);
        $contribution = &$objects['contribution'];

        if (strtoupper($contribution->invoice_id) != strtoupper($input['invoice'])) {
            CRM_Core_Error::debug_log_message("Invoice values dont match between database and IPN request");
            return false;
        }

        $input['amount'] = $input['amount'] / 100;
        $input['net_amount'] = $input['amount'];
        if ($contribution->total_amount != $input['amount']) {
            CRM_Core_Error::debug_log_message("Amount values dont match between database and IPN request");
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
        } else {
            if (CRM_Utils_Array::value('event', $ids)) {
                $contribution->trxn_id = $input['trxn_id'];
            } elseif (CRM_Utils_Array::value('membership', $ids)) {
                $contribution->trxn_id = $input['trxn_id'];
            }
        }

        $this->completeTransaction($input, $ids, $objects, $transaction, $recur);

        return true;
    }

    /**
     * The function returns the component(Event/Contribute..)and whether it is Test or not
     *
     * @param $privateData array
     * @return array
     */
    static function getContext($privateData) {
        $isTest = null;
        $component = $privateData['comp'];
        $contributionID = $privateData['contribution'];
        $contribution = new CRM_Contribute_DAO_Contribution();
        $contribution->id = $contributionID;

        if (!$contribution->find(true)) {
            CRM_Core_Error::debug_log_message("Could not find contribution record: $contributionID");
            echo "Failure: Could not find contribution record for $contributionID<p>";
            exit;
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
                exit;
            }
        } else {
            $eventID = $privateData['event'];
            if (!$eventID) {
                CRM_Core_Error::debug_log_message("Could not find event ID");
                echo "Failure: Could not find eventID<p>";
                exit;
            }
            $event = new CRM_Event_DAO_Event();
            $event->id = $eventID;

            if (!$event->find(true)) {
                CRM_Core_Error::debug_log_message("Could not find event: $eventID");
                echo "Failure: Could not find event: $eventID<p>";
                exit;
            }
        }
        return array(
            $isTest,
            $duplicateTransaction
        );
    }

}
?>
