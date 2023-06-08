<?php

namespace AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Maintenance;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConstants;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapterApi;

class PaymentCaptureModel extends PaymentTypeAdapterApi
{
    public string $paymentType = APSConstants::PAYMENT_TYPE_CREDIT_CARD;
    public string $integrationType = APSConstants::INTEGRATION_TYPE_STANDARD;

    public string $command = APSConstants::PAYMENT_COMMAND_CAPTURE;

    protected array $requiredParameters = [
        'command',
        'merchant_reference',
        'amount',
        'currency',
        'language',
    ];

    protected array $optionalParameters = [
        'order_description',
    ];
}
