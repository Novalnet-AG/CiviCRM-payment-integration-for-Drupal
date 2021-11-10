<?php
#########################################################
#                                                       #
#  Direct Debit Austria payment method class            #
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
#  Script : elvat.php                                   #
#                                                       #
#########################################################
require_once  'CRM/Core/Payment/novalnet/novalnet.php';
require_once  'elvat.civix.php';

/**
 * Implementation of hook_civicrm_config().
 */
 function elvat_civicrm_config(&$config) {
   _elvat_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu().
 *
 * @param $files array(string)
 */
 function elvat_civicrm_xmlMenu(&$files) {
  _elvat_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install().
 */
 function elvat_civicrm_install() {
  require_once "CRM/Core/DAO.php";
  return _elvat_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall().
 */
 function elvat_civicrm_uninstall() {
  require_once "CRM/Core/DAO.php";
  return _elvat_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable().
 */
 function elvat_civicrm_enable() {
   return _elvat_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable().
 */
  function elvat_civicrm_disable() {
  return _elvat_civix_civicrm_disable();
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
  function elvat_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _elvat_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_validate().
 *
 * @param $formName - the name of the form
 * @param $form - reference to the form object
 */
  function elvat_civicrm_validate($formName, &$fields, &$files, &$form) {
    if ( $formName == 'CRM_Contribute_Form_Contribution_Main'  || $formName == 'CRM_Event_Form_Registration_Register' || $formName == 'CRM_Contribute_Form_Contribution' ) {
            if (isset($form->_paymentProcessor['payment_processor_type']) && $form->_paymentProcessor['payment_processor_type'] == 'Novalnet Direct Debit Austria') {
          $_SESSION['elvat']['account_holder']  = isset($form->_submitValues['nn_account_holder'])?trim($form->_submitValues['nn_account_holder']):'';
          $_SESSION['elvat']['account_number']  = isset($form->_submitValues['nn_bank_account'])?trim($form->_submitValues['nn_bank_account']):'';
          $_SESSION['elvat']['bank_code']       = isset($form->_submitValues['nn_bank_code'])?trim($form->_submitValues['nn_bank_code']):'';
			$_SESSION['elvat']['sessionid'] = session_id();
           }
       }
     return true;
   }


// Implemenation of hook_civicrm_buildForm()
  function elvat_civicrm_buildForm( $formName, &$form ) {
    if ( $formName == 'CRM_Contribute_Form_Contribution_Main'  || $formName == 'CRM_Event_Form_Registration_Register' || $formName == 'CRM_Contribute_Form_Contribution' ) {
        if (isset($form->_paymentProcessor['payment_processor_type']) && $form->_paymentProcessor['payment_processor_type'] == 'Novalnet Direct Debit Austria') {
          $config = CRM_Core_Config::singleton();

    $form->_paymentFields['nn_account_holder'] = array(
          'htmlType' => 'text',
          'name' => 'nn_account_holder',
          'title' => ts('Account holder'),
          'cc_field' => TRUE,
          'attributes' => array(
                          'size'            => 20,
                          'maxlength'       => 34,
                          'autocomplete'    => 'off',
                          'placeholder'     =>ts('Account holder'),
                          'value'           => (isset($_SESSION['elvat']['account_holder'])?$_SESSION['elvat']['account_holder']:''),
                          ),

    );
    $form->_paymentFields['nn_bank_account'] = array(
          'htmlType' => 'text',
          'name' => 'nn_bank_account',
          'title' => ts('Account Number'),
          'cc_field' => TRUE,
          'attributes' => array(
                        'size' => 20,
                        'maxlength' => 34,
                        'autocomplete' => 'off',
                        'placeholder' => ts('Account Number'),
                        'value'    => (isset($_SESSION['elvat']['account_number'])?$_SESSION['elvat']['account_number']:''),
                        ),
    );
    $form->_paymentFields['nn_bank_code'] = array(
          'htmlType' => 'text',
          'name' => 'nn_bank_code',
          'title' => ts('Bank code'),
          'cc_field' => TRUE,
          'attributes' => array(
                          'size' => 20,
                          'maxlength' => 34,
                          'autocomplete' => 'off',
                          'placeholder' => ts('Bank code'),
                          'value' => (isset($_SESSION['elvat']['bank_code'])?$_SESSION['elvat']['bank_code']:''),
                           ),
    );

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
      $refill = (isset($config->nn_at_auto_refill) && $config->nn_at_auto_refill =='1'&& isset($_SESSION['elvat']['sessionid']) && $_SESSION['elvat']['sessionid'] == session_id())? '1': '0';
      $template = CRM_Core_Smarty::singleton();
      $template->assign( 'at',  $form);
      $template->assign( 'nn_elvat_name',  $form->_paymentProcessor['name']);
      $template->assign( 'at_refill',  $refill);
    }
  }
 }
/**
 * Implementation of hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 */
function elvat_civicrm_managed(&$entities) {
  $entities[] = array(
    'module' => 'org.novalnet.payment.elvat',
    'name'   => 'Direct Debit Austria',
    'entity' => 'PaymentProcessorType',
    'params' => array(
      'version'        => 3,
      'name'           => 'Novalnet Direct Debit Austria',
      'title'          => 'Novalnet Direct Debit Austria',
      'description'    => 'Novalnet Direct Debit Austria Payment Processor',
      'class_name'     => 'Payment_elvat',
      'billing_mode'   => 'notify',
      'user_name_label'=> 'Novalnet Merchant ID',
      'password_label' => 'Novalnet Merchant Authorisation code',
      'signature_label'=> 'Novalnet Product ID',
      'subject_label'  => 'Novalnet Tariff ID',
      'is_recur'       => 0,
      'payment_type'   => 2
    ),
  );

  return _elvat_civix_civicrm_managed($entities);
}
