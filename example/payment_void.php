<?php

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\AmazonPaymentServicesCore;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\FrontEndAdapter\Maintenance\PaymentVoidAuthorization;

require_once '_autoload.php';

$merchantParams = include 'merchant_config.php';
APSMerchant::setMerchantParams($merchantParams);

$paymentData = [
    'merchant_reference'    => 'O-00001-16440',
    'language'              => 'en',
];

$activeFile = '/maintenance.php';
include '_header.php';
?>
<div>
    <h3>Payment option: Void Payment</h3>
    <div>
        <?php
        try {
            $callResult = (new PaymentVoidAuthorization())->paymentVoid($paymentData);
            // handle the response here
        } catch (APSException $e) {
            // do your thing here to handle this error
        }
        ?>
    </div>
</div>
<?php
include '_footer.php';
