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

class CRM_Admin_Form_Setting_novalnetgatewayForm extends CRM_Admin_Form_Setting
{
    public function buildQuickForm( ) {
        CRM_Utils_System::setTitle(ts('Novalnet Settings'));
        $status       = array('1'=> ts('Yes'),'0'=>ts('No'));
        $manuallimit_title_text = ts('Manual checking of order, above amount in cents (Note: this is a onhold booking, needs your manual verification and activation)');
        $tariffid_2_title = ts('Second Tariff ID for manual check condition');
        $productid_2_title = ts('Second Product ID for manual check condition');
        $reference_one = ts('Transaction reference 1');
        $reference_two = ts('Transaction reference 2');

        $this->addElement('text',
                          'nn_password',
                          ts('Novalnet Payment access key')
                          );
        $this->addElement('select',
                          'nn_enable_logo',
                          ts('Enable Novalnet Logo'),
                          $status
                          );
        $this->addElement('text',
                          'nn_referrer_id',
                          ts('Referrer ID')
                          );
        $this->addElement('text',
                          'nn_inv_duedate',
                          ts('Payment period in days')
                          );
        $this->addElement('text',
                          'nn_inv_ref_one',
                          $reference_one
                          );
        $this->addElement('text',
                          'nn_inv_ref_two',
                          $reference_two
                          );
        $this->addElement('text',
                          'nn_cc_manualamount',
                          $manuallimit_title_text
                          );
       $this->addElement('text',
                          'nn_cc_productid2',
                          $productid_2_title
                          );
        $this->addElement('text',
                          'nn_cc_tariffid2',
                          $tariffid_2_title
                          );
        $this->addRadio('nn_cc_secure_active', ts('3D Secure(note : this has to be set up at Novalnet first. Please contact support@novalnet.de, in case you wish this.)'), $status);
        $this->addRadio('nn_cc_auto_refill', ts('Auto refill the payment data entered in payment page'), $status);
        $this->addElement('text',
                          'nn_cc_ref_one',
                          $reference_one
                          );
        $this->addElement('text',
                          'nn_cc_ref_two',
                          $reference_two
                          );
        $this->addRadio('nn_cc_amexlogo_active', ts('Enable AMEX Logo'), $status);

        $this->addElement('text',
                          'nn_at_manualamount',
                          $manuallimit_title_text
                          );
       $this->addElement('text',
                          'nn_at_productid2',
                          $productid_2_title
                          );
        $this->addElement('text',
                          'nn_at_tariffid2',
                          $tariffid_2_title
                          );
        $this->addRadio('nn_at_auto_refill', ts('Auto refill the payment data entered in payment page'), $status);
        $this->addElement('text',
                          'nn_at_ref_one',
                          $reference_one
                          );
        $this->addElement('text',
                          'nn_at_ref_two',
                          $reference_two
                          );
        $this->addElement('text',
                          'nn_de_manualamount',
                          $manuallimit_title_text
                          );
       $this->addElement('text',
                          'nn_de_productid2',
                          $productid_2_title
                          );
        $this->addElement('text',
                          'nn_de_tariffid2',
                          $tariffid_2_title
                          );
        $this->addElement('select',
                          'nn_de_acdc',
                          ts('Enable Account Check Direct Control (ACDC)'),
                          $status
                          );
        $this->addRadio('nn_de_auto_refill', ts('Auto refill the payment data entered in payment page'), $status);
        $this->addElement('text',
                          'nn_de_ref_one',
                          $reference_one
                          );
        $this->addElement('text',
                          'nn_de_ref_two',
                          $reference_two
                          );
          $this->addElement('text',
                          'nn_sepa_manualamount',
                          $manuallimit_title_text
                          );

        $this->addElement('text',
                          'nn_sepa_productid2',
                          $productid_2_title
                          );
        $this->addElement('text',
                          'nn_sepa_tariffid2',
                          $tariffid_2_title
                          );
         $options = array('DIRECT_DEBIT_SEPA' => ts('SEPA Direct Debit: Real time processing with online SEPA Direct Debit Mandate (electronic transmission).').'<br>',
         'DIRECT_DEBIT_SEPA_SIGNED' => ts('SEPA Direct Debit SIGNED: Please note that in this process first only the order will be processed.
').'<br><span class="description">'.ts('On successful order a link for downloading the SEPA Mandate will be sent to the end user who has to print out and complete the form and send it per post to you. On receipt of this SEPA mandate, you need to confirm the mandate inside the admin portal giving the mandate date. Only then this transaction will be executed through Novalnet.')
              );

        $this->addRadio('nn_sepa_payment_type', ts('SEPA Payment Type'), $options);
		 
        $this->addRadio('nn_sepa_auto_refill', ts('Auto refill the payment data entered in payment page'), $status);
        $this->addElement('text',
                          'nn_sepa_due_date',
                          ts('SEPA Payment duration in days')
                          );
        $this->addElement('text',
                          'nn_sepa_ref_one',
                          $reference_one
                          );
        $this->addElement('text',
                          'nn_sepa_ref_two',
                          $reference_two
                          );
         $this->addElement('text',
                          'nn_prepayment_ref_one',
                          $reference_one
                          );
        $this->addElement('text',
                          'nn_prepayment_ref_two',
                          $reference_two
                          );
        $this->addElement('text',
                          'nn_ideal_ref_one',
                          $reference_one
                          );
        $this->addElement('text',
                          'nn_ideal_ref_two',
                          $reference_two
                          );
        $this->addElement('text',
                          'nn_instant_ref_one',
                          $reference_one
                          );
        $this->addElement('text',
                          'nn_instant_ref_two',
                          $reference_two
                          );
        $this->addElement('text',
                          'nn_safety_ref_one',
                          $reference_one
                          );
        $this->addElement('text',
                          'nn_safety_ref_two',
                          $reference_two
                          );
        $this->addElement('text',
                          'nn_paypal_user',
                          ts('PayPal API User Name')
                          );
       $this->addElement('text',
                          'nn_paypal_pwd',
                          ts('PayPal API Password')
                          );
        $this->addElement('text',
                          'nn_paypal_sig',
                          ts('PayPal API Signature')
                          );
        $this->addElement('text',
                          'nn_paypal_ref_one',
                          $reference_one
                          );
        $this->addElement('text',
                          'nn_paypal_ref_two',
                          $reference_two
                          );
        $this->addElement('select',
                          'nn_callback_testmode',
                          ts('Enable Test Mode'),
                          $status
                          );
        $this->addElement('select',
                          'nn_callback_debug',
                          ts('Enable Debug Mode'),
                          $status
                          );
        $this->addElement('select',
                          'nn_callback_sendmail',
                          ts('E-mail Send Option'),
                          $status
                          );

       $this->addElement('text',
                          'nn_callback_frommail',
                          ts('E-mail From Address')
                          );
        $this->addElement('text',
                          'nn_callback_tomail',
                          ts('E-mail To Address')
                          );

        $this->addElement('textarea',
                          'nn_callback_mailbody',
                          ts('E-mail Body')

                          );
       $this->addElement('text',
                          'nn_callback_mailcc',
                          ts('CC')
                          );
        $this->addElement('text',
                          'nn_callback_mailbcc',
                          ts('BCC')
                          );

        parent::buildQuickForm();
    }

 
}
