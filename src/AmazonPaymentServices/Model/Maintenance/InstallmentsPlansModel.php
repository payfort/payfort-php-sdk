<?php

namespace AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Maintenance;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConstants;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapterApi;

class InstallmentsPlansModel extends PaymentTypeAdapterApi
{
    public string $paymentType = APSConstants::PAYMENT_TYPE_INSTALMENTS;
    public string $integrationType = APSConstants::INTEGRATION_TYPE_CUSTOM;

    public string $command = APSConstants::INSTALLMENTS_PLANS;

    public array $requiredParameters = [
        'query_command',
    ];

    public array $optionalParameters = [
        'amount',
        'currency',
        'language',
        'issuer_code',
    ];
}
