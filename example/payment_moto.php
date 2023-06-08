<?php

use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\FrontEndAdapter\Maintenance\PaymentMoto;

require_once '_autoload.php';

$sampleAppConfig = include '_sample_app_config.php';

$merchantParams = include 'merchant_config.php';
APSMerchant::setMerchantParams($merchantParams);

$paymentData = include 'payment_data_config.php';
$paymentData['token_name'] = '5540cb3b9e6a40e38227ab9141e7342a';
$paymentData['customer_ip'] = '127.0.0.1';


$activeFile = '/payment_moto.php';
include '_header.php';
?>
    <div>
        <h3>Payment option: Moto Channel</h3>
        <div>Order number: <?php echo $paymentData['merchant_reference']; ?></div>
        <div>Amount (Fort): <?php echo $paymentData['amount']; ?></div>
        <div>Token Name: <?php echo $paymentData['token_name']; ?></div>
        <br/>
        <div>
            <?php
            try {
                $callResult = (new PaymentMoto())->paymentMoto($paymentData);
                // handle the response here
            } catch (APSException $e) {
                // do your thing here to handle this error
            }
            ?>
        </div>
    </div>
<?php
include '_footer.php';