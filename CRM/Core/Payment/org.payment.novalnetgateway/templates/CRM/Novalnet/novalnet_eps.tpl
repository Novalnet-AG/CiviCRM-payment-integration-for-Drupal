<div id="payment_information">
    <fieldset class="billing_mode-group">
      <legend>
        {$novalnet_eps_name}
      </legend>
    </fieldset>
</div>
{if $novalnet_eps_logo}
    {$novalnet_eps_logo}<br>
{/if}
{$novalnet_eps_desc}
{if $smarty.const.MODE_EPS == '1' || $paymentProcessor.is_test == '1'}
<div style =color:red;>{ts}The payment will be processed in the test mode therefore amount for this transaction will not be charged{/ts}</div>
{/if}
{$novalnet_eps_notify}
