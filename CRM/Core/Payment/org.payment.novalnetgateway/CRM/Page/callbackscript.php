<?php
/**
 * This script is used for real time capturing of
 * parameters passed from Novalnet AG after Payment
 * processing of customers.
 *
 * Copyright (c) Novalnet AG
 *
 * This script is only free to the use for Merchants of
 * Novalnet AG
 *
 * If you have found this script useful a small
 * recommendation as well as a comment on merchant form
 * would be greatly appreciated.
 *
 * Version : 3.0.0
 *
 * Please contact sales@novalnet.de for enquiry or Info
 *
 * Script: callbackscript.php
 *
 */
//Include shop files
require_once 'CRM/Core/DAO.php';
require_once 'Mail/mime.php';
$aryCaptureParams   = array_map('trim', $_REQUEST); # Assign Callback parameters
$aryCaptureParams   = array_map('check_plain', $_REQUEST); # Assign Callback parameters

global $processTestMode, $processDebugMode;

$nn_callback_testmode  = Civi::settings()->get('nn_callback_testmode');
$nn_callback_debug  = Civi::settings()->get('nn_callback_debug');
$processTestMode = isset($nn_callback_testmode) ? $nn_callback_testmode : FALSE;
$processDebugMode = isset($nn_callback_debug) ? $nn_callback_debug : FALSE;

$nnVendorScript = new NovalnetVendorScript($aryCaptureParams);
list($nntransHistory, $contribution) = $nnVendorScript->getOrderReference();
$nnCaptureParams = $nnVendorScript->getCaptureParams();

