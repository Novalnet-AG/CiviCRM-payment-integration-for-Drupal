<?php
#########################################################
#                                                       #
#  Novalnet payment method class                        #
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
#  Script : novalnetgateway.php                         #
#                                                       #
#########################################################
require_once 'novalnetgateway.civix.php';
require_once  'CRM/Core/Payment/novalnet/novalnet.php';
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
   ( 10, '102', 'Novalnet Direct Debit Austria', 1, 102,'Novalnet Direct Debit Austria'),
   ( 10, '103', 'Novalnet Direct Debit German', 1, 103,'Novalnet Direct Debit German'),
   ( 10, '104', 'Novalnet Credit Card', 1, 104,'Novalnet Credit Card'),
   ( 10, '106', 'Novalnet iDEAL', 1, 106,'Novalnet iDEAL'),
   ( 10, '107', 'Novalnet Instant Bank Transfer', 1, 107,'Novalnet Instant Bank Transfer'),
   ( 10, '108', 'Novalnet PayPal', 1, 108,'Novalnet PayPal'),
   ( 10, '109', 'Novalnet Direct Debit SEPA', 1, 109,'Novalnet Direct Debit SEPA'),
   ( 10, '110', 'Novalnet SafetyPay', 1, 110,'Novalnet SafetyPay')");


  return _novalnetgateway_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall().
 */
function novalnetgateway_civicrm_uninstall() {
  require_once "CRM/Core/DAO.php";
  CRM_Core_DAO::executeQuery("DELETE from  civicrm_option_value where name LIKE 'Novalnet%'");
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

function novalnetgateway_civicrm_navigationMenu( &$params ) {

   //  Get the maximum key of $params
    $maxKey = ( max ( array_keys($params) ) );
     $params[$maxKey+1] = array (
                       'attributes' => array (
                                              'label'      => ts('Novalnet Settings'),
                                              'name'       => ts('Novalnet Settings'),
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
                                                'label'      => ts('Novalnet Additional Configuration'),
                                                'name'       => ts('Novalnet Additional Configuration'),
                                                'url'        => 'civicrm/admin/setting/novalnet',
                                                'permission' => 'administer CiviCRM',
                                                'operator'   => null,
                                                'separator'  => 1,
                                                'parentID'   => $maxKey+1,
                                                'navID'      => 1,
                                                'active'     => 1
                                              )),
                                        '2' => array (
                                            'attributes' => array (
                                                'label'      => ts('Novalnet Mandate Confirmation'),
                                                'name'       => ts('Novalnet Mandate Confirmation'),
                                                'url'        => 'civicrm/admin/setting/novalnet/mandateconfirm',
                                                'permission' => 'administer CiviCRM',
                                                'operator'   => null,
                                                'separator'  => 1,
                                                'parentID'   => $maxKey+1,
                                                'navID'      => 1,
                                                'active'     => 1
                                                )),
                                          )
                                );
}

