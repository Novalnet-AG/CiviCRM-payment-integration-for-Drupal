
{crmRegion name="billing-block"}
{* Add 'required' marker to billing fields in this template for front-end / online contribution and event registration forms only. *}
{if $context EQ 'front-end'}
  {assign var=reqMark value=' <span class="crm-marker" title="This field is required.">*</span>'}
{else}
  {assign var=reqMark value=''}
{/if}

{if $paymentProcessor.payment_processor_type == 'Novalnet PayPal'}
  <div id="payment_information">
    <fieldset class="billing_mode-group">
      <legend>
        {$novalnet_paypal_name}
      </legend>
    </fieldset>
  </div>
  {$novalnet_paypal_logo}<br>
  {$novalnet_paypal_desc}
{/if}
{if $paymentProcessor.payment_processor_type == 'Novalnet Prepayment'}
  <div id="payment_information">
    <fieldset class="billing_mode-group">
      <legend>
        {$novalnet_prepayment_name}
      </legend>
    </fieldset>
  </div>
  {$novalnet_prepayment_logo}<br>
  {$novalnet_prepayment_desc}
{/if}
{if $paymentProcessor.payment_processor_type == 'Novalnet Invoice'}
  <div id="payment_information">
    <fieldset class="billing_mode-group">
      <legend>
        {$novalnet_invoice_name}
      </legend>
    </fieldset>
  </div>
  {$novalnet_invoice_logo}<br>
  {$novalnet_invoice_desc}
{/if}
{if $paymentProcessor.payment_processor_type == 'Novalnet Instant Bank Transfer'}
  <div id="payment_information">
    <fieldset class="billing_mode-group">
      <legend>
        {$novalnet_instantbank_name}
      </legend>
    </fieldset>
  </div>
  {$novalnet_instantbank_logo}<br>
  {$novalnet_instantbank_desc}
{/if}
{if $paymentProcessor.payment_processor_type == 'Novalnet iDEAL'}
  <div id="payment_information">
    <fieldset class="billing_mode-group">
      <legend>
        {$novalnet_ideal_name}
      </legend>
    </fieldset>
  </div>
  {$novalnet_ideal_logo}<br>
  {$novalnet_ideal_desc}
{/if}
{if $paymentProcessor.payment_processor_type == 'Novalnet SafetyPay'}
  <div id="payment_information">
    <fieldset class="billing_mode-group">
      <legend>
        {$novalnet_safetypay_name}
      </legend>
    </fieldset>
  </div>
  {$novalnet_safetypay_logo}<br>
  {$novalnet_safetypay_desc}
{/if}
{if $paymentProcessor.payment_processor_type == 'Novalnet Direct Debit Austria'}
 <div id="payment_information">
    <fieldset class="billing_mode-group">
      <legend>
        {$novalnet_elvat_name}
      </legend>
    </fieldset>
  </div>
  {$novalnet_elvat_logo}<br>
    <div class="crm-section {$form.nn_account_holder.name}-section">
      <div class="label">{$form.nn_account_holder.label}{$reqMark}</div>
      <div class="content">{$form.nn_account_holder.html}</div>
      <div class="clear"></div>

    </div>
    <div class="crm-section {$form.nn_bank_account.name}-section">
      <div class="label">{$form.nn_bank_account.label}{$reqMark}</div>
      <div class="content">{$form.nn_bank_account.html}</div>
      <div class="clear"></div>
    </div>
    <div class="crm-section {$form.nn_bank_code.name}-section">
      <div class="label">{$form.nn_bank_code.label}{$reqMark}</div>
      <div class="content">{$form.nn_bank_code.html}</div>
      <div class="clear"></div>
    </div>
      <input type='hidden' id="at_refill" value="{$at_refill}" name ="refill">
    <br>
    {$novalnet_elvat_desc}
{/if}
{if $paymentProcessor.payment_processor_type == 'Novalnet Direct Debit German' }
  <div id="payment_information">
    <fieldset class="billing_mode-group">
      <legend>
        {$novalnet_elvde_name}
      </legend>
    </fieldset>
  </div>
  {$novalnet_elvde_logo}<br>
    <div class="crm-section {$form.nn_de_account_holder.name}-section">
      <div class="label">{$form.nn_de_account_holder.label}{$reqMark}</div>
      <div class="content">{$form.nn_de_account_holder.html}</div>
      <div class="clear"></div>
    </div>
    <div class="crm-section {$form.nn_de_bank_account.name}-section">
      <div class="label">{$form.nn_de_bank_account.label}{$reqMark}</div>
      <div class="content">{$form.nn_de_bank_account.html}</div>
      <div class="clear"></div>
    </div>
    <div class="crm-section {$form.nn_de_bank_code.name}-section">
      <div class="label">{$form.nn_de_bank_code.label}{$reqMark}</div>
      <div class="content">{$form.nn_de_bank_code.html}</div>
      <div class="clear"></div>
    </div>
    {if $nnacdc && $nnacdc==1 }
      <div class="crm-section {$form.nn_de_acdc.name}-section">
        <div class="label">{$form.nn_de_acdc.label}{$reqMark}</div>
        <div class="content">{$form.nn_de_acdc.html}</div>
        <div class="clear"></div>
      </div>
    {/if}<input type='hidden' id="de_refill" value="{$de_refill}" name ="refill"><br>
    {$novalnet_elvde_desc}
{/if}
{if $paymentProcessor.payment_processor_type == 'Novalnet Credit Card 3D Secure' }
  <div id="payment_information">
    <fieldset class="billing_mode-group">
      <legend>
        {$novalnet_cc3d_name}
      </legend>
    </fieldset>
  </div>
  {$novalnet_cc3d_logo}<br>
    <div class="crm-section {$form.nn_cc_card_holder.name}-section">
          <div class="label">{$form.nn_cc_card_holder.label}{$reqMark}</div>
          <div class="content">{$form.nn_cc_card_holder.html}</div>
          <div class="clear"></div>
    </div>
    <div class="crm-section {$form.nn_cc_card_number.name}-section">
          <div class="label">{$form.nn_cc_card_number.label}{$reqMark}</div>
          <div class="content">{$form.nn_cc_card_number.html}</div>
          <div class="clear"></div>
    </div>
    <div class="crm-section {$form.nn_cc_expmonth.name}-section">
          <div class="label">{$form.nn_cc_expmonth.label}{$reqMark}</div>
          <div class="content">{$form.nn_cc_expmonth.html}
           {$form.nn_cc_expyear.html} </div>
          <div class="clear"></div>
    </div>
    <div class="crm-section {$form.nn_cc_cvc.name}-section">
          <div class="label">{$form.nn_cc_cvc.label}{$reqMark}</div>
          <div class="content">{$form.nn_cc_cvc.html}<br>{ts}* On Visa-, Master- and Eurocard you will find the 3 digit CVC-Code near the signature field at the rearside of the creditcard.{/ts}</div>
          <div class="clear"></div>
    </div>
  {$novalnet_cc3d_desc}
{/if}
{if $paymentProcessor.payment_processor_type == 'Novalnet Credit Card'}
  <div id="payment_information">
    <fieldset class="billing_mode-group">
      <legend>
        {$novalnet_cc_name}
      </legend>
    </fieldset>
  </div>
  {$novalnet_cc_logo}<br>
  {if $nnerror}
    <div style='color:red'>{$novalnet_cc_error}</div>
  {else}
    <div id="loading"><img src={$cc_loading_image} alt="Novalnet AG" /></div>
    <div> <iframe id="payment_form_novalnetCc" name="payment_form_novalnetCc" width="100%" height="250" src="{$cc_path}" onload="getFormValue(this)" frameBorder="0" scrolling="no"></iframe></div>
    {literal}
      <script type='text/javascript'>
        function getFormValue(element){
        document.getElementById('payment_form_novalnetCc').style.display = 'block';
            document.getElementById('loading').style.display = 'none';
            var frameObj =(element.contentWindow || element.contentDocument);
            if (frameObj.document) frameObj=frameObj.document;
            var getInputForm = document.getElementById("original_vendor_id");
            var formid=getInputForm.form.getAttribute("id");
             
            document.getElementById(formid).onsubmit = function() {
                document.getElementById("cc_pan_hash").value = frameObj.getElementById("nncc_cardno_id").value;
                document.getElementById("cc_unique_id").value = frameObj.getElementById("nncc_unique_id").value;
                document.getElementById('cc_owner').value = frameObj.getElementById("novalnetCc_cc_owner").value;
                document.getElementById('cc_exp_month').value = frameObj.getElementById("novalnetCc_expiration").value;
                document.getElementById('cc_exp_year').value = frameObj.getElementById("novalnetCc_expiration_yr").value;
                document.getElementById('cc_type').value = frameObj.getElementById("novalnetCc_cc_type").value;
                document.getElementById('cc_cid').value = frameObj.getElementById("novalnetCc_cc_cid").value;

                var cc_type=0; var cc_owner=0; var cc_no=0; var cc_hash=0; var cc_month=0; var cc_year=0; var cc_cid=0;
                if(frameObj.getElementById("novalnetCc_cc_type").value!= '') cc_type = 1;
                if(frameObj.getElementById("novalnetCc_cc_owner").value!= '') cc_owner = 1;
                if(frameObj.getElementById("novalnetCc_cc_number").value!= '') cc_no = 1;
                if(frameObj.getElementById("novalnetCc_expiration").value!= '') cc_month = 1;
                if(frameObj.getElementById("novalnetCc_expiration_yr").value!= '') cc_year = 1;
                if(frameObj.getElementById("novalnetCc_cc_cid").value!= '') cc_cid = 1;
               document.getElementById('cc_fldVdr').value      = cc_type+','+cc_owner+','+cc_no+','+cc_month+','+cc_year+','+cc_cid;
            }
            if(document.getElementById("_qf_Main_upload-bottom")) {
                document.getElementById("_qf_Main_upload-bottom").onclick = function() {
                  document.getElementById("cc_pan_hash").value = frameObj.getElementById("nncc_cardno_id").value;
                  document.getElementById("cc_unique_id").value = frameObj.getElementById("nncc_unique_id").value;
                }
            }
            if (document.getElementById("_qf_Register_upload-bottom")) {
              document.getElementById("_qf_Register_upload-bottom").onclick = function() {
                document.getElementById("cc_pan_hash").value = frameObj.getElementById("nncc_cardno_id").value;
                document.getElementById("cc_unique_id").value = frameObj.getElementById("nncc_unique_id").value;
              }
            }
        }
      </script>
    {/literal}
    {if $cc_customcss}
      <input type=hidden value="{$cc_css_style}" id="original_customstyle_css" name="original_customstyle_css">
      <input type=hidden value="{$cc_css_styleval}" id="original_customstyle_cssval" name="original_customstyle_cssval">
    {/if}
    <input id="original_vendor_id" type="hidden" value="{$vendor_id}" name="original_vendorid">
    <input id="original_vendor_authcode" type="hidden" value="{$auth_code}" name="original_vendorauthcode">
    <input id="cc_unique_id" name="cc_unique_id" type="hidden" value="">
    <input id="cc_pan_hash" name="cc_pan_hash" type="hidden" value="">
    <input id="cc_type" type="hidden" name="cc_type" value="" />
    <input id="cc_owner" type="hidden" name="cc_owner" value="" />
    <input id="cc_exp_month" type="hidden" name="cc_exp_month" value="" />
    <input id="cc_exp_year" type="hidden" name="cc_exp_year" value="" />
    <input id="cc_cid" type="hidden" name="cc_cid" value="" />
    <input id="cc_fldVdr" type="hidden" name="cc_fldVdr" value="" />
    <input id="cc_field_src" type="hidden" name="cc_field_src" value="{$cc_field_src}"/>
    <br>
    {/if}
    {$novalnet_cc_desc}
{/if}
{if $paymentProcessor.payment_processor_type == 'Novalnet Direct Debit SEPA'}

  <div id="payment_information">
    <fieldset class="billing_mode-group">
      <legend>
        {$novalnet_sepa_name}
      </legend>
    </fieldset>
  </div>
  {$novalnet_sepa_logo}<br>
  {if $nnerror}
    <div style='color:red'>{$novalnet_sepa_error}</div>
  {else}

    <div id="sepaloading"><img src={$sepa_loading_image} alt="Novalnet AG" /></div>
    <div> <iframe id="payment_form_novalnetSepa" name="payment_form_novalnetSepa" width="100%" height="415" src="{$sepa_path}" onload="getSepaFormValue(this)" frameBorder="0" scrolling="no"></iframe></div>
    {literal}
      <script type='text/javascript'>
        function getSepaFormValue(element){
            document.getElementById('payment_form_novalnetSepa').style.display = 'block';
            document.getElementById('sepaloading').style.display = 'none';
            var frameObj =(element.contentWindow || element.contentDocument);
            if (frameObj.document) frameObj=frameObj.document;
            var getInputForm = document.getElementById("sepa_pan_hash");
            var formid=getInputForm.form.getAttribute("id");

            var card_owner = frameObj.getElementById("novalnet_sepa_owner");
            if(card_owner) {
            card_owner.onkeyup = function () {
                document.getElementById('sepa_owner').value = card_owner.value;
            }
            }
            document.getElementById(formid).onsubmit = function() {

             var sepa_owner = 0;var sepa_accountno = 0; var sepa_bankcode = 0; var sepa_iban = 0;var sepa_swiftbic = 0;var sepa_hash = 0;var sepa_country = 0;
             getvalue(frameObj);
              if(frameObj.getElementById("novalnet_sepa_owner").value!= '') sepa_owner = 1;
              if(frameObj.getElementById("novalnet_sepa_accountno").value!= '') sepa_accountno = 1;
              if(frameObj.getElementById("novalnet_sepa_bankcode").value!= '') sepa_bankcode = 1;
              if(frameObj.getElementById("novalnet_sepa_iban").value!= '') sepa_iban = 1;
              if(frameObj.getElementById("novalnet_sepa_swiftbic").value!= '') sepa_swiftbic = 1;
              if(frameObj.getElementById("nnsepa_hash").value!= '') sepa_hash = 1;
              if(frameObj.getElementById("novalnet_sepa_country").value!= '') {
                var country = frameObj.getElementById("novalnet_sepa_country");
                sepa_country = 1+'-'+country.options[country.selectedIndex].value;
              }
                document.getElementById('sepa_owner').value = frameObj.getElementById("novalnet_sepa_owner").value;
                document.getElementById("sepa_pan_hash").value = frameObj.getElementById("nnsepa_hash").value;
                document.getElementById("sepa_unique_id").value = frameObj.getElementById("nnsepa_unique_id").value;
                document.getElementById("sepa_bic_confirm").value = frameObj.getElementById("nnsepa_iban_confirmed").value;
                document.getElementById("sepa_mandate_ref").value = frameObj.getElementById("nnsepa_mandate_ref").value;
                document.getElementById("sepa_mandate_date").value = frameObj.getElementById("nnsepa_mandate_date").value;
                document.getElementById("sepa_fldVdr").value = sepa_owner+','+sepa_accountno+','+sepa_bankcode+','+sepa_iban+','+sepa_swiftbic+','+sepa_hash+','+sepa_country;
            }
            if(document.getElementById("_qf_Main_upload-bottom")) {
                document.getElementById("_qf_Main_upload-bottom").onclick = function() {
                document.getElementById("sepa_pan_hash").value = frameObj.getElementById("nnsepa_hash").value;
                document.getElementById("sepa_unique_id").value = frameObj.getElementById("nnsepa_unique_id").value;
                document.getElementById("sepa_bic_confirm").value = frameObj.getElementById("nnsepa_iban_confirmed").value;
                document.getElementById("sepa_mandate_ref").value = frameObj.getElementById("nnsepa_mandate_ref").value;
                document.getElementById("sepa_mandate_date").value = frameObj.getElementById("nnsepa_mandate_date").value;
                }
            }
            if (document.getElementById("_qf_Register_upload-bottom")) {
              document.getElementById("_qf_Register_upload-bottom").onclick = function() {
              getvalue(frameObj);
              }
            }
        }
        function getvalue(frameObj) {
          document.getElementById("sepa_pan_hash").value = frameObj.getElementById("nnsepa_hash").value;
          document.getElementById("sepa_unique_id").value = frameObj.getElementById("nnsepa_unique_id").value;
          document.getElementById("sepa_bic_confirm").value = frameObj.getElementById("nnsepa_iban_confirmed").value;
          document.getElementById("sepa_mandate_ref").value = frameObj.getElementById("nnsepa_mandate_ref").value;
          document.getElementById("sepa_mandate_date").value = frameObj.getElementById("nnsepa_mandate_date").value;
        }

      </script>
    {/literal}
     {if $sepa_customcss}
      <input type=hidden value="{$sepa_css_style}" id="original_sepa_customstyle_css" name="original_sepa_customstyle_css">
      <input type=hidden value="{$sepa_css_styleval}" id="original_sepa_customstyle_cssval" name="original_sepa_customstyle_cssval">
    {/if}
    <input id="sepa_unique_id" name="sepa_unique_id" type="hidden" value="">
    <input id="sepa_pan_hash" name="sepa_pan_hash" type="hidden" value="">
    <input id="sepa_owner" type="hidden" name="sepa_owner" value="" />
    <input id="sepa_mandate_ref" type="hidden" name="sepa_mandate_ref" value="" />
    <input id="sepa_mandate_date" type="hidden" name="sepa_mandate_date" value="" />
    <input id="sepa_bic_confirm" type="hidden" name="sepa_bic_confirm" value="" />
    <input id="sepa_fldVdr" type="hidden" name="sepa_fldVdr" value="" />
    <input id="sepa_src" type="hidden" name="sepa_src" value="{$sepa_src}"/>
    <br>
    {/if}
    {$novalnet_sepa_desc}
{/if}
<script type="text/javascript">
 {literal}
  cj( function( ) {
    assignvalue();
  cj('#first_name').change(function() {
    assignvalue();
  });
  cj('#last_name').change(function() {
      assignvalue();
   });
  cj('#email-5').change(function() {
    assignvalue();
  });
  cj('#street_address-1').change(function() {
    assignvalue();
  });
  cj('#postal_code-1').change(function() {
    assignvalue();
  });
  cj('#city-1').change(function() {
    assignvalue();
  });

 cj('#email').change(function() {
    assignvalue();
  });
  cj('#street_address').change(function() {
    assignvalue();
  });
  cj('#postal_code').change(function() {
    assignvalue();
  });
  cj('#city').change(function() {
    assignvalue();
  });

  cj('#email-Primary').change(function() {
    assignvalue();
  });
  cj('#street_address-Primary').change(function() {
    assignvalue();
  });
  cj('#postal_code-Primary').change(function() {
    assignvalue();
  });
  cj('#city-primary').change(function() {

    assignvalue();
  });

  if (cj('#at_refill').length > 0)  {
    if(cj('#at_refill').val() == 0) {
       cj('#nn_bank_code').val('');
       cj('#nn_bank_account').val('');
       cj('#nn_account_holder').val('');
    }
  }
  if (cj('#de_refill').length > 0)  { 
    if(cj('#de_refill').val() == 0) {
       cj('#nn_de_bank_code').val('');
       cj('#nn_de_bank_account').val('');
       cj('#nn_de_account_holder').val('');
    }
  }
  });

  function assignvalue() {
    var first_name=''; var last_name=''; var address=''; var email=''; var zip='';
    var city='';
    if (cj('#first_name').length > 0)  {
       first_name= document.getElementById("first_name").value;
    }
    if (cj('#last_name').length > 0)  {
       last_name= document.getElementById("last_name").value;
    }
    if (cj('#street_address-1').length > 0)  {
       address= document.getElementById("street_address-1").value;
    } else if (cj('#street_address-Primary').length > 0)  {
       address= document.getElementById("street_address-Primary").value;
    } else if (cj('#street_address').length > 0)  {
       address= document.getElementById("street_address").value;
    }

    if (cj('#city-1').length > 0)  {
       city= document.getElementById("city-1").value;
    } else if (cj('#city').length > 0)  {
       city= document.getElementById("city").value;
    } else if (cj('#city-primary').length > 0)  {
       city= document.getElementById("city-primary").value;
    }

    if (cj('#postal_code-1').length > 0)  {
       zip= document.getElementById("postal_code-1").value;
    } else if (cj('#postal_code-Primary').length > 0) {
       zip= document.getElementById("postal_code-Primary").value;
    } else if (cj('#postal_code').length > 0) {
       zip= document.getElementById("postal_code").value;
    }

    if (cj('#email-5').length > 0)  {
       email= document.getElementById("email-5").value;
    } else if(cj('#email-Primary').length > 0) {
       email= document.getElementById("email-Primary").value;
    } else if(cj('#email').length > 0) {
       email= document.getElementById("email").value;
    }
    var customerdetails = "&name="+first_name+' '+last_name+"&email="+email+"&zip="+zip+"&address="+address+"&city="+city;

    if (document.getElementById("payment_form_novalnetCc")) {
      var cciframesrc = document.getElementById("cc_field_src").value;
      var ccsrc= cciframesrc + "&cc_holder="+first_name+' '+ last_name;
      cj('#payment_form_novalnetCc').attr('src', ccsrc);
      document.getElementById('payment_form_novalnetCc').style.display = 'none';
      document.getElementById('loading').style.display = 'block';
    }
    if (document.getElementById("payment_form_novalnetSepa")) {
      var iframesrc = document.getElementById("sepa_src").value;
      cj('#payment_form_novalnetSepa').attr('src', iframesrc + customerdetails);
      document.getElementById('payment_form_novalnetSepa').style.display = 'none';
      document.getElementById('sepaloading').style.display = 'block';
    }
  }

{/literal}
</script>
{/crmRegion}
