<?php

require_once 'CRM/Core/Payment/novalnet/novalnetIPN.php';
CRM_Core_Payment_cc::handlePaymentNotification($_REQUEST);

