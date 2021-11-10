/*
 * Novalnet Direct Debit SEPA Script
 * By Novalnet AG (https://www.novalnet.de)
 * Copyright (c) Novalnet AG
*/

cj( function() {
    sepa_refill_cal();
    var formname = cj('#sepa_cardholder').closest('form').attr('name');
    cj("form[name="+formname+"]").submit(function(evt) {
        if (cj("#novalnet_sepa_confirm_id").length > 0 && cj("#novalnet_sepa_confirm_id").is(":checked") == false) {
            evt.preventDefault();
            alert(cj("#nn_sepa_confirm_iban_bic_msg").val());
       }
    });
    cj("#novalnet_sepa_confirm_id").click(function() {
        if (cj("#novalnet_sepa_confirm_id").is(":checked") == true) {
            ibanbic_call();
        } else {
            hide_bank_details();
        }
    });
	cj("#sepa_bic, #sepa_iban, #sepa_cardholder").change(function() {
            hide_bank_details();
    });
    assignValue();
    cj('#first_name, #last_name,#email-5, #email, #email-Primary, #street_address-1, #street_address, #street_address-Primary,#postal_code-1, #postal_code, #postal_code-Primary, #city-1, #city, #city-primary, #city-1, #city, #city-primary, #country-1,#country,#country-primary').change(function() {
        assignValue();
    });
    cj('#novalnet_sepa_confirm_id').attr('checked',false);
});

function ibanbic_call(){

    if (document.getElementById('novalnet_sepa_confirm_id').checked == true){
        cj('#novalnet_sepa_confirm_id').attr('disabled','disabled');
        if (cj('#sepa_cardholder').val() == '' || cj('#sepa_iban').val() == '' || cj('#sepa_country').val() == ''){
            alert(cj("#nn_sepa_valid_message").val());
            hide_bank_details();
            cj("#novalnet_sepa_confirm_id").removeAttr('disabled');
            document.getElementById('novalnet_sepa_confirm_id').checked = false;
            return false;
        }
        var vendor_id       = cj('#nn_vendor').val();
        var vendor_authcode = cj('#nn_auth_code').val();
        var bank_country   = cj('#sepa_country').val();
        var account_holder = cj('#sepa_cardholder').val();
        var bank_account   = cj('#sepa_iban').val();
        var bank_code      = cj('#sepa_bic').val();
        var unique_id      = cj('#nn_sepa_uniqueid').val();
        account_holder = cj.trim(account_holder);
        if (vendor_id == '' || vendor_authcode == '') {
            alert( cj( '#nn_sepa_merchant_valid_message' ).val() );
            hide_bank_details();
            return false;
        }
        if(bank_country == ''){
             alert( cj( '#nn_sepa_countryerror_msg' ).val() );
             return false;
        }
        if(bank_country == 'DE' && bank_code == '' && isNaN(bank_account)){
            bank_code = '123456';
        }
        else if(bank_country == 'DE' && !isNaN(bank_code) && isNaN(bank_account)){
            alert( cj( '#nn_sepa_valid_message' ).val() );
            hide_bank_details();
            return false;
        }
        if(bank_country != 'DE' && (bank_code == '' || ((!isNaN(bank_account) && isNaN(bank_code))
            || (isNaN(bank_account) && !isNaN(bank_code)))) ){
            alert(cj( '#nn_sepa_valid_message' ).val());
            hide_bank_details();
            return false;
        }
        else if((bank_country == 'DE' && (!isNaN(bank_account) && (bank_code == '' || isNaN(bank_code))))){
            alert(cj( '#nn_sepa_valid_message' ).val() );
            hide_bank_details();
            return false;
        }

        if (is_numeric(bank_account) && is_numeric(bank_code)){

            var iban_bic = {'account_holder':account_holder,'bank_account':bank_account,'bank_code':bank_code,'vendor_id':vendor_id,'vendor_authcode':vendor_authcode,'bank_country':bank_country,'unique_id':unique_id,'get_iban_bic':1}
            iban_bic = cj.param(iban_bic);

            sent_xdomainreq_sepa( iban_bic, 'nnsepa_iban' , null);
        }
        else {
            var hash_gen = {'account_holder':account_holder,'bank_account':'','bank_code':'','vendor_id':vendor_id,'vendor_authcode':vendor_authcode,'bank_country':bank_country,'unique_id':unique_id,'sepa_data_approved':1,'mandate_data_req':1,'iban':bank_account,'bic':bank_code}
            hash_gen = cj.param(hash_gen);
            sent_xdomainreq_sepa( hash_gen, 'nnsepa_hash' , null);
        }

        document.getElementById('sepa_bic_gen').value = bank_code;
        document.getElementById('sepa_iban_gen').value = bank_account;
    } else {
        cancel_ibanbic_values();
    }
}

