<div id="payment_information">
	<input type="hidden" id="novalnet_cc" value="novalnet_cc">
    <fieldset class="billing_mode-group">
      <legend>
        {$novalnet_cc_name}
      </legend>
    </fieldset>
</div>

{$novalnet_cc_logo}<br>
{$novalnet_cc_desc}
{if $smarty.const.MODE_CC == '1' || $paymentProcessor.is_test == '1'}
<div style =color:red;>{ts}The payment will be processed in the test mode therefore amount for this transaction will not be charged{/ts}</div>
{/if}
{$novalnet_cc_notify}
{$novalnet_cc_iframe}

<script type="text/javascript" src='{$assetsurl}js/novalnet_cc.js'></script>
