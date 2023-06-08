<?php

namespace AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConstants;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter;

class CCCustomTokenization extends PaymentTypeAdapter
{
    protected string $discriminator =
        APSConstants::PAYMENT_TYPE_CREDIT_CARD . '_' .
        APSConstants::INTEGRATION_TYPE_CUSTOM . '_' .
        APSConstants::PAYMENT_COMMAND_TOKENIZATION;

    protected string $paymentType = APSConstants::PAYMENT_TYPE_CREDIT_CARD;
    protected string $integrationType = APSConstants::INTEGRATION_TYPE_CUSTOM;

    protected string $command = APSConstants::PAYMENT_COMMAND_TOKENIZATION;

    protected array $requiredParameters;

    protected array $optionalParameters;

    public function __construct()
    {
        $this->requiredParameters = $this->buildRequiredParameters($this->discriminator);
        $this->optionalParameters = $this->buildOptionalParameters($this->discriminator);
    }

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
        if (in_array($parameter, ['expiry_date', 'card_number', 'card_security_code'], true)) {
            return null;
        }

        return parent::getPaymentParameter($parameter, $possibleValue, $isStrict);
    }
}
