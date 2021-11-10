<div class="crm-block crm-form-block crm-mysettings-form-block">

  <table>{if $nnerror}
  <tr><span style="color:red"><b>{$nnerror}</b></tr>{/if}
  {if $nnsuccess}
  <tr><span style="color:green"><b>{$nnsuccess}</b></tr>{/if}
    <tr class="crm-mysettings-form-block-specialty">

    <thead>
      <th>{ts}Select{/ts}</th>
      <th>{ts}Novalnet Transaction ID{/ts}</th>
      <th>{ts}Invoice ID{/ts}</th>
      <th>{ts}Amount{/ts}</th>
      </thead>
    </tr>
    {foreach from=$mandateorders item=val key=field}
      <tr>
        <td><input type="radio" name="selectorder" value="{$field}"></td>
        <td>{$val.tid}</td>
        <td>{$val.invoice_id}</td>
        <td>{$val.amount}</td>

      </tr>
    {/foreach}
  </table>
{$form.nn_mandate_date.label}
{$form.nn_mandate_date.html}<br><span class="description">{ts}Enter Mandate signature date in the format of DD-MM-YYYY{/ts}

<div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
{$error}
</div>

