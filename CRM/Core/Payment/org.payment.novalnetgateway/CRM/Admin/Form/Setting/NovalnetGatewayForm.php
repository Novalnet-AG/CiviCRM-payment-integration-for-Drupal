<?php
/**
 * Novalnet payment method module
 * This module is used for real time processing of
 * Novalnet transaction of customers.
 *
 * Copyright (c) Novalnet AG
 *
 * Released under the GNU General Public License
 * This free contribution made by request.
 * If you have found this script useful a small
 * recommendation as well as a comment on merchant form
 * would be greatly appreciated.
 *
 * Script: NovalnetGatewayForm.php
 *
 */
require_once 'CRM/Core/Payment/org.payment.novalnetgateway/lib/novalnet.php';
require_once 'CRM/Admin/Form/Setting.php';
require_once 'CRM/Core/BAO/CustomField.php';

class CRM_Admin_Form_Setting_NovalnetGatewayForm extends CRM_Admin_Form_Setting
{

	public $_settings = array(
		'nn_vendor' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		'nn_authcode' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		'nn_product' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		'nn_tariff' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		'nn_password' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		'nn_subscription_tariff_id' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		'nn_subs_cancel_status' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		'nn_referrer_id' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		'nn_manualamount' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		'novalnet_pay_logo' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		'nn_callback_testmode' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		'nn_callback_debug' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		'nn_callback_sendmail' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		'nn_callback_frommail' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		'nn_callback_tomail' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		'nn_callback_mailbcc' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		'nn_notify_url' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		// Invoice
		'nn_inv_testmode' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		'nn_inv_duedate' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		'novalnet_invoice_cont_status' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		'novalnet_invoice_cont_cb_status' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		'novalnet_invoice_ref_one' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		'novalnet_invoice_ref_two' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		'novalnet_invoice_payment_ref1' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		'novalnet_invoice_payment_ref2' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		'novalnet_invoice_payment_ref3' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		//Prepayment
		'nn_prepayment_testmode' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		'novalnet_prepayment_cont_cb_status' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		'novalnet_prepayment_cont_status' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		'novalnet_prepayment_ref_one' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		'novalnet_prepayment_ref_two' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		'novalnet_prepayment_payment_ref1' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		'novalnet_prepayment_payment_ref2' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		'novalnet_prepayment_payment_ref3' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		//Credit Card
		'nn_cc_testmode' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		'nn_cc_secure_active' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		'novalnet_cc_cont_status' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		'novalnet_cc_ref_one' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		'novalnet_cc_ref_two' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		'nn_cc_amexlogo_active' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		'nn_cc_cartasilogo_active' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		'nn_cc_maestrologo_active' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		//Sepa
		'nn_sepa_testmode' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		'nn_sepa_due_date' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		'novalnet_sepa_cont_status' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		'nn_sepa_auto_refill' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		'novalnet_sepa_ref_one' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		'novalnet_sepa_ref_two' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		//Paypal
		'nn_paypal_testmode' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		'nn_paypal_user' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		'nn_paypal_pwd' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		'nn_paypal_sig' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		'novalnet_paypal_cont_status' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		'novalnet_paypal_pending_status' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		'novalnet_paypal_ref_one' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		'novalnet_paypal_ref_two' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		//Ideal
		'nn_ideal_testmode' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		'novalnet_ideal_cont_status' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		'novalnet_ideal_ref_one' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		'novalnet_ideal_ref_two' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		//Instant
		'nn_instant_testmode' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		'novalnet_instant_ref_one' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		'novalnet_instant_ref_two' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		'novalnet_instant_cont_status' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		//EPS
		'nn_eps_testmode' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		'novalnet_eps_cont_status' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		'novalnet_eps_ref_one' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		'novalnet_eps_ref_two' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		//Giropay
		'nn_giropay_testmode' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		'novalnet_giropay_cont_status' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		'novalnet_giropay_ref_one' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
		'novalnet_giropay_ref_two' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
	);

    /**
     * Builds the config form
     *
     * @param none
     * @return none
     */
    public function buildQuickForm() {

        list($text_elements, $select_elements, $radio_elements) = CRM_Core_Payment_novalnet::setConfigParameters();
        foreach ($text_elements as $k => $v) {
            $this->addElement('text', $k, $v['title']);
        }
        foreach ($select_elements as $k => $v) {
            $this->addElement('select', $k, $v['title'], $v['option']);
            $this->setDefaults(array($k => $v['default']));
        }
        foreach ($radio_elements as $k => $v) {
            $this->addRadio($k, $v['title'], $v['option']);
            $this->setDefaults(array($k => $v['default']));
        }
        $this->addFormRule(array('CRM_Admin_Form_Setting_NovalnetGatewayForm', 'formRule'));
        parent::buildQuickForm();
		if (isset($_GET['lcMessages']))
			$_SESSION['nnadmin'] = '?lcMessages='.$_GET['lcMessages'];
    }

    /**
     * set the default value
     *
     * @param none
     * @return array
     */

