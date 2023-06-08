<?php

use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\FrontEndAdapter\Maintenance\PaymentRecurring;

require_once '_autoload.php';

$sampleAppConfig = include '_sample_app_config.php';

$merchantParams = include 'merchant_config.php';
APSMerchant::setMerchantParams($merchantParams);

$paymentData = include 'payment_data_config.php';
$paymentData['token_name'] = '035cd5dec15c436fa787af4fec0a77f5';

$activeFile = '/payment_recurring.php';
include '_header.php';
?>
<div>
    <h3>Payment option: Credit Card Recurring</h3>
    <div>Order number: <?php echo $paymentData['merchant_reference']; ?></div>
    <div>Amount (Fort): <?php echo $paymentData['amount']; ?></div>
    <div>Token Name: <?php echo $paymentData['token_name']; ?></div>
    <br />
    <div>
        <?php
        try {
	        $callResult = (new PaymentRecurring())->paymentRecurring($paymentData);
            // handle the response here
        } catch (APSException $e) {
            // do your thing here to handle this error
        }
        ?>
    </div>
</div>
<?php
include '_footer.php';

