<?php

use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;

require_once '_autoload.php';

$sampleAppConfig = include '_sample_app_config.php';

$merchantParams = include 'merchant_config.php';
APSMerchant::setMerchantParams($merchantParams);

$paymentResponse = filter_input_array(INPUT_GET);

include '_header.php';
?>
<div>
    <h3>Order transaction FAILED!</h3>
    <div>Order number: <?php echo $paymentResponse['merchant_reference'] ?? ''; ?></div>
    <div>Amount (Fort): <?php echo $paymentResponse['amount'] ?? ''; ?> <?php echo $paymentResponse['currency'] ?? ''; ?></div>
    <div>Response message: <?php echo $paymentResponse['response_message'] ?? ''; ?></div>
    <div>Response status: <?php echo $paymentResponse['status'] ?? ''; ?></div>
</div>
<script type="text/javascript">
    // If this page was loaded inside the checkout / 3DS iframes, show it in the main page.
    // Use window.top: from the nested 3DS iframe, window.parent is the (sandboxed) card
    // iframe, and sandboxed frames may only navigate the top window, not other ancestors.
    if (window.top !== window.self) {
        window.top.location.href = window.location.href;
    }
</script>
