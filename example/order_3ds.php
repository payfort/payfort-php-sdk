<?php

use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;

require_once '_autoload.php';

$sampleAppConfig = include '_sample_app_config.php';

$merchantParams = include 'merchant_config.php';
APSMerchant::setMerchantParams($merchantParams);

$paymentResponse = filter_input_array(INPUT_GET);

$secure3dsURL = $paymentResponse['3ds_url'] ?? '';

?>
<script type="text/javascript">
    // redirects the main page to the 3ds verification page
    if (window.parent && window.parent.location && window.parent.location !== window.location) {
        // in case this is inside an iframe, redirect the main browser page
        window.parent.location = window.location;
    } else {
        // in case this is the main browser page
        window.location = "<?php echo $secure3dsURL; ?>";
    }
</script>
