<?php

namespace AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConstants;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter;

class ApplePaySession extends PaymentTypeAdapter
{
    protected string $paymentType = APSConstants::PAYMENT_TYPE_APPLE_PAY;
    protected string $integrationType = APSConstants::INTEGRATION_TYPE_STANDARD;
    protected string $command = APSConstants::PAYMENT_COMMAND_TOKENIZATION;

    protected array $requiredParameters = [
        'MerchantIdentifier',
        'domainName',
        'displayName',
    ];

    protected array $optionalParameters = [
    ];

    /**
     * @param string $parameter
     * @param mixed|null $possibleValue
     * @param bool $isStrict
     *
     * @return mixed
     */
    public function getPaymentParameter(string $parameter, mixed $possibleValue = null, bool $isStrict = false): mixed
    {
        return $possibleValue;
    }
}
