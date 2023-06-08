<?php

namespace AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Maintenance;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConstants;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapterApi;

class PaymentMotoModel extends PaymentTypeAdapterApi
{
    protected string $paymentType = APSConstants::PAYMENT_TYPE_CREDIT_CARD;
    protected string $integrationType = APSConstants::INTEGRATION_TYPE_CUSTOM;
    protected string $command = APSConstants::PAYMENT_COMMAND_PURCHASE;

    protected array $requiredParameters = [
        'command',
        'merchant_reference',
        'amount',
        'currency',
        'language',
        'customer_email',
        'eci',
        'token_name',
        'customer_ip',
    ];

    protected array $optionalParameters = [
        'payment_option',
        'order_description',
        'customer_name',
        'phone_number',
        'settlement_reference',
        'merchant_extra',
        'merchant_extra1',
        'merchant_extra2',
        'merchant_extra3',
        'merchant_extra4',
        'merchant_extra5',
        'return_url',
    ];

    public function getPaymentParameter(string $parameter, mixed $possibleValue = null, bool $isStrict = false): mixed
    {
        if ('eci' === $parameter) {
            return APSConstants::PAYMENT_ECI_MOTO;
        }

        return parent::getPaymentParameter($parameter, $possibleValue, $isStrict);
    }
}