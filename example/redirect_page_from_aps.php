<?php

use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\APSResponse;
use AmazonPaymentServicesSdk\FrontEndAdapter\Core\ResponseHandler;

require_once '_autoload.php';

$merchantParams = include 'merchant_config.php';
APSMerchant::setMerchantParams($merchantParams);

$sampleAppConfig = include '_sample_app_config.php';

$orderNumber = $_REQUEST['merchant_reference'] ?? '';
// get your order data based on merchant_reference

// sample app approach is to load the order data from a sample file
$paymentData = include 'payment_data_config.php';

try {
    (new ResponseHandler($paymentData))
        ->onSuccess(function(APSResponse $responseHandlerResult) use($sampleAppConfig) {
            // the payment transaction was a success
            // do your thing and process the response

            // redirect user to the success page
            header('Location: ' . $sampleAppConfig['base_url'] . 'order_success.php?' . $responseHandlerResult->getRedirectParams());
            exit;
        })
        ->onError(function(APSResponse $responseHandlerResult) use($sampleAppConfig) {
            // the payment failed
            // process the response

            // redirect user to the error page
            header('Location: ' . $sampleAppConfig['base_url'] . 'order_failed.php?' . $responseHandlerResult->getRedirectParams());
            exit;
        })
        ->onHtml(function(string $htmlContent, APSResponse $responseHandlerResult) use($sampleAppConfig, $merchantParams) {
            // the payment requires 3ds verification

            if (!($merchantParams['3ds_modal'] ?? false)) {
                if ($responseHandlerResult->isStandardImplementation()) {
                    // this is the standard implementation

                    // although standard checkout is inside a popup
                    // user wants 3ds verification to be redirection of main page

                    // redirect user to the 3ds redirection page,
                    // where it will detect that it is inside an iframe
                    // and jump out from the iframe to be able to redirect the user
                    // to 3ds in the navigation bar
                    header('Location: ' . $sampleAppConfig['base_url'] . 'order_3ds.php?' . $responseHandlerResult->getRedirectParams());
                    exit;
                }

                // we simply redirect the user to the 3ds verification page
                header('Location: ' . $responseHandlerResult->get3dsUrl());
                exit;
            } else {
                // open 3ds verification inside the iframe (print html code)
                echo $htmlContent;
            }
        })
        ->handleResponse()
    ;
} catch (APSException $e) {
    include '_header.php';

    // do your thing here to handle this error

    include '_footer.php';
}

