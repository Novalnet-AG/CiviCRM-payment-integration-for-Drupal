<?php
/**
 * Novalnet payment method module
 * This module is used for real time processing of
 * Novalnet transaction of customers.
 *
 * Copyright (c) Novalnet AG
 *
 * Released under the GNU General Public License
 * This free contribution made by request.
 * If you have found this script useful a small
 * recommendation as well as a comment on merchant form
 * would be greatly appreciated.
 *
 * Script: novalnet.php
 *
 */

class CRM_Core_Payment_novalnet {

    /**
     * Get the payment name
     *
     * @param  $payment_type string
     *
     * @return array
     */
     static public function getPaymentMethods($payment_type) {
        $key = array(
            'novalnet_prepayment' => ts('Prepayment'),
            'novalnet_invoice' => ts('Invoice'),
            'novalnet_cc' => ts('Credit Card'),
            'novalnet_paypal' => ts('PayPal'),
            'novalnet_ideal' => ts('iDEAL'),
            'novalnet_instant' => ts('Instant Bank Transfer'),
            'novalnet_sepa' => ts('Direct Debit SEPA'),
            'novalnet_eps' => ts('eps'),
            'novalnet_giropay' => ts('Giropay'),
        );
        return $key[$payment_type];
    }

    /**
     * Get the payment key
     *
     * @param $payment_type string
     *
     * return integer
     */
     static public function getPaymentKey($payment_type) {
        $key = array(
            'novalnet_cc' => 6,
            'novalnet_prepayment' => 27,
            'novalnet_invoice' => 27,
            'novalnet_instant' => 33,
            'novalnet_paypal' => 34,
            'novalnet_ideal' => 49,
            'novalnet_sepa' => 37,
            'novalnet_eps' => 50,
            'novalnet_giropay' => 69,
        );
        return $key[$payment_type];
    }

    /**
     * Set the redirection URL
     *
     * @param $payment_type string
     *
     * return string
     */
     static public function getPaymentUrl($paymentType = '') {

        switch($paymentType) {
            case 'novalnet_prepayment':
            case 'novalnet_invoice':
            case 'novalnet_sepa':
                $url =  'https://payport.novalnet.de/paygate.jsp';
                break;
            case 'novalnet_instant':
            case 'novalnet_ideal':
                $url = 'https://payport.novalnet.de/online_transfer_payport';
                break;
            case 'novalnet_paypal':
                $url = 'https://payport.novalnet.de/paypal_payport';
                break;
            case 'novalnet_eps':
            case 'novalnet_giropay':
                $url = 'https://payport.novalnet.de/giropay';
                break;
        }

        return $url;
    }

    /**
     * Set the payment description
     *
     * @param  $selected_payment string
     * @param  $mode integer
     *
     * @return string
     */
    static public function novalnetPaymentDescription($selected_payment, $mode) {
        $description = '';
        switch($selected_payment) {
            case 'novalnet_prepayment':
            case 'novalnet_invoice':
                $description = ts("Once you've submitted the order, you will receive an e-mail with account details to make payment");
                break;
            case 'novalnet_cc':
                $description = ts('The amount will be debited from your credit card once the order is submitted');
                break;
            case 'novalnet_instant':
            case 'novalnet_ideal':
            case 'novalnet_paypal':
            case 'novalnet_eps':
            case 'novalnet_giropay':
                $description = ts('After the successful verification, you will be redirected to Novalnet secure order page to proceed with the payment').'<br>';
                $description .= ts('Please don’t close the browser after successful payment, until you have been redirected back to the Shop');
                break;
            case 'novalnet_sepa':
                $description = ts('Your account will be debited upon the order submission');
                break;
        }
        if ($mode) {
            $description .= '<br><div style =color:red;>' . ts('The payment will be processed in the test mode therefore amount for this transaction will not be charged') . '</div>';
        }

        return $description;
    }

    /**
     * Set the logo and description of the payment
     *
     * @param  $payment object
     * @param  $name string
     *
     * @return string
     */
   static public function assignLogoAndDescription($payment, $name = '') {
        $config = CRM_Core_Config::singleton();
        $template = CRM_Core_Smarty::singleton();
        $logo =  '';$pay = 'novalnet_pay_logo';
        $novalnet_image_path = $config->resourceBase . 'CRM/Core/Payment/org.payment.novalnetgateway/logos/';
        $redirectionUrl = ts('http://www.novalnet.com');
		// Check whether the logo is enabled
        if (!isset($config->$pay) || $config->$pay) {
            $paymentname = self::getPaymentMethods($payment->_paymentType);
			$logo .= "<a href=$redirectionUrl target=_blank><img src =" . $novalnet_image_path . $payment->_paymentType .".png alt ='$paymentname' title='$paymentname' height ='20px'></a>";

            $nn_cc_amexlogo_active = Civi::settings()->get('nn_cc_amexlogo_active');
            $nn_cc_cartasilogo_active = Civi::settings()->get('nn_cc_cartasilogo_active');
            $nn_cc_maestrologo_active = Civi::settings()->get('nn_cc_maestrologo_active');

            if ($payment->_paymentType == 'novalnet_cc') {
				$logo .= "  <a href=$redirectionUrl target=_blank><img src =" . $novalnet_image_path . "mastercard.png" . " alt ='$paymentname' title='$paymentname' height ='20px'></a>";
				if(isset($nn_cc_amexlogo_active) && $nn_cc_amexlogo_active == 1) {
					$logo .= "  <a href=$redirectionUrl target=_blank><img src =" . $novalnet_image_path . "amex.png" . " alt ='$paymentname' title='$paymentname' height ='20px'></a>";
				}
				if (isset($nn_cc_cartasilogo_active) && $nn_cc_cartasilogo_active == 1) {
					$logo .= "  <a href=$redirectionUrl target=_blank><img src =" . $novalnet_image_path . "cartasi.png" . " alt ='$paymentname' title='$paymentname' height ='20px'></a>";
				}
				if (isset($nn_cc_maestrologo_active) && $nn_cc_maestrologo_active == 1) {
					$logo .= "  <a href=$redirectionUrl target=_blank><img src =" . $novalnet_image_path . "maestro.png" . " alt ='$paymentname' title='$paymentname' height ='20px'></a>";
				}
			}
        }
        $desc = self::novalnetPaymentDescription($payment->_paymentType, $payment->_testmode);
        if ($payment->_paymentType == 'novalnet_cc') {
            $desc .= ts('Please don’t close the browser after successful payment, until you have been redirected back to the Shop');
        }
        $template->assign($payment->_paymentType . '_name', $name);
        $template->assign($payment->_paymentType . '_logo', $logo);
        $template->assign($payment->_paymentType . '_desc', $desc);
    }