    public function setDefaultValues() {
		parent::setDefaultValues();
		$url  = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
		$url .= $_SERVER['SERVER_NAME'];
		$url .= $_SERVER['REQUEST_URI'];
		$url = dirname($url);
		$callbackurl = explode('/admin/',$url);
		$this->_defaults['nn_notify_url'] = $callbackurl['0'].'/callback_novalnet2civicrm';
		return $this->_defaults;
	}


    /**
     * validate the config values
     *
     * @param $fields
     * @return string
     */
     public static function formRule($fields) {

       $errors = array();
        if (!is_numeric($fields['nn_vendor'])) {
            $errors['nn_vendor']= ts('Please enter valid Novalnet Merchant ID'). "<br>";
        }
        if ( empty($fields['nn_authcode'])) {
            $errors['nn_authcode']= ts('Please enter valid Novalnet Merchant Authorisation code'). "<br>";
        }
        if ( empty($fields['nn_tariff'])) {
            $errors['nn_tariff']= ts('Please enter valid Novalnet Tariff ID'). "<br>";
        }
        if ( empty($fields['nn_product'])) {
            $errors['nn_product']= ts('Please enter valid Novalnet Product ID'). "<br>";
        }
        if ( !empty($fields['nn_subscription_tariff_id']) && !is_numeric($fields['nn_subscription_tariff_id'])) {
            $errors['nn_subscription_tariff_id']= ts('Please enter valid Novalnet Subscription Tariff ID'). "<br>";
        }
        if (!empty($fields['nn_manualamount']) && !is_numeric($fields['nn_manualamount'])) {
          $errors['nn_manualamount']= ts('Novalnet Credit Card') . '-' . ts('Please enter valid Manual checking amount') . '<br>';
        }

        if (isset($fields['nn_sepa_due_date']) && $fields['nn_sepa_due_date'] !='' && (!is_numeric($fields['nn_sepa_due_date'])
          || $fields['nn_sepa_due_date'] < 7 || $fields['nn_sepa_due_date'] == 0)) {
            $errors['nn_sepa_due_date']= ts('Novalnet Direct Debit SEPA') . '-' . ts('Please enter valid due date') . '<br>';
        }
        if ($fields['novalnet_invoice_payment_ref1'] == '0' && $fields['novalnet_invoice_payment_ref2'] == '0'
            && $fields['novalnet_invoice_payment_ref3'] == '0') {
            $errors['novalnet_invoice_payment_ref1']= ts('Novalnet Invoice') . '-' . ts('Please select atleast one payment reference.') . '<br>';
        }

        if (!empty($fields['nn_inv_duedate']) && (!is_numeric($fields['nn_inv_duedate']))) {
            $errors['nn_inv_duedate']= ts('Novalnet Invoice') . '-' . ts('Please enter valid due date') . '<br>';
        }
         if ($fields['novalnet_prepayment_payment_ref1'] == '0' && $fields['novalnet_prepayment_payment_ref2'] == '0'
            && $fields['novalnet_prepayment_payment_ref3'] == '0') {
            $errors['novalnet_prepayment_payment_ref1']= ts('Novalnet Prepayment') . '-' . ts('Please select atleast one payment reference.') . '<br>';
        }
        if (!empty($fields['nn_callback_frommail']) && !CRM_Utils_Rule::email($fields['nn_callback_frommail'])) {
            $errors['nn_callback_frommail']= ts('E-mail address (FROM)') . '-' . ts('Your E-mail address is invalid') . '<br>';
        }
        if (!empty($fields['nn_callback_tomail']) && !CRM_Utils_Rule::emailList($fields['nn_callback_tomail'])) {
            $errors['nn_callback_tomail']= ts('E-mail address (To)') . '-' . ts('Your E-mail address is invalid') . '<br>';
        }
        if ($fields['nn_callback_mailbcc'] && !CRM_Utils_Rule::emailList($fields['nn_callback_mailbcc'])) {
            $errors['nn_callback_mailbcc']= ts('E-mail address (Bcc)') . '-' . ts('Your E-mail address is invalid') . '<br>';
        }

        return empty($errors) ? TRUE : $errors;
    }
    /**
     * Process the form submission.
     *
     * @param none
     * @return none
     */
    public function postProcess() {
        // store the submitted values in an array
        $params = $this->exportValues();
        parent::commonProcess($params);
        $url = CRM_Utils_System::baseCMSURL() . "civicrm/admin/setting/novalnet";
		$url .= isset($_SESSION['nnadmin']) ? $_SESSION['nnadmin'] : '';
		unset($_SESSION['nnadmin']);
        CRM_Utils_System::redirect($url);
    }

    /**
     * Get the order details from core
     *
     * @param none
     * @return array
     */

    public static function getNovalnetStatus() {
		$novalnetStatus = CRM_Core_PseudoConstant::get('CRM_Contribute_DAO_Contribution', 'contribution_status_id');
		$status = array();
		foreach ($novalnetStatus as $key => $value) {
		  $status[$key] = "$key";
		  if ($value) {
			$status[$key] .= " ($value)";
		  }
		}
		return $status;
	}

}