if(!empty($nntransHistory))
{
    $order_id = $nntransHistory['invoice_id']; # Given shop invoice ID
    $payment_level = $nnVendorScript->getPaymentTypeLevel();
    if ($payment_level == 2) #CreditEntry payment and Collections available
    {
        //Credit entry of INVOICE or PREPAYMENT
        if ($nnCaptureParams['payment_type'] == 'INVOICE_CREDIT')
        {
            //Subscription renewal of INVOICE_CREDIT
            if(isset($nnCaptureParams['subs_billing']) && $nnCaptureParams['subs_billing'] == 1)
            {
                $contributionrec = new CRM_Contribute_BAO_ContributionRecur();
                $contributionrec->invoice_id = $contribution->invoice_id;
                $contributionrec->find(TRUE);
                $queryParams = array(
                    1 => array($nnCaptureParams['shop_tid'], 'Integer'),
                );
                $novalnet_details = CRM_Core_DAO::executeQuery("SELECT id, installments from novalnet_subscription_details WHERE  novalnet_tid=%1", $queryParams);
                $novalnet_details->fetch();

                $nnconfig = CRM_Core_Payment_novalnet::getSubscriptionConfigDetails(array('tid' => $nnCaptureParams['shop_tid']));
                $nnconfig['auth_code'] = isset($nnconfig['authcode']) ? $nnconfig['authcode'] : '';
                $nextcycle_date = !empty($nnCaptureParams['next_subs_cycle']) ? $nnCaptureParams['next_subs_cycle'] : $nnCaptureParams['paid_until'];
                $comments = '<br>Novalnet Callback Script executed successfully for the Subscription TID: ' .$nnCaptureParams['shop_tid'] . ' with amount:' . sprintf('%.2f',($nnCaptureParams['amount']/100)).' '.$nnCaptureParams['currency'] . ' on ' . date('Y-m-d H:i:s') . '. Please refer PAID transaction in our Novalnet Merchant Administration with the TID:' . $nnCaptureParams['tid'];

                if ($contributionrec->installments == 0 || $novalnet_details->installments < $contributionrec->installments) {
                    $new_installments = isset($novalnet_details->installments)?$novalnet_details->installments + 1 : 1;
                    if (is_null($contributionrec->installments) ||  $new_installments < $contributionrec->installments) {
                        $comments .=  "<br> Next payment Date:" . $nextcycle_date;
                    }
                    $status = 5;
                    if (!is_null($contributionrec->installments) && $new_installments >= $contributionrec->installments) {
                        $updateData = array('end_date' => CRM_Utils_Date::isoToMysql(date('YmdHis')));
                        CRM_Core_Payment_novalnet::contributionrecurUpdate($updateData, $contributionrec->invoice_id);
                        $updateData = array('contribution_status_id' => 1);
                        CRM_Core_Payment_novalnet::contributionUpdate($updateData, $contribution->invoice_id);
                        $status = 1;
                        $result = $nnVendorScript->sendSubscriptionStopRequest(array(
                                'tid' => $nnCaptureParams['shop_tid'],
                                'cancel_reason' => 'other',
                                'vendor' => isset($nnconfig['vendor']) ? $nnconfig['vendor'] : '',
                                'product' => isset($nnconfig['product']) ? $nnconfig['product'] : '',
                                'key' => isset($nnconfig['key']) ? $nnconfig['key'] : '',
                                'tariff' => isset($nnconfig['tariff'])?$nnconfig['tariff']:'',
                                'auth_code' => isset($nnconfig['authcode'])?$nnconfig['authcode']:'',
                                'cancel_sub' => 1
                            ));
                        if (!$result)
                            $comments .= ts("<br>Recurring contribution cycles was completed. But Transaction was not completed in Novalnet.");
                    }
                    $updateData = array(
                        'modified_date' => date('Ymd'),
                        'next_sched_contribution_date' => CRM_Utils_Date::isoToMysql($nextcycle_date),
                        'contribution_status_id' => $status,
                    );

                    CRM_Core_Payment_novalnet::contributionrecurUpdate($updateData, $contributionrec->invoice_id);

                    if ($new_installments >= $contributionrec->installments) {
                        CRM_Contribute_BAO_ContributionPage::recurringNotify(CRM_Core_Payment::RECURRING_PAYMENT_END, $contribution->contact_id, $contribution->contribution_page_id, $contributionrec);
                    }
                    $nnVendorScript->setNote($comments, $contribution);
                    $nnVendorScript->sendEmail($comments);
                    $nnVendorScript->debugError($comments);
                } else {
                    $nnVendorScript->debugError('Novalnet callback received. Subscription got completed already. Refer Invoice ID :' . $contribution->invoice_id);
                }
            }
            else
            {
                if ($nntransHistory['order_paid_amount'] < $nntransHistory['order_total_amount']) {
                    $comments = '<br>Novalnet Callback Script executed successfully for the TID: ' .$nnCaptureParams['shop_tid'] . ' with amount:' . sprintf('%.2f',($nnCaptureParams['amount']/100)).' '.$nnCaptureParams['currency'] . ' on ' . date('Y-m-d H:i:s') . '. Please refer PAID transaction in our Novalnet Merchant Administration with the TID:' . $nnCaptureParams['tid'];

                    $totalPaid = $nntransHistory['order_paid_amount'] + $nnCaptureParams['amount'];
                    if ($nntransHistory['order_total_amount'] <= $totalPaid) {
                        #Full Payment paid
                        if ($totalPaid > $nntransHistory['order_total_amount']) {
                            $comments .= '. Paid amount is greater than Order amount.';
                        }
                        $sKey = $nntransHistory['payment_key'] . '_cont_cb_status';
                        $updateData = array('contribution_status_id' => Civi::settings()->get($sKey));
                        CRM_Core_Payment_novalnet::contributionUpdate($updateData, $contribution->invoice_id);
                        $contributionrec = new CRM_Contribute_BAO_ContributionRecur();
                        $contributionrec->invoice_id = $contribution->invoice_id;
                        if ($contributionrec->find(TRUE)) {
                            $updateData = array('contribution_status_id' => 5);
                            CRM_Core_Payment_novalnet::contributionrecurUpdate($updateData, $contributionrec->invoice_id);
                        }
                    }
                    $nnVendorScript->setPaidTotal($aryCaptureParams, $contribution, $nnCaptureParams['shop_tid']);
                    $nnVendorScript->setNote($comments, $contribution);
                    $nnVendorScript->sendEmail($comments);
                    $nnVendorScript->debugError($comments);
                }
                $nnVendorScript->debugError('Novalnet callback received. Order got completed already. Refer Invoice ID :' . $contribution->invoice_id);
            }
        }

        //Subscription renewal of level 0 payments
        if (isset($nnCaptureParams['subs_billing']) && $nnCaptureParams['subs_billing'] == 1)
        {
          #### Step1: THE SUBSCRIPTION IS RENEWED, PAYMENT IS MADE, SO JUST CREATE A NEW ORDER HERE WITHOUT A PAYMENT PROCESS AND SET THE ORDER STATUS AS PAID ####

          #### Step2: THIS IS OPTIONAL: UPDATE THE BOOKING REFERENCE AT NOVALNET WITH YOUR ORDER_NO BY CALLING NOVALNET GATEWAY, IF U WANT THE USER TO USE ORDER_NO AS PAYMENT REFERENCE ###

          #### Step3: ADJUST THE NEW ORDER CONFIRMATION EMAIL TO INFORM THE USER THAT THIS ORDER IS MADE ON SUBSCRIPTION RENEWAL ###
        }
        $error = 'Payment type ( '.$nnCaptureParams['payment_type'].' ) is not applicable for this process!';
        $nnVendorScript->debugError($error);
    }
    elseif($payment_level== 1) //level 1 payments - Type of Chargebacks
    {
        ### DO THE STEPS TO UPDATE THE STATUS OF THE ORDER OR THE USER AND NOTE THAT THE PAYMENT WAS RECLAIMED FROM USER ###

		$is_bookback_chargeback = in_array($nnCaptureParams['payment_type'], array('CREDITCARD_BOOKBACK', 'PAYPAL_BOOKBACK') ) ? 'Refund/Bookback ' : 'Chargeback ';
        $comments ='<br>Novalnet callback received. '.$is_bookback_chargeback.' executed successfully for the TID:'.$nnCaptureParams['tid_payment'].' with the amount:'. sprintf('%.2f',($nnCaptureParams['amount']/100)).' '.$nnCaptureParams['currency']  .' on ' . date("Y-m-d H:i:s"). '. The subsequent TID: '.$nnCaptureParams['tid'];

        $nnVendorScript->setNote($comments, $contribution);
        $nnVendorScript->sendEmail($comments);
        $nnVendorScript->debugError($comments);
    }
    elseif($payment_level === 0) //level 0 payments - Type of payment
    {
        if (isset($nnCaptureParams['subs_billing']) && $nnCaptureParams['subs_billing'] == 1)
        {
			if ($nnCaptureParams['status'] == 100) {
				$contributionrec = new CRM_Contribute_BAO_ContributionRecur();
				$contributionrec->invoice_id = $contribution->invoice_id;
				$contributionrec->find(TRUE);
				$queryParams = array(
					1 => array($nnCaptureParams['shop_tid'], 'Integer'),
				);
				$novalnet_details = CRM_Core_DAO::executeQuery("SELECT id, installments from novalnet_subscription_details WHERE  novalnet_tid=%1", $queryParams);
				$novalnet_details->fetch();

				$nnconfig = CRM_Core_Payment_novalnet::getSubscriptionConfigDetails(array('tid'=> $nnCaptureParams['shop_tid']));
				$nnconfig['auth_code'] = isset($nnconfig['authcode']) ? $nnconfig['authcode'] : '';
				$nextcycle_date = !empty($nnCaptureParams['next_subs_cycle']) ? $nnCaptureParams['next_subs_cycle'] : $nnCaptureParams['paid_until'];
				$comments = '<br>Novalnet Callback Script executed successfully for the Subscription TID: ' .$nnCaptureParams['shop_tid'] . ' with amount:' . sprintf('%.2f',($nnCaptureParams['amount']/100)).' '.$nnCaptureParams['currency'] . ' on ' . date('Y-m-d H:i:s') . '. Please refer PAID transaction in our Novalnet Merchant Administration with the TID:' . $nnCaptureParams['tid'];

				if ($contributionrec->installments == 0 || $novalnet_details->installments < $contributionrec->installments) {
					$new_installments = isset($novalnet_details->installments)?$novalnet_details->installments + 1 : 1;
					if (is_null($contributionrec->installments) || $new_installments < $contributionrec->installments) {
						$comments .=  "<br> Next payment Date:" . $nextcycle_date;
					}
					$queryParams = array(
						1 => array($nextcycle_date, 'String'),
						2 => array($new_installments, 'Integer'),
						3 => array($novalnet_details->id, 'Integer'),
					);
					 CRM_Core_DAO::executeQuery("UPDATE novalnet_subscription_details SET paid_upto=%1,installments=%2 WHERE id =%3", $queryParams);
					$params = (array) $contribution;
					$contribution_new = new CRM_Contribute_BAO_Contribution();
					$params['invoice_id'] = $nnVendorScript->checkDupe(md5(uniqid(rand(), TRUE)));
					$params['trxn_id'] =  $nnCaptureParams['tid'];
					$paymentKey = $nnVendorScript->getPaymentKey($params['payment_instrument_id']);
					$key = $paymentKey.'_cont_status';
					$params['contribution_status_id'] = Civi::settings()->get($key);
					$params['id'] = '';
					$params['receive_date'] = $params['receipt_date'] = date('YmdHis');
					$contribution_new->copyValues($params);
					$contribution_new->add($params);
					$status = 5;
					if (!is_null($contributionrec->installments) && $new_installments >= $contributionrec->installments) {
						$updateData = array('end_date' => date('YmdHis'));
						CRM_Core_Payment_novalnet::contributionrecurUpdate($updateData, $contributionrec->invoice_id);
						$status = 1;
						$result = $nnVendorScript->sendSubscriptionStopRequest(array(
							'tid' => $nnCaptureParams['shop_tid'],
							'cancel_reason' => 'other',
							'vendor' => isset($nnconfig['vendor'])?$nnconfig['vendor']:'',
							'product' => isset($nnconfig['product'])?$nnconfig['product']:'',
							'key' => isset($nnconfig['key'])?$nnconfig['key']:'',
							'tariff' => isset($nnconfig['tariff'])?$nnconfig['tariff']:'',
							'auth_code' => isset($nnconfig['authcode'])?$nnconfig['authcode']:'',
							'cancel_sub' => 1
						));
						if (!$result)
							$comments .= "<br>Recurring contribution cycles was completed. But Transaction was not completed in Novalnet.";
					}

					$newcontribution = new CRM_Contribute_BAO_Contribution();
					$newcontribution->trxn_id = $nnCaptureParams['tid'];
					if ($newcontribution->find(TRUE)) {

						$newordercomments = $nnVendorScript->buildNewOrderComments($nnCaptureParams, $newcontribution, $nextcycle_date);
						$nnVendorScript->setNote($newordercomments, $newcontribution);
					}
					$updateData = array(
						'modified_date' => date('Ymd'),
						'next_sched_contribution_date' => CRM_Utils_Date::isoToMysql($nextcycle_date),
						'contribution_status_id' => $status,
					);
					CRM_Core_Payment_novalnet::contributionrecurUpdate($updateData, $contributionrec->invoice_id);

					if ($new_installments >= $contributionrec->installments && $nnCaptureParams['payment_type'] != 'INVOICE_CREDIT') {
					  CRM_Contribute_BAO_ContributionPage::recurringNotify(CRM_Core_Payment::RECURRING_PAYMENT_END, $contribution->contact_id, $contribution->contribution_page_id, $contributionrec);
					}
					$nnVendorScript->setNote($comments, $contribution);
					$nnVendorScript->sendEmail($comments);
					$nnVendorScript->debugError($comments);
				} else {
					$nnVendorScript->debugError('Novalnet callback received. Subscription got completed already. Refer Invoice ID :' .   $contribution->invoice_id);
				}
			}
        }
        elseif($nnCaptureParams['payment_type'] == 'PAYPAL')
        {
            if ($nntransHistory['order_paid_amount'] < $nntransHistory['order_total_amount']) {
                ### PayPal payment success ###
                $comments = '<br>Novalnet Callback Script executed successfully for the TID: ' .$nnCaptureParams['shop_tid'] . ' with amount:' . sprintf('%.2f',($nnCaptureParams['amount']/100)).' '.$nnCaptureParams['currency'] . ' on ' . date('Y-m-d H:i:s') . ' Please refer PAID transaction in our Novalnet Merchant Administration with the TID:' . $nnCaptureParams['tid'];
                $skey = $nntransHistory['payment_key'] . '_pending_status';
                $updateData = array('contribution_status_id' => Civi::settings()->get($skey));
                CRM_Core_Payment_novalnet::contributionUpdate($updateData, $contribution->invoice_id);
                $contributionrec = new CRM_Contribute_BAO_ContributionRecur();
                $contributionrec->invoice_id = $contribution->invoice_id;
                if ($contributionrec->find(TRUE)) {
                    $updateData = array('contribution_status_id' => 5);
                    CRM_Core_Payment_novalnet::contributionrecurUpdate($updateData, $contributionrec->invoice_id);
                }
                $nnVendorScript->setPaidTotal($aryCaptureParams, $contribution, $nnCaptureParams['shop_tid']);
                $nnVendorScript->setNote($comments, $contribution);
                $nnVendorScript->sendEmail($comments);
                $nnVendorScript->debugError($comments);
            }
            $nnVendorScript->debugError('Novalnet Callbackscript received. Order already Paid');
        } else {
            $error = 'Novalnet Callbackscript received. Payment type ( '.$nnCaptureParams['payment_type'].' ) is not applicable for this process!';
            $nnVendorScript->debugError($error);
        }
    }
    //Cancellation of a Subscription
    if ($nnCaptureParams['payment_type'] == 'SUBSCRIPTION_STOP' || (isset($nnCaptureParams['subs_billing']) && $nnCaptureParams['subs_billing'] == 1 && $nnCaptureParams['status'] != 100))
    {
		$reason = isset($nnCaptureParams['termination_reason']) ? $nnCaptureParams['termination_reason'] : $nnCaptureParams['status_message'];
        $comments = '<br>Novalnet Callbackscript received. Subscription has been stopped for the TID: '.$nnCaptureParams['shop_tid']. ' on ' . date('Y-m-d H:i:s');
        $comments .= '<br>Reason for Cancellation: '. $reason;
        $contributionrec = new CRM_Contribute_BAO_ContributionRecur();
        $contributionrec->invoice_id = $contribution->invoice_id;
        if ($contributionrec->find(TRUE)) {
            if ($contributionrec->contribution_status_id )
              $updateData = array('contribution_status_id' => Civi::settings()->get('nn_subs_cancel_status'));
              CRM_Core_Payment_novalnet::contributionrecurUpdate($updateData, $contributionrec->invoice_id);
        }
        $nnVendorScript->setNote($comments, $contribution);
        $nnVendorScript->sendEmail($comments);
        $nnVendorScript->debugError($comments);
    }
    /**
     * Implement rest all other payment type process here
     */
    $error = 'Novalnet Callbackscript received. Payment type ( '.$nnCaptureParams['payment_type'].' ) is not applicable for this process!';
    $nnVendorScript->debugError($error);
} else {
    $nnVendorScript->debugError('Order Reference not exist!');
}

