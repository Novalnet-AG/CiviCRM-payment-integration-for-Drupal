<div class="crm-block crm-form-block crm-mysettings-form-block">
  <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div>
    <table class="form-layout">
      <tr class="crm-mysettings-form-block-specialty">
        <div id = "generalexpand" class="crm-accordion-wrapper crm-address-accordion collapsed">
          <div id ="generaltitle" class="crm-accordion-header"><b>{ts}Novalnet Global Configuration{/ts}</b></div>
          <table id ='generaldetails' class="novalnet-header">
            <tr><td class="label" style="text-align:left">{$form.nn_vendor.label}</td>
              <td>{$form.nn_vendor.html}<br><span class="description">{ts}Enter Novalnet merchant ID{/ts}</td>
            </tr>
            <tr><td class="label" style="text-align:left">{$form.nn_authcode.label}</td>
              <td>{$form.nn_authcode.html}<br><span class="description">{ts}Enter Novalnet authentication code{/ts}</td>
            </tr>
            <tr><td class="label" style="text-align:left">{$form.nn_product.label}</td>
              <td>{$form.nn_product.html}<br><span class="description">{ts}Enter Novalnet project ID{/ts}</td>
            </tr>
            <tr><td class="label" style="text-align:left">{$form.nn_tariff.label}</td>
              <td>{$form.nn_tariff.html}<br><span class="description">{ts}Enter Novalnet tariff ID{/ts}</td>
            </tr>
            <tr><td class="label" style="text-align:left">{$form.nn_password.label}</td>
              <td>{$form.nn_password.html}<br><span class="description">{ts}Enter the Novalnet payment access key{/ts}</td>
            </tr>
            <tr><td class="label" style="text-align:left">{$form.nn_subscription_tariff_id.label}</td>
              <td>{$form.nn_subscription_tariff_id.html}<br><span class="description">{ts}Enter Novalnet subscription tariff ID{/ts}</td>
            </tr>
            <tr><td class="label" style="text-align:left">{$form.nn_subs_cancel_status.label}</td>
              <td>{$form.nn_subs_cancel_status.html}</td>
            </tr>
            <tr><td class="label" style="text-align:left">{$form.nn_referrer_id.label}</td>
              <td>{$form.nn_referrer_id.html}<br><span class="description">{ts}Enter the referrer ID of the person/company who recommended you Novalnet{/ts}</td>
            </tr>

            <tr><td class="label" style="text-align:left">{$form.nn_manualamount.label} </td>
                <td>{$form.nn_manualamount.html}<br><span class="description">{ts}In case the order amount exceeds mentioned limit, the transaction will be set on hold till your confirmation of transaction{/ts}</td>
            </tr>

            <tr><td><b>{ts}Logos display management{/ts}</b></td></tr>
			<tr><td class="label" style="text-align:left">{$form.novalnet_pay_logo.label}</td>
              <td>{$form.novalnet_pay_logo.html}<br><span class="description">{ts}The payment method logo will be displayed on the checkout page{/ts}</td>
            </tr>

           <tr><td><b>{ts}Merchant script management{/ts}</b></td></tr>
              <tr>
                <td class="label" style="text-align:left"> {$form.nn_callback_testmode.label}</td>
                <td>{$form.nn_callback_testmode.html}</td>
              </tr>
              <tr>
                <td class="label" style="text-align:left">{$form.nn_callback_debug.label} </td>
                <td> {$form.nn_callback_debug.html}<br><span class="description">{ts}Set the debug mode to execute the merchant script in debug mode{/ts}</td>
              </tr>
              <tr>
                <td class="label" style="text-align:left">{$form.nn_callback_sendmail.label} </td>
                <td>{$form.nn_callback_sendmail.html}</td>
              </tr>

              <tr>
                <td class="label" style="text-align:left">{$form.nn_callback_frommail.label} </td>
                <td>{$form.nn_callback_frommail.html}<br><span class="description">{ts}E-Mail address of the sender{/ts}</td>
              </tr>
              <tr>
                <td class="label" style="text-align:left">{$form.nn_callback_tomail.label} </td>
                <td>{$form.nn_callback_tomail.html}<br><span class="description">{ts}E-Mail address of the recipient{/ts}</td>
              </tr>
              <tr>
                <td class="label" style="text-align:left">{$form.nn_callback_mailbcc.label} </td>
                <td>{$form.nn_callback_mailbcc.html}<br><span class="description">{ts}E-Mail address of the recipient for BCC{/ts}</td>
              </tr>
              <tr>
                <td class="label" style="text-align:left">{$form.nn_notify_url.label}</td>
                <td>{$form.nn_notify_url.html}<br><span class="description">{ts}The notification URL is used to keep your database/system actual and synchronizes with the Novalnet transaction status.{/ts}</td>
            </tr>
            </table>
      </tr>
      <tr class="crm-mysettings-form-block-specialty">
      <div class="crm-accordion-wrapper crm-address-accordion collapsed">
          <div id ="invoicetitle" class="crm-accordion-header"><b>{ts}Novalnet Invoice{/ts}</b></div>
            <table id ="invoicedetails" class="novalnet-header">
              <tr><td class="label" style="text-align:left">{$form.nn_inv_testmode.label}</td>
                  <td>{$form.nn_inv_testmode.html}<br><span class="description">{ts}The payment will be processed in the test mode therefore amount for this transaction will not be charged{/ts}</span></td>
              </tr>
              <tr><td class="label" style="text-align:left">{$form.nn_inv_duedate.label}</td>
                  <td>{$form.nn_inv_duedate.html}<br><span class='description'>{ts}Enter the number of days to transfer the payment amount to Novalnet (must be greater than 7 days). In case if the field is empty, 14 days will be set as due date by default{/ts}</td>
              </tr>
               <tr><td class="label" style="text-align:left">{$form.novalnet_invoice_cont_status.label}</td>
                <td>{$form.novalnet_invoice_cont_status.html}</td>
              </tr>
               <tr><td class="label" style="text-align:left">{$form.novalnet_invoice_cont_cb_status.label}</td>
                <td>{$form.novalnet_invoice_cont_cb_status.html}</td>
              </tr>
               <tr><td class="label" style="text-align:left">{$form.novalnet_invoice_ref_one.label}</td>
                <td>{$form.novalnet_invoice_ref_one.html}<br><span class="description">{ts}This reference will appear in your bank account statement{/ts}</td>
              </tr>
              <tr><td class="label" style="text-align:left">{$form.novalnet_invoice_ref_two.label}</td>
                <td>{$form.novalnet_invoice_ref_two.html}<br><span class="description">{ts}This reference will appear in your bank account statement{/ts}</td>
              </tr>
            <tr><td class="label" style="text-align:left">{$form.novalnet_invoice_payment_ref1.label}</td>
              <td>{$form.novalnet_invoice_payment_ref1.html}</td>
            </tr>
            <tr><td class="label" style="text-align:left">{$form.novalnet_invoice_payment_ref2.label}</td>
              <td>{$form.novalnet_invoice_payment_ref2.html}</td>
            </tr>
            <tr><td class="label" style="text-align:left">{$form.novalnet_invoice_payment_ref3.label}</td>
              <td>{$form.novalnet_invoice_payment_ref3.html}</td>
            </tr>
            </table>
        </div>
      </tr>
      <tr class="crm-mysettings-form-block-specialty">
      <div class="crm-accordion-wrapper crm-address-accordion collapsed">
          <div id ="prepaymenttitle" class="crm-accordion-header"><b>{ts}Novalnet Prepayment{/ts}</b></div>
            <table id ="prepaymentdetails" class="novalnet-header">
            <tr><td class="label" style="text-align:left">{$form.nn_prepayment_testmode.label}</td>
                  <td>{$form.nn_prepayment_testmode.html}<br><span class="description">{ts}The payment will be processed in the test mode therefore amount for this transaction will not be charged{/ts}</span></td>
              </tr>
              <tr><td class="label" style="text-align:left">{$form.novalnet_prepayment_cont_status.label}</td>
                <td>{$form.novalnet_prepayment_cont_status.html}</td>
              </tr>
               <tr><td class="label" style="text-align:left">{$form.novalnet_prepayment_cont_cb_status.label}</td>
                <td>{$form.novalnet_prepayment_cont_cb_status.html}</td>
              </tr>
               <tr><td class="label" style="text-align:left">{$form.novalnet_prepayment_ref_one.label}</td>
                <td>{$form.novalnet_prepayment_ref_one.html}<br><span class="description">{ts}This reference will appear in your bank account statement{/ts}</td>
              </tr>
              <tr><td class="label" style="text-align:left">{$form.novalnet_prepayment_ref_two.label}</td>
                <td>{$form.novalnet_prepayment_ref_two.html}<br><span class="description">{ts}This reference will appear in your bank account statement{/ts}</td>
              </tr>
            <tr><td class="label" style="text-align:left">{$form.novalnet_prepayment_payment_ref1.label}</td>
              <td>{$form.novalnet_prepayment_payment_ref1.html}</td>
            </tr>
            <tr><td class="label" style="text-align:left">{$form.novalnet_prepayment_payment_ref2.label}</td>
              <td>{$form.novalnet_prepayment_payment_ref2.html}</td>
            </tr>
            <tr><td class="label" style="text-align:left">{$form.novalnet_prepayment_payment_ref3.label}</td>
              <td>{$form.novalnet_prepayment_payment_ref3.html}</td>
            </tr>
            </table>
        </div>
      </tr>
      <tr class="crm-mysettings-form-block-specialty">
        <div class="crm-accordion-wrapper crm-address-accordion collapsed">
          <div id ="cctitle" class="crm-accordion-header"><b>{ts}Novalnet Credit Card{/ts}</b></div>
          <table id ="ccdetails" class="novalnet-header">
          <tr><td class="label" style="text-align:left">{$form.nn_cc_testmode.label}</td>
                  <td>{$form.nn_cc_testmode.html}<br><span class="description">{ts}The payment will be processed in the test mode therefore amount for this transaction will not be charged{/ts}</span></td>
              </tr>
            <tr>
              <td class="label" style="text-align:left">{$form.nn_cc_secure_active.label} </td>
              <td>{$form.nn_cc_secure_active.html}<br><span class="description">{ts}The 3D-Secure will be activated for credit cards. The issuing bank prompts the buyer for a password what, in turn, help to prevent a fraudulent payment. It can be used by the issuing bank as evidence that the buyer is indeed their card holder. This is intended to help decrease a risk of charge-back.{/ts}</td>
            </tr>
            <tr><td class="label" style="text-align:left">{$form.novalnet_cc_cont_status.label}</td>
                <td>{$form.novalnet_cc_cont_status.html}</td>
              </tr>
            <tr><td class="label" style="text-align:left">{$form.novalnet_cc_ref_one.label}</td>
                <td>{$form.novalnet_cc_ref_one.html}<br><span class="description">{ts}This reference will appear in your bank account statement{/ts}</td>
              </tr>
            <tr><td class="label" style="text-align:left">{$form.novalnet_cc_ref_two.label}</td>
                <td>{$form.novalnet_cc_ref_two.html}<br><span class="description">{ts}This reference will appear in your bank account statement{/ts}</td>
            </tr>
            <tr><td class="label" style="text-align:left">{$form.nn_cc_amexlogo_active.label}</td>
                <td>{$form.nn_cc_amexlogo_active.html}<br><span class="description">{ts}Display AMEX logo in checkout page{/ts}</td>
             </tr>
             <tr><td class="label" style="text-align:left">{$form.nn_cc_cartasilogo_active.label}</td>
				<td>{$form.nn_cc_cartasilogo_active.html}<br><span class="description">{ts}Display CartaSi logo in checkout page{/ts}</td>
             </tr>
             <tr><td class="label" style="text-align:left">{$form.nn_cc_maestrologo_active.label}</td>
				<td>{$form.nn_cc_maestrologo_active.html}<br><span class="description">{ts}Display Maestro logo in checkout page{/ts}</td>
             </tr>
        </table>
      </div>
      </tr>
      <tr class="crm-mysettings-form-block-specialty">
        <div class="crm-accordion-wrapper crm-address-accordion collapsed">
          <div id ="sepatitle" class="crm-accordion-header"><b>{ts}Novalnet Direct Debit SEPA{/ts}</b></div>
          <table id ="sepadetails" class="novalnet-header">
            <tr><td class="label" style="text-align:left">{$form.nn_sepa_testmode.label}</td>
                  <td>{$form.nn_sepa_testmode.html}<br><span class="description">{ts}The payment will be processed in the test mode therefore amount for this transaction will not be charged{/ts}</span></td>
            </tr>
            <tr id= 'nn_sepa_duedate'>
              <td class="label" style="text-align:left">{$form.nn_sepa_due_date.label} </td>
              <td>{$form.nn_sepa_due_date.html}<br><span class="description">{ts}Enter the number of days after which the payment should be processed (must be greater than 6 days){/ts}</td>
            </tr>
            <tr><td class="label" style="text-align:left">{$form.novalnet_sepa_cont_status.label}</td>
                <td>{$form.novalnet_sepa_cont_status.html}</td>
              </tr>
            <tr>
              <td class="label" style="text-align:left">{$form.nn_sepa_auto_refill.label} </td>
              <td>{$form.nn_sepa_auto_refill.html}<br><span class="description">{ts}The payment details will be filled automatically in the payment form during the checkout process{/ts}</td>
            </tr>
            <tr><td class="label" style="text-align:left">{$form.novalnet_sepa_ref_one.label}</td>
                <td>{$form.novalnet_sepa_ref_one.html}<br><span class="description">{ts}This reference will appear in your bank account statement{/ts}</td>
              </tr>
              <tr><td class="label" style="text-align:left">{$form.novalnet_sepa_ref_two.label}</td>
                <td>{$form.novalnet_sepa_ref_two.html}<br><span class="description">{ts}This reference will appear in your bank account statement{/ts}</td>
              </tr>
         </table>
        </div>
      </tr>
      <tr class="crm-mysettings-form-block-specialty">
        <div class="crm-accordion-wrapper crm-address-accordion collapsed">
          <div id ="paypaltitle" class="crm-accordion-header"><b>{ts}Novalnet PayPal{/ts}</b></div>
          <table id ="paypaldetails" class="novalnet-header">
            <tr><td class="label" style="text-align:left">{$form.nn_paypal_testmode.label}</td>
                  <td>{$form.nn_paypal_testmode.html}<br><span class="description">{ts}The payment will be processed in the test mode therefore amount for this transaction will not be charged{/ts}</span></td>
              </tr>
            <tr>
            <td class="label" style="text-align:left">{$form.nn_paypal_user.label} </td>
              <td>{$form.nn_paypal_user.html}<br><span class="description">{ts}Please enter your PayPal API username{/ts}</td>
            </tr>
            <tr>
              <td class="label" style="text-align:left">{$form.nn_paypal_pwd.label} </td>
              <td>{$form.nn_paypal_pwd.html}<br><span class="description">{ts}Please enter your PayPal API password{/ts}</td>
            </tr>
            <tr>
              <td class="label" style="text-align:left">{$form.nn_paypal_sig.label} </td>
              <td>{$form.nn_paypal_sig.html}<br><span class="description">{ts}Please enter your PayPal API signature{/ts}</td>
            </tr>
            <tr><td class="label" style="text-align:left">{$form.novalnet_paypal_pending_status.label}</td>
                <td>{$form.novalnet_paypal_pending_status.html}</td>
              </tr>
            <tr><td class="label" style="text-align:left">{$form.novalnet_paypal_cont_status.label}</td>
                <td>{$form.novalnet_paypal_cont_status.html}</td>
              </tr>
            <tr><td class="label" style="text-align:left">{$form.novalnet_paypal_ref_one.label}</td>
                <td>{$form.novalnet_paypal_ref_one.html}<br><span class="description">{ts}This reference will appear in your bank account statement{/ts}</td>
              </tr>
              <tr><td class="label" style="text-align:left">{$form.novalnet_paypal_ref_two.label}</td>
                <td>{$form.novalnet_paypal_ref_two.html}<br><span class="description">{ts}This reference will appear in your bank account statement{/ts}</td>
              </tr>
          </table>
        </div>
      </tr>
      <tr class="crm-mysettings-form-block-specialty">
      <div class="crm-accordion-wrapper crm-address-accordion collapsed">
          <div id ="idealtitle" class="crm-accordion-header"><b>{ts}Novalnet iDEAL{/ts}</b></div>
            <table id ="idealdetails" class="novalnet-header">
            <tr><td class="label" style="text-align:left">{$form.nn_ideal_testmode.label}</td>
                  <td>{$form.nn_ideal_testmode.html}<br><span class="description">{ts}The payment will be processed in the test mode therefore amount for this transaction will not be charged{/ts}</span></td>
              </tr>
            <tr><td class="label" style="text-align:left">{$form.novalnet_ideal_cont_status.label}</td>
                <td>{$form.novalnet_ideal_cont_status.html}</td>
              </tr>
               <tr><td class="label" style="text-align:left">{$form.novalnet_ideal_ref_one.label}</td>
                <td>{$form.novalnet_ideal_ref_one.html}<br><span class="description">{ts}This reference will appear in your bank account statement{/ts}</td>
              </tr>
              <tr><td class="label" style="text-align:left">{$form.novalnet_ideal_ref_two.label}</td>
                <td>{$form.novalnet_ideal_ref_two.html}<br><span class="description">{ts}This reference will appear in your bank account statement{/ts}</td>
              </tr>
            </table>
        </div>
      </tr>
      <tr class="crm-mysettings-form-block-specialty">
      <div class="crm-accordion-wrapper crm-address-accordion collapsed">
          <div id ="instanttitle" class="crm-accordion-header"><b>{ts}Novalnet Instant Bank Transfer{/ts}</b></div>
            <table id ="instantdetails" class="novalnet-header">
            <tr><td class="label" style="text-align:left">{$form.nn_instant_testmode.label}</td>
                  <td>{$form.nn_instant_testmode.html}<br><span class="description">{ts}The payment will be processed in the test mode therefore amount for this transaction will not be charged{/ts}</span></td>
              </tr>
            <tr><td class="label" style="text-align:left">{$form.novalnet_instant_cont_status.label}</td>
                <td>{$form.novalnet_instant_cont_status.html}</td>
              </tr>
               <tr><td class="label" style="text-align:left">{$form.novalnet_instant_ref_one.label}</td>
                <td>{$form.novalnet_instant_ref_one.html}<br><span class="description">{ts}This reference will appear in your bank account statement{/ts}</td>
              </tr>
              <tr><td class="label" style="text-align:left">{$form.novalnet_instant_ref_two.label}</td>
                <td>{$form.novalnet_instant_ref_two.html}<br><span class="description">{ts}This reference will appear in your bank account statement{/ts}</td>
              </tr>
            </table>
        </div>
      </tr>
       <tr class="crm-mysettings-form-block-specialty">
        <div class="crm-accordion-wrapper crm-address-accordion collapsed">
          <div id ="epstitle" class="crm-accordion-header"><b>{ts}Novalnet EPS{/ts}</b></div>
            <table id ="epsdetails" class="novalnet-header">
            <tr><td class="label" style="text-align:left">{$form.nn_eps_testmode.label}</td>
                  <td>{$form.nn_eps_testmode.html}<br><span class="description">{ts}The payment will be processed in the test mode therefore amount for this transaction will not be charged{/ts}</span></td>
              </tr>
            <tr><td class="label" style="text-align:left">{$form.novalnet_eps_cont_status.label}</td>
                <td>{$form.novalnet_eps_cont_status.html}</td>
              </tr>
               <tr><td class="label" style="text-align:left">{$form.novalnet_eps_ref_one.label}</td>
                <td>{$form.novalnet_eps_ref_one.html}<br><span class="description">{ts}This reference will appear in your bank account statement{/ts}</td>
              </tr>
              <tr><td class="label" style="text-align:left">{$form.novalnet_eps_ref_two.label}</td>
                <td>{$form.novalnet_eps_ref_two.html}<br><span class="description">{ts}This reference will appear in your bank account statement{/ts}</td>
              </tr>
            </table>
        </div>
      </tr>

      <tr class="crm-mysettings-form-block-specialty">
        <div class="crm-accordion-wrapper crm-address-accordion collapsed">
          <div id ="giropaytitle" class="crm-accordion-header"><b>{ts}Novalnet Giropay{/ts}</b></div>
            <table id ="giropaydetails" class="novalnet-header">
            <tr><td class="label" style="text-align:left">{$form.nn_giropay_testmode.label}</td>
                  <td>{$form.nn_giropay_testmode.html}<br><span class="description">{ts}The payment will be processed in the test mode therefore amount for this transaction will not be charged{/ts}</span></td>
              </tr>
            <tr><td class="label" style="text-align:left">{$form.novalnet_giropay_cont_status.label}</td>
                <td>{$form.novalnet_giropay_cont_status.html}</td>
              </tr>
               <tr><td class="label" style="text-align:left">{$form.novalnet_giropay_ref_one.label}</td>
                <td>{$form.novalnet_giropay_ref_one.html}<br><span class="description">{ts}This reference will appear in your bank account statement{/ts}</td>
              </tr>
              <tr><td class="label" style="text-align:left">{$form.novalnet_giropay_ref_two.label}</td>
                <td>{$form.novalnet_giropay_ref_two.html}<br><span class="description">{ts}This reference will appear in your bank account statement{/ts}</td>
              </tr>
            </table>
        </div>
      </tr>


    </table>
</div>
 <script type="text/javascript">
 {literal}
cj(".novalnet-header").hide();

cj( function( ) {

  cj('#generaltitle').click(function() { cj('#generaldetails').toggle();});
  cj('#invoicetitle').click(function() { cj('#invoicedetails').toggle();});
  cj('#cctitle').click(function() { cj('#ccdetails').toggle();});
  cj('#epstitle').click(function() { cj('#epsdetails').toggle();});
  cj('#giropaytitle').click(function() { cj('#giropaydetails').toggle();});
  cj('#paypaltitle').click(function() { cj('#paypaldetails').toggle();});
  cj('#sepatitle').click(function() { cj('#sepadetails').toggle();});
  cj('#prepaymenttitle').click(function() { cj('#prepaymentdetails').toggle();});
  cj('#idealtitle').click(function() { cj('#idealdetails').toggle();});
  cj('#instanttitle').click(function() { cj('#instantdetails').toggle();});


});
{/literal}
</script>
<div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
</div>
