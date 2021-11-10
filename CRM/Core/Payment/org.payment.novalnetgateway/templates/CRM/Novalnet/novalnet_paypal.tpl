<div id="payment_information">
    <fieldset class="billing_mode-group">
      <legend>
        {$novalnet_paypal_name}
      </legend>
    </fieldset>
</div>
{if $novalnet_paypal_logo}
    {$novalnet_paypal_logo}<br>
{/if}
{$novalnet_paypal_desc}
{if $smarty.const.MODE_PAYPAL == '1' || $paymentProcessor.is_test == '1'}
<div style =color:red;>{ts}The payment will be processed in the test mode therefore amount for this transaction will not be charged{/ts}</div>
{/if}
{$novalnet_paypal_notify}