//class to handle entire process of callback
class NovalnetVendorScript {
    /** @Array Type of payment available - Level : 0 */
    protected $aryPayments = array('CREDITCARD','INVOICE_START','DIRECT_DEBIT_SEPA','GUARANTEED_INVOICE_START','PAYPAL','ONLINE_TRANSFER','IDEAL','EPS','PAYSAFECARD');

    /** @Array Type of Chargebacks available - Level : 1 */
    protected $aryChargebacks = array('RETURN_DEBIT_SEPA','CREDITCARD_BOOKBACK','CREDITCARD_CHARGEBACK','REFUND_BY_BANK_TRANSFER_EU');

    /** @Array Type of CreditEntry payment and Collections available - Level : 2 */
    protected $aryCollection = array('INVOICE_CREDIT','GUARANTEED_INVOICE_CREDIT','CREDIT_ENTRY_CREDITCARD','CREDIT_ENTRY_SEPA','DEBT_COLLECTION_SEPA','DEBT_COLLECTION_CREDITCARD');

    protected $arySubscription = array('SUBSCRIPTION_STOP');

    protected $aryPaymentGroups = array(
		'100' => array('INVOICE_START',  'INVOICE_CREDIT',  'SUBSCRIPTION_STOP'),
		'101' => array('INVOICE_START','INVOICE_CREDIT', 'SUBSCRIPTION_STOP'),
        '102' => array('CREDITCARD', 'CREDITCARD_BOOKBACK', 'CREDITCARD_CHARGEBACK', 'CREDIT_ENTRY_CREDITCARD','SUBSCRIPTION_STOP','DEBT_COLLECTION_CREDITCARD'),
        '103' => array('IDEAL'),
        '104' => array('ONLINE_TRANSFER'),
        '105' => array('PAYPAL', 'PAYPAL_BOOKBACK', 'SUBSCRIPTION_STOP'),
        '106' => array('DIRECT_DEBIT_SEPA', 'RETURN_DEBIT_SEPA','SUBSCRIPTION_STOP','DEBT_COLLECTION_SEPA','CREDIT_ENTRY_SEPA'),
        '107' => array('EPS'),
        '108' => array('GIROPAY'),
    );
    /** @Array Callback Capture parameters */
    protected $arycaptureparams = array();
    protected $paramsRequired = array();
    /** @IP-ADDRESS Novalnet IP, is a fixed value, DO NOT CHANGE!!!!! */
    protected $ipAllowed = array('195.143.189.210', '195.143.189.214');

