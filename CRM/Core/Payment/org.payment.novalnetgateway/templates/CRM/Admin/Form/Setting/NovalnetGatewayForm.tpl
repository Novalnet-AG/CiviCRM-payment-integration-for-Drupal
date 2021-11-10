<div class="crm-block crm-form-block crm-mysettings-form-block">
  <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div>
<div style="font-family: Helvetica Neue, Helvetica, Arial, sans-serif;padding: 4px;margin-bottom: 4px;font-size: 13px; background-color: #F1F8EB;border: 1px solid #B0D730;color: #3E3E3E;border-radius: 4px;">{ts}For additional configurations login to{/ts} <a href="https://admin.novalnet.de/" target="_blank"><u>{ts}Novalnet Merchant Administration portal{/ts}</u></a>.<br> {ts}To login to the Portal you need to have an account at Novalnet. If you don't have one yet, please contact{/ts} <a href="mailto:sales@novalnet.de" target="_blank"><u>{ts}sales@novalnet.de{/ts}</u></a> {ts}/ tel. +49 (089) 923068320{/ts}<br><br>
        {ts}To use the PayPal payment method please enter your PayPal API details in{/ts} <a href="https://admin.novalnet.de/" target="_blank"><u>{ts}Novalnet Merchant Administration portal{/ts}</u></a></div>
    <table class="form-layout">
      <tr class="crm-mysettings-form-block-specialty">
        <div id = "generalexpand" class="crm-accordion-wrapper crm-address-accordion collapsed">
          <div id ="generaltitle" class="crm-accordion-header"><b>{ts}Novalnet Global Configuration{/ts}</b></div>
          <table id ="generaldetails" class="novalnet-header">
            <tr><td class="label" style="text-align:left;width:40%;">{$form.novalnet_product_activation_key.label}</td>
              <td> {*<input type="text" name="novalnet_product_activation_key" id="novalnet_product_activation_key" value="">*}
              <input type="hidden" id="novalnet_product_activation_url" value="{$activation_url}" />
              <input type="hidden" id="nn_server_addr" value="{$nn_server}" />
              <input type="hidden" id="novalnet_ajax_complete" value="1" />
              {$form.novalnet_product_activation_key.html}<br><span class="description">{ts}Enter Novalnet Product activation key. To get the Product activation key, go to{/ts} <a href="https://admin.novalnet.de/" target="_blank"><u>{ts}Novalnet Merchant Administration portal{/ts}</u></a> -<b>{ts}PROJECTS:{/ts}</b> {ts}Project Information{/ts} - <b>{ts}Shop Parameters: API Signature (Product activation key).{/ts}</b></td>
            </tr>
			  
            <tr style="display:none;"><td class="label" style="text-align:left">{$form.nn_vendor.label}</td>
              <td>{$form.nn_vendor.html}<br><span class="description">{ts}Enter Novalnet merchant ID{/ts}</td>
            </tr>
            <tr style="display:none;"><td class="label" style="text-align:left">{$form.nn_authcode.label}</td>
              <td>{$form.nn_authcode.html}<br><span class="description">{ts}Enter Novalnet authentication code{/ts}</td>
            </tr>
            <tr style="display:none;"><td class="label" style="text-align:left">{$form.nn_product.label}</td>
              <td>{$form.nn_product.html}<br><span class="description">{ts}Enter Novalnet project ID{/ts}</td>
            </tr>
            <tr><td class="label" style="text-align:left">{$form.nn_tariff.label}</td>
              <td>{$form.nn_tariff.html}<br><span class="description">{ts}Enter Novalnet tariff ID{/ts}</td>
            </tr>
            <tr style="display:none;"><td class="label" style="text-align:left">{$form.nn_password.label}</td>
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
            <tr><td colspan="2"><b>{ts}Logos display management{/ts}</b></td></tr>
			<tr><td class="label" style="text-align:left">{$form.novalnet_pay_logo.label}</td>
              <td>{$form.novalnet_pay_logo.html}<br><span class="description">{ts}The payment method logo will be displayed on the checkout page{/ts}</td>
            </tr>
			<tr><td class="label" style="text-align:left" colspan="2"><b>{ts}Order status management for on-hold transaction(-s){/ts}</b></td>
            </tr>
            <tr><td class="label" style="text-align:left">{$form.novalnet_onhold_order_complete.label}</td>
              <td>{$form.novalnet_onhold_order_complete.html}</td>
            </tr>
            <tr><td class="label" style="text-align:left">{$form.novalnet_onhold_order_cancel.label}</td>
              <td>{$form.novalnet_onhold_order_cancel.html}</td>
            </tr>

           <tr><td colspan="2"><b>{ts}Merchant script management{/ts}</b></td></tr>
              <tr>
                <td class="label" style="text-align:left"> {$form.nn_callback_testmode.label}</td>
                <td>{$form.nn_callback_testmode.html}<br/><span class="description">{ts}This option will allow performing a manual execution. Please disable this option before setting your shop to LIVE mode, to avoid unauthorized calls from external parties (excl. Novalnet){/ts}</td>
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
              <tr><td class="label" style="text-align:left;width:40%;">{$form.nn_inv_testmode.label}</td>
                  <td>{$form.nn_inv_testmode.html}<br><span class="description">{ts}The payment will be processed in the test mode therefore amount for this transaction will not be charged{/ts}</span></td>
              </tr>
              <tr><td class="label" style="text-align:left">{$form.nn_inv_duedate.label}</td>
                  <td>{$form.nn_inv_duedate.html}<br><span class="description">{ts}Enter the number of days to transfer the payment amount to Novalnet (must be greater than 7 days). In case if the field is empty, 14 days will be set as due date by default{/ts}</td>
              </tr>
            <tr><td class="label" style="text-align:left">{$form.nn_invoice_manualamount.label} </td>
                <td>{$form.nn_invoice_manualamount.html}<br><span class="description">{ts}In case the order amount exceeds mentioned limit, the transaction will be set on hold till your confirmation of transaction{/ts}</td>
            </tr>
               <tr><td class="label" style="text-align:left">{$form.novalnet_invoice_cont_status.label}</td>
                <td>{$form.novalnet_invoice_cont_status.html}</td>
              </tr>
               <tr><td class="label" style="text-align:left">{$form.novalnet_invoice_cont_cb_status.label}</td>
                <td>{$form.novalnet_invoice_cont_cb_status.html}</td>
              </tr>
            <tr><td class="label" style="text-align:left">{$form.novalnet_invoice_notify.label} </td>
                <td>{$form.novalnet_invoice_notify.html}<br><span class="description">{ts}The entered text will be displayed on the checkout page{/ts}</td>
            </tr>
               <tr><td class="label" style="text-align:left" colspan="2"><b>{ts}Payment guarantee configuration{/ts}</b><br/>
               <span class="description"><b>{ts}Basic requirements for payment guarantee{/ts}</b>
							<ul>
							<li>{ts}Allowed countries: AT, DE, CH{/ts}</li>
							<li>{ts}Allowed currency: EUR{/ts}</li>
							<li>{ts}Minimum amount of order >= 9,99 EUR{/ts}</li>
							<li>{ts}Minimum age of end customer >= 18 Years{/ts}</li>
							<li>{ts}The billing address must be the same as the shipping address{/ts}</li>
							<li>{ts}Gift certificates/vouchers are not allowed{/ts}</li>
							</ul>
                </td>
              </tr>
               <tr><td class="label" style="text-align:left">{$form.novalnet_invoice_guarantee.label}</td>
                <td>{$form.novalnet_invoice_guarantee.html}</td>
              </tr>
               <tr><td class="label" style="text-align:left">{$form.novalnet_invoice_guarantee_status.label}</td>
                <td>{$form.novalnet_invoice_guarantee_status.html}</td>
              </tr>
               <tr><td class="label" style="text-align:left">{$form.novalnet_invoice_guarantee_amt.label}</td>
                <td>{$form.novalnet_invoice_guarantee_amt.html}<br><span class="description">{ts}This setting will override the default setting made in the minimum order amount. Note that amount should be in the range of 9,99 EUR{/ts}</td>
              </tr>
               <tr><td class="label" style="text-align:left">{$form.novalnet_invoice_force_guarantee.label}</td>
                <td>{$form.novalnet_invoice_force_guarantee.html}<br><span class="description">{ts}If the payment guarantee is activated (True), but the above mentioned requirements are not met, the payment should be processed as non-guarantee payment.{/ts}</td>
              </tr>
            </table>
        </div>
      </tr>
      <tr class="crm-mysettings-form-block-specialty">
      <div class="crm-accordion-wrapper crm-address-accordion collapsed">
          <div id ="prepaymenttitle" class="crm-accordion-header"><b>{ts}Novalnet Prepayment{/ts}</b></div>
            <table id ="prepaymentdetails" class="novalnet-header">
            <tr><td class="label" style="text-align:left;width:40%;">{$form.nn_prepayment_testmode.label}</td>
                  <td>{$form.nn_prepayment_testmode.html}<br><span class="description">{ts}The payment will be processed in the test mode therefore amount for this transaction will not be charged{/ts}</span></td>
              </tr>
              <tr><td class="label" style="text-align:left">{$form.novalnet_prepayment_cont_status.label}</td>
                <td>{$form.novalnet_prepayment_cont_status.html}</td>
              </tr>
               <tr><td class="label" style="text-align:left">{$form.novalnet_prepayment_cont_cb_status.label}</td>
                <td>{$form.novalnet_prepayment_cont_cb_status.html}</td>
              </tr>
            <tr><td class="label" style="text-align:left">{$form.novalnet_prepayment_notify.label} </td>
                <td>{$form.novalnet_prepayment_notify.html}<br><span class="description">{ts}The entered text will be displayed on the checkout page{/ts}</td>
            </tr>
            </table>
        </div>
      </tr>
      <tr class="crm-mysettings-form-block-specialty">
        <div class="crm-accordion-wrapper crm-address-accordion collapsed">
          <div id ="cctitle" class="crm-accordion-header"><b>{ts}Novalnet Credit Card{/ts}</b></div>
          <table id ="ccdetails" class="novalnet-header">
          <tr><td class="label" style="text-align:left;width:40%;">{$form.nn_cc_testmode.label}</td>
                  <td>{$form.nn_cc_testmode.html}<br><span class="description">{ts}The payment will be processed in the test mode therefore amount for this transaction will not be charged{/ts}</span></td>
              </tr>
            <tr>
              <td class="label" style="text-align:left">{$form.nn_cc_secure_active.label} </td>
              <td>{$form.nn_cc_secure_active.html}<br><span class="description">{ts}The 3D-Secure will be activated for credit cards. The issuing bank prompts the buyer for a password what, in turn, help to prevent a fraudulent payment. It can be used by the issuing bank as evidence that the buyer is indeed their card holder. This is intended to help decrease a risk of charge-back.{/ts}</td>
            </tr>
            <tr>
              <td class="label" style="text-align:left">{$form.nn_cc_force_secure_active.label} </td>
              <td>{$form.nn_cc_force_secure_active.html}<br><span class="description">{ts}If 3D secure is not enabled in the above field, then force 3D secure process as per the 'Enforced 3D secure (as per predefined filters &amp; settings)' module configuration at the{/ts} <a href="https://admin.novalnet.de/" target="_blank"><u>{ts}Novalnet Merchant Administration portal{/ts}</u></a>{ts}. If the predefined filters &amp; settings from Enforced 3D secure module are met, then the transaction will be processed as 3D secure transaction otherwise it will be processed as non 3D secure.{/ts} <br/>
			{ts}Please note that the 'Enforced 3D secure (as per predefined filters &amp; settings)' module should be configured at{/ts} <a href="https://admin.novalnet.de/" target="_blank"><u>{ts}Novalnet Merchant Administration portal{/ts}</u></a> {ts}prior to the activation here.{/ts} <br/>
			{ts}For further information, please refer the description of this fraud module at 'Fraud Modules' tab, below 'Projects' menu, under the selected project in {/ts}<a href="https://admin.novalnet.de/" target="_blank"><u>{ts}Novalnet Merchant Administration portal{/ts}</u></a>{ts} or contact Novalnet support team.{/ts}</td>
            </tr>
            <tr><td class="label" style="text-align:left">{$form.nn_cc_manualamount.label} </td>
                <td>{$form.nn_cc_manualamount.html}<br><span class="description">{ts}In case the order amount exceeds mentioned limit, the transaction will be set on hold till your confirmation of transaction{/ts}</td>
            </tr>
            <tr><td class="label" style="text-align:left">{$form.novalnet_cc_cont_status.label}</td>
                <td>{$form.novalnet_cc_cont_status.html}</td>
              </tr>
            <tr><td class="label" style="text-align:left">{$form.nn_cc_amexlogo_active.label}</td>
                <td>{$form.nn_cc_amexlogo_active.html}<br><span class="description">{ts}Display AMEX logo in checkout page{/ts}</td>
             </tr>
             <tr><td class="label" style="text-align:left">{$form.nn_cc_maestrologo_active.label}</td>
				<td>{$form.nn_cc_maestrologo_active.html}<br><span class="description">{ts}Display Maestro logo in checkout page{/ts}</td>
             </tr>
            <tr><td class="label" style="text-align:left">{$form.novalnet_cc_notify.label} </td>
                <td>{$form.novalnet_cc_notify.html}<br><span class="description">{ts}The entered text will be displayed on the checkout page{/ts}</td>
            </tr>
			<tr><td colspan="2"><b>{ts}CSS settings for Credit Card iframe{/ts}</b></td></tr>
             <tr><td class="label" style="text-align:left">{$form.nn_cc_css_settings_label.label}</td>
				<td>{$form.nn_cc_css_settings_label.html}</td>
             </tr>
             <tr><td class="label" style="text-align:left">{$form.nn_cc_css_settings_input.label}</td>
				<td>{$form.nn_cc_css_settings_input.html}</td>
             </tr>
             <tr><td class="label" style="text-align:left">{$form.nn_cc_css_settings_css_text.label}</td>
				<td>{$form.nn_cc_css_settings_css_text.html}</td>
             </tr>
             
        </table>
      </div>
      </tr>
      <tr class="crm-mysettings-form-block-specialty">
        <div class="crm-accordion-wrapper crm-address-accordion collapsed">
          <div id ="sepatitle" class="crm-accordion-header"><b>{ts}Novalnet Direct Debit SEPA{/ts}</b></div>
          <table id ="sepadetails" class="novalnet-header">
            <tr><td class="label" style="text-align:left;width:40%;">{$form.nn_sepa_testmode.label}</td>
                  <td>{$form.nn_sepa_testmode.html}<br><span class="description">{ts}The payment will be processed in the test mode therefore amount for this transaction will not be charged{/ts}</span></td>
            </tr>
            <tr id="nn_sepa_duedate">
              <td class="label" style="text-align:left">{$form.nn_sepa_due_date.label} </td>
              <td>{$form.nn_sepa_due_date.html}<br><span class="description">{ts}Enter the number of days after which the payment should be processed (must be between 2 and 14 days){/ts}</td>
            </tr>
            <tr><td class="label" style="text-align:left">{$form.nn_sepa_manualamount.label} </td>
                <td>{$form.nn_sepa_manualamount.html}<br><span class="description">{ts}In case the order amount exceeds mentioned limit, the transaction will be set on hold till your confirmation of transaction{/ts}</td>
            </tr>
            <tr><td class="label" style="text-align:left">{$form.novalnet_sepa_cont_status.label}</td>
                <td>{$form.novalnet_sepa_cont_status.html}</td>
              </tr>
            <tr><td class="label" style="text-align:left">{$form.novalnet_sepa_notify.label} </td>
                <td>{$form.novalnet_sepa_notify.html}<br><span class="description">{ts}The entered text will be displayed on the checkout page{/ts}</td>
            </tr>
               <tr><td class="label" style="text-align:left" colspan="2"><b>{ts}Payment guarantee configuration{/ts}</b><br/>
               <span class="description"><b>{ts}Basic requirements for payment guarantee{/ts}</b>
							<ul>
							<li>{ts}Allowed countries: AT, DE, CH{/ts}</li>
							<li>{ts}Allowed currency: EUR{/ts}</li>
							<li>{ts}Minimum amount of order >= 9,99 EUR{/ts}</li>
							<li>{ts}Minimum age of end customer >= 18 Years{/ts}</li>
							<li>{ts}The billing address must be the same as the shipping address{/ts}</li>
							<li>{ts}Gift certificates/vouchers are not allowed{/ts}</li>
							</ul>
							</td>
              </tr>
               <tr><td class="label" style="text-align:left">{$form.novalnet_sepa_guarantee.label}</td>
                <td>{$form.novalnet_sepa_guarantee.html}</td>
              </tr>
               <tr><td class="label" style="text-align:left">{$form.novalnet_sepa_guarantee_status.label}</td>
                <td>{$form.novalnet_sepa_guarantee_status.html}</td>
              </tr>
               <tr><td class="label" style="text-align:left">{$form.novalnet_sepa_guarantee_amt.label}</td>
                <td>{$form.novalnet_sepa_guarantee_amt.html}<br><span class="description">{ts}This setting will override the default setting made in the minimum order amount. Note that amount should be in the range of 9,99 EUR{/ts}</td>
              </tr>
               <tr><td class="label" style="text-align:left">{$form.novalnet_sepa_force_guarantee.label}</td>
                <td>{$form.novalnet_sepa_force_guarantee.html}<br><span class="description">{ts}If the payment guarantee is activated (True), but the above mentioned requirements are not met, the payment should be processed as non-guarantee payment.{/ts}</td>
              </tr>
            
         </table>
        </div>
      </tr>
      <tr class="crm-mysettings-form-block-specialty">
        <div class="crm-accordion-wrapper crm-address-accordion collapsed">
          <div id ="paypaltitle" class="crm-accordion-header"><b>{ts}Novalnet PayPal{/ts}</b></div>
          <table id ="paypaldetails" class="novalnet-header">
            <tr><td class="label" style="text-align:left;width:40%;">{$form.nn_paypal_testmode.label}</td>
                  <td>{$form.nn_paypal_testmode.html}<br><span class="description">{ts}The payment will be processed in the test mode therefore amount for this transaction will not be charged{/ts}</span></td>
              </tr>
            <tr><td class="label" style="text-align:left">{$form.nn_paypal_manualamount.label} </td>
                <td>{$form.nn_paypal_manualamount.html}<br><span class="description">{ts}In case the order amount exceeds mentioned limit, the transaction will be set on hold till your confirmation of transaction. In order to use this option you must have billing agreement option enabled in your PayPal account. Please contact your account manager at PayPal.{/ts}</td>
            </tr>
            <tr><td class="label" style="text-align:left">{$form.novalnet_paypal_pending_status.label}</td>
                <td>{$form.novalnet_paypal_pending_status.html}</td>
              </tr>
            <tr><td class="label" style="text-align:left">{$form.novalnet_paypal_cont_status.label}</td>
                <td>{$form.novalnet_paypal_cont_status.html}</td>
              </tr>
            <tr><td class="label" style="text-align:left">{$form.novalnet_paypal_notify.label} </td>
                <td>{$form.novalnet_paypal_notify.html}<br><span class="description">{ts}The entered text will be displayed on the checkout page{/ts}</td>
            </tr>
          </table>
        </div>
      </tr>
      <tr class="crm-mysettings-form-block-specialty">
      <div class="crm-accordion-wrapper crm-address-accordion collapsed">
          <div id ="idealtitle" class="crm-accordion-header"><b>{ts}Novalnet iDEAL{/ts}</b></div>
            <table id ="idealdetails" class="novalnet-header">
            <tr><td class="label" style="text-align:left;width:40%;">{$form.nn_ideal_testmode.label}</td>
                  <td>{$form.nn_ideal_testmode.html}<br><span class="description">{ts}The payment will be processed in the test mode therefore amount for this transaction will not be charged{/ts}</span></td>
              </tr>
            <tr><td class="label" style="text-align:left">{$form.novalnet_ideal_cont_status.label}</td>
                <td>{$form.novalnet_ideal_cont_status.html}</td>
              </tr>
            <tr><td class="label" style="text-align:left">{$form.novalnet_ideal_notify.label} </td>
                <td>{$form.novalnet_ideal_notify.html}<br><span class="description">{ts}The entered text will be displayed on the checkout page{/ts}</td>
            </tr>
            </table>
        </div>
      </tr>
      <tr class="crm-mysettings-form-block-specialty">
      <div class="crm-accordion-wrapper crm-address-accordion collapsed">
          <div id ="instanttitle" class="crm-accordion-header"><b>{ts}Novalnet Instant Bank Transfer{/ts}</b></div>
            <table id ="instantdetails" class="novalnet-header">
            <tr><td class="label" style="text-align:left;width:40%;">{$form.nn_instant_testmode.label}</td>
                  <td>{$form.nn_instant_testmode.html}<br><span class="description">{ts}The payment will be processed in the test mode therefore amount for this transaction will not be charged{/ts}</span></td>
              </tr>
            <tr><td class="label" style="text-align:left">{$form.novalnet_instant_cont_status.label}</td>
                <td>{$form.novalnet_instant_cont_status.html}</td>
              </tr>
            <tr><td class="label" style="text-align:left">{$form.novalnet_instant_notify.label} </td>
                <td>{$form.novalnet_instant_notify.html}<br><span class="description">{ts}The entered text will be displayed on the checkout page{/ts}</td>
            </tr>
            </table>
        </div>
      </tr>
       <tr class="crm-mysettings-form-block-specialty">
        <div class="crm-accordion-wrapper crm-address-accordion collapsed">
          <div id ="epstitle" class="crm-accordion-header"><b>{ts}Novalnet EPS{/ts}</b></div>
            <table id ="epsdetails" class="novalnet-header">
            <tr><td class="label" style="text-align:left;width:40%;">{$form.nn_eps_testmode.label}</td>
                  <td>{$form.nn_eps_testmode.html}<br><span class="description">{ts}The payment will be processed in the test mode therefore amount for this transaction will not be charged{/ts}</span></td>
              </tr>
            <tr><td class="label" style="text-align:left">{$form.novalnet_eps_cont_status.label}</td>
                <td>{$form.novalnet_eps_cont_status.html}</td>
              </tr>
            <tr><td class="label" style="text-align:left">{$form.novalnet_eps_notify.label} </td>
                <td>{$form.novalnet_eps_notify.html}<br><span class="description">{ts}The entered text will be displayed on the checkout page{/ts}</td>
            </tr>
            </table>
        </div>
      </tr>

      <tr class="crm-mysettings-form-block-specialty">
        <div class="crm-accordion-wrapper crm-address-accordion collapsed">
          <div id ="giropaytitle" class="crm-accordion-header"><b>{ts}Novalnet Giropay{/ts}</b></div>
            <table id ="giropaydetails" class="novalnet-header">
            <tr><td class="label" style="text-align:left;width:40%;">{$form.nn_giropay_testmode.label}</td>
                  <td>{$form.nn_giropay_testmode.html}<br><span class="description">{ts}The payment will be processed in the test mode therefore amount for this transaction will not be charged{/ts}</span></td>
              </tr>
            <tr><td class="label" style="text-align:left">{$form.novalnet_giropay_cont_status.label}</td>
                <td>{$form.novalnet_giropay_cont_status.html}</td>
              </tr>
            <tr><td class="label" style="text-align:left">{$form.novalnet_giropay_notify.label} </td>
                <td>{$form.novalnet_giropay_notify.html}<br><span class="description">{ts}The entered text will be displayed on the checkout page{/ts}</td>
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
       <script type="text/javascript" src='{$assetsurl}js/Admin/Form/Setting/novalnet_api.js'></script>
