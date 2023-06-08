<?php

namespace AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConstants;

class CCCustomPurchase extends CCCustomAuthorization
{
    protected string $discriminator =
        APSConstants::PAYMENT_TYPE_CREDIT_CARD . '_' .
        APSConstants::INTEGRATION_TYPE_CUSTOM . '_' .
        APSConstants::PAYMENT_COMMAND_PURCHASE;

    protected string $command = APSConstants::PAYMENT_COMMAND_PURCHASE;
}