    function __construct($aryCapture = array()) {
        global $processTestMode;

        //Validate Authenticated IP
        if (!in_array(CRM_Utils_System::ipAddress(), $this->ipAllowed) && $processTestMode == false) {
            echo (ts('Novalnet callback received. Unauthorised access from the IP '). CRM_Utils_System::ipAddress()); exit;
        }
        if (empty($aryCapture)) {
            self::debugError(ts('Novalnet callback received. No params passed over!'));
        }
        if (isset($aryCapture['vendor_activation']) && $aryCapture['vendor_activation'] == 1) {
            $this->paramsRequired = array('vendor_id', 'vendor_authcode', 'product_id', 'aff_id', 'aff_authcode', 'aff_accesskey');
            $this->arycaptureparams = self::validateAffiliateCaptureParams($aryCapture);
        } else {
            $this->paramsRequired = array('vendor_id', 'tid', 'payment_type', 'status','tid_status');
            if (isset($aryCapture['subs_billing']) && $aryCapture['subs_billing'] == 1) {
                array_push($this->paramsRequired, 'signup_tid');
            } elseif (in_array($aryCapture['payment_type'], array_merge($this->aryChargebacks, array('INVOICE_CREDIT')))) {
                array_push($this->paramsRequired, 'tid_payment');
            }
            if($aryCapture['payment_type'] != 'SUBSCRIPTION_STOP')
            {
				array_push($this->paramsRequired, 'amount');
			}
            $this->arycaptureparams = self::validateCaptureParams($aryCapture);
        }
    }


