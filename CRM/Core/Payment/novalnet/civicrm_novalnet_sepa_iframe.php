<?php

    $request                = array();
    $nn_url                 = 'https://payport.novalnet.de/direct_form_sepa.jsp';
    $request['lang']        = $_REQUEST['lang_code'];
    $request['vendor_id']   = $_REQUEST['vendor_id'];
    $request['product_id']  = $_REQUEST['product_id'];
    $request['authcode']    = $_REQUEST['authcode'];
    $request['country']     = 'DE';//static because unable to get country on form load
    $request['payment_id']  = $_REQUEST['payment_id'];
    $request['panhash']     = $_REQUEST['panhash'];
    $request['fldVdr']      = $_REQUEST['fldVar'];
    $request['mandate_ref'] = $_REQUEST['mandate_ref'];
    $request['mandate_date']= $_REQUEST['mandate_date'];
    $request['zip']         = isset($_REQUEST['zip']) ? $_REQUEST['zip'] :'';
    $request['email']       = isset($_REQUEST['email']) ? $_REQUEST['email']:'';
    $request['name']        = isset($_REQUEST['name']) ? utf8_decode($_REQUEST['name']):'';
    $request['comp']        = '';
    $request['address']     = isset($_REQUEST['address'])? utf8_decode($_REQUEST['address']):'';
    $request['city']        = isset($_REQUEST['city'])? utf8_decode($_REQUEST['city']):'';

    $request                = array_map('trim', $request);


    if (empty($request['vendor_id']) || empty($request['product_id'])
        || empty($request['authcode']) || empty($request['payment_id'])) {
      echo "Basic parameter not valid";
    }
    elseif (($request['payment_id'] == '37') && (empty($request['name']) || empty($request['zip']) || empty($request['email'])
      || empty($request['address']) || empty($request['city']))) {
      echo "Address details are required to load form";
    }
  else {
    $ch = curl_init($nn_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 240);
    $data = curl_exec($ch);
    $errno = curl_errno($ch);
    $errmsg = curl_error($ch);
    if($errno < 0) $errno = 0;
    curl_close($ch);
    print_r($data);// exit;
 }
?>
