<?php
/**
 * Novalnet payment method class
 * This module is used for real time processing of
 * Novalnet transaction of customers.
 *
 * Author    Novalnet AG
 * Copyright (c) Novalnet
 * License   https://www.novalnet.de/payment-plugins/kostenlos/lizenz
 * 
 * If you have found this script usefull a small
 * recommendation as well as a comment on merchant form
 * would be greatly appreciated.
 *
 * Script : novalnetAJAX.php
 */
class CRM_Page_novalnetAJAX {

    /**
     * Perform API call in CURL process
     *
     * @param       none
     * @return      string
     */
    public static function apicall()
    {
        $api_params = $_POST;
        $api_params['lang'] = CRM_Core_Payment_novalnet::getLanguage();
        $dataQuery = CRM_Utils_System::makeQueryString($api_params);
        $httppost  = CRM_Utils_HttpClient::singleton();
        $response  = $httppost->post('https://payport.novalnet.de/autoconfig', $dataQuery);
        echo $response[1];
        exit;
    }    
}