    /**
     * Perform parameter validation process
     * Set Empty value if not exist in aryCapture
     *
     * @return Array
     */
    function validateCaptureParams($aryCapture) {

        foreach ($this->paramsRequired as $k => $v) {
            if (empty($aryCapture[$v])) {
                self::debugError(ts('Required param (') . $v . ts(') missing!'));
            }
        }
        if (!in_array($aryCapture['payment_type'], array_merge($this->aryPayments, $this->aryChargebacks, $this->aryCollection, $this->arySubscription))) {
            self::debugError(ts('Novalnet callback received. Payment type (') . $aryCapture['payment_type'] . t(') is mismatched!'));
        }
        if (isset($aryCapture['status']) && !is_numeric($aryCapture['status']))  {
            self::debugError(ts('Novalnet callback received. Status (') . $aryCapture['status'] . ts(') is not valid'));
        }
        if ((isset($aryCapture['signup_tid']) && !empty($aryCapture['signup_tid']))
        || (isset($aryCapture['subs_billing']) && $aryCapture['subs_billing'] == 1)) {
            if (!preg_match('/^\d{17}$/', $aryCapture['signup_tid'])) {
                self::debugError(ts('Novalnet callback received. Invalid TID [') . $aryCapture['signup_tid'] . ts('] for Order.'));
            }
            if ($aryCapture['subs_billing'] == 1 && (!preg_match('/^\d{17}$/', $aryCapture['tid']))) {
                self::debugError(ts('Novalnet callback received. TID [') . $aryCapture['tid'] . ts('] is not valid.'));
            }
        } else {
            if (in_array($aryCapture['payment_type'], array_merge($this->aryChargebacks, array('INVOICE_CREDIT')))) {
                if (!preg_match('/^\d{17}$/', $aryCapture['tid_payment'])) {
                    self::debugError(ts('Novalnet callback received. Invalid TID [') . $aryCapture['tid_payment'] . ts('] for Order.'));
                }
            }
            if (!preg_match('/^\d{17}$/', $aryCapture['tid'])) {
                self::debugError(ts('Novalnet callback received. TID [') . $aryCapture['tid'] . ts('] is not valid.'));
            }
        }
        if ( $aryCapture['payment_type'] != 'SUBSCRIPTION_STOP' && ( !$aryCapture['amount']|| !is_numeric($aryCapture['amount']) || $aryCapture['amount'] < 0)) {
            self::debugError(ts('Novalnet callback received. The requested amount (') . $aryCapture['amount'] . ts(') is not valid'));
        }
        //Assign Original Transaction ID
        if (isset($aryCapture['signup_tid']) && $aryCapture['signup_tid'] != '') { //subscription
            $aryCapture['shop_tid'] = $aryCapture['signup_tid'];
        }
        elseif(in_array($aryCapture['payment_type'], array_merge($this->aryChargebacks, array('INVOICE_CREDIT')))) {
            $aryCapture['shop_tid'] = $aryCapture['tid_payment'];
        }
        elseif(isset($aryCapture['tid']) && $aryCapture['tid'] != '') {
            $aryCapture['shop_tid'] = $aryCapture['tid'];
        }

        return $aryCapture;
    }

