/*
 * Novalnet Invoice Script
 * By Novalnet AG (https://www.novalnet.de)
 * Copyright (c) Novalnet AG
*/
cj('document').ready(function(){
var number = cj('#pricevalue').html();
var invnumber = cj('#nn_inv_force_amount').val();
var invforce = cj('#nn_inv_force_gnt').val();
number = number.replace(/\D/g, '');
number = parseInt(number, 10);
	if(invnumber <= number == false && invforce == '1'){
		cj('#invoice_dob').css('display', 'none');
	}
    cj('#_qf_Main_upload-bottom').click(function (event) {
		if(cj('[name="invoice_date"]').val() == '' && invnumber < number) {
			alert('Please enter your date of birth');
			event.preventDefault();
		}
	});

});
