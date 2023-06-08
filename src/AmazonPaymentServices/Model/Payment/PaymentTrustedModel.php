<?php

namespace AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConstants;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSExceptionCodes;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapterApi;

class PaymentTrustedModel extends PaymentTypeAdapterApi
{
    protected string $discriminator =
        APSConstants::PAYMENT_TYPE_CREDIT_CARD . '_' .
        APSConstants::INTEGRATION_TYPE_TRUSTED . '_' .
        APSConstants::PAYMENT_COMMAND_PURCHASE;

    protected string $paymentType = APSConstants::PAYMENT_TYPE_CREDIT_CARD;
    protected string $integrationType = APSConstants::INTEGRATION_TYPE_TRUSTED;
    protected string $command = APSConstants::PAYMENT_COMMAND_PURCHASE;

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
    public function getPaymentParameter(string $parameter, mixed $possibleValue = null,bool $isStrict = false): mixed
    {
        switch ($parameter) {
            case 'card_security_code':
                if ($possibleValue) {
                    return substr($possibleValue, 0, 3);
                }
                return null;

            case 'eci':
                if (!$possibleValue || !in_array($possibleValue, [
                        APSConstants::PAYMENT_ECI_ECOMMERCE,
                        APSConstants::PAYMENT_ECI_MOTO,
                        APSConstants::PAYMENT_ECI_RECURRING,
                    ])) {
                    throw new APSException(
                        APSI18n::getText('aps_trusted_model_parameter', [
                            'parameter' => $parameter
                        ]),
	                    APSExceptionCodes::APS_INVALID_PARAMETER,
                    );
                }
                return $possibleValue;

            case 'expiry_date':
            case 'card_number':
                if ($possibleValue) {
                    return $possibleValue;
                }
                return null;

            default:
                return parent::getPaymentParameter($parameter, $possibleValue, $isStrict);
        }
    }

}