    /**
     * Perform parameter validation process for affiliate
     *
     * @param $aryCapture
     * @return none
     */
    function validateAffiliateCaptureParams($aryCapture) {
        $error = '';
        foreach ($this->paramsRequired as $k => $v) {
            if (empty($aryCapture[$v])) {
              $error .= ts('Required param (') . $v . ts(') missing!');
            }
        }
        if ($error)
            self::debugError($error);
        self::insertAffiliateDetails($aryCapture);
    }

    /**
     * insert the affiliate details
     *
     * @param $aryCapture array
     * @return none
     */
    function insertAffiliateDetails($aryCapture) {
        global $processTestMode;

        $aryCapture = array_map('trim', $aryCapture);
        $params = array(
            '1' => $aryCapture['vendor_id'],
            '2' => $aryCapture['vendor_authcode'],
            '3' => $aryCapture['product_id'],
            '4' => $aryCapture['product_url'],
            '5' => (isset($aryCapture['activation_date']) && $aryCapture['activation_date'] ? date('Y-m-d H:i:s', strtotime($aryCapture['activation_date'])):''),
            '6' => $aryCapture['aff_id'],
            '7' => $aryCapture['aff_authcode'],
            '8' => $aryCapture['aff_accesskey']
        );
        CRM_Core_DAO::executeQuery("INSERT INTO novalnet_affiliates (vendor_id, vendor_authcode, product_id, product_url, activation_date,aff_id,aff_authcode,aff_accesskey) VALUES (%1, %2, %3, %4, %5, %6, %7, %8)", $params);
        self::debugError(t('Novalnet Callback received. Affiliate details has been added successfully.'));
      }

