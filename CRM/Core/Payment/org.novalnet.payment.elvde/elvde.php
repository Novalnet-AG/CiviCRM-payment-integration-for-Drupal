<?php
#########################################################
#                                                       #
#  Direct Debit German payment method class             #
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
#  Script : elvde.php                                   #
#                                                       #
#########################################################
require_once  'CRM/Core/Payment/novalnet/novalnet.php';
require_once 'elvde.civix.php';

/**
 * Implementation of hook_civicrm_config().
 */
 function elvde_civicrm_config(&$config) {
  _elvde_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu().
 *
 * @param $files array(string)
 */
 function elvde_civicrm_xmlMenu(&$files) {
  _elvde_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install().
 */
 function elvde_civicrm_install() {
  require_once "CRM/Core/DAO.php";
  return _elvde_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall().
 */
 function elvde_civicrm_uninstall() {
  require_once "CRM/Core/DAO.php";
  return _elvde_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable().
 */
 function elvde_civicrm_enable() {
 return _elvde_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable().
 */
 function elvde_civicrm_disable() {
  return _elvde_civix_civicrm_disable();
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
 function elvde_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _elvde_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_validate().
 *
 * @param $formName - the name of the form
 * @param $form - reference to the form object
 */
 function elvde_civicrm_validate($formName, &$fields, &$files, &$form) {

   if ( $formName == 'CRM_Contribute_Form_Contribution_Main'  || $formName == 'CRM_Event_Form_Registration_Register' || $formName == 'CRM_Contribute_Form_Contribution' ) {
            if (isset($form->_paymentProcessor['payment_processor_type']) && $form->_paymentProcessor['payment_processor_type'] == 'Novalnet Direct Debit German') {

          $_SESSION['elvde']['account_holder']   = isset($form->_submitValues['nn_de_account_holder'])?$form->_submitValues['nn_de_account_holder']:'';
          $_SESSION['elvde']['account_number']   = isset($form->_submitValues['nn_de_bank_account'])?$form->_submitValues['nn_de_bank_account']:'';
          $_SESSION['elvde']['bank_code']        = isset($form->_submitValues['nn_de_bank_code'])?$form->_submitValues['nn_de_bank_code']:'';
          $_SESSION['elvde']['acdc_control']     = isset($form->_submitValues['nn_de_acdc'])?$form->_submitValues['nn_de_acdc']:'';
          $_SESSION['elvde']['sessionid'] = session_id();
           }
       }
   }

// Implemenation of hook_civicrm_buildForm()
function elvde_civicrm_buildForm( $formName, &$form ) {

 if ( $formName == 'CRM_Contribute_Form_Contribution_Main'  || $formName == 'CRM_Event_Form_Registration_Register' || $formName == 'CRM_Contribute_Form_Contribution' ) {
     if (isset($form->_paymentProcessor['payment_processor_type']) && $form->_paymentProcessor['payment_processor_type'] == 'Novalnet Direct Debit German') {
    $form->_paymentFields['nn_de_account_holder'] = array(
          'htmlType' => 'text',
          'name' => 'nn_de_account_holder',
          'title' => ts('Account holder'),
          'cc_field' => TRUE,
          'attributes' => array('size' => 20, 'maxlength' => 34, 'autocomplete' => 'off',  'placeholder'     =>ts('Account holder'),  'value'           => (isset($_SESSION['elvde']['account_holder'])?$_SESSION['elvde']['account_holder']:''),),
    );
    $form->_paymentFields['nn_de_bank_account'] = array(
          'htmlType' => 'text',
          'name' => 'nn_de_bank_account',
          'title' => ts('Account Number'),
          'cc_field' => TRUE,
          'attributes' => array('size' => 20, 'maxlength' => 34, 'autocomplete' => 'off', 'placeholder' => ts('Account Number'), 'value'    => (isset($_SESSION['elvde']['account_number'])?$_SESSION['elvde']['account_number']:''),),
    );
    $form->_paymentFields['nn_de_bank_code'] = array(
          'htmlType' => 'text',
          'name' => 'nn_de_bank_code',
          'title' => ts('Bank code'),
          'cc_field' => TRUE,
          'attributes' => array('size' => 20, 'maxlength' => 34, 'autocomplete' => 'off', 'placeholder' => ts('Bank code'),'value' => (isset($_SESSION['elvde']['bank_code'])?$_SESSION['elvde']['bank_code']:''),),
    );
  $req_type = (getenv('HTTPS') == '1' || getenv('HTTPS') == 'on') ? 'https://' : 'http://';
    $acdc_logo_link = array('title' => ts('The ACDC-Check Accepted'),
            'href' => $req_type . 'www.novalnet.de/img/acdc_info.png',
            'attributes' => array(
            'class' => 'cls-acdc',
            'target' => '_blank')
    );
    $form->_paymentFields['nn_de_acdc'] = array(
          'htmlType' => 'checkbox',
          'name' => 'nn_de_acdc',
          'title' => l($acdc_logo_link['title'], $acdc_logo_link['href'], $acdc_logo_link),
          'cc_field' => TRUE,
          'attributes' => '',
    );
    $useRequired = FALSE;
    foreach ($form->_paymentFields as $name => $field) {
        if (isset($field['cc_field']) &&
            $field['cc_field']
        ) {
            $form->add($field['htmlType'],
            $field['name'],
            $field['title'],
            $field['attributes']

          );
        }
      }
      $config                   = CRM_Core_Config::singleton();
       $refill = (isset($config->nn_de_auto_refill) &&  isset($_SESSION['elvde']['sessionid']) && $_SESSION['elvde']['sessionid'] == session_id()) ? $config->nn_de_auto_refill : '0';
       $config = CRM_Core_Config::singleton();
       $template = CRM_Core_Smarty::singleton();
       $template->assign( 'de',  $form);
       $template->assign( 'nnacdc',  isset($config->nn_de_acdc)?$config->nn_de_acdc:0);
       $template->assign( 'nn_elvde_name',  $form->_paymentProcessor['name']);
       $template->assign( 'de_refill',  $refill);

    }
  }
 }

/**
 * Implementation of hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 */
 function elvde_civicrm_managed(&$entities) {
  $entities[] = array(
    'module' => 'org.novalnet.payment.elvde',
    'name'   => 'Novalnet Direct Debit German',
    'entity' => 'PaymentProcessorType',
    'params' => array(
      'version'        => 3,
      'name'           => 'Novalnet Direct Debit German',
      'title'          => 'Novalnet Direct Debit German',
      'description'    => 'Novalnet Direct Debit German Payment Processor',
      'class_name'     => 'Payment_elvde',
      'billing_mode'   => 'notify',
      'user_name_label'=> 'Novalnet Merchant ID',
      'password_label' => 'Novalnet Merchant Authorisation code',
      'signature_label'=> 'Novalnet Product ID',
      'subject_label'  => 'Novalnet Tariff ID',
      'is_recur'       => 0,
      'payment_type'   => 2
    ),
  );

  return _elvde_civix_civicrm_managed($entities);
}
