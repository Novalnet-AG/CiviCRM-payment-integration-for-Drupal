
{crmRegion name="billing-block"}
{* Add 'required' marker to billing fields in this template for front-end / online contribution and event registration forms only. *}
{if $context EQ 'front-end'}
  {assign var=reqMark value=' <span class="crm-marker" title="This field is required.">*</span>'}
{else}
  {assign var=reqMark value=''}
{/if}

{if $paymentProcessor.payment_processor_type == 'Novalnet_Credit_Card' }
    {include file="CRM/Novalnet/novalnet_cc.tpl" context="front-end"}
{elseif $paymentProcessor.payment_processor_type == 'Novalnet_Direct_Debit_SEPA'}
    {include file="CRM/Novalnet/novalnet_sepa.tpl" context="front-end"}
{elseif $paymentProcessor.payment_processor_type == 'Novalnet_Prepayment'}
    {include file="CRM/Novalnet/novalnet_prepayment.tpl" context="front-end"}
{elseif $paymentProcessor.payment_processor_type == 'Novalnet_Invoice'}
    {include file="CRM/Novalnet/novalnet_invoice.tpl" context="front-end"}
{elseif $paymentProcessor.payment_processor_type == 'Novalnet_PayPal'}
    {include file="CRM/Novalnet/novalnet_paypal.tpl" context="front-end"}
{elseif $paymentProcessor.payment_processor_type == 'Novalnet_Instant_Bank_Transfer'}
    {include file="CRM/Novalnet/novalnet_instant.tpl" context="front-end"}
{elseif $paymentProcessor.payment_processor_type == 'Novalnet_iDEAL'}
    {include file="CRM/Novalnet/novalnet_ideal.tpl" context="front-end"}
{elseif $paymentProcessor.payment_processor_type == 'Novalnet_eps'}
    {include file="CRM/Novalnet/novalnet_eps.tpl" context="front-end"}
{elseif $paymentProcessor.payment_processor_type == 'Novalnet_Giropay'}
    {include file="CRM/Novalnet/novalnet_giropay.tpl" context="front-end"}
{/if}
{/crmRegion}