    /**
     * load the contribution reference
     *
     * @param none
     * @return array
     */
    function getOrderReference(){
        $tid = $this->arycaptureparams['shop_tid'];
        $order_no = isset($this->arycaptureparams['order_no']) ? $this->arycaptureparams['order_no'] : '';

        $contribution = self::fetchContributionDetails($tid, $order_no);
        $civi_orderId = $contribution->invoice_id;
        $civi_paymentId = $contribution->payment_instrument_id;

        if (!empty($contribution->trxn_id) && $contribution->trxn_id != $tid )
            self::debugError(ts("Novalnet callback received. TID is not valid"));

        if (!empty($order_no) && $order_no != $civi_orderId)
            self::debugError(ts("Novalnet callback received. Order Number Not Valid"));

        if (!in_array($this->arycaptureparams['payment_type'], $this->aryPaymentGroups[$civi_paymentId]))
            self::debugError(ts("Novalnet callback received.Payment type [") . $this->arycaptureparams['payment_type'] . ts("] is mismatched!"));

        if (trim($contribution->trxn_id) == '') {
            $updateData = array('trxn_id' => $tid);
            CRM_Core_Payment_novalnet::contributionUpdate($updateData, $contribution->invoice_id);
        }
        if ($this->arycaptureparams['status'] != 100)  {
            self::debugError(ts('Novalnet callback received. Status (') . $aryCapture['status'] . ts(') is not valid: Only 100 is allowed'));
        }

        $dbVal['order_total_amount'] = $contribution->total_amount * 100;
        $dbVal['order_paid_amount'] = self::getPaidTotal($tid);
        $dbVal['payment_key'] = self::getPaymentKey($contribution->payment_instrument_id);
        $dbVal['invoice_id'] = $civi_orderId;

        return array($dbVal, $contribution);
    }

    /**
     * check the order is duplicate or not
     *
     * @param $invoiceId string
     * @return string
     */
    function checkDupe($invoiceId) {
        $contribution = new CRM_Contribute_DAO_Contribution();
        $contribution->invoice_id = $invoiceId;
        if ($contribution->find())
           self::checkDupe(md5(uniqid(rand(), TRUE)));
        return $invoiceId;
    }

    /**
     * Get Paid total
     *
     * @param $org_tid double
     * @return double
     */
    function getPaidTotal($org_tid) {
        $queryParams = array(
            1 => array($org_tid, 'Integer'),
        );
        $paidamount = CRM_Core_DAO::executeQuery("SELECT SUM(callback_amount) as paidamount FROM novalnet_callback  WHERE callback_tid  =%1", $queryParams);
        $paidamount->fetch();
        return isset($paidamount->paidamount) ? $paidamount->paidamount : '0';
    }

    /**
     * Fetch the contribution details
     *
     * @param $tid double
     * @param $order_no string
     * @return object
     */
    function fetchContributionDetails($tid, $order_no) {

        $contribution = new CRM_Contribute_BAO_Contribution();
        $contribution->trxn_id = $tid;
        if (!$contribution->find(TRUE)) {
            $contribution = new CRM_Contribute_BAO_Contribution();
            $contribution->invoice_id = $order_no;
            if (!$contribution->find(TRUE))
                self::debugError(ts('Novalnet callback received. Transaction Mapping Failed'));
        }
        return $contribution;
    }

    /**
     * Update the note data
     *
     * @param $comments string
     * @param $entity object
     * @return none
     */
    function setNote($comments, $entity) {
        $note = new CRM_Core_DAO_Note();
        $note->entity_table = 'civicrm_contribution';
        $note->entity_id = $entity->id;
        $modified_date = date('Y-m-d');
        if ($note->find(TRUE)) {
            $note_msg = $note->note . $comments;
            $note->note = $note_msg;
            $note->modified_date = CRM_Utils_Date::isoToMysql($modified_date);
        } else {
            $note->note = $comments;
            $note->modified_date = CRM_Utils_Date::isoToMysql($modified_date);
            $note->contact_id = $entity->contact_id;
            $note->entity_id = $entity->id;
        }
        $note->save();
    }

    /**
     * Update the paid amount
     *
     * @param $request array
     * @param $contribution object
     * @param $org_tid double
     * @return none
     */
    function setPaidTotal($request, $contribution, $org_tid) {
        $queryParams = array(
            1 => array($contribution->invoice_id, 'String'),
            2 => array($request['amount'], 'Integer'),
            3 => array($request['tid'], 'Integer'),
            4 => array(date('Y-m-d H:i:s'), 'String'),
            5 => array($org_tid, 'Integer'),
        );
        CRM_Core_DAO::executeQuery("INSERT INTO novalnet_callback (order_id, callback_amount, reference_tid, callback_datetime, callback_tid) VALUES (%1, %2, %3, %4,%5)", $queryParams);
    }
    /**
     * return the capture params
     *
     * @param none
     * @return array
     */
    function getCaptureParams() {
        return $this->arycaptureparams;
    }

    /**
     * sets the payment level
     *
     * @param none
     * @return Integer
     */
    function getPaymentTypeLevel() {
        if (!empty($this->arycaptureparams)) {
            if (in_array($this->arycaptureparams['payment_type'], $this->aryPayments))
                return 0;
            elseif(in_array($this->arycaptureparams['payment_type'], $this->aryChargebacks))
                return 1;
            elseif(in_array($this->arycaptureparams['payment_type'], $this->aryCollection))
                return 2;
            else
                return 3;
        }
        return '';
    }