function cancel_ibanbic_values(){
    document.onkeydown = function(evt) {
        return true;
    };
    document.getElementById('novalnet_sepa_confirm_id').checked = false;
    document.getElementById('nn_sepa_ibanbic_confirm_id').value = 0;
    cj('#novalnet_sepa_confirm_id').removeAttr('disabled');
    hide_bank_details();
}

function is_numeric(ele){
    return (/^[0-9]+$/.test(ele));
}

function hide_bank_details() {
    cj('#novalnet_sepa_iban_span').hide();
    cj('#novalnet_sepa_bic_span').hide();
    cj('#novalnet_sepa_confirm_id').attr('checked',false);
    cj("#novalnet_sepa_confirm_id").removeAttr('disabled');
    cj("#nn_sepa_ibanbic_confirm_id").val(0);
}

function sent_xdomainreq_sepa(qryString, ptype, from_iban){
    cj('#sepaloader').css('display', 'block');
    var nnurl = "https://payport.novalnet.de/sepa_iban";
        if ('XDomainRequest' in window && window.XDomainRequest !== null) {
            var xdr = new XDomainRequest();
            xdr.open('POST', nnurl);
            xdr.onload = function () {
                return check_result_sepa(this.responseText, ptype, qryString , from_iban);
            }
            xdr.onerror = function() {
                _result = false;
            };
            xdr.send(qryString);
        }
        else{
            var xmlhttp = (window.XMLHttpRequest) ? new XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP");
            xmlhttp.onreadystatechange=function(){
                if (xmlhttp.readyState==4 && xmlhttp.status==200){
                    return check_result_sepa( xmlhttp.responseText, ptype, qryString , from_iban);
                }
            }
            xmlhttp.open("POST",nnurl,true);
            xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
            xmlhttp.send(qryString);
        }
    }


    function check_result_sepa(response, ptype, qryString, from_iban){
        var data = cj.parseJSON(response);
        cj('#novalnet_sepa_confirm_id').removeAttr('disabled');
        cj('#sepaloader').css('display', 'none');
        if(data.hash_result != 'success'){
            alert(data.hash_result);
            return false;
        }
      switch (ptype){
        case 'nnsepa_iban':
            if(data.IBAN =='' || data.BIC == ''){
                alert( cj( '#nn_sepa_valid_message').val() );
                hide_bank_details();
                return false;
            }
            document.getElementById('sepa_bic_gen').value = data.BIC;
            document.getElementById('sepa_iban_gen').value = data.IBAN;
            cj("#novalnet_sepa_iban_span").text('IBAN : '+ data.IBAN);
            cj("#novalnet_sepa_bic_span").text('BIC : '+ data.BIC);
            cj('#novalnet_sepa_iban_span').show();
            cj('#novalnet_sepa_bic_span').show();

            hash_data = qryString.replace('&get_iban_bic=1','') + '&sepa_data_approved=1&mandate_data_req=1&iban=' + data.IBAN + '&bic=' + data.BIC ;
            sent_xdomainreq_sepa(hash_data, 'nnsepa_hash', null);
          break;
        case 'nnsepa_hash':

            document.getElementById('nn_sepa_hash').value = data.sepa_hash;
          break;
        case 'nnsepa_refil':
            cj('#nn_sepa_hash_generated').val(1);
            cj('#loading-img').hide();
            var hash_string = data.hash_string;
            var acc_hold = hash_string.match('account_holder=(.*)&bank_code');
            var holder='';
            if(acc_hold != null && acc_hold[1] != undefined) holder = acc_hold[1];
            var params = data.hash_string.split("&");
            cj('#nnsepa_mandate_ref').html( data.mandate_ref );
            cj( '#nnsepa_owner' ).val( cj( '#nnsepa_holder_name').val() );
            cj.each( params, function( i, keyVal ){
            temp = keyVal.split('=');
            document.getElementById('sepa_cardholder').value = holder;
            switch (temp[0]){
              case 'bank_account' :
                    cj('#sepa_iban').val( temp[1] );
                break
              case 'bank_code' :
                    cj('#sepa_bic').val( temp[1] );
                break
              case 'bank_country' :
                    cj('#sepa_country').val( temp[1]);
                break;
              case 'iban' :
                    cj('#sepa_iban').val( temp[1] );
                break;
              case 'bic' :
                    if(temp[1] != '123456') {
                       cj('#sepa_bic').val( temp[1] );
                    }
                break;
            }
          });
          if(cj( '#frm_iban').html() != ''){
            cj('#nnsepa_iban_div').show();
            cj('#nnsepa_bic_div').show();
          }
      }
    }

