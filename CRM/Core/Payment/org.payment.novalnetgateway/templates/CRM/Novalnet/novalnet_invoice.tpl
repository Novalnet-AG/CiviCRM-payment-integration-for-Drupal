<div id="payment_information">
    <fieldset class="billing_mode-group">
      <legend>
        {$novalnet_invoice_name}
      </legend>
    </fieldset>
</div>
{if $novalnet_invoice_logo}
    {$novalnet_invoice_logo}<br>
{/if}
{$novalnet_invoice_desc}
{if $smarty.const.MODE_INVOICE == '1' || $paymentProcessor.is_test == '1'}
<div style =color:red;>{ts}The payment will be processed in the test mode therefore amount for this transaction will not be charged{/ts}</div>
{/if}

{$novalnet_invoice_notify}

{if $invoiceGuarantee =='1'}
<div class="crm-section" id="invoice_dob">{$nn_dateofbirth}<span class="crm-marker" style ="padding-left:3px;">*</span> <input type="date" class="medium crm-form-text" id="invoice" name="invoice_date" size="25" ></div>
    <br>
{/if}
<input type="hidden" class="medium crm-form-text" id="nn_inv_force_amount" name="nn_inv_force_amount" size="25" value="{$nn_inv_force_amount}" >
<input type="hidden" class="medium crm-form-text" id="nn_inv_force_gnt" name="nn_inv_force_gnt" size="25" value="{$nn_inv_force_gnt}" >
<script src='{$assetsurl}js/novalnet_invoice.js'></script>
