<?php
#########################################################
#                                                       #
#  This module is used for real time processing of      #
#  Novalnet transaction of customers.                   #
#                                                       #
#  Copyright (c) Novalnet AG                            #
#                                                       #
#  Released under the GNU General Public License        #
#  This free contribution made by request.              #
#  If you have found this script usefull a small        #
#  recommendation as well as a comment on merchant form #
#  would be greatly appreciated.                        #
#                                                       #
#  Script : civicrm_novalnet_cc_iframe.php              #
#                                                       #
#########################################################

$nn_url                       = 'https://payport.novalnet.de/direct_form.jsp';
$request                      = array();
$request['nn_lang_nn']        = $_REQUEST['lang_code'];
$request['nn_vendor_id_nn']   = $_REQUEST['vendor_id'];
$request['nn_product_id_nn']  = $_REQUEST['product_id'];
$request['nn_payment_id_nn']  = $_REQUEST['payment_id'];#default
$request['nn_authcode_nn']    = $_REQUEST['authcode'];#default
$request['nn_hash']           = $_REQUEST['panhash'];
$request['fldVdr']            = $_REQUEST['fldVar'];
$request['cc_holder']         = isset($_REQUEST['cc_holder'])?$_REQUEST['cc_holder']:'';

$request                      = array_map('trim', $request);

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
?>
