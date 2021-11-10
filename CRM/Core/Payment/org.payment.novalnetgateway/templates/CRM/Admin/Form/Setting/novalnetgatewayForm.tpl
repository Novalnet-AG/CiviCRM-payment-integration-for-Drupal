<div class="crm-block crm-form-block crm-mysettings-form-block">
  <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div>
    <table class="form-layout">
      <tr class="crm-mysettings-form-block-specialty">
        <div id = "generalexpand" class="crm-accordion-wrapper crm-address-accordion collapsed">
          <div id ="generaltitle" class="crm-accordion-header"><b>{ts}Novalnet General Settings{/ts}</b></div>
          <table id ='generaldetails'>
            <tr><td class="label" style="text-align:left">{$form.nn_password.label}</td>
              <td>{$form.nn_password.html}<br><span class="description">{ts}Enter your Novalnet payment access key{/ts}</td>
            </tr>
            <tr><td class="label" style="text-align:left">{$form.nn_enable_logo.label}</td>
              <td>{$form.nn_enable_logo.html}<br><span class="description">{ts}To display Novalnet logo in front end{/ts}</td>
            </tr>
            <tr><td class="label" style="text-align:left">{$form.nn_referrer_id.label}</td>
              <td>{$form.nn_referrer_id.html}<br><span class="description">{ts}Referrer ID of the partner at Novalnet, who referred you (only numbers allowed){/ts}</td>
            </tr>
          </table>
      </tr>
      <tr class="crm-mysettings-form-block-specialty">
      <div class="crm-accordion-wrapper crm-address-accordion collapsed">
          <div id ="invoicetitle" class="crm-accordion-header"><b>{ts}Novalnet Invoice{/ts}</b></div>
            <table id ="invoicedetails">
              <tr><td class="label" style="text-align:left">{$form.nn_inv_duedate.label}</td>
                  <td>{$form.nn_inv_duedate.html}</td>
              </tr>
               <tr><td class="label" style="text-align:left">{$form.nn_inv_ref_one.label}</td>
                <td>{$form.nn_inv_ref_one.html}<br><span class="description">{ts}This will appear in the transactions details / account statement{/ts}</td>
              </tr>
              <tr><td class="label" style="text-align:left">{$form.nn_inv_ref_two.label}</td>
                <td>{$form.nn_inv_ref_two.html}<br><span class="description">{ts}This will appear in the transactions details / account statement{/ts}</td>
              </tr>
            </table>
        </div>
      </tr>
      <tr class="crm-mysettings-form-block-specialty">
      <div class="crm-accordion-wrapper crm-address-accordion collapsed">
          <div id ="prepaymenttitle" class="crm-accordion-header"><b>{ts}Novalnet Prepayment{/ts}</b></div>
            <table id ="prepaymentdetails">
               <tr><td class="label" style="text-align:left">{$form.nn_prepayment_ref_one.label}</td>
                <td>{$form.nn_prepayment_ref_one.html}<br><span class="description">{ts}This will appear in the transactions details / account statement{/ts}</td>
              </tr>
              <tr><td class="label" style="text-align:left">{$form.nn_prepayment_ref_two.label}</td>
                <td>{$form.nn_prepayment_ref_two.html}<br><span class="description">{ts}This will appear in the transactions details / account statement{/ts}</td>
              </tr>
            </table>
        </div>
      </tr>
      <tr class="crm-mysettings-form-block-specialty">
        <div class="crm-accordion-wrapper crm-address-accordion collapsed">
          <div id ="cctitle" class="crm-accordion-header"><b>{ts}Novalnet Credit Card{/ts}</b></div>
          <table id ="ccdetails">
            <tr><td class="label" style="text-align:left">{$form.nn_cc_manualamount.label} </td>
                <td>{$form.nn_cc_manualamount.html}<br><span class="description">{ts}All the orders above this amount will be set on hold by Novalnet and only after your manual verifcation and confirmation at Novalnet the booking will be done{/ts}</td>
            </tr>
            <tr>
              <td class="label" style="text-align:left">{$form.nn_cc_productid2.label} </td>
              <td>{$form.nn_cc_productid2.html}<br><span class="description">{ts}Second Product ID in Novalnet to use the manual check condition{/ts}</td>
            </tr>
            <tr>
              <td class="label" style="text-align:left">{$form.nn_cc_tariffid2.label} </td>
              <td>{$form.nn_cc_tariffid2.html}<br><span class="description">{ts}Second Tariff ID in Novalnet to use the manual check condition{/ts}</td>
            </tr>
            <tr>
              <td class="label" style="text-align:left">{$form.nn_cc_secure_active.label} </td>
              <td>{$form.nn_cc_secure_active.html}<br><span class="description">{ts}(Please note that this procedure has a low acceptance among end customers.) As soon as 3D-Secure is activated for credit cards, the bank prompts the end customer for a password, to prevent credit card abuse. This can serve as a proof, that the customer is actually the owner of the credit card{/ts}</td>
            </tr>
            <tr>
              <td class="label" style="text-align:left">{$form.nn_cc_auto_refill.label} </td>
              <td>{$form.nn_cc_auto_refill.html}<br><span class="description"></td>
            </tr>
            <tr><td class="label" style="text-align:left">{$form.nn_cc_ref_one.label}</td>
                <td>{$form.nn_cc_ref_one.html}<br><span class="description">{ts}This will appear in the transactions details / account statement{/ts}</td>
              </tr>
            <tr><td class="label" style="text-align:left">{$form.nn_cc_ref_two.label}</td>
                <td>{$form.nn_cc_ref_two.html}<br><span class="description">{ts}This will appear in the transactions details / account  statement{/ts}</td>
            </tr>
            <tr><td class="label" style="text-align:left">{$form.nn_cc_amexlogo_active.label}</td>
                <td>{$form.nn_cc_amexlogo_active.html}<br><span class="description">{ts}{/ts}</td>
             </tr>
        </table>
      </div>
      </tr>

      <tr class="crm-mysettings-form-block-specialty">
        <div class="crm-accordion-wrapper crm-address-accordion collapsed">
          <div id ="attitle" class="crm-accordion-header"><b>{ts}Novalnet Direct Debit Austria{/ts}</b></div>
            <table id ="atdetails">
              <tr>
                <td class="label" style="text-align:left">{$form.nn_at_manualamount.label} </td>
                <td>{$form.nn_at_manualamount.html}<br><span class="description">{ts}All the orders above this amount will be set on hold by Novalnet and only after your manual verifcation and confirmation at Novalnet the booking will be done{/ts}</td>
              </tr>
            <tr>
              <td class="label" style="text-align:left">{$form.nn_at_productid2.label} </td>
              <td>{$form.nn_at_productid2.html}<br><span class="description">{ts}Second Product ID in Novalnet to use the manual check condition{/ts}</td>
            </tr>
            <tr>
              <td class="label" style="text-align:left">{$form.nn_at_tariffid2.label} </td>
              <td>{$form.nn_at_tariffid2.html}<br><span class="description">{ts}Second Tariff ID in Novalnet to use the manual check condition{/ts}</td>
            </tr>
            <tr>
              <td class="label" style="text-align:left">{$form.nn_at_auto_refill.label} </td>
              <td>{$form.nn_at_auto_refill.html}<br><span class="description"></td>
            </tr>
            <tr><td class="label" style="text-align:left">{$form.nn_at_ref_one.label}</td>
                <td>{$form.nn_at_ref_one.html}<br><span class="description">{ts}This will appear in the transactions details / account statement{/ts}</td>
              </tr>
              <tr><td class="label" style="text-align:left">{$form.nn_at_ref_two.label}</td>
                <td>{$form.nn_at_ref_two.html}<br><span class="description">{ts}This will appear in the transactions details / account statement{/ts}</td>
              </tr>
          </table>
        </div>
      </tr>

      <tr class="crm-mysettings-form-block-specialty">
        <div class="crm-accordion-wrapper crm-address-accordion collapsed">
          <div id ="detitle" class="crm-accordion-header"><b>{ts}Novalnet Direct Debit German{/ts}</b></div>
          <table id ="dedetails">
            <tr>
              <td class="label" style="text-align:left">{$form.nn_de_acdc.label} </td>
              <td>{$form.nn_de_acdc.html}<span class="description"><br>{ts}Activates the Account Check Direct Control at the payment process{/ts}</td>
            </tr>
            <tr>
              <td class="label" style="text-align:left">{$form.nn_de_manualamount.label} </td>
              <td>{$form.nn_de_manualamount.html}<br><span class="description">{ts}All the orders above this amount will be set on hold by Novalnet and only after your manual verifcation and confirmation at Novalnet the booking will be done{/ts}</td>
            </tr>
            <tr>
              <td class="label" style="text-align:left">{$form.nn_de_productid2.label} </td>
              <td>{$form.nn_de_productid2.html}<br><span class="description">{ts}Second Product ID in Novalnet to use the manual check condition{/ts}</td>
            </tr>
            <tr>
              <td class="label" style="text-align:left">{$form.nn_de_tariffid2.label} </td>
              <td>{$form.nn_de_tariffid2.html}<br><span class="description">{ts}Second Tariff ID in Novalnet to use the manual check condition{/ts}</td>
            </tr>
            <tr>
              <td class="label" style="text-align:left">{$form.nn_de_auto_refill.label} </td>
              <td>{$form.nn_de_auto_refill.html}<br><span class="description"></td>
            </tr>
            <tr><td class="label" style="text-align:left">{$form.nn_de_ref_one.label}</td>
                <td>{$form.nn_de_ref_one.html}<br><span class="description">{ts}This will appear in the transactions details / account statement{/ts}</td>
              </tr>
              <tr><td class="label" style="text-align:left">{$form.nn_de_ref_two.label}</td>
                <td>{$form.nn_de_ref_two.html}<br><span class="description">{ts}This will appear in the transactions details / account statement{/ts}</td>
              </tr>
          </table>
        </div>
      </tr>
      <tr class="crm-mysettings-form-block-specialty">
        <div class="crm-accordion-wrapper crm-address-accordion collapsed">
          <div id ="sepatitle" class="crm-accordion-header"><b>{ts}Novalnet Direct Debit SEPA{/ts}</b></div>
          <table id ="sepadetails">
            <tr>
              <td class="label" style="text-align:left">{$form.nn_sepa_manualamount.label} </td>
              <td>{$form.nn_sepa_manualamount.html}<br><span class="description">{ts}All the orders above this amount will be set on hold by Novalnet and only after your manual verifcation and confirmation at Novalnet the booking will be done{/ts}</td>
            </tr>
            <tr>
              <td class="label" style="text-align:left">{$form.nn_sepa_productid2.label} </td>
              <td>{$form.nn_sepa_productid2.html}<br><span class="description">{ts}Second Product ID in Novalnet to use the manual check condition{/ts}</td>
            </tr>
            <tr>
              <td class="label" style="text-align:left">{$form.nn_sepa_tariffid2.label}</td>
              <td>{$form.nn_sepa_tariffid2.html}<br><span class="description">{ts}Second Product ID in Novalnet to use the manual check condition{/ts}</td>
            </tr>
            <tr>
              <td class="label" style="text-align:left">{$form.nn_sepa_payment_type.label} </td>
              <td>{$form.nn_sepa_payment_type.html}<br><span class="description">{ts}{/ts}</td>
            </tr>
            <tr id= 'nn_sepa_duedate'>
              <td class="label" style="text-align:left">{$form.nn_sepa_due_date.label} </td>
              <td>{$form.nn_sepa_due_date.html}<br><span class="description">{ts}Enter the Due date in days, it should be greater than 6. If you leave as empty means default value will be considered as 7 days.{/ts}</td>
            </tr>
            <tr>
              <td class="label" style="text-align:left">{$form.nn_sepa_auto_refill.label} </td>
              <td>{$form.nn_sepa_auto_refill.html}<br><span class="description"></td>
            </tr>
            <tr><td class="label" style="text-align:left">{$form.nn_sepa_ref_one.label}</td>
                <td>{$form.nn_sepa_ref_one.html}<br><span class="description">{ts}This will appear in the transactions details / account statement{/ts}</td>
              </tr>
              <tr><td class="label" style="text-align:left">{$form.nn_sepa_ref_two.label}</td>
                <td>{$form.nn_sepa_ref_two.html}<br><span class="description">{ts}This will appear in the transactions details / account statement{/ts}</td>
              </tr>
         </table>
        </div>
      </tr>

      <tr class="crm-mysettings-form-block-specialty">
        <div class="crm-accordion-wrapper crm-address-accordion collapsed">
          <div id ="paypaltitle" class="crm-accordion-header"><b>{ts}Novalnet PayPal{/ts}</b></div>
          <table id ="paypaldetails">
            <tr><td colspan=2><b>{ts}Novalnet PayPal{/ts}</b></td></tr>
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
            <tr><td class="label" style="text-align:left">{$form.nn_paypal_ref_one.label}</td>
                <td>{$form.nn_paypal_ref_one.html}<br><span class="description">{ts}This will appear in the transactions details / account statement{/ts}</td>
              </tr>
              <tr><td class="label" style="text-align:left">{$form.nn_paypal_ref_two.label}</td>
                <td>{$form.nn_paypal_ref_two.html}<br><span class="description">{ts}This will appear in the transactions details / account statement{/ts}</td>
              </tr>
          </table>
        </div>
      </tr>
      <tr class="crm-mysettings-form-block-specialty">
      <div class="crm-accordion-wrapper crm-address-accordion collapsed">
          <div id ="idealtitle" class="crm-accordion-header"><b>{ts}Novalnet iDEAL{/ts}</b></div>
            <table id ="idealdetails">
               <tr><td class="label" style="text-align:left">{$form.nn_ideal_ref_one.label}</td>
                <td>{$form.nn_ideal_ref_one.html}<br><span class="description">{ts}This will appear in the transactions details / account statement{/ts}</td>
              </tr>
              <tr><td class="label" style="text-align:left">{$form.nn_ideal_ref_two.label}</td>
                <td>{$form.nn_ideal_ref_two.html}<br><span class="description">{ts}This will appear in the transactions details / account statement{/ts}</td>
              </tr>
            </table>
        </div>
      </tr>
      <tr class="crm-mysettings-form-block-specialty">
      <div class="crm-accordion-wrapper crm-address-accordion collapsed">
          <div id ="instanttitle" class="crm-accordion-header"><b>{ts}Novalnet Instant Bank Transfer{/ts}</b></div>
            <table id ="instantdetails">
               <tr><td class="label" style="text-align:left">{$form.nn_instant_ref_one.label}</td>
                <td>{$form.nn_instant_ref_one.html}<br><span class="description">{ts}This will appear in the transactions details / account statement{/ts}</td>
              </tr>
              <tr><td class="label" style="text-align:left">{$form.nn_instant_ref_two.label}</td>
                <td>{$form.nn_instant_ref_two.html}<br><span class="description">{ts}This will appear in the transactions details / account statement{/ts}</td>
              </tr>
            </table>
        </div>
      </tr>
      <tr class="crm-mysettings-form-block-specialty">
      <div class="crm-accordion-wrapper crm-address-accordion collapsed">
          <div id ="safetytitle" class="crm-accordion-header"><b>{ts}Novalnet SafetyPay{/ts}</b></div>
            <table id ="safetydetails">
               <tr><td class="label" style="text-align:left">{$form.nn_safety_ref_one.label}</td>
                <td>{$form.nn_safety_ref_one.html}<br><span class="description">{ts}This will appear in the transactions details / account statement{/ts}</td>
              </tr>
              <tr><td class="label" style="text-align:left">{$form.nn_safety_ref_two.label}</td>
                <td>{$form.nn_safety_ref_two.html}<br><span class="description">{ts}This will appear in the transactions details / account statement{/ts}</td>
              </tr>
            </table>
        </div>
      </tr>
      <tr class="crm-mysettings-form-block-specialty">
        <div class="crm-accordion-wrapper crm-address-accordion collapsed">
          <div id ="callbacktitle" class="crm-accordion-header"><b>{ts}Novalnet Callback Script Settings{/ts}</b></div>
          <table id ="callbackdetails">
              <tr>
                <td class="label" style="text-align:left"> {$form.nn_callback_testmode.label}</td>
                <td>{$form.nn_callback_testmode.html}</td>
              </tr>
              <tr>
                <td class="label" style="text-align:left">{$form.nn_callback_debug.label} </td>
                <td> {$form.nn_callback_debug.html}<br><span class="description">{ts}Enable Your Debug Mode{/ts}</td>
              </tr>
              <tr>
                <td class="label" style="text-align:left">{$form.nn_callback_sendmail.label} </td>
                <td>{$form.nn_callback_sendmail.html} <br><span class="description">{ts}Enable Your E-mail Send Option{/ts}</td>
              </tr>

              <tr>
                <td class="label" style="text-align:left">{$form.nn_callback_frommail.label} </td>
                <td>{$form.nn_callback_frommail.html}<br><span class="description">{ts}Enter the Sender E-mail From Address{/ts}</td>
              </tr>
              <tr>
                <td class="label" style="text-align:left">{$form.nn_callback_tomail.label} </td>
                <td>{$form.nn_callback_tomail.html}<br><span class="description">{ts}Recipient E-mail Address (Use Multiple Email-ID's seprated by comma ( , )){/ts}</td>
              </tr>
              <tr>
                <td class="label" style="text-align:left">{$form.nn_callback_mailbody.label} </td>
                <td class="html-adjust">{$form.nn_callback_mailbody.html}<br><span class="description">{ts}E-mail Your Comment Text{/ts}</td>
              </tr>
              <tr>
                <td class="label" style="text-align:left">{$form.nn_callback_mailcc.label} </td>
                <td>{$form.nn_callback_mailcc.html}<br><span class="description">{ts}If You are Sending them a copy for their Information (Use Multiple Email-ID's seprated by comma ( , )){/ts}</td>
              </tr>
              <tr>
                <td class="label" style="text-align:left">{$form.nn_callback_mailbcc.label} </td>
                <td>{$form.nn_callback_mailbcc.html}<br><span class="description">{ts}Send a Message to “Undisclosed Recipients” (Use Multiple Email-ID's seprated by comma ( , )){/ts}</td>
              </tr>
            </table>
          </div>
        </tr>
    </table>
</div>
 <script type="text/javascript">
 {literal}
cj("#generaldetails,#invoicedetails,#ccdetails,#cc3ddetails,#atdetails,#safetydetails").hide();
cj("#dedetails,#paypaldetails,#sepadetails,#prepaymentdetails,#idealdetails,#instantdetails,#callbackdetails").hide();

cj( function( ) {
  if(cj('#CIVICRM_QFID_DIRECT_DEBIT_SEPA_nn_sepa_payment_type').is(':checked')) {
    cj('#nn_sepa_duedate').show();
  }
  cj('#CIVICRM_QFID_DIRECT_DEBIT_SEPA_nn_sepa_payment_type').click(function()  {
    cj('#nn_sepa_duedate').show();
  });
  cj('#CIVICRM_QFID_DIRECT_DEBIT_SEPA_SIGNED_nn_sepa_payment_type').click(function()
  {
    cj('#nn_sepa_duedate').hide();
  });
  cj('#generaltitle').click(function() { cj('#generaldetails').toggle();});
  cj('#invoicetitle').click(function() { cj('#invoicedetails').toggle();});
  cj('#cctitle').click(function() { cj('#ccdetails').toggle();});
  cj('#cc3dtitle').click(function() { cj('#cc3ddetails').toggle();});
  cj('#attitle').click(function() { cj('#atdetails').toggle();});
  cj('#detitle').click(function() { cj('#dedetails').toggle();});
  cj('#paypaltitle').click(function() { cj('#paypaldetails').toggle();});
  cj('#sepatitle').click(function() { cj('#sepadetails').toggle();});
  cj('#prepaymenttitle').click(function() { cj('#prepaymentdetails').toggle();});
  cj('#idealtitle').click(function() { cj('#idealdetails').toggle();});
  cj('#instanttitle').click(function() { cj('#instantdetails').toggle();});
  cj('#safetytitle').click(function() { cj('#safetydetails').toggle();});
  cj('#callbacktitle').click(function() { cj('#callbackdetails').toggle();});


});
{/literal}
</script>

<div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
</div>
