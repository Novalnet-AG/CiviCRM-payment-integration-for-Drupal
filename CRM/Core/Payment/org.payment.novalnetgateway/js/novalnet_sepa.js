/*
 * Novalnet Direct Debit SEPA Script
 * By Novalnet AG (https://www.novalnet.de)
 * Copyright (c) Novalnet AG
*/

function iban_validate(event, key){
    var keycode = ('which' in event) ? event.which : event.keyCode;
    var reg = /^(?:[A-Za-z0-9]+$)/;
    if(key == 'sepa_cardholder') var reg = /^(?:[a-zA-Z\s\&\-\.]+$)/;

    return (reg.test(String.fromCharCode(keycode)) || keycode == 0 || keycode == 8 || (event.ctrlKey == true && keycode == 114))? true : false;
 }
function sepa_HolderFormat(evt)
{
	var keycode = ( 'which' in evt ) ? evt.which : evt.keyCode;
	var reg = /[^0-9\[\]\/\\#,+@!^()$~%'"=:;<>{}\_\|*°§?`]/g;
	
	return ( reg.test(String.fromCharCode(keycode)) || keycode == 0 || keycode == 8 || keycode == 45 );
}

cj('document').ready(function(){
    cj('#sepa_mandate_toggle').click(function () {
        cj('#sepa_mandate_details').toggle();
    });
    cj('#sepa_cardholder').val((cj('#first_name').val() ? cj('#first_name').val() : '') + ' ' + (cj('#last_name').val() ? cj('#last_name').val() : '')) ;
	var number = cj('#pricevalue').html();
	var sepanumber = cj('#nn_sepa_force_amount').val();
	var sepaforce = cj('#nn_sepa_force_gnt').val();
	number = number.replace(/\D/g, '');
	number = parseInt(number, 10);
	if(sepanumber <= number == false && sepaforce == '1'){
		cj('#sepa_dob').css('display', 'none');
	}
    
    cj('#_qf_Main_upload-bottom').click(function (event) {
		if(cj('[name="sepa_date"]').val() == '' && sepanumber < number) {
			alert('Please enter your date of birth');
			event.preventDefault();
		}
	});
});
