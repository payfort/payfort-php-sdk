<?php

namespace AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConstants;

class ApplePayPurchase extends ApplePayAuthorization
{
    protected string $discriminator =
        APSConstants::PAYMENT_TYPE_APPLE_PAY
        . '_' . APSConstants::PAYMENT_COMMAND_PURCHASE;

    protected string $paymentType = APSConstants::PAYMENT_TYPE_APPLE_PAY;
    protected string $integrationType = APSConstants::INTEGRATION_TYPE_STANDARD;
    protected string $command = APSConstants::PAYMENT_COMMAND_PURCHASE;
}