    /**
     * sets the payment id
     *
     * @param $id int
     * @return string
     */

    function getPaymentKey($id) {
        $keys = array(
            '100' => 'novalnet_invoice',
            '101' => 'novalnet_prepayment',
            '102' => 'novalnet_cc',
            '103' => 'novalnet_ideal',
            '104' => 'novalnet_instant',
            '105' => 'novalnet_paypal',
            '106' => 'novalnet_sepa',
            '107' => 'novalnet_eps',
            );
        return $keys[$id];
    }

    /**
     * Send the mail notification
     *
     * @param $emailBody string
     * @return none
     */
    function sendEmail($emailBody) {
        global $processDebugMode;
        $emailBody = str_replace('<br />', PHP_EOL, $emailBody);
        $nn_callback_sendmail  = Civi::settings()->get('nn_callback_sendmail');
        $nn_callback_frommail  = Civi::settings()->get('nn_callback_frommail');
        $nn_callback_tomail  = Civi::settings()->get('nn_callback_tomail');
        $nn_callback_mailbcc  = Civi::settings()->get('nn_callback_mailbcc');
        $sendmail = isset($nn_callback_sendmail) ? trim($nn_callback_sendmail) : TRUE;
        $params['subject'] = 'Novalnet Callback Script Access Report';
        $params['from'] = isset($nn_callback_frommail) ? trim($nn_callback_frommail) : '';
        $params['html'] = $emailBody;
        $params['toEmail'] = isset($nn_callback_tomail) ? trim($nn_callback_tomail) : '';
        $params['bcc'] = isset($nn_callback_mailbcc) ? trim($nn_callback_mailbcc) : '';

        if ($sendmail ) {
            CRM_Utils_Mail::send($params);
            self::debugError('Mail sent!<br>'. $emailBody);
        } else {
            self::debugError('Mail not sent!<br>'. $emailBody);
        }
    }

    /**
     * Perform subscription stop
     *
     * @param $data array
     *
     * @return boolean
     */
    function sendSubscriptionStopRequest($data) {
        if (empty($data['tid']) || empty($data['vendor']) || empty($data['cancel_reason']) || empty($data['product']) || empty($data['tariff']) || empty($data['auth_code'])) {
            return false;
        }
        $urlData = CRM_Utils_System::makeQueryString($data);
        $host = 'https://payport.novalnet.de/paygate.jsp';
        $httppost = CRM_Utils_HttpClient::singleton();
        list($result, $response) = $httppost->post($host, $urlData);
        $parsed =array();
        parse_str($response, $parsed);
        if (!isset($parsed['status']) && $parsed['status'] != '100') {
            return false;
        }
        return true;
    }
	/**
     * build comments for new order
     *
     * @param $response array
     * @param $newcontribution object
     * @param $nextdate
     *
     * @return string
     */
	public function buildNewOrderComments($response, $newcontribution, $nextdate) {
        $paymentKey = self::getPaymentKey($newcontribution->payment_instrument_id);
        $comments = ($response['test_mode']) ? ts('Test order') : '';
        $payment_name = CRM_Core_Payment_novalnet::getPaymentMethods($paymentKey);
        $comments .= "<br>" .'Novalnet ' . $payment_name . "<br>" . ts('Novalnet transaction ID :') . $response['tid']."<br>";
        if (in_array($paymentKey, array('novalnet_invoice','novalnet_prepayment'))) {
            $comments .= "<br>" .ts('Please transfer the amount to the below mentioned account details of our payment processor Novalnet') . "<br>";
            if ($response['due_date'] != '') {
                $comments .= ts('Due date :') . ' ' . CRM_Utils_Date::customFormat($response['due_date']) . "<br>";
            }
            $comments .= ts('Account holder :') . ' ' . 'NOVALNET AG' . "<br>";
            $comments .= ts('IBAN :') . ' ' . $response['invoice_iban'] . "<br>";
            $comments .= ts('BIC :') . ' ' . $response['invoice_bic'] . "<br>";
            $comments .= ts('Bank :') . ' ' . $response['invoice_bankname'] . ' ' . $response['invoice_bankplace'] . "<br>";
            $comments .= ts('Amount :') . ' ' . CRM_Utils_Money::format($newcontribution->total_amount) . "<br>" ;
        }
		 $comments .= ts('Reference TID :') . $response['signup_tid'];
		 $comments .= "<br>" . ts('Next Payment Date : ') . $nextdate;
        return $comments;
    }

    /**
     * Display the message on the debug mode
     *
     * @param $errorMsg string
     *
     * @return void
     */
    public function debugError($errorMsg = 'Authentication Failed!') {
        global $processDebugMode;

        if ($processDebugMode)
            echo $errorMsg;
        exit;
    }
}

