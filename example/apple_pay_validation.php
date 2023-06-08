<?php

use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\FrontEndAdapter\Maintenance\PaymentApplePaySession;

require_once '_autoload.php';

$merchantParams = include 'merchant_config.php';
APSMerchant::setMerchantParams($merchantParams);

// get the apple url that Safari sends us
$appleUrl = filter_input(INPUT_POST, 'url') ?? '';

// the response must be JSON
header('Content-Type: application/json; charset=utf-8');

try {
    echo (new PaymentApplePaySession())->applePayValidateSession($appleUrl);
} catch (APSException $e) {
    // an error occurred

    // throw a bad request status code
    http_response_code(400);

    // return the failed status
    echo json_encode([
        'status'    => 'fail',
        'message'   => 'Session validation failed' . (APSMerchant::isDebugMode() ? ': ' . $e->getMessage() : ''),
    ]);
}

exit;