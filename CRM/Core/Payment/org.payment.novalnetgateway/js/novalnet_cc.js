/*
 * Novalnet Direct Debit SEPA Script
 * By Novalnet AG (https://www.novalnet.de)
 * Copyright (c) Novalnet AG
*/

var targetOrigin = 'https://secure.novalnet.de';

function getFormValue()
{
	var textObj   = {
        cvcHintText: cj('#nn_cvc_hint').val(),
        errorText  : cj('#nn_iframe_error').val(),
        card_holder : {
            labelText : cj('#nn_holder_label').val(),
            inputText : cj('#nn_holder_input').val(),
        },
        card_number : {
            labelText : cj('#nn_number_label').val(),
            inputText : cj('#nn_number_input').val(),
        },
        expiry_date : {
            labelText : cj('#nn_expiry_label').val(),
            inputText : cj('#nn_expiry_input').val(),
        },
        cvc  : {
            labelText : cj('#nn_cvc_label').val(),
            inputText : cj('#nn_cvc_input').val(),
        }
    };
     var styleObj = {
            labelStyle: cj("#nn_label").val(),
            inputStyle: cj("#nn_input").val(),
            styleText: cj("#nn_css_text").val(),
    };
    var requestObj = {
        callBack: 'createElements',
        customText: textObj,
        customStyle: styleObj,
    };
    ccloadIframe(JSON.stringify(requestObj))
}

function ccloadIframe(request)
{
    var iframe = cj('#nnIframe')[0];
    iframe = iframe.contentWindow ? iframe.contentWindow : iframe.contentDocument.defaultView;
    iframe.postMessage(request, targetOrigin);
    iframe.postMessage(JSON.stringify({callBack: 'getHeight'}), targetOrigin);
}

cj(window).resize(function() {
    ccloadIframe();
});

cj('document').ready(function(){
    cj('#_qf_Main_upload-bottom').click(
        function (event) {

            var selected_payment = cj("#novalnet_cc").val();
            if ( selected_payment == 'novalnet_cc') {
                var pan_hash = cj("#nn_cc_pan_hash").val();
                if (pan_hash == '') {
                     event.preventDefault();
                     getHashFromserver();
                }
            }else{
                return true;
            }
        }
    );

    cj("radio").click(function(event){
          var payment_name = cj('#novalnet_cc').val();
          if(payment_name ='novalnet_cc'){
            ccloadIframe(JSON.stringify({callBack: 'getHeight'}))
          }
    });

    if (window.addEventListener) {
        // addEventListener works for all major browsers
        window.addEventListener('message', function (e) {
            addEvent(e);
        }, false);
    } else {
        // attachEvent works for IE8
        window.attachEvent('onmessage', function (e) {
            addEvent(e);
        });
    }
    // Function to handle Event Listener
    function addEvent(e)
    {
        if (e.origin === targetOrigin) {
            if (typeof e.data === 'string') {
                // Convert message string to object
                var data = eval('(' + e.data.replace(/(<([^>]+)>)/gi, "") + ')');
            } else {
                var data = e.data;
            }

            if (data['callBack'] == 'getHash') {
                if (data['error_message'] != undefined) {
                    alert(cj('<textarea />').html(data['error_message']).text());
                    return false;
                } else {
                    cj('#nn_cc_pan_hash').val(data['hash']);
                    cj('#nn_cc_uniqueid').val(data['unique_id']);
                    cj('#Main').submit();
                }
            } else if (data['callBack'] == 'getHeight') {
                cj('#nnIframe').attr('height',data['contentHeight']);
            }
        }
    }

    function getHashFromserver()
    {
        ccloadIframe(JSON.stringify({callBack: 'getHash'})); // Call the postMessage event for getting the iframe content height dynamically
    }

});
