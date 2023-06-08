<?php

namespace AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConstants;

class CCRedirectPurchase extends CCRedirectAuthorization
{
    protected string $discriminator = APSConstants::PAYMENT_TYPE_CREDIT_CARD . '_'
    . APSConstants::INTEGRATION_TYPE_REDIRECT . '_'
    . APSConstants::PAYMENT_COMMAND_PURCHASE;

    protected string $command = APSConstants::PAYMENT_COMMAND_PURCHASE;
}