<?php

namespace AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConstants;

class CCStandardPurchase extends CCStandardAuthorization
{
    protected string $discriminator = APSConstants::PAYMENT_TYPE_CREDIT_CARD . '_'
    . APSConstants::INTEGRATION_TYPE_STANDARD . '_'
    . APSConstants::PAYMENT_COMMAND_PURCHASE;

    protected string $command = APSConstants::PAYMENT_COMMAND_PURCHASE;
}