function validateSpecialChars(input_val){
    var re = /[\/\\#,+@!^()$~%.":*?<>{}]/g;
    return re.test(input_val);
}

function disable_background_events(){
    document.onkeydown = function(evt) {
        var charCode = (evt.which) ? evt.which : evt.keyCode;
        if ((evt.ctrlKey == true && charCode == 114)|| charCode == 116) {
          return true;
        }
        return false;
    };
}

function on_change(){
    cj('#novalnet_sepa_confirm_id').attr('checked',false);
    cj('#nn_sepa_panhash').val ='';
    hide_bank_details();
}

function normalizeDate(input) {
  if(input != 'undefined' && input != '') {
    var parts = input.split('-');

    return (parts[2] < 10 ? '0' : '') + Number(parts[2]) + '.'
      + (parts[1] < 10 ? '0' : '') + Number(parts[1]) + '.'
      + parseInt(parts[0]);
  }
}
function sepa_refill_cal() {
    if (cj('#nn_sepa_input_panhash').val() == undefined) {  return false;}
    if (cj('#nn_sepa_autorefill').val() == '1') {
        var vendor_id = cj('#nn_vendor').val();
        var vendor_authcode = cj('#nn_auth_code').val();
        var refillpanhash = cj('#nn_sepa_input_panhash').val();
        var refilluniq = cj('#nn_sepa_uniqueid').val();
        var refill_params = "vendor_id="+vendor_id+"&vendor_authcode="+vendor_authcode+"&unique_id="+refilluniq+"&sepa_data_approved=1&mandate_data_req=1&sepa_hash="+refillpanhash;
        if (refillpanhash != '' && refillpanhash !=undefined && refilluniq != '' && refilluniq !=undefined) {
            sent_xdomainreq_sepa( refill_params , 'nnsepa_refil' , null);
        }
    }else{
        cj('#novalnet_sepa_confirm_id').attr('checked',false);
        cj('#nn_sepa_panhash').attr('value','');
        cj('#sepa_bic').attr('value','');
        cj('#sepa_iban').attr('value','');
    }

}

function keyLock(evt) {
    var charCode = (evt.which) ? evt.which : evt.keyCode
    if ((evt.ctrlKey == true && charCode == 114)|| charCode == 116) {
      return true;
    }
    return false;
}
function ibanbic_validate(event, key){
    var keycode = ('which' in event) ? event.which : event.keyCode;
    var reg = /^(?:[A-Za-z0-9]+$)/;
    if(key == 'sepa_cardholder') var reg = /^(?:[a-zA-Z\s\&\-\.]+$)/;

    return (reg.test(String.fromCharCode(keycode)) || keycode == 0 || keycode == 8 || (event.ctrlKey == true && keycode == 114))? true : false;
 }

function assignValue() {
    var address=''; var email=''; var zip='';
    var city='';var country='';
     var name='';
    if (cj('#first_name').length > 0)  { name= name + cj('#first_name').val(); }
    if (cj('#last_name').length > 0)  { name = name +' ' + cj('#last_name').val(); }

    if (cj('#street_address-1').length > 0)  { address= cj('#street_address-1').val(); }
    else if (cj('#street_address-Primary').length > 0)  { address= cj('#street_address-Primary').val();}
    else if (cj('#street_address').length > 0)  { address= cj('#street_address').val(); }

    if (cj('#city-1').length > 0)  { city= cj("#city-1").val();}
    else if (cj('#city').length > 0)  {city= cj("#city").val();}
    else if (cj('#city-primary').length > 0)  { city= cj("#city-primary").val(); }

    if (cj('#country-1').length > 0)  { country= cj("#country-1").val(); }
    else if (cj('#country').length > 0)  {country= cj("#country").val();}
    else if (cj('#country-primary').length > 0)  { country= cj("#country-primary").val(); }

    if (cj('#postal_code-1').length > 0)  { zip= cj("#postal_code-1").val();}
    else if (cj('#postal_code-Primary').length > 0) { zip= cj("#postal_code-Primary").val();}
    else if (cj('#postal_code').length > 0) { zip= cj("#postal_code").value; }

    if (cj('#email-5').length > 0)  { email= cj("#email-5").val();}
    else if(cj('#email-Primary').length > 0) { email= cj("#email-Primary").val();}
    else if(cj('#email').length > 0) {email= cj("#email").val();}
    cj('#sepa_cardholder').val(name);

    cj.ajax({
        type: 'POST',
        url: cj("#url_country").val(),
        data: {'country_id':country},
        dataType: 'json',
        success: function(data, status) {
            cj("#sepa_country").val(data.iso_code);
            cj( "#sepa_country_content span:first-child" ).text(data.name);
        }
    });
}
