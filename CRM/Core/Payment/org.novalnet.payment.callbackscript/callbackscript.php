<?php
#########################################################
#                                                       #
#  Callbackscript payment method class                  #
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
#  Script : callbackscript.php                          #
#                                                       #
#########################################################
require_once 'callbackscript.civix.php';
require_once  'CRM/Core/Payment/novalnet/novalnet.php';
/**
 * Implementation of hook_civicrm_config().
 */
function callbackscript_civicrm_config(&$config) {
  _callbackscript_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu().
 *
 * @param $files array(string)
 */
function callbackscript_civicrm_xmlMenu(&$files) {
  _callbackscript_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install().
 */
function callbackscript_civicrm_install() {
  require_once "CRM/Core/DAO.php";
  CRM_Core_DAO::executeQuery("CREATE TABLE IF NOT EXISTS `novalnet_callback` (
                      `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                      `order_id` varchar(200) CHARACTER SET utf8 DEFAULT NULL,
                      `callback_amount` int(11) NOT NULL,
                      `reference_tid` bigint(20) NOT NULL,
                      `callback_datetime` datetime NOT NULL,
                      `callback_tid` bigint(20) DEFAULT NULL,
                      PRIMARY KEY (`id`),
                      KEY `order_id` (`order_id`),
                      KEY `callback_amount` (`callback_amount`),
                      KEY `reference_tid` (`reference_tid`),
                      KEY `callback_datetime` (`callback_datetime`),
                      KEY `callback_tid` (`callback_tid`)
                    ) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;");
  return _callbackscript_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall().
 */
function callbackscript_civicrm_uninstall() {
  require_once "CRM/Core/DAO.php";
  return _callbackscript_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable().
 */
function callbackscript_civicrm_enable() {
 return _callbackscript_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable().
 */
function callbackscript_civicrm_disable() {
  return _callbackscript_civix_civicrm_disable();
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
function callbackscript_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _callbackscript_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 */
function callbackscript_civicrm_managed(&$entities) {
  return _callbackscript_civix_civicrm_managed($entities);
}
