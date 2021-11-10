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
require_once 'CRM/Admin/Form/Setting.php';
require_once 'CRM/Core/BAO/CustomField.php';

class CRM_Admin_Form_Setting_novalnetMandateConfirm extends CRM_Core_Form {


    public function buildQuickForm( ) {
      $template           = CRM_Core_Smarty::singleton();
      $mandateorders = $this->getMandatePendingOrders();
      if ($mandateorders)
        $template->assign( 'mandateorders', $mandateorders);
      $this->addDate('nn_mandate_date', ts('Mandate Signature Date'), 'TRUE', array('placeholder'=>'DD-MM-YYYY'));
      $this->addButtons(array(array(
                          'type'      => 'submit',
                          'name'      => ts('Mandate Confirmation'),
                          'isDefault' => TRUE,
                        )));
    parent::buildQuickForm();
  }

  public function postProcess() {
    $template           = CRM_Core_Smarty::singleton();
    $selected_order     = trim(CRM_Utils_Array::value('selectorder', $_REQUEST));
    $nn_mandate_date    = trim(CRM_Utils_Array::value('nn_mandate_date', $_REQUEST));
    if (!$selected_order) {
      $template->assign( 'nnerror', ts('Please select your order'));
      return '';
    }
    if(empty($nn_mandate_date) || ($nn_mandate_date && !$this->checkMandateDate($nn_mandate_date))) {
        $template->assign( 'nnerror', ts('Mandate signature date is invalid'));
        return '';
    }
    $dao = CRM_Core_DAO::executeQuery("SELECT id, novalnet_tid, user_id, amount, invoice_id, nnconfig FROM novalnet_sepa_orders WHERE id=$selected_order");
    $dao->fetch();
    $tid      = $dao->novalnet_tid;
    $order_id      = $dao->invoice_id;
    $uid      = $dao->user_id;
    $config   = unserialize($dao->nnconfig);
    $vendor   = $config['vendor'];
    $authcode = $config['authcode'];
    $nn_mandate_date  = date('Y-m-d',strtotime($nn_mandate_date));
    if ($vendor !='' && $authcode !='' && $tid !='' && $uid !='' && $nn_mandate_date !='' ) {
      $xml = "<?xml version='1.0' encoding='UTF-8'?>
              <nnxml>
              <info_request>
              <vendor_id>$vendor</vendor_id>
              <vendor_authcode>$authcode</vendor_authcode>
              <request_type>MANDATE_CONFIRMATION</request_type>
              <mandate_signature_date>$nn_mandate_date</mandate_signature_date>
              <tid>$tid</tid>
              <order_no>$order_id</order_no>
              <customer_no>$uid</customer_no >
              </info_request>
              </nnxml>";
      $options = array(
        'headers'   => array('Content-Type' => 'application/x-www-form-urlencoded'),
        'method'    => 'POST',
        'data'      => $xml
      );

      $host         = 'https://payport.novalnet.de/nn_infoport.xml';
      $response     = drupal_http_request($host, $options);
      $xml_response = (array)simplexml_load_string($response->data);

      if ($xml_response['status'] == 100) {
        $mandate_sign_date  = CRM_Utils_Date::customFormat($nn_mandate_date);
        $comment = "<br>" .ts('Novalnet Mandate confirmed') . "<br>" . ts('Mandate Signature Date') . ' : ' . $mandate_sign_date;
        $dao = CRM_Core_DAO::executeQuery("UPDATE novalnet_sepa_orders SET status=1 WHERE id=$selected_order");
        $dao = CRM_Core_DAO::executeQuery("UPDATE civicrm_contribution SET contribution_status_id=1 WHERE invoice_id='$order_id'");
        $dao = CRM_Core_DAO::executeQuery("SELECT id FROM civicrm_contribution WHERE invoice_id='$order_id'");
        $dao->fetch();
        if($dao->id) {
            $dao = CRM_Core_DAO::executeQuery("UPDATE civicrm_note SET note=CONCAT(note, '$comment') WHERE entity_id=$dao->id and entity_table ='civicrm_contribution'");

        }
        $template->assign( 'nnsuccess', $xml_response['status_message']);
        $mandateorders = $this->getMandatePendingOrders();
        $template->assign( 'mandateorders', $mandateorders);
      }
      else {
         $template->assign( 'nnerror', $xml_response['status_message']);
      return '';
      }
    } else {
      $template->assign( 'nnerror', ts('Basic parameter not valid'));
      return '';
    }
  }

  function checkMandateDate( $value ) {
    $value = trim($value);
    $time = strtotime($value);
    return $is_valid = date('d-m-Y', $time) == $value;
  }

  function getMandatePendingOrders()  {
     $dao = CRM_Core_DAO::executeQuery("SELECT id,novalnet_tid,user_id,amount,invoice_id FROM novalnet_sepa_orders WHERE status=0");
$mandateorders =array();
      while ($dao->fetch()) {
        $mandateorders[$dao->id] = array(
          'tid' => $dao->novalnet_tid,
          'uid' => $dao->user_id,
          'amount' => CRM_Utils_Money::format(($dao->amount/100), 'EUR'),
          'invoice_id' => $dao->invoice_id,
          );
      }
    return $mandateorders;
  }
}
