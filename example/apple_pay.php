<?php

use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\FrontEndAdapter\Payments\ApplePayButton;

require_once '_autoload.php';

// load the sample app configuration
$sampleAppConfig = include '_sample_app_config.php';

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

// sample application menu settings
$activeFile = '/apple_pay.php';
include '_header.php';

?>
    <div>
        <h3>Payment option: Apple Pay</h3>
        <div>Order number: <?php echo $paymentData['merchant_reference']; ?></div>
        <div>Amount (Fort): <?php echo $paymentData['amount']; ?> <?php echo $paymentData['currency']; ?></div>
        <br />
        <div>
            <?php
            try {
                // initialize the Apple Pay button
                echo (new ApplePayButton())
                    // set payment data
                    ->setPaymentData($paymentData)

                    // apple specific settings
                    ->setDisplayName($merchantParams['Apple_DisplayName'])
                    ->setCurrencyCode($paymentData['currency'])
                    ->setCountryCode($paymentData['country'])
                    ->setSupportedCountries($merchantParams['Apple_SupportedCountries'])
                    ->setSupportedNetworks($merchantParams['Apple_SupportedNetworks'])

                    // URL where the validation logic is placed
                    ->setValidationCallbackUrl($sampleAppConfig['base_url'] . 'apple_pay_validation.php')

                    // URL where the APS purchase logic is placed
                    ->setCommandCallbackUrl($sampleAppConfig['base_url'] . 'apple_pay_purchase.php')

                    ->render();
            } catch (APSException $e) {
                // An error occurred while generating HTML and Javascript code
                // do your thing here to handle this error
            }
            ?>
        </div>
    </div>
<?php
include '_footer.php';

