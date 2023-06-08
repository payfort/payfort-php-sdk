<?php


use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\FrontEndAdapter\Maintenance\PaymentCheckStatus;

require_once '_autoload.php';

$merchantParams = include 'merchant_config.php';
APSMerchant::setMerchantParams($merchantParams);

$paymentData = [
    'merchant_reference'    => 'O-00001-27297',
    'language'              => 'en',
];

$activeFile = '/maintenance.php';
include '_header.php';
?>
<div>
    <h3>Payment option: Check Payment Status</h3>
    <div>
        <?php
        try {
            $callResult = (new PaymentCheckStatus())->paymentCheckStatus($paymentData);
            // handle the response here
        } catch (APSException $e) {
            // do your thing here to handle this error
        }
        ?>
    </div>
</div>
<?php
include '_footer.php';
