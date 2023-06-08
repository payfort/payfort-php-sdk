<?php

use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\FrontEndAdapter\Payments\CCStandard;

require_once '_autoload.php';

$sampleAppConfig = include '_sample_app_config.php';

$merchantParams = include 'merchant_config.php';
APSMerchant::setMerchantParams($merchantParams);

$paymentData = include 'payment_data_config.php';

$activeFile = '/credit_card_standard.php';
include '_header.php';
?>
<div>
    <h3>Payment option: Credit Card Standard</h3>
    <div>Order number: <?php echo $paymentData['merchant_reference']; ?></div>
    <div>Amount (Fort): <?php echo $paymentData['amount']; ?></div>
    <br />
    <div>
        <?php
        try {
            echo (new CCStandard())
                ->setPaymentData($paymentData)
                ->usePurchaseCommand()
                ->setCallbackUrl($sampleAppConfig['base_url'] . 'redirect_page_from_aps.php')
                ->render([
                    'button_text'   => 'Place order with Purchase'
                ]);
        } catch (APSException $e) {
            // do your thing here to handle this error
        }
        ?>
    </div>
</div>
<?php
include '_footer.php';

