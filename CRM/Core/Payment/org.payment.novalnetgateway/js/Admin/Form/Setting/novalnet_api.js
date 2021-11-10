/*

 * Novalnet payment module
 *
 * This file is used for loading configuration helper form in back office.
 *
 * This is free contribution made by request.
 * If you have found this file useful a small
 * recommendation as well as a comment on merchant form
 * would be greatly appreciated.
 *
 * @author    Novalnet AG
 * @copyright Copyright by Novalnet
 * @license   https://www.novalnet.de/payment-plugins/kostenlos/lizenz
 *
 * Script: novalnet_api.js
*/
cj( function() {
    if (window.addEventListener) { // For all major browsers, except IE 8 and earlier   
        window.addEventListener("load", novalnet_api_load)
    } else if (window.attachEvent) { // For IE 8 and earlier versions
        window.attachEvent("onload", novalnet_api_load);
    }
});
function novalnet_api_load() {
	if( cj('#novalnet_product_activation_key').val() != '' )
        get_merchant_details();
    cj('#novalnet_product_activation_key').change(function() {
        get_merchant_details();
        return true;
    });
    cj('#novalnet_product_activation_key').closest('form').submit(function(event) {
        var form = this;
        if (cj('#novalnet_ajax_complete').attr('value') == '0') {
            event.preventDefault();
            cj(document).ajaxComplete(function() {
                cj(form).submit();
            });
        }
        return true;
     });
}

function null_basic_params() {
	cj('#nn_vendor, #nn_authcode, #nn_product, #nn_password,#novalnet_product_activation_key').val('');
    cj('#nn_tariff').find('option').remove();
    cj('#nn_subscription_tariff_id').find('option').remove();
    cj('#novalnet_ajax_complete').attr('value', 1);
    cj('#nn_tariff').append(
        cj(
            '<option>', {
                value: '',
                text : '',
            }
        )
    );
    cj('#nn_subscription_tariff_id').append(
        cj(
            '<option>', {
                value: '',
                text : '',
            }
        )
    );
}

function get_merchant_details() {
	var public_key = cj('#novalnet_product_activation_key').val();
    if(cj.trim(public_key) == '') {
        null_basic_params();
        return false;
    }
    cj('#novalnet_ajax_complete').attr('value', 0);
    var data_to_send = {
        'hash': public_key,
    }; 
        cj.ajax(
            {
                type : 'POST',
                url: cj('#novalnet_product_activation_url').val()+'/novalnet/apicall',
                data: data_to_send,
                success: function(data) {
                    process_result(data);
                }
            }
        );

            return true;
}
function process_result(hash_string) {
	var hash_string = JSON.parse(hash_string);
    
    var language = (cj('#crm-container').attr('lang'));
    if(cj('#crm-container').attr('lang') == 'de') {
		cj('label:contains("Yes")').html('Ja');
		cj('label:contains("No")').html('Nein');
	}
    if(hash_string.status == '106') {
         if (language == 'de') {
            alert("Sie müssen die IP-Adresse Ihres Outgoing-Servers ("+ cj('#nn_server_addr').val() +") bei Novalnet hinterlegen. Bitte hinterlegen Sie diese im Novalnet-Administrationsportal oder kontaktieren Sie uns unter technic\@novalnet.de.");
            } else {
                alert("You need to configure your outgoing server IP address ("+ cj('#nn_server_addr').val() +") at Novalnet. Please configure it in Novalnet admin portal or contact technic\@novalnet.de");
            }
    }
    var saved_tariff_id = cj('#nn_tariff').val();
    var saved_sub_tariff_id = cj('#nn_subscription_tariff_id').val();
        if (hash_string.vendor != undefined && hash_string.product != undefined) {

    cj('#nn_tariff').replaceWith('<select id="nn_tariff" name="nn_tariff" ></select>');
    cj('#nn_subscription_tariff_id').replaceWith('<select id="nn_subscription_tariff_id" name= "nn_subscription_tariff_id" ></select>');
        cj('#nn_vendor').val(hash_string.vendor);
        cj('#nn_authcode').val(hash_string.auth_code);
        cj('#nn_product').val(hash_string.product);
        cj('#nn_password').val(hash_string.access_key);
        cj('#novalnet_ajax_complete').attr('value', 1);

        for ( var tariff_id in hash_string.tariff ) {
			var tariff_value = cj.trim( hash_string.tariff[ tariff_id ].name );
			var tariff_type = cj.trim( hash_string.tariff[ tariff_id ].type );
			if (tariff_type.trim() == 2 || tariff_type.trim() == 3) {
				cj('#nn_tariff').append(
					cj(
						'<option>', {
							value: tariff_id.trim(),
							text: tariff_value
						}
					)
				);

				if (saved_tariff_id != undefined && saved_tariff_id == tariff_id.trim()) {
					cj('#nn_tariff').val(saved_tariff_id);
				}
			}
            if (tariff_type.trim() == 1 || tariff_type.trim() == 4) {
                cj('#nn_subscription_tariff_id').append(
                   cj (
                        '<option>', {
                            value: tariff_id.trim(),
                            text: tariff_value
                        }
                    )
                );

                if (saved_sub_tariff_id != undefined && saved_sub_tariff_id == tariff_id.trim()) {
                    cj('#nn_subscription_tariff_id').val(tariff_id);
                }
            }
       }
    } else {
		null_basic_params();
        alert(hash_string.config_result);
    }
}
