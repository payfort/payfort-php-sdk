<?php
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\Logger;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\FrontEndAdapter\Core\WebhookAdapter;

require_once '_autoload.php';

try {
    $merchantParams = include 'merchant_config.php';
    APSMerchant::setMerchantParams($merchantParams);
} catch (APSException $e) {
    error_log('MERCHANT SETUP ERROR at WEBHOOK', E_ERROR);

    exit;
}

try {
    $webhookParameters = WebhookAdapter::getWebhookData();
    // your code here

} catch (APSException $e) {
    Logger::getInstance()->info( 'Webhook parameters failed to validate! (' . $e->getMessage() . ')' );
}
