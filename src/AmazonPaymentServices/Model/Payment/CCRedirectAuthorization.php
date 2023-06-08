<?php

namespace AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConstants;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter;

class CCRedirectAuthorization extends PaymentTypeAdapter
{
    protected string $discriminator = APSConstants::PAYMENT_TYPE_CREDIT_CARD
        . '_' . APSConstants::INTEGRATION_TYPE_REDIRECT
        . '_' . APSConstants::PAYMENT_COMMAND_AUTHORIZATION;

    protected string $paymentType = APSConstants::PAYMENT_TYPE_CREDIT_CARD;
    protected string $integrationType = APSConstants::INTEGRATION_TYPE_REDIRECT;
    protected string $command = APSConstants::PAYMENT_COMMAND_AUTHORIZATION;

    protected array $requiredParameters;

    protected array $optionalParameters;

    public function __construct()
    {
        $this->requiredParameters = $this->buildRequiredParameters($this->discriminator);
        $this->optionalParameters = $this->buildOptionalParameters($this->discriminator);
    }
}
