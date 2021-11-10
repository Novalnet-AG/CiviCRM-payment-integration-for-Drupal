<?php
#########################################################
#                                                       #
#  Credit Card payment method class                     #
#  This module is used for real time processing of      #
#  Credit Card data of customers.                       #
#                                                       #
#  Copyright (c) Novalnet AG                            #
#                                                       #
#  Released under the GNU General Public License        #
#  This free contribution made by request.              #
#  If you have found this script usefull a small        #
#  recommendation as well as a comment on merchant form #
#  would be greatly appreciated.                        #
#                                                       #
#  Script : cc.php                                      #
#                                                       #
#########################################################
require_once  'CRM/Core/Payment/novalnet/novalnet.php';
require_once  'CRM/Core/Payment/novalnet/novalnet_css_link.php';
require_once 'cc.civix.php';

/**
 * Implementation of hook_civicrm_config().
 */
function cc_civicrm_config(&$config) {
  _cc_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu().
 *
 * @param $files array(string)
 */
function cc_civicrm_xmlMenu(&$files) {

  _cc_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install().
 */
function cc_civicrm_install() {
  require_once "CRM/Core/DAO.php";
  return _cc_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall().
 */
function cc_civicrm_uninstall() {
  require_once "CRM/Core/DAO.php";
  return _cc_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable().
 */
function cc_civicrm_enable() {
 return _cc_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable().
 */
function cc_civicrm_disable() {
  return _cc_civix_civicrm_disable();
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
function cc_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _cc_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_validate().
 *
 * @param $formName - the name of the form
 * @param $form - reference to the form object
 */
function cc_civicrm_validate($formName, &$fields, &$files, &$form) {
      if ( $formName == 'CRM_Contribute_Form_Contribution_Main'  || $formName == 'CRM_Event_Form_Registration_Register' ||
       $formName == 'CRM_Contribute_Form_Contribution' ) {
    if (isset($form->_paymentProcessor['payment_processor_type'])
    && $form->_paymentProcessor['payment_processor_type'] == 'Novalnet Credit Card') {
          $_SESSION['cc']['cc_panhash']     = isset($form->_submitValues['cc_pan_hash']) ? $form->_submitValues['cc_pan_hash'] : '';
          $_SESSION['cc']['cc_uniqid']      = isset($form->_submitValues['cc_unique_id']) ? $form->_submitValues['cc_unique_id'] : '';
          $_SESSION['cc']['cc_type']        = isset($form->_submitValues['cc_type']) ? $form->_submitValues['cc_type'] : '';
          $_SESSION['cc']['cc_holder']      = isset($form->_submitValues['cc_owner']) ? $form->_submitValues['cc_owner'] : '';
          $_SESSION['cc']['cc_exp_month']   = isset($form->_submitValues['cc_exp_month']) ? $form->_submitValues['cc_exp_month'] : '';
          $_SESSION['cc']['cc_exp_year']    = isset($form->_submitValues['cc_exp_year']) ? $form->_submitValues['cc_exp_year'] : '';
          $_SESSION['cc']['cc_cvc2']        = isset($form->_submitValues['cc_cid']) ? $form->_submitValues['cc_cid'] : '';
          $_SESSION['cc']['cc_fldVdr']      = isset($form->_submitValues['cc_fldVdr']) ? $form->_submitValues['cc_fldVdr'] : '';
          $_SESSION['cc']['sessionid'] = session_id();
       }
    }
  return true;
  }
/**
 * Implementation of hook_civicrm_buildForm().
 */
function cc_civicrm_buildForm( $formName, &$form ) {
    if ( $formName == 'CRM_Contribute_Form_Contribution_Main'  || $formName == 'CRM_Event_Form_Registration_Register' | $formName == 'CRM_Contribute_Form_Contribution' ) {
      if (isset($form->_paymentProcessor['payment_processor_type']) && $form->_paymentProcessor['payment_processor_type'] == 'Novalnet Credit Card') {
        $config = CRM_Core_Config::singleton();
        $iframedata = array (
          'vendorid'        => trim($form->_paymentProcessor['user_name']),
          'productid'       => trim($form->_paymentProcessor['signature']),
          'authcode'        => trim($form->_paymentProcessor['password']),
          'tariffid'        => trim($form->_paymentProcessor['subject']),
          'manuallimit'     => (isset($config->nn_cc_manualamount))? trim($config->nn_cc_manualamount): '',
          'productid2'      => (isset($config->nn_cc_productid2))? trim($config->nn_cc_productid2) :'',
          'tariffid2'       => (isset($config->nn_cc_tariffid2))? trim($config->nn_cc_tariffid2) : '',
          'language'        => CRM_Core_Payment_novalnet::getLanguage(),
          'request_type'    => (getenv('HTTPS') == '1' || getenv('HTTPS') == 'on') ? 'https://' : 'http://',
          'path'            =>  $config->userFrameworkResourceURL. 'CRM/Core/Payment/novalnet/civicrm_novalnet_cc_iframe.php?',
          'payment_id'      => CRM_Core_Payment_novalnet::setPaymentKey('novalnet_cc'),
          );
        $iframedata['loadingimage'] = $iframedata['request_type'] . 'www.novalnet.de/img/novalnet-loading-icon.gif';
        $iframedata['panhash'] = (isset($_SESSION['cc']['cc_panhash']) && isset($config->nn_cc_auto_refill) && $config->nn_cc_auto_refill==1 && isset($_SESSION['cc']['sessionid']) && $_SESSION['cc']['sessionid'] == session_id()) ? $_SESSION['cc']['cc_panhash'] : '';
        $iframedata['fldVar'] = (isset($_SESSION['cc']['cc_fldVdr']) && isset($config->nn_cc_auto_refill) && $config->nn_cc_auto_refill==1  && $_SESSION['cc']['sessionid'] && $_SESSION['cc']['sessionid'] == session_id()) ? $_SESSION['cc']['cc_fldVdr'] : '';
        CRM_Core_Payment_novalnet::iframeDataValidation($iframedata);
 
        $cc_src = $iframedata['path'] . "lang_code=" . $iframedata['language'] . "&vendor_id=" . $iframedata['vendorid'];
        $cc_src .= "&product_id=" . $iframedata['productid'] . "&payment_id=" .$iframedata['payment_id'] . "&authcode=" . $iframedata['authcode'];
        $cc_src .= "&panhash=" . $iframedata['panhash'] . "&fldVar=" . $iframedata['fldVar'];

        $template = CRM_Core_Smarty::singleton();
        $template->assign( 'nnerror', FALSE);
        if (isset($iframedata['error'])) {
          $template->assign( 'nnerror', TRUE);
          $template->assign( 'novalnet_cc_error', $iframedata['error']);
        }
        else {
          $template->assign( 'cc_path', $cc_src);
          $template->assign( 'cc_field_src', $cc_src);
          $template->assign( 'vendor_id', $iframedata['vendorid']);
          $template->assign( 'auth_code', $iframedata['authcode']);
          $template->assign( 'cc_loading_image', $iframedata['loadingimage']);
          if (defined('NOVALNET_CC_CUSTOM_CSS') && NOVALNET_CC_CUSTOM_CSS != '') {
            $template->assign( 'cc_customcss',TRUE);
            $template->assign( 'cc_css_style',NOVALNET_CC_CUSTOM_CSS);
            $template->assign( 'cc_css_styleval',NOVALNET_CC_CUSTOM_CSS_STYLE);
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
function cc_civicrm_managed(&$entities) {
  $entities[] = array(
    'module' => 'org.novalnet.payment.cc',
    'name'   => 'Novalnet Credit Card',
    'entity' => 'PaymentProcessorType',
    'params' => array(
      'version'        =>  3,
      'name'           => 'Novalnet Credit Card',
      'title'          => 'Novalnet Credit Card',
      'description'    => 'Novalnet Credit Card Payment Processor',
      'class_name'     => 'Payment_cc',
      'billing_mode'   => 'notify',
      'user_name_label'=> 'Novalnet Merchant ID',
      'password_label' => 'Novalnet Merchant Authorisation code',
      'signature_label'=> 'Novalnet Product ID',
      'subject_label'  => 'Novalnet Tariff ID',
      'is_recur'       =>  0,
      'payment_type'   =>  1
    ),
  );

  return _cc_civix_civicrm_managed($entities);
}
