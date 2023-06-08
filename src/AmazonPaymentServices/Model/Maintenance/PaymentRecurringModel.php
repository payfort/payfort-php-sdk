<?php

namespace AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Maintenance;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConstants;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapterApi;

class PaymentRecurringModel extends PaymentTypeAdapterApi
{
    protected string $paymentType = APSConstants::PAYMENT_TYPE_CREDIT_CARD;
    protected string $integrationType = APSConstants::INTEGRATION_TYPE_STANDARD;
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
    ];

    protected array $optionalParameters = [
        'payment_option',
        'order_description',
        'customer_name',
        'merchant_extra',
        'merchant_extra1',
        'merchant_extra2',
        'merchant_extra3',
        'merchant_extra4',
        'merchant_extra5',
        'phone_number',
        'settlement_reference',
        'agreement_id',
    ];

    /**
     * @param string $parameter
     * @param mixed|null $possibleValue
     * @param bool $isStrict
     *
     * @return mixed
     *
     * @throws APSException
     */
    public function getPaymentParameter(string $parameter, mixed $possibleValue = null, bool $isStrict = false): mixed
    {
        if ('eci' === $parameter) {
            return APSConstants::PAYMENT_COMMAND_RECURRING;
        }

        return parent::getPaymentParameter($parameter, $possibleValue, $isStrict);
    }
}
