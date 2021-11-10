<?php
/**
 * Novalnet payment method class
 * This module is used for real time processing of
 * Novalnet transaction of customers.
 *
 * Copyright (c) Novalnet AG
 *
 * Released under the GNU General Public License
 * This free contribution made by request.
 * If you have found this script usefull a small
 * recommendation as well as a comment on merchant form
 * would be greatly appreciated.
 *
 * Script : novalnetAJAX.php
 */
class CRM_Page_novalnetAJAX {

    /**
     * Get the Country ISO code and name
     *
     * @param none
     * @return none
     */
    public static function getCountryISO() {
        $country = new CRM_Core_DAO_Country();
        $country->id = $_POST['country_id'];
        if ($_POST['country_id'] && $country->find(TRUE)) {
           CRM_Utils_JSON::output(array('iso_code' => $country->iso_code, 'name' => $country->name));
        } else {
            CRM_Utils_JSON::output(array('iso_code' => '', 'name' => ''));
        }
    }
}
