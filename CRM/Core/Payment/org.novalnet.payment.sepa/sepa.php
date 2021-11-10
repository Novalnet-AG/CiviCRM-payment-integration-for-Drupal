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

require_once  'CRM/Core/Payment/novalnet/novalnet.php';
require_once  'CRM/Core/Payment/novalnet/novalnet_css_link.php';
require_once 'sepa.civix.php';

/**
 * Implementation of hook_civicrm_config().
 */
function sepa_civicrm_config(&$config) {
  _sepa_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu().
 *
 * @param $files array(string)
 */
function sepa_civicrm_xmlMenu(&$files) {

  _sepa_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install().
 */
function sepa_civicrm_install() {
  require_once "CRM/Core/DAO.php";

  CRM_Core_DAO::executeQuery("CREATE TABLE IF NOT EXISTS `novalnet_sepa_orders` (
          `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
          `invoice_id` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
          `novalnet_tid` bigint(20) NOT NULL,
          `user_id` varchar(20) DEFAULT NULL,
          `amount` bigint(20) DEFAULT NULL,
          `status` int(5) DEFAULT NULL,
          `nnconfig` text DEFAULT NULL,
          PRIMARY KEY (`id`),
          KEY `invoice_id` (`invoice_id`),
          KEY `novalnet_tid` (`novalnet_tid`),
          KEY `user_id` (`user_id`),
          KEY `amount` (`amount`),
          KEY `status` (`status`) 
        ) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");
  return _sepa_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall().
 */
function sepa_civicrm_uninstall() {
  require_once "CRM/Core/DAO.php";
  return _sepa_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable().
 */
function sepa_civicrm_enable() {
 return _sepa_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable().
 */
function sepa_civicrm_disable() {
  return _sepa_civix_civicrm_disable();
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
function sepa_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _sepa_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_validate().
 *
 * @param $formName - the name of the form
 * @param $form - reference to the form object
 */
function sepa_civicrm_validate($formName, &$fields, &$files, &$form) {
    if (  $formName == 'CRM_Contribute_Form_Contribution_Main'
      ||  $formName == 'CRM_Event_Form_Registration_Register'
      ||  $formName == 'CRM_Contribute_Form_Contribution' ) {
        if (isset($form->_paymentProcessor['payment_processor_type'])
          && $form->_paymentProcessor['payment_processor_type'] == 'Novalnet Direct Debit SEPA') {
 
            $_SESSION['sepa']['panhash']            = isset($form->_submitValues['sepa_pan_hash']) ? $form->_submitValues['sepa_pan_hash'] : '';
            $_SESSION['sepa']['uniqid']             = isset($form->_submitValues['sepa_unique_id'])?$form->_submitValues['sepa_unique_id'] : '';
            $_SESSION['sepa']['holder']             = isset($form->_submitValues['sepa_owner']) ? $form->_submitValues['sepa_owner'] : '';
            $_SESSION['sepa']['mandate_ref']        = isset($form->_submitValues['sepa_mandate_ref']) ? $form->_submitValues['sepa_mandate_ref'] : '';
            $_SESSION['sepa']['mandate_date']       = isset($form->_submitValues['sepa_mandate_date']) ? $form->_submitValues['sepa_mandate_date'] : '';
            $_SESSION['sepa']['sepa_bic_confirm']   = isset($form->_submitValues['sepa_bic_confirm']) ? $form->_submitValues['sepa_bic_confirm'] : '';
            $_SESSION['sepa']['fldVdr']   = isset($form->_submitValues['sepa_fldVdr']) ? $form->_submitValues['sepa_fldVdr'] : '';
            $_SESSION['sepa']['sessionid'] = session_id();
        }
    }
  return true;
  }

/**
 * Implementation of hook_civicrm_buildForm().
 */
function sepa_civicrm_buildForm( $formName, &$form ) {
  global $user;

    if ( $formName == 'CRM_Contribute_Form_Contribution_Main'  || $formName == 'CRM_Event_Form_Registration_Register'
      || $formName == 'CRM_Contribute_Form_Contribution' ) {
        if (isset($form->_paymentProcessor['payment_processor_type'])
          && $form->_paymentProcessor['payment_processor_type'] == 'Novalnet Direct Debit SEPA') {
            $config                       = CRM_Core_Config::singleton();
            $iframe_request_data = array (
              'vendorid'        => trim($form->_paymentProcessor['user_name']),
              'productid'       => trim($form->_paymentProcessor['signature']),
              'authcode'        => trim($form->_paymentProcessor['password']),
              'tariffid'        => trim($form->_paymentProcessor['subject']),
              'manuallimit'     => (isset($config->nn_sepa_manualamount))? trim($config->nn_sepa_manualamount): '',
              'productid2'      => (isset($config->nn_sepa_productid2))? trim($config->nn_sepa_productid2) :'',
              'tariffid2'       => (isset($config->nn_sepa_tariffid2))? trim($config->nn_sepa_tariffid2) : '',
              'language'        => CRM_Core_Payment_novalnet::getLanguage(),
              'sepa_src'            =>  $config->userFrameworkResourceURL. 'CRM/Core/Payment/novalnet/civicrm_novalnet_sepa_iframe.php?',
            );

        $iframe_request_data['panhash'] = (isset($_SESSION['sepa']['panhash']) && isset($config->nn_sepa_auto_refill) && $config->nn_sepa_auto_refill==1 && $_SESSION['sepa']['sessionid'] && $_SESSION['sepa']['sessionid'] == session_id()) ? $_SESSION['sepa']['panhash'] : '';
        $iframe_request_data['fldVar'] = (isset($_SESSION['sepa']['fldVdr']) && isset($config->nn_sepa_auto_refill) && $config->nn_sepa_auto_refill==1 && $_SESSION['sepa']['panhash']!='' && $_SESSION['sepa']['sessionid'] && $_SESSION['sepa']['sessionid'] == session_id()) ? $_SESSION['sepa']['fldVdr'] : '';
        $iframe_request_data['mandate_ref'] = (isset($_SESSION['sepa']['mandate_ref']) ? $_SESSION['sepa']['mandate_ref'] : '');
        $iframe_request_data['mandate_date'] = (isset($_SESSION['sepa']['mandate_date']) ? $_SESSION['sepa']['mandate_date'] : '');
        $iframe_request_data['loadingimage'] = $config->userFrameworkResourceURL. 'CRM/Core/Payment/novalnet/logos/novalnet-loading-icon.gif';

        if (isset($config->nn_sepa_payment_type) && trim($config->nn_sepa_payment_type) == 'DIRECT_DEBIT_SEPA_SIGNED') {
            $iframe_request_data['payment_id'] = CRM_Core_Payment_novalnet::setPaymentKey('novalnet_sepa_signed');
        }
        else {
          $iframe_request_data['payment_id'] = CRM_Core_Payment_novalnet::setPaymentKey('novalnet_sepa');
        }
 
        CRM_Core_Payment_novalnet::iframeDataValidation($iframe_request_data);
        $sepa_src = $iframe_request_data['sepa_src'] . "lang_code=" . $iframe_request_data['language'];
        $sepa_src .= "&vendor_id=" . $iframe_request_data['vendorid'] . "&product_id=" . $iframe_request_data['productid'];
        $sepa_src .= "&payment_id=" . $iframe_request_data['payment_id'] . "&authcode=" . $iframe_request_data['authcode'];
        $sepa_src .= "&panhash=" . $iframe_request_data['panhash'] . "&fldVar=" . $iframe_request_data['fldVar'];
        $sepa_src .= "&mandate_ref=" . $iframe_request_data['mandate_ref']."&mandate_date=" . $iframe_request_data['mandate_date'];

        $template = CRM_Core_Smarty::singleton();
        $template->assign( 'nnerror', FALSE);
        if(isset($iframe_request_data['error'])) {
          $template->assign( 'nnerror', TRUE);
          $template->assign( 'novalnet_sepa_error', $iframe_request_data['error']);
        }
        else {
          $template->assign( 'sepa_path', $sepa_src);
          $template->assign( 'sepa_src', $sepa_src);
          $template->assign( 'sepa_loading_image', $iframe_request_data['loadingimage']);
          $template->assign( 'loadiframe','no');

          if (defined('NOVALNET_SEPA_CUSTOM_CSS') && NOVALNET_SEPA_CUSTOM_CSS != '') {
            $template->assign( 'sepa_customcss',TRUE);
            $template->assign( 'sepa_css_style',NOVALNET_SEPA_CUSTOM_CSS);
            $template->assign( 'sepa_css_styleval',NOVALNET_SEPA_CUSTOM_CSS_STYLE);
          }
        }
      }
    }
  }

/**
 * Implementation of hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 */
function sepa_civicrm_managed(&$entities) {
  $entities[] = array(
    'module' => 'org.novalnet.payment.sepa',
    'name'   => 'Novalnet Direct Debit SEPA',
    'entity' => 'PaymentProcessorType',
    'params' => array(
      'version'        =>  3,
      'name'           => 'Novalnet Direct Debit SEPA',
      'title'          => 'Novalnet Direct Debit SEPA',
      'description'    => 'Novalnet Direct Debit SEPA Payment Processor',
      'class_name'     => 'Payment_sepa',
      'billing_mode'   => 'notify',
      'user_name_label'=> 'Novalnet Merchant ID',
      'password_label' => 'Novalnet Merchant Authorisation code',
      'signature_label'=> 'Novalnet Product ID',
      'subject_label'  => 'Novalnet Tariff ID',
      'is_recur'       =>  0,
      'payment_type'   =>  1
    ),
  );

  return _sepa_civix_civicrm_managed($entities);
}
