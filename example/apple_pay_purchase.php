<?php

use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\FrontEndAdapter\Maintenance\PaymentApplePayAps;

require_once '_autoload.php';

// load test merchant configuration
$merchantParams = include 'merchant_config.php';

// set test merchant configuration one time
APSMerchant::setMerchantParams($merchantParams);

// load test payment data
$paymentData = include 'payment_data_config.php';

// ADDITIONAL Apple Pay required parameters
$paymentData['subtotal'] = 1245.00;
$paymentData['discount'] = 200;
$paymentData['shipping'] = 50;
$paymentData['tax'] = 5;
$paymentData['currency'] = 'AED';
$paymentData['country'] = 'AE';



header('Content-Type: application/json; charset=utf-8');

try {
    // collect data sent over by Safari
    // and prepare parameters to be sent to APS
    // retrieve and validate response from APS
    $callResult = (new PaymentApplePayAps())
        // call to authorize
        // ->applePayAuthorization($paymentData)

        // call to purchase
        ->applePayPurchase($paymentData)
    ;

    // at this point $callResult has the response from APS
    // and also the response signature is validated

    // process callResult data

    // return all ok to Safari
    echo json_encode([
        'status'    => 'success',
        'message'   => $callResult['response_message'] ?? 'no message',
    ]);
} catch (APSException $e) {
    // an error occurred

    // throw a bad request status code
    http_response_code(400);

    // return the failed status
    echo json_encode([
        'status'    => 'fail',
        'message'   => 'APS purchase call failed' . (APSMerchant::isDebugMode() ? ': '
                . $e->getMessage() : ''),
    ]);
}
exit;