    /**
     * Convert amount into cents.
     *
     * @param $amount double
     * @param $data array
     *
     * return double
     */
     static public function centsConvert($amount, &$data) {
        $amount = sprintf('%0.2f', $amount);
        $data['amount'] = str_replace(array('.',','), array('',''), $amount);
    }

    /**
     * Encode the data of array
     * @param $data array
     * @param $password string
     * @param $toBeEncoded array
     *
     * @param mixed
     */

     static public function novalnetEncode(&$data, $password, $toBeEncoded) {
        if (!function_exists('base64_encode') || !function_exists('pack') || !function_exists('crc32')) {
            return FALSE;
        }

        foreach ($toBeEncoded as $_value) {
            $fields = $data[$_value];
            if ($fields === '') {
                return FALSE;
            }
            try {
                $crc = sprintf('%u', crc32($fields)); //%u is a must for ccrc32 returns a signed value
                $fields = $crc . "|" . $fields;
                $fields = bin2hex($fields . $password);
                $fields = strrev(base64_encode($fields));
                $data[$_value] = $fields;
            } catch (Exception $e) {
                return FALSE;
            }
        }
        return TRUE;
    }

    /**
     * Check the return hash value
     *
     * @param $response array
     * @param $key string
     *
     * @return boolean
     */
    static public function novalnetCheckHash($response, $key) {
        self::generateNovalnetHash($response, $key);
        if ($response['hash2'] == $response['hash']) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Generate hash for before redirection
     *
     * @param $data array
     * @param boolean value string
     *
     * @return boolean
     */
    static public function generateNovalnetHash(&$data, $password) {

        $hashFields = array('auth_code', 'product', 'tariff', 'amount', 'test_mode', 'uniqid');
        $str = NULL;
        foreach ($hashFields as $_value) {
            if ($data[$_value] == '') {
                return FALSE;
            }
            $str .= $data[$_value];
        }
        $data['hash'] = md5($str . strrev($password));
        return true;
    }

    /**
     * decode the data
     *
     * @param $data array
     * @param $password string
     *
     * @return mixed
     */
     static public function novalnetDecode(&$data, $password) {

        $toBeEncoded = array('auth_code', 'product', 'tariff', 'test_mode', 'uniqid', 'amount');
        foreach ($toBeEncoded as $_value) {
            $fields = $data[$_value];
            if (empty($data)) {
                return FALSE;
            }
            try {
                $fields = base64_decode(strrev($fields));
                $fields = pack("H" . strlen($fields), $fields);
                $fields = substr($fields, 0, stripos($fields, $password));
                $pos = strpos($fields, "|");
                if ($pos === FALSE) {
                    return FALSE;
                }
                $crc = substr($fields, 0, $pos);
                $value = trim(substr($fields, $pos + 1));
                if ($crc != sprintf('%u', crc32($value))) {
                    return FALSE;
                }
                $data[$_value] = $value;
            } catch (Exception $e) {
                return FALSE;
            }
        }
    }

    /*
     *  Get the submit form for redirection payments
     *
     *  @param $form_name string
     *  @param $form_elements array
     *  @param $form_action_url string
     *
     *  @return string
     */

     static public function getSubmitForm($form_name, $form_elements, $form_action_url) {
        $form_start = '<form name="' . $form_name . '" id="' . $form_name . '" action="' . $form_action_url . '" method="post">';
        $form_elements_html = '';
        foreach ($form_elements as $key => $value) {
            $form_elements_html .= '<input type="hidden" name="' . $key . '" value="' . $value . '" id="' . $key . '" />';
        }
        $form_elements_html .= ts('You will be redirected to Novalnet AG in a few seconds.');
        $form_elements_html .= '<br><input type="submit" name="submitbutton" value="' . ts('Continue') . '" id="submitbutton" />';
        $form_end = '</form>';
        echo ($form_start . $form_elements_html . $form_end);
        echo '<script type="text/javascript">document.getElementById("nn_redirect_form").submit();</script>';
        exit;
    }

    /*
     *  Get the submit form for cc payment
     *
     *  @param $component string
     *
     *  @return none
     */

     static public function getCCSubmitForm($component) {
		if ($component == 'contribute') {

			CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/contribute/transact', "_qf_Confirm_display=1&qfKey={$_SESSION['qfKey']}", TRUE, NULL, FALSE));
		} else {
			CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/event/register', "_qf_Confirm_display=1&qfKey={$_SESSION['qfKey']}", TRUE, NULL, FALSE));
		}
    }

    /**
     *  Check the valid component
     *
     *  @param $component string
     *
     *  @return none
     */
    static public function checkComponent($component) {
        if ($component != 'contribute' && $component != 'event') {
            CRM_Core_Error::fatal(ts('Component is invalid'));
        }
    }

    /**
     * Get the Profile details
     *
     * param $params array
     * param $data array
     *
     * @return none
     */
    static public function getProfileDetails($params, &$data) {

        $data['email'] = (isset($params['email-Primary']) ? trim($params['email-Primary']) : (isset($params['email-5']) ? trim($params['email-5']) : (isset($params['email']) ? trim(($params['email'])) : '')));

        $data['first_name'] = (isset($params['first_name']) ? trim($params['first_name']) : '');

        $data['last_name'] = (isset($params['last_name']) ? trim($params['last_name']) : '');

        $data['city'] = (isset($params['city']) ? trim($params['city']) : (isset($params['city-1']) ? trim($params['city-1']) : (isset($params['city-primary']) ? trim(($params['city-primary'])) : '')));

        $data['street'] = (isset($params['street_address-Primary']) ? trim($params['street_address-Primary']) : (isset($params['street_address-1']) ? trim($params['street_address-1']) : (isset($params['street_address']) ? trim(($params['street_address'])) : '')));

        $data['zip'] = (isset($params['postal_code-Primary']) ? trim($params['postal_code-Primary']) : (isset($params['postal_code-1']) ? trim($params['postal_code-1']) : (isset($params['postal_code']) ? trim(($params['postal_code'])) : '')));

        $data['country'] = (isset($params['country-Primary']) ? trim($params['country-Primary']) : (isset($params['country-1']) ? trim($params['country-1']) : (isset($params['country']) ? trim(($params['country'])) : '')));
        $data['country']    = CRM_Core_PseudoConstant::countryIsoCode($data['country']);

        if (!CRM_Utils_Rule::email($data['email']) || (empty($data['first_name']) && empty($data['last_name']))) {
            $data['value'] = ts('Customer name/email fields are not valid');
            return false;
        }
        if (empty($data['first_name']) || empty($data['last_name'])) {
            $name = trim($data['first_name']) . trim($data['last_name']);
            list($data['first_name'], $data['last_name']) = preg_match('/\s/', $name) ? explode(' ', $name, 2) : array($name, $name);
        }
        foreach ($data as $key => $value) {
            if ($value == '') {
                $data['value'] = ts('Address details are required to continue this transaction.');
                return false;
            }
        }
        return true;

    }

    /**
     *  Set product on_hold param
     *
     *  @param $data array
     *  @param $ml double
     *  @return  none
     */
    static public function manualCheckLimit(&$data, $ml) {
        if ($ml) {
            if (intval($ml) > 0 && $data['amount'] >= intval($ml)) {
                $data['on_hold'] = 1;
            }
        }
    }

    /**
     * Set complete params for redirection method
     *
     * @param $component string
     * @param $params array
     * @param $add_data array
     * @param $store_session bool
     *
     * @return  none
     */
    static public function orderCompleteParam($component, $params, &$add_data, $store_session= false) {
        if (isset($params['id'])) {
            $temp_data['id'] = $params['id'];
        }
        $temp_data['module'] = $component;
        $temp_data['orderid'] = $params['invoiceID'];
        $temp_data['cntid'] = $params['contactID'];
        $temp_data['contid'] = $params['contributionID'];
        $temp_data['cntpid'] = isset($params['contributionPageID']) ? $params['contributionPageID'] : '';
        $temp_data['org_amount'] = $params['amount'];
        $temp_data['qfKey'] = $params['qfKey'];
        $temp_data['is_recur'] = (isset($params['is_recur']) && $params['is_recur'] == '1') ? '1' : '0';
        if ($temp_data['is_recur']) {
            $temp_data['frequency_unit'] = $params['frequency_unit'];
            $temp_data['frequency_interval'] = $params['frequency_interval'];
            $temp_data['installments'] = (isset($params['installments']) ? $params['installments'] : '');
        }
        if ($component == 'event') {
            $temp_data['eid'] = $params['eventID'];
            $temp_data['pid'] = $params['participantID'];
        } else {
            $membershipID = CRM_Utils_Array::value('membershipID', $params);
            if ($membershipID) {
                $temp_data['mid'] = $membershipID;
            }
            $relatedContactID = CRM_Utils_Array::value('related_contact', $params);
            if ($relatedContactID) {
                $temp_data['rcid'] = $relatedContactID;
                $onBehalfDupeAlert = CRM_Utils_Array::value('onbehalf_dupe_alert', $params);
                if ($onBehalfDupeAlert) {
                    $temp_data['oda'] = $onBehalfDupeAlert;
                }
            }
        }
        if ($store_session) {
                $_SESSION['novalnet']['completedata'] = $temp_data;
        } else {
            $add_data = array_merge($add_data, $temp_data);
        }

    }

    /**
     * Set url for redirecting to the corresponding page (event/contribution)
     *
     * @param  $params array
     * @param  $component string
     * @param  $cancelURL string
     * @return none
     */
    static public function redirectUrl($component, &$cancelURL, $params) {
        $cancelUrlString = "=1&cancel=1&qfKey={$params['qfKey']}";
        if ($component == 'contribute') {
            $cancelURL = CRM_Utils_System::url('civicrm/contribute/transact', $cancelUrlString, TRUE, NULL, FALSE);
        } elseif ($component == 'event') {
            $eventid = $params['eventID'];
            $cancelUrlString = "id={$eventid}";
            $cancelURL = CRM_Utils_System::url('civicrm/event/register', $cancelUrlString, TRUE, NULL, FALSE);
        }
    }

    /**
     * Set postback call
     *
     * @param $response array
     * @param $amount double
     * @param $paymentType string
     *
     * @return null
     */
    static public function updateCallbackTable($response, $amount = NULL, $paymentType = NULL) {
        $postback = array();
        $queryParams = array(
            1 => array($response['order_no'], 'String'),
            2 => array($amount, 'Integer'),
            3 => array(date('Y-m-d H:i:s'), 'String'),
            4 => array($response['tid'], 'Integer'),
        );
        $update = CRM_Core_DAO::executeQuery("Insert into novalnet_callback (order_id, callback_amount, reference_tid, callback_datetime, callback_tid) values (%1, %2, '', %3, %4)", $queryParams);
        if (isset($_SESSION['nn']))
            unset($_SESSION['nn']);
        if (isset($_SESSION['sepa']))
            unset($_SESSION['sepa']);
        if (isset($_SESSION['invoice']))
            unset($_SESSION['invoice']);
        if (isset($_SESSION['novalnet']))
            unset($_SESSION['novalnet']);
    }

    /**
     * method to validate the card details of user.
     *
     * @param $sepaData array
     *
     * @return boolean
     */
    static public function validateSepaData($sepaData) {
        return (empty($sepaData['panhash']) || empty($sepaData['uniqid']) || preg_match('/[#%\^<>@$=*!]/', $sepaData['holder']));
    }

    /**
     * Check status of payment.
     *
     * @param $_response array
     *
     * @return bool/string
     */
    static public function checkstatus($_response) {

        if (isset($_response['status']) && $_response['status'] == 100) {
            return TRUE;
        } else {
            $error = self::getstatus_error($_response);
            return $error;
        }
    }

    /**
     * payment method error display.
     *
     * @param $_response array
     *
     * @return string
     */
     static public function getstatus_error($_response) {

        if (isset($_response['subscription_update']->status_message)) {
            $nn_status_error = html_entity_decode($_response['subscription_update']->status_message, ENT_QUOTES);
        } elseif (isset($_response['status_desc'])) {
            $nn_status_error = html_entity_decode($_response['status_desc'], ENT_QUOTES);
        } elseif (isset($_response['status_text'])) {
            $nn_status_error = html_entity_decode($_response['status_text'], ENT_QUOTES);
        } elseif (isset($_response['status_message'])) {
            $nn_status_error = html_entity_decode($_response['status_message'], ENT_QUOTES);
        } else {
            $nn_status_error = ts('There was an error and your payment could not be completed.');
        }
        return $nn_status_error;
    }

    /**
     * payment method language.
     *
     * @param none
     *
     * @return string
     */
    static public function getLanguage() {
        global $tsLocale;
        $language = strtoupper(strstr($tsLocale, '_', True));
        return (($language == 'DE' || $language == 'EN') ? $language : 'EN');
    }

    /*
     * build transaction comments
     *
     * @param $response array
     * @param $test_mode string
     * @param $paymentType string
     *
     * @return string
     */

    static public function getTransactionComments($response, $test_mode, $paymentType= NULL) {
        $config = CRM_Core_Config::singleton();
        $comments = '';$break = '<br>';$space = ' ';$comments .= $break;

        if ($test_mode)
            $comments .= $test_mode . $break;

        $comments .= ts(str_replace('_',' ',$response['processor_name'])) . $break . ts('Novalnet transaction ID :') . $response['tid'];
        if (in_array($paymentType, array('novalnet_prepayment', 'novalnet_invoice'))) {
            $ref1 = $paymentType.'_payment_ref1';$ref2 = $paymentType.'_payment_ref2';$ref3 = $paymentType.'_payment_ref3';
            $ref = array ('ref1'=> Civi::settings()->get($ref1), 'ref2'=> Civi::settings()->get($ref2),'ref3' => Civi::settings()->get($ref3));
            $enabled_ref = array_count_values($ref);
            $comments .= $break . $break . ts('Please transfer the amount to the below mentioned account details of our payment processor Novalnet') . $break;
            if ($response['due_date'] != '') {
                $comments .= ts('Due date :') . $space . CRM_Utils_Date::customFormat($response['due_date']) . $break;
            }
            $comments .= ts('Account holder :') . $space . 'NOVALNET AG' . $break;
            $comments .= ts('IBAN :') . $space . $response['invoice_iban'] . $break;
            $comments .= ts('BIC :') . $space . $response['invoice_bic'] . $break;
            $comments .= ts('Bank :') . $space . $response['invoice_bankname'] . $space . $response['invoice_bankplace'] . $break;
            $comments .= ts('Amount :') . $space . CRM_Utils_Money::format($response['org_amount']) . $break. $break;
            $comments .= (($enabled_ref[1] > 1) ? ts('Please use any one of the following references as the payment reference, as only through this way your payment is matched and assigned to the order:'):ts('Please use the following payment reference for your money transfer, as only through this way your payment is matched and assigned to the order:')) . $break;
            $i = 1;
            if ($ref['ref1']) {
                $comments .= ($enabled_ref[1] == 1) ? ts('Payment Reference :') : ts("Payment Reference %1 :", array(1 => $i));
                $comments .= $space .'BNR-' . $response['product'] . '-' .$response['order_no']. $break;
                $i++;
            }
            if ($ref['ref2']) {
                $comments .= ($enabled_ref[1] == 1) ? ts('Payment Reference :') : ts("Payment Reference %1 : TID", array(1 => $i));
                $comments .= $space . $response['tid'] . $break;
                $i++;
            }
            if ($ref['ref3']) {
                $comments .= ($enabled_ref[1] == 1) ? ts('Payment Reference :') : ts("Payment Reference %1 : Order number", array(1 => $i));
                $comments .= $space . $response['order_no'] . $break . $break;
            }
        }
        return $comments;
    }

    /*
     * build the parameters in common
     *
     * @param $params array
     * @param $data array
     * @param $paymentType string
     *
     * @return none
     */
    static public function urlparams($params, &$data, $paymentType) {
        global $user;

        $config = CRM_Core_Config::singleton();

        $customer_no = (isset($user->uid) && $user->uid != 0) ? $user->uid : 'guest';
        $data['currency'] = $params['currencyID'];
        $data['gender'] = 'u';
        $data['search_in_street'] = 1;
        $data['country_code'] = $data['country'];
        $data['remote_ip'] = CRM_Utils_System::ipAddress();
        $data['order_no'] = $params['invoiceID'];
        $data['session'] = session_id();
        $data['customer_no'] = $customer_no;
        $data['system_name'] = 'drupal - civicrm';
        $data['system_version'] = VERSION . '-' . CRM_Utils_System::version() . '-NN3.0.0';
        $data['system_ip'] = CRM_Utils_System::ipAddress();
        $data['system_url'] = url('', array('absolute' => TRUE));
        if (in_array($paymentType, array('novalnet_instant', 'novalnet_paypal', 'novalnet_ideal', 'novalnet_eps', 'novalnet_giropay'))) {
            $data['implementation'] = 'PHP';
        }
        $nn_referrer_id = Civi::settings()->get('nn_referrer_id');
        if (isset($nn_referrer_id) && is_numeric(trim($nn_referrer_id))) {
            $data['referrer_id'] = trim($nn_referrer_id);
        }
    }

    /**
     * build returl urls
     *
     * @param $data array
     * @param $component string
     * @param $processorName string
     *
     * @return none
     */
    static public function returnUrlParams( &$data ,$component, $processorName) {
		$return_url = CRM_Utils_System::baseCMSURL() . "?q=civicrm/payment/ipn?processor_name=$processorName&md=".$component."&qfKey=" . $_SESSION['qfKey'] . '&inId=' . $data['order_no'] . '&processor_name=' . $processorName;
        $data['return_url'] = $return_url;
        $data['return_method'] = 'POST';
        $data['error_return_url'] = $return_url;
        $data['error_return_method'] = 'POST';
    }

    /**
     * payment methods to update the paymentinstrumentid.
     *
     * @param $response array
     *
     * @return null
     */
    static public function paymentNameUpdation($response) {
        $instrumentid = self::getProcessorId($response['processor_name']);
        if ($instrumentid) {
            $updateData = array('payment_instrument_id' => $instrumentid);
            self::contributionUpdate($updateData, $response['orderid']);
        }
    }

    /**
     * set payment instrument value.
     *
     * @param $processor_name string
     *
     * @return double
     */
    static public function getProcessorId($processor_name) {
        $processor_name = str_replace('_', ' ', $processor_name);
        $optionValue = new CRM_Core_BAO_OptionValue();
        $optionValue->name = $processor_name;

        return ($optionValue->find(TRUE)) ? $optionValue->value : '';
    }

    /**
     * update contrubution
     *
     * @param $invoice_id string
     * @param $data array
     *
     * @return none
     */
    static public function contributionUpdate($data, $invoice_id) {
        $contribution = new CRM_Contribute_BAO_Contribution();
        $contribution->invoice_id = $invoice_id;
        if ($contribution->find(TRUE)) {
            foreach ($data as $k => $v) {
                $contribution->$k = $v;
            }
            $contribution->receive_date = CRM_Utils_Date::isoToMysql($contribution->receive_date);
            $contribution->receipt_date = CRM_Utils_Date::isoToMysql($contribution->receipt_date);
            $contribution->thankyou_date = CRM_Utils_Date::isoToMysql($contribution->thankyou_date);
            $contribution->cancel_date = CRM_Utils_Date::isoToMysql($contribution->cancel_date);
            $contribution->save();
        }
    }

    /**
     * update contrubution on recurring
     *
     * @param $invoice_id string
     * @param $data array
     *
     * @return none
     */
    static public function contributionrecurUpdate($data, $invoice_id) {
        $contributionrec = new CRM_Contribute_BAO_ContributionRecur();
        $contributionrec->invoice_id = $invoice_id;

        if ($contributionrec->find(TRUE)) {
            foreach ($data as $k => $v) {
                $contributionrec->$k = $v;
            }
            $contributionrec->modified_date = date('YmdHis');
            if(isset($contributionrec->end_date)) {
              $contributionrec->end_date = CRM_Utils_Date::isoToMysql($contributionrec->end_date);
            }
            $contributionrec->start_date = CRM_Utils_Date::isoToMysql($contributionrec->start_date);
            $contributionrec->create_date = CRM_Utils_Date::isoToMysql($contributionrec->create_date);
            $contributionrec->next_sched_contribution_date = CRM_Utils_Date::isoToMysql($contributionrec->next_sched_contribution_date);
            $contributionrec->save();
        }
    }

    /**
     * payment methods to update the paymentinstrumentid.
     *
     * @param $response array
     *
     * @return null
     */
     static public function paymentUpdation($response) {
        $updateData = array('contribution_status_id' => 3);
        self::contributionUpdate($updateData, $response['orderid']);
        if (isset($response['is_recu']) && $response['is_recu'])
            self::contributionrecurUpdate($updateData, $response['orderid']);
    }

    /**
     * payment methods to update the paymentinstrumentid.
     *
     * @param $response array
     *
     * @return null
     */
    static  public function paymentUpdationOnRecur($response) {
        $updateData = array('contribution_status_id' => 3);
        self::contributionrecurUpdate($updateData, $response['orderid']);
    }

    /**
     * update comments for error
     *
     * @param $novalnetResponse array
     * @param $error string
     *
     * @return none
     */
    static  public function commentsOnError($novalnetResponse, $error) {

        $notes = ts('Novalnet transaction details') . '<br>' . $error;

        if (isset($novalnetResponse['tid']))
            $notes .= '<br>' . ts('Novalnet transaction ID :') . $novalnetResponse['tid'];

        $contribution = new CRM_Contribute_BAO_Contribution();
        $contribution->invoice_id = $novalnetResponse['orderid'];
        if ($contribution->find(TRUE)) {
            $note = new CRM_Core_DAO_Note();
            $note->entity_table = 'civicrm_contribution';
            $note->entity_id = $contribution->id;
            $note->note = $notes;
            $note->modified_date = CRM_Utils_Date::isoToMysql(date('Y-m-d'));
            $note->contact_id = $contribution->contact_id;
            $note->save();
        }
    }

    /**
     * Get Reference for the novalnet orders
     *
     * @param $data array
     * @param $paymentkey string
     *
     * @return null
     */
     static public function getPaymentReference(&$data, $paymentkey) {
        $config = (array) CRM_Core_Config::singleton();

        $reference_one = (isset($config[$paymentkey . '_ref_one']) ? trim(strip_tags(html_entity_decode($config[$paymentkey . '_ref_one']))) : '');
        $reference_two = (isset($config[$paymentkey . '_ref_two']) ? trim(strip_tags(html_entity_decode($config[$paymentkey . '_ref_two']))) : '');
        if (!empty($reference_one)) {
            $data['input1'] = 'reference1';
            $data['inputval1'] = $reference_one;
        }
        if (!empty($reference_two)) {
            $data['input2'] = 'reference2';
            $data['inputval2'] = $reference_two;
        }
    }

    /**
     * Get subscription Frequency interval
     *
     * @param $params array
     *
     * @return string
     */
    static public function getFrequencyInterval($params) {


        if ($params['frequency_unit'] == 'week') {
			$params['frequency_unit'] = 'day';
            $params['frequency_interval'] = $params['frequency_interval'] *7 ;
        }
        return $params['frequency_interval'] . str_replace(array('day','month','year'),array('d','m','y'),$params['frequency_unit']);
    }

    /**
     * Validate the subscription params
     *
     * @param  $frequency_interval int
     *
     * @return string
     */
     static public function validateOnRecurring($frequency_interval) {
         $nn_subscription_tariff_id  = Civi::settings()->get('nn_subscription_tariff_id');
        if (empty($frequency_interval)) {
            return ts('Please fill in all the mandatory fields');
        }
        if (!trim($nn_subscription_tariff_id)) {
            return ts('Please enter valid Novalnet Subscription Tariff ID');
        }
        return '';
    }

    /**
     * Update the details after successfull subscription cancel
     *
     * @param $data array
     * @param $reason string
     *
     * @return none
     */
     static public function updateOnSubscriptionCancel($data, $reason) {
        $comments = "<br>" . ts('Subscription has been canceled due to:') . $reason;
        $config = CRM_Core_Config::singleton();

        $contribution = new CRM_Contribute_BAO_Contribution();
        $contribution->trxn_id = $data['tid'];
        if ($contribution->find(TRUE)) {
			$nn_subs_cancel_status  = Civi::settings()->get('nn_subs_cancel_status');
            $updateData = array('contribution_status_id' => $nn_subs_cancel_status);
            $updateData['cancel_date'] = CRM_Utils_Date::isoToMysql(date('YmdHis'));
            self::contributionrecurUpdate($updateData, $contribution->invoice_id);
        }
        $note = new CRM_Core_DAO_Note();
        $note->entity_table = 'civicrm_contribution';
        $note->entity_id = $contribution->id;
        $modified_date = date('Y-m-d');
        if ($note->find(TRUE)) {
            $note->note = $note->note . $comments;
        } else {
            $note->note = $comments;
            $note->contact_id = $entity->id;
        }
        $note->modified_date = CRM_Utils_Date::isoToMysql($modified_date);
        $note->save();
    }

    /**
     * set server call for amount updation
     *
     * @param $cancelParams array
     * @param $nnconfig array
     * @param $tid string
     *
     * @return mixed
     */
     static public function updateSubscriptionAmount($cancelParams, $tid, $nnconfig) {
        $amount = $cancelParams['amount'] * 100;
        if (preg_match('/[^\d\.]/', $amount) or !$amount) {
            $response['status_text'] = ts('The amount is invalid');
            return $response;
        }
        $httppost = CRM_Utils_HttpClient::singleton();
        $url = 'https://payport.novalnet.de/nn_infoport.xml';
        $vendor = isset($nnconfig['vendor']) ? $nnconfig['vendor'] : '';
        $product = isset($nnconfig['product']) ? $nnconfig['product'] : '';
        $authcode = isset($nnconfig['authcode']) ? $nnconfig['authcode'] : '';
        $subs_id = $cancelParams['subscriptionId'];
        if (!empty($vendor) && !empty($product) && !empty($authcode) && !empty($tid)) {
            $xmlparams = "<?xml version='1.0' encoding='UTF-8'?>
                    <nnxml>
                      <info_request>
                        <vendor_id>$vendor</vendor_id>
                        <vendor_authcode>$authcode</vendor_authcode>
                        <request_type>SUBSCRIPTION_UPDATE</request_type>
                        <product_id>$product</product_id>
                        <subs_id>$subs_id</subs_id>
                        <subs_tid>$tid</subs_tid>
                        <payment_ref>$tid</payment_ref>
                        <old_payment_type></old_payment_type>
                        <status></status>
                        <reason></reason>
                        <amount>$amount</amount>
                        <update_flag>amount</update_flag>
                        <tid>$tid</tid>
                      </info_request>
                    </nnxml>";
            list($result, $response) = $httppost->post($url, $xmlparams);
            $xml_response = (array) simplexml_load_string($response);
            return $xml_response;
        }
        return false;
    }

    /**
     * Update the details after successfull subscription cancel
     *
     * @param $data array
     *
     * @return none
     */
    static public function updateOnSubscriptionAmountChange($data) {
        $comments = "<br>" . ts('Subscription recurring amount').CRM_Utils_Money::format($data['amount']) . ts('has been updated successfully');
        $contribution = new CRM_Contribute_BAO_Contribution();
        $contribution->trxn_id = $data['tid'];
        if ($contribution->find(TRUE)) {
            $note = new CRM_Core_DAO_Note();
            $note->entity_table = 'civicrm_contribution';
            $note->entity_id = $contribution->id;
            $modified_date = CRM_Utils_Date::isoToMysql(date('Y-m-d'));
            if ($note->find(TRUE)) {
                $note->note = $note->note . $comments;
                $note->modified_date = $modified_date;
            } else {
                $note->note = $comments;
                $note->modified_date = $modified_date;
                $note->contact_id = $entity->id;
            }
            $note->save();
        }

        return $comments;
    }

    /**
     * Get subscription configuration details
     *
     * @param  $data array
     * @return string
     */
     static public function getSubscriptionConfigDetails($data) {

        $queryParams = array(
            1 => array($data['tid'], 'Integer'),
        );
        $dao = CRM_Core_DAO::executeQuery('SELECT id, novalnet_tid, nnconfig FROM novalnet_subscription_details where novalnet_tid=%1 LIMIT 1', $queryParams);
        $dao->fetch();
        return isset($dao->nnconfig) ? unserialize($dao->nnconfig) : array();
    }

    /**
     * Get subscription Original amount
     *
     * @param  $data array
     *
     * @return double
     *
     */
    static public function getOriginalRecurringAmount($data) {
        $contributionrec = new CRM_Contribute_BAO_ContributionRecur();
        $contributionrec->id = $data['id'];
        if ($contributionrec->find(TRUE)) {
            $amount = $contributionrec->amount;
        }

        return trim($amount);
    }

    /**
     * Get subscription TID
     *
     * @param  $data array
     *
     * @return double
     *
     */
    static public function getSubscriptionTID($data) {
        $contributionrec = new CRM_Contribute_BAO_ContributionRecur();
        $contributionrec->processor_id = $data['subscriptionId'];
        if ($contributionrec->find(TRUE)) {
            $trxn_id = $contributionrec->trxn_id;
        }

        return trim($trxn_id);
    }

    /**
     * Get subscription cancel reasons
     *
     * @param  none
     *
     * @return array
     *
     */
     static public function getSubscriptionCancelReason() {
        return array('0' => ts('Please select reason'),
            '1' => ts('Product is costly'),
            '2' => ts('Cheating'),
            '3' => ts('Partner interfered'),
            '4' => ts('Financial problem'),
            '5' => ts('Content does not match my likes'),
            '6' => ts('Content is not enough'),
            '7' => ts('Interested only for a trial'),
            '8' => ts('Page is very slow'),
            '9' => ts('Satisfied customer'),
            '10' => ts('Logging in problems'),
            '11' => ts('Other'),
        );
    }

    /**
     * sets the credit card details for form
     *
     * @param  $config array
     * @param  $merchant_config array
     *
     * @return none
     */
    static public function setCreditCardDetails($config, $merchant_config) {
        $template = CRM_Core_Smarty::singleton();
        $template->assign('logourl', $config->resourceBase . 'CRM/Core/Payment/org.payment.novalnetgateway/logos/');
    }

    /**
     * sets the debit card details for form
     *
     * @param  $config array
     * @param  $merchant_config array
     *
     * @return none
     */
    static public function setSepaDetails($config, $merchant_config) {
        $template = CRM_Core_Smarty::singleton();

        $dao = CRM_Core_DAO::executeQuery("SELECT name,iso_code FROM civicrm_country");
        $dao->fetch();
        while($dao->fetch()){
            $countries[$dao->iso_code] = $dao->name;
        }
        if (isset($_SESSION['sepa']['account_details']))
            $sepaacc_details =  unserialize(strrev(base64_decode($_SESSION['sepa']['account_details'])));
		$nn_sepa_auto_refill  = Civi::settings()->get('nn_sepa_auto_refill');
        $template->assign('assetsurl', $config->resourceBase . 'CRM/Core/Payment/org.payment.novalnetgateway/');
        $template->assign('nnvendor', $merchant_config->_vendorid);
        $template->assign('nnauthcode', $merchant_config->_authcode);
        $template->assign('nncountries', $countries);
        $template->assign('nnsepauniqueid', self::randomString());
        $template->assign('nnsepaautorefill', isset($nn_sepa_auto_refill)?$nn_sepa_auto_refill:0);
        $template->assign('nnsepaiphash', '');
        $template->assign('nnsepavendormsg', ts('Please fill in all the mandatory fields'));
        $template->assign('nnsepacardmsg', ts('Your account details are invalid'));
        $template->assign('nnsepaacceptmandate', ts('Please accept the SEPA direct debit mandate'));
        $template->assign('nnsepacountryerror', ts('Please select the country'));
        $template->assign('nnsepaiphash', isset($sepaacc_details['panhash'])?$sepaacc_details['panhash']:'');
        $template->assign('ajaxurl', $config->userFrameworkBaseURL."civicrm/novalnet/getcountry");
    }

    /**
     * sets the config details for form
     *
     * @param none
     *
     * @return none
     */
    static public function setConfigParameters() {
        CRM_Core_OptionValue::getValues(array('name'=>'contribution_status'), $values);
        $status                 = array('1'=> ts('Yes'),'0'=>ts('No'));
        $reference_one          = ts('Transaction reference 1');
        $reference_two          = ts('Transaction reference 2');
        $stauts_comp_title      = ts('Order completion status');
        $testmode_title         = ts('Enable test mode');
        $nnlogo_title           = ts('Display Novalnet logo');
        $paylogo_title          = ts('Display payment method logo');
        $ref1_title             = ts('Payment Reference 1 (Novalnet Invoice Reference)');
        $ref2_title             = ts('Payment Reference 2 (TID)');
        $ref3_title             = ts('Payment Reference 3 (Order No)');
        foreach ($values as $key => $val) {
            $status_option[$val['value']] = $val['label'];
        }
        $text_elements= array(
            'nn_vendor' => array('title'=> ts('Merchant ID')),
            'nn_product' => array('title'=> ts('Project ID')),
            'nn_tariff' => array('title'=> ts('Tariff ID')),
            'nn_authcode' => array('title'=> ts('Authentication code')),
            'nn_password' => array('title'=> ts('Payment access key')),
            'nn_subscription_tariff_id' => array('title'=> ts('Subscription Tariff ID')),
            'nn_referrer_id' => array('title'=> ts('Referrer ID')),
            'nn_manualamount' => array('title'=> ts('Set a limit for on-hold transaction (in cents)')),
            'nn_inv_duedate' => array('title'=> ts('Payment due date (in days)')),
            'novalnet_invoice_ref_one' => array('title'=> $reference_one),
            'novalnet_invoice_ref_two' => array('title'=> $reference_two),
            'novalnet_prepayment_ref_one' => array('title'=> $reference_one),
            'novalnet_prepayment_ref_two' => array('title'=> $reference_two),
            'novalnet_cc_ref_one' => array('title'=> $reference_one),
            'novalnet_cc_ref_two' => array('title'=> $reference_two),
            'nn_sepa_due_date' => array('title'=> ts('SEPA payment duration (in days)')),
            'novalnet_sepa_ref_one' => array('title'=> $reference_one),
            'novalnet_sepa_ref_two' => array('title'=> $reference_two),
            'novalnet_instant_ref_one' => array('title'=> $reference_one),
            'novalnet_instant_ref_two' => array('title'=> $reference_two),
            'novalnet_ideal_ref_one' => array('title'=> $reference_one),
            'novalnet_ideal_ref_two' => array('title'=> $reference_two),
            'novalnet_eps_ref_one' => array('title'=> $reference_one),
            'novalnet_eps_ref_two' => array('title'=> $reference_two),
            'novalnet_giropay_ref_one' => array('title'=> $reference_one),
            'novalnet_giropay_ref_two' => array('title'=> $reference_two),
            'novalnet_paypal_ref_one' => array('title'=> $reference_one),
            'novalnet_paypal_ref_two' => array('title'=> $reference_two),
            'nn_paypal_user' => array('title'=> ts('PayPal API User Name')),
            'nn_paypal_pwd' => array('title'=> ts('PayPal API Password')),
            'nn_paypal_sig' => array('title'=> ts('PayPal API Signature')),
            'nn_callback_frommail' => array('title'=> ts('E-mail address (From)')),
            'nn_callback_tomail' => array('title'=> ts('E-mail address (To)')),
            'nn_callback_mailbcc' => array('title'=> ts('E-mail address (Bcc)')),
            'nn_notify_url' => array('title'=> ts('Notification URL'))
        );
        $select_elements = array (
            'nn_subs_cancel_status' => array('title'=> ts('Cancellation status of subscription'), 'option' => $status_option, 'default' => 3),
            'novalnet_invoice_cont_status' => array('title'=> $stauts_comp_title, 'option' => $status_option,'default' => 2),
            'novalnet_invoice_cont_cb_status' => array('title'=> ts('Callback order status'), 'option' => $status_option,'default' => 1),
            'novalnet_cc_cont_status' => array('title'=> $stauts_comp_title, 'option' => $status_option,'default' => 1),
            'novalnet_sepa_cont_status' => array('title'=> $stauts_comp_title, 'option' => $status_option,'default' => 1),
            'novalnet_prepayment_cont_cb_status' => array('title'=> ts('Callback order status'), 'option' => $status_option,'default' => 1),
            'novalnet_prepayment_cont_status' => array('title'=> $stauts_comp_title, 'option' => $status_option,'default' => 2),
            'novalnet_ideal_cont_status' => array('title'=> $stauts_comp_title, 'option' => $status_option,'default' => 1),
            'novalnet_instant_cont_status' => array('title'=> $stauts_comp_title, 'option' => $status_option,'default' => 1),
            'novalnet_eps_cont_status' => array('title'=> $stauts_comp_title, 'option' => $status_option,'default' => 1),
            'novalnet_giropay_cont_status' => array('title'=> $stauts_comp_title, 'option' => $status_option,'default' => 1),
            'novalnet_paypal_cont_status' => array('title'=> $stauts_comp_title, 'option' => $status_option,'default' => 1),
            'novalnet_paypal_pending_status' => array('title'=> ts('Order status for the pending payment'), 'option' => $status_option,'default' => 2),
        );
        $radio_elements = array(
            'nn_inv_testmode' => array('title'=> $testmode_title, 'option' => $status,'default' => 0),
            'novalnet_pay_logo' => array('title'=> $paylogo_title, 'option' => $status,'default' => 1),

            'nn_cc_testmode' => array('title'=> $testmode_title, 'option' => $status,'default' => 0),
            'nn_cc_secure_active' => array('title'=> ts('Enable 3D secure'), 'option' => $status,'default' => 0),
            'nn_cc_amexlogo_active' => array('title'=> ts('Display AMEX logo'), 'option' => $status,'default' => 0),
            'nn_cc_cartasilogo_active' => array('title'=> ts('Display CartaSi logo'), 'option' => $status,'default' => 0),
            'nn_cc_maestrologo_active' => array('title'=> ts('Display Maestro logo'), 'option' => $status,'default' => 0),

            'nn_sepa_testmode' => array('title'=> $testmode_title, 'option' => $status,'default' => 0),
            'nn_sepa_auto_refill' => array('title'=> ts('Enable auto-fill'), 'option' => $status,'default' => 0),

            'nn_prepayment_testmode' => array('title'=> $testmode_title, 'option' => $status,'default' => 0),

            'nn_ideal_testmode' => array('title'=> $testmode_title, 'option' => $status,'default' => 0),

            'nn_instant_testmode' => array('title'=> $testmode_title, 'option' => $status,'default' => 0),

            'nn_eps_testmode' => array('title'=> $testmode_title, 'option' => $status,'default' => 0),

            'nn_giropay_testmode' => array('title'=> $testmode_title, 'option' => $status,'default' => 0),

            'nn_paypal_testmode' => array('title'=> $testmode_title, 'option' => $status,'default' => 0),

            'nn_callback_testmode' => array('title'=> $testmode_title, 'option' => $status,'default' => 0),
            'nn_callback_debug' => array('title'=> ts('Enable debug mode'), 'option' => $status,'default' => 0),
            'nn_callback_sendmail' => array('title'=> ts('Enable E-mail notification for callback'), 'option' => $status,'default' => 0),

            'novalnet_invoice_payment_ref1' => array('title'=> $ref1_title, 'option' => $status,'default' => 1),
            'novalnet_invoice_payment_ref2' => array('title'=> $ref2_title, 'option' => $status,'default' => 1),
            'novalnet_invoice_payment_ref3' => array('title'=> $ref3_title, 'option' => $status,'default' => 1),

            'novalnet_prepayment_payment_ref1' => array('title'=> $ref1_title, 'option' => $status,'default' => 1),
            'novalnet_prepayment_payment_ref2' => array('title'=> $ref2_title, 'option' => $status,'default' => 1),
            'novalnet_prepayment_payment_ref3' => array('title'=> $ref3_title, 'option' => $status,'default' => 1)
        );

        return array ($text_elements, $select_elements, $radio_elements);
    }

    /**
     * generate the random string
     *
     * @param none
     *
     * @return string
     */
    public static function randomString() {
        $randomwordarray = explode(",", "a,b,c,d,e,f,g,h,i,j,k,l,m,1,2,3,4,5,6,7,8,9,0");
        shuffle($randomwordarray);
        return substr(implode($randomwordarray, ""), 0, 30);
    }

    /**
     * cancel the subscription/recurring payment
     *
     * @param  $message string
     * @param  $cancelParams array
     *
     * @return boolean
     *
     */
    public static function subscriptionCancel($message, $cancelParams) {
		$cancelStatus = self::getSubscriptionCancelReason();
        $cancelParams['tid'] = self::getSubscriptionTID($cancelParams);
        $nnconfig = self::getSubscriptionConfigDetails($cancelParams);
        $data = array(
            'vendor'        => CRM_Utils_Array::value('vendor', $nnconfig),
            'product'       => CRM_Utils_Array::value('product', $nnconfig),
            'tariff'        => CRM_Utils_Array::value('tariff', $nnconfig),
            'auth_code'     => CRM_Utils_Array::value('authcode', $nnconfig),
            'tid'           => $cancelParams['tid'],
            'cancel_reason' => CRM_Utils_Array::value('nn_cancel_reason', $_REQUEST),
            'key'           => CRM_Utils_Array::value('key', $nnconfig),
            'cancel_sub'    => 1
        );

        if (empty($data['tid']) || empty($data['vendor']) || empty($data['cancel_reason'])
            || empty($data['product']) || empty($data['tariff']) || empty($data['auth_code'])) {
            $session = CRM_Core_Session::singleton();
            $session->setStatus(ts('Please select the reason of subscription cancellation'), 'Novalnet Error:', 'error');
            return false;
        }
        $data['cancel_reason'] = $cancelStatus[$data['cancel_reason']];
        $urlData = CRM_Utils_System::makeQueryString($data);
        $host ='https://payport.novalnet.de/paygate.jsp';
        $httppost = CRM_Utils_HttpClient::singleton();
        list($result, $response) = $httppost->post($host, $urlData);
        parse_str($response, $parsed);

        if (isset($parsed['status']) && $parsed['status'] == '100') {
           self::updateOnSubscriptionCancel($data, $data['cancel_reason']);
            return true;
        }
        $error = self::getstatus_error($parsed);
        drupal_set_message($error, 'error');
        return false;
    }

    /**
     * change subscription/recurring amount
     *
     * @param  $message string
     * @param  $cancelParams array
     *
     * @return boolean
     */
    public static function subscriptionAmountChange(&$message, $cancelParams) {

        $org_amount = self::getOriginalRecurringAmount($cancelParams);
        $org_amount = $org_amount * 100;
        $update_amount = $cancelParams['amount'] * 100;
        if ($org_amount != $update_amount) {
            $tid = self::getSubscriptionTID($cancelParams);
            $cancelParams['tid'] = $tid;
            $nnconfig = self::getSubscriptionConfigDetails($cancelParams);
            $update_error = new CRM_Core_Error;
            if ($tid) {
                $response = self::updateSubscriptionAmount($cancelParams, $tid, $nnconfig);

                if (isset($response['status']) && $response['status'] == 100) {
                    $message .= self::updateOnSubscriptionAmountChange($cancelParams);
                    $message .= "<br>" . ts('Novalnet transaction ID :') . $cancelParams['tid'];
                    return true;
                } else {
                    $error = self::getstatus_error($response);
                    $session = CRM_Core_Session::singleton();
                    $session->setStatus($error, ts('Novalnet Error:'), 'error');
                    return $update_error;
                }
            }
            return $update_error;
        }
        return true;
    }

}

?>
