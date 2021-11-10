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
 * Script: novalnetgateway.php
 *
 */
require_once 'novalnetgateway.civix.php';
require_once  'CRM/Core/Payment/org.payment.novalnetgateway/lib/novalnet.php';
require_once  'CRM/Core/Payment/org.payment.novalnetgateway/CRM/Admin/Form/Setting/NovalnetGatewayForm.php';
/**
 * Implementation of hook_civicrm_config().
 */
function novalnetgateway_civicrm_config(&$config) {
  _novalnetgateway_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu().
 *
 * @param $files array(string)
 */
function novalnetgateway_civicrm_xmlMenu(&$files) {
  _novalnetgateway_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install().
 */
function novalnetgateway_civicrm_install() {
    require_once "CRM/Core/DAO.php";

    CRM_Core_DAO::executeQuery("INSERT INTO civicrm_option_value ( option_group_id, value, name, is_active, weight, label) VALUES
    ( 10, '100', 'Novalnet Invoice', 1, 100,'Novalnet Invoice'),
    ( 10, '101', 'Novalnet Prepayment', 1, 101,'Novalnet Prepayment'),
    ( 10, '102', 'Novalnet Credit Card', 1, 104,'Novalnet Credit Card'),
    ( 10, '103', 'Novalnet iDEAL', 1, 106,'Novalnet iDEAL'),
    ( 10, '104', 'Novalnet Instant Bank Transfer', 1, 107,'Novalnet Instant Bank Transfer'),
    ( 10, '105', 'Novalnet PayPal', 1, 108,'Novalnet PayPal'),
    ( 10, '106', 'Novalnet Direct Debit SEPA', 1, 109,'Novalnet Direct Debit SEPA'),
    ( 10, '107', 'Novalnet eps', 1, 110,'Novalnet eps'),
    ( 10, '108', 'Novalnet Giropay', 1, 111,'Novalnet Giropay')
    ");
    CRM_Core_DAO::executeQuery("CREATE TABLE IF NOT EXISTS novalnet_affiliates(
        `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
        `vendor_id` int(11) unsigned NOT NULL,
        `vendor_authcode` varchar(40) NOT NULL,
        `product_id` int(11) unsigned NOT NULL,
        `product_url` varchar(200) DEFAULT NULL,
        `activation_date` datetime DEFAULT NULL,
        `aff_id` int(11) unsigned NOT NULL,
        `aff_authcode` varchar(40) NOT NULL,
        `aff_accesskey` varchar(40) NOT NULL,
            PRIMARY KEY (`id`),
            KEY `aff_id` (`aff_id`)
        ) COMMENT='Novalnet merchant / affiliate account information'");
    CRM_Core_DAO::executeQuery("CREATE TABLE IF NOT EXISTS `novalnet_subscription_details` (
        `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        `nnconfig` text CHARACTER SET utf8 DEFAULT NULL,
        `novalnet_tid` bigint(20) DEFAULT NULL,
        `invoice_id` varchar(255) DEFAULT NULL,
        `frequency_unit` varchar(20) DEFAULT NULL,
        `installments` varchar(20) DEFAULT NULL,
        `frequency_interval` int(10) DEFAULT NULL,
        `paid_upto` varchar(20) DEFAULT NULL,
          PRIMARY KEY (`id`),
          KEY `novalnet_tid` (`novalnet_tid`)
        ) COMMENT='Novalnet subscription information'");

    CRM_Core_DAO::executeQuery("CREATE TABLE IF NOT EXISTS `novalnet_callback` (
        `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        `order_id` varchar(200) CHARACTER SET utf8 DEFAULT NULL,
        `callback_amount` int(11) NOT NULL,
        `reference_tid` bigint(20) NOT NULL,
        `callback_datetime` datetime NOT NULL,
        `callback_tid` bigint(20) DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `callback_tid` (`callback_tid`)
        ) COMMENT='Novalnet callback information'");
    CRM_Core_DAO::executeQuery("INSERT INTO civicrm_payment_processor_type
      (name, title, description, is_active, is_default, user_name_label, password_label, signature_label, subject_label, class_name, url_site_default, url_recur_default, url_button_default, url_site_test_default, url_recur_test_default, url_button_test_default, billing_mode, is_recur )
        VALUES
      ('Novalnet_Direct_Debit_SEPA','Novalnet Direct Debit SEPA', 'Novalnet Direct Debit SEPA Payment Processor',1,0,'Novalnet Merchant ID','','','','Payment_sepa','','','','','','',4,1),
      ('Novalnet_Credit_Card','Novalnet Credit Card', 'Novalnet Credit Card Payment Processor',1,0,'Novalnet Merchant ID','','','','Payment_cc','','','','','','',4,1),
      ('Novalnet_Prepayment','Novalnet Prepayment', 'Novalnet Prepayment Payment Processor',1,0,'Novalnet Merchant ID','','','','Payment_prepayment','','','','','','',4,1),
      ('Novalnet_Invoice','Novalnet Invoice', 'Novalnet Invoice Payment Processor',1,0,'Novalnet Merchant ID','','','','Payment_invoice','','','','','','',4,1),
      ('Novalnet_PayPal','Novalnet PayPal', 'Novalnet PayPal Payment Processor',1,0,'Novalnet Merchant ID','','','','Payment_paypal','','','','','','',4,1),
      ('Novalnet_Instant_Bank_Transfer','Novalnet Instant Bank Transfer', 'Novalnet Instant Bank Transfer Payment Processor',1,0,'Novalnet Merchant ID','','','','Payment_instant','','','','','','',4,0),
      ('Novalnet_iDEAL','Novalnet iDEAL', 'Novalnet iDEAL Payment Processor',1,0,'Novalnet Merchant ID','','','','Payment_ideal','','','','','','',4,0),
      ('Novalnet_eps','Novalnet eps', 'Novalnet eps Payment Processor',1,0,'Novalnet Merchant ID','','','','Payment_eps','','','','','','',4,0),
      ('Novalnet_Giropay','Novalnet Giropay', 'Novalnet Giropay Payment Processor',1,0,'Novalnet Merchant ID','','','','Payment_giropay','','','','','','',4,0)");

  return _novalnetgateway_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall().
 */
function novalnetgateway_civicrm_uninstall() {
    require_once "CRM/Core/DAO.php";
    CRM_Core_DAO::executeQuery("DELETE from civicrm_option_value where name LIKE 'Novalnet%'");
    CRM_Core_DAO::executeQuery("DELETE from civicrm_payment_processor_type where name LIKE 'Novalnet%'");

    $sql = "SELECT config_backend FROM civicrm_domain WHERE  id = %1";
    $params = array(1 => array(CRM_Core_Config::domainID(), 'Integer'));
    $configBackend = CRM_Core_DAO::singleValueQuery($sql, $params);
    $configBackend = unserialize($configBackend);
    list($text_elements, $select_elements, $radio_elements) = CRM_Core_Payment_novalnet::setConfigParameters();
    $fields = array_merge(array_keys($text_elements),array_keys($select_elements),array_keys($radio_elements));
    foreach ($fields as $k => $v){
        if (isset($configBackend[$v])) unset($configBackend[$v]);
     }
    $configBackend = serialize($configBackend);
    $sql = "
    UPDATE civicrm_domain
    SET    config_backend = %2
    WHERE  id = %1
    ";
    $params[2] = array($configBackend, 'String');
    CRM_Core_DAO::executeQuery($sql, $params);

  return _novalnetgateway_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable().
 */

function novalnetgateway_civicrm_enable() {
  return _novalnetgateway_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable().
 */
function novalnetgateway_civicrm_disable() {
  return _novalnetgateway_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 */
function novalnetgateway_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _novalnetgateway_civix_civicrm_upgrade($op, $queue);
}
/**
 * Implementation of hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 */
function novalnetgateway_civicrm_managed(&$entities) {
  return _novalnetgateway_civix_civicrm_managed($entities);
}

/**
 * Implementation of hook_civicrm_navigationMenu().
 *
 * Generate a list of Menus
 */
function novalnetgateway_civicrm_navigationMenu( &$params ) {

   //  Get the maximum key of $params
    $maxKey = ( max ( array_keys($params) ) );
    $params[$maxKey+1] = array (
        'attributes' => array (
            'label'      => 'Novalnet',
            'name'       => 'Novalnet',
            'url'        =>  null,
            'permission' => 'administer CiviCRM',
            'operator'   => null,
            'separator'  => null,
            'parentID'   => null,
            'navID'      => $maxKey+1,
            'active'     => 1
            ),
        'child' =>  array (
            '1' => array (
                'attributes' => array (
                    'label'      => ts('Novalnet Payment Configuration'),
                    'name'       => ts('Novalnet Payment Configuration'),
                    'url'        => 'civicrm/admin/setting/novalnet',
                    'permission' => 'administer CiviCRM',
                    'operator'   => null,
                    'separator'  => 1,
                    'parentID'   => $maxKey+1,
                    'navID'      => 1,
                    'active'     => 1
                )
            ),
        )
    );
}

/**
 * Implementation of hook_civicrm_validateForm().
 *
 * Validation of the novalnet config and card details
 */
function novalnetgateway_civicrm_validateForm( $formName, &$fields, &$files, &$form, &$errors ) {
    if ($formName == 'CRM_Contribute_Form_Contribution_Main' || $formName == 'CRM_Event_Form_Registration_Register') {
        $paymentClass = strtolower($form->_paymentProcessor['class_name']);
        $passwordPayments = array('payment_ideal', 'payment_paypal', 'payment_instant', 'payment_eps', 'payment_giropay');
        if (in_array($paymentClass, array('payment_invoice', 'payment_ideal', 'payment_paypal', 'payment_instant', 'payment_cc', 'payment_prepayment', 'payment_sepa', 'payment_eps', 'payment_giropay'))) {
            $config     = CRM_Core_Config::singleton();
            $nn_vendor  = trim(Civi::settings()->get('nn_vendor'));
            $nn_cc_secure_active  = Civi::settings()->get('nn_cc_secure_active');
            if ($nn_cc_secure_active == 1) {
                $passwordPayments[] = 'payment_cc';
            }
            $nn_password  = Civi::settings()->get('nn_password');
            $nn_paypal_user  = Civi::settings()->get('nn_paypal_user');
            $nn_paypal_sig  = Civi::settings()->get('nn_paypal_sig');
            $nn_paypal_pwd  = Civi::settings()->get('nn_paypal_pwd');
            if (!$nn_vendor || (in_array($paymentClass, $passwordPayments) && $nn_password == '')
                || ($paymentClass == 'payment_paypal' && (empty($nn_paypal_user) || empty($nn_paypal_sig)
                    || empty($nn_paypal_pwd)))) {
                        $errors['payment_processor'] = ts('Please fill in all the mandatory fields');
                return;
            }

            if ($paymentClass == 'payment_sepa') {
                $_SESSION['novalnet']['payment_type']  = 'novalnet_sepa';
                $account_details['panhash']            = isset($fields['nn_sepa_hash']) ? $fields['nn_sepa_hash'] : '';
                $account_details['uniqid']             = isset($fields['nn_sepa_uniqueid'])? $fields['nn_sepa_uniqueid'] : '';
                $account_details['holder']             = isset($fields['sepa_cardholder']) ? $fields['sepa_cardholder'] : '';
                $account_details['sessionid']          = session_id();
                $_SESSION['sepa']['account_details']   = base64_encode(strrev(serialize($account_details)));

                if (CRM_Core_Payment_novalnet::validateSepaData($account_details)) {
                  $errors['payment_processor'] = ts('Your account details are invalid');
                  return;
                }
            }
        return true;
        }
    }
}
