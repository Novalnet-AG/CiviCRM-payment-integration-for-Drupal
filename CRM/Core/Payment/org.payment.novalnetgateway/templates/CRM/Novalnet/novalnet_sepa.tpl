<div id="payment_information">
    <fieldset class="billing_mode-group">
      <legend>
        {$novalnet_sepa_name}
      </legend>
    </fieldset>
</div>
{$novalnet_sepa_logo}<br>
{$novalnet_sepa_desc}
<div class="crm-section billing_mode-section direct_debit_info-section" id="sepa_form">
	<div class="crm-section account_holder-section">
        <div class="label"><label for="sepa_cardholder">{ts}Account holder{/ts}</label><span class="crm-marker" style ="padding-left:3px;">*</span></div>
        <div class="content">
            <input type="text" class="medium crm-form-text" id="sepa_cardholder" name="sepa_cardholder" size="25" onkeypress="return ibanbic_validate(event, 'sepa_cardholder')">
        </div>
        <div class="clear"></div>
    </div>
    <div class="crm-section editrow_country-1-section form-item">
        <div class="label"><label for="sepa_country">{ts}Bank country{/ts}</label><span class="crm-marker" style ="padding-left:3px;">*</span></div>
        <div class="content" id='sepa_country_content' >
        <select id="sepa_country" name="sepa_country" placeholder="- select -" class="crm-select2" tabindex="-1" title="Bank Country">
            {foreach from=$nncountries item=txt key=val}
                <option value={$val}>{$txt}</option>
            {/foreach}
            </select>
        </div>
        <div class="clear"></div>
    </div>

    <div class="crm-section sepa_iban-section">
        <div class="label"><label for="sepa_iban">{ts}IBAN or Account number{/ts}</label><span class="crm-marker" style ="padding-left:3px;">*</span></div>
        <div class="content">
            <input type="text" class="medium crm-form-text" id="sepa_iban" autocomplete="off"  size="25" onkeypress="return ibanbic_validate(event, 'iban')">
            <span id="novalnet_sepa_iban_span"></span>
        </div>
        <div class="clear"></div>
    </div>
    <div class="crm-section sepa_bic-section">
        <div class="label"><label for="sepa_bic">{ts}BIC or Bank code{/ts}</label><span class="crm-marker" style ="padding-left:3px;">*</span></div>
        <div class="content">
            <input type="text" class="medium crm-form-text" id="sepa_bic" autocomplete="off" size="25"  onkeypress="return ibanbic_validate(event, 'bic')">
            <span id="novalnet_sepa_bic_span"></span>
        </div>
        <div class="clear"></div>
    </div>
    <div class="crm-section novalnet_sepa_confirm_id-section">
        <div class="label"></div>
        <div class="content">
            <input type="checkbox" id="novalnet_sepa_confirm_id" autocomplete="off" align='left'>
            <label for="novalnet_sepa_confirm_id">{ts}I hereby grant the SEPA direct debit mandate and confirm that the given IBAN and BIC are correct{/ts}</label>
        </div>
        <div class="clear"></div>
    </div>
     <div class="sepaloader" id="sepaloader" style="display:none"> </div>
</div>
<input type='hidden' id='nn_sepa_hash' name='nn_sepa_hash'>
<input type='hidden' id='nn_vendor' value='{$nnvendor}'>
<input type='hidden' id='nn_auth_code' value='{$nnauthcode}'>
<input type='hidden' id='nn_sepa_uniqueid' name='nn_sepa_uniqueid' value='{$nnsepauniqueid}'>
<input type='hidden' id='nn_sepa_autorefill' value='{$nnsepaautorefill}'>
<input type='hidden' id='nn_sepa_input_panhash' value='{$nnsepaiphash}'>
<input type='hidden' id='nn_sepa_merchant_valid_message' value='{$nnsepavendormsg}'>
<input type='hidden' id='nn_sepa_valid_message' value='{$nnsepacardmsg}'>
<input type='hidden' id='nn_sepa_confirm_iban_bic_msg' value='{$nnsepaacceptmandate}'>
<input type='hidden' id='nn_sepa_countryerror_msg' value='{$nnsepacountryerror}'>
<input type='hidden' id='nn_sepa_ibanbic_confirm_id'>
<input type='hidden' id='sepa_bic_gen'>
<input type='hidden' id='sepa_iban_gen'>
<input type='hidden' id='url_country' value="{$ajaxurl}">
    <br>

<style>
{literal}
    .sepaloader {
        position: fixed;
        left: 0px;
        top: 0px;
        width: 100%;
        height: 100%;
        z-index: 9999;
        background: url("{/literal}{$assetsurl}{literal}logos/novalnetloader.gif") 50% 50% no-repeat;
    }
{/literal}
</style>
<noscript>
<div id='javascript_error' style='color:red'>{ts}Please enable the Javascript in your browser to proceed further with the payment{/ts}</div>
    <style>
        {literal}
            #sepa_form {
                display:none;
            }
        {/literal}
    </style>
</noscript>
<script src='{$assetsurl}js/novalnet_sepa.js'></script>

