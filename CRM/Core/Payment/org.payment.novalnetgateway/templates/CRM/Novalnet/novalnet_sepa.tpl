<div id="payment_information">
    <fieldset class="billing_mode-group">
      <legend>
        {$novalnet_sepa_name}
      </legend>
    </fieldset>
</div>
{$novalnet_sepa_logo}<br>
{$novalnet_sepa_desc}
{if $smarty.const.MODE_SEPA == '1' || $paymentProcessor.is_test == '1'}
<div style =color:red;>{ts}The payment will be processed in the test mode therefore amount for this transaction will not be charged{/ts}</div>
{/if}
{$novalnet_sepa_notify}
<div class="crm-section billing_mode-section direct_debit_info-section" id="sepa_form">
	<div class="crm-section account_holder-section">
        <div class="label"><label for="sepa_cardholder">{ts}Account holder{/ts}</label><span class="crm-marker" style ="padding-left:3px;">*</span></div>
        <div class="content">
            <input type="text" class="medium crm-form-text" id="sepa_cardholder" placeholder="{ts}Account holder{/ts}" name="sepa_cardholder" size="25"  onkeypress="return sepa_HolderFormat(event)">
        </div>
        <div class="clear"></div>
    </div>
    
    <div class="crm-section sepa_iban-section">
        <div class="label"><label for="sepa_iban">{ts}IBAN{/ts}</label><span class="crm-marker" style ="padding-left:3px;">*</span></div>
        <div class="content">
            <input type="text" class="medium crm-form-text" id="sepa_iban" name="sepa_iban" autocomplete="off"  size="25" onkeypress="return iban_validate(event, 'iban')" style="text-transform: uppercase;">
            <span id="novalnet_sepa_iban_span"></span>
        </div>
        <div class="clear"></div>
{if $sepaGuarantee =='1'}
<div class="crm-section" id="sepa_dob">
<div class="label"><label>{$nn_dateofbirth}<span class="crm-marker" style ="padding-left:3px;">*</span></label></div>
<div class="content"> <input type="date" class="medium crm-form-text" id="sepa" name="sepa_date" size="25" >
    </div>
    </div>
{/if}
        
    </div>


        <div class="content">
            <a id="sepa_mandate_toggle" style="cursor:pointer"><b>{ts}I hereby grant the mandate for the SEPA direct debit (electronic transmission) and confirm that the given bank details are correct!{/ts}</b></a>
            <div id="sepa_mandate_details" style="display:none">
            <p>{ts}I authorise (A) Novalnet AG to send instructions to my bank to debit my account and (B) my bank to debit my account in accordance with the instructions from Novalnet AG.{/ts}</p>

			<p><b>{ts}Creditor identifier: DE53ZZZ00000004253{/ts}</b></p>

			<p><b>{ts}Note:{/ts}</b> {ts}You are entitled to a refund from your bank under the terms and conditions of your agreement with bank. A refund must be claimed within 8 weeks starting from the date on which your account was debited.{/ts}</p>
			</div>
        </div>
        <div class="clear"></div>
    </div>
</div>
<input type="hidden" class="medium crm-form-text" id="nn_sepa_force_amount" name="nn_sepa_force_amount" size="25" value="{$nn_sepa_force_amount}" >
<input type="hidden" class="medium crm-form-text" id="nn_sepa_force_gnt" name="nn_sepa_force_gnt" size="25" value="{$nn_sepa_force_gnt}" >
<script src='{$assetsurl}js/novalnet_sepa.js'></script>

