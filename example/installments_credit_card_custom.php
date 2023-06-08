<?php

use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\InstallmentsCCCustomTokenization;
use AmazonPaymentServicesSdk\FrontEndAdapter\Maintenance\InstallmentsPlans;
use AmazonPaymentServicesSdk\FrontEndAdapter\Payments\InstallmentsCCCustom;

require_once '_autoload.php';

$sampleAppConfig = include '_sample_app_config.php';

$merchantParams = include 'merchant_config.php';
APSMerchant::setMerchantParams($merchantParams);

$paymentData = include 'payment_data_config.php';

$activeFile = '/installments_credit_card_custom.php';
include '_header.php';
?>
<div>
    <h3>Payment option: Installments Credit Card Custom</h3>
    <div>Order number: <?php echo $paymentData['merchant_reference']; ?></div>
    <div>Amount (Fort): <?php echo $paymentData['amount']; ?></div>
    <br />
    <div>
        <?php
        try {
            $callResult = (new InstallmentsPlans())->getInstallmentsPlans($paymentData);

            // save the plan code that will be used at authorization
            session_start();
            $_SESSION['plan_code'] = 'M9mv5Z';
            $_SESSION['issuer_code'] = 'zaQnN1';
            session_commit();

	        echo (new InstallmentsCCCustom())
                ->setPaymentData($paymentData)
                ->setCallbackUrl($sampleAppConfig['base_url'] . 'redirect_page_from_aps.php')
                ->render(
                    [
                        'installment_plans_list' => $callResult,
                    ]
                );
        } catch (APSException $e) {
            // do your thing here to handle this error
        }
        ?>
    </div>
</div>
<?php
include '_footer.php';

