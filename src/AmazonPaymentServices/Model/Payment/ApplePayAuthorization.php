<?php

namespace AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConstants;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSExceptionCodes;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapterApi;

class ApplePayAuthorization extends PaymentTypeAdapterApi
{
    protected string $discriminator =
        APSConstants::PAYMENT_TYPE_APPLE_PAY
        . '_' . APSConstants::PAYMENT_COMMAND_AUTHORIZATION;

    protected string $paymentType = APSConstants::PAYMENT_TYPE_APPLE_PAY;
    protected string $integrationType = APSConstants::INTEGRATION_TYPE_STANDARD;
    protected string $command = APSConstants::PAYMENT_COMMAND_AUTHORIZATION;

    protected array $requiredParameters = [
        'digital_wallet',
        'command',
        'merchant_reference',
        'amount',
        'currency',
        'language',
        'customer_email',
        'apple_data',
        'apple_signature',
        'apple_header',
        'apple_paymentMethod',
        'customer_ip',
    ];

    protected array $optionalParameters = [
        'apple_applicationData',
        'eci',
        'order_description',
        'customer_ip',
        'customer_name',
        'merchant_extra',
        'merchant_extra1',
        'merchant_extra2',
        'merchant_extra3',
        'merchant_extra4',
        'merchant_extra5',
        'phone_number',
    ];

    // only allow these keys in the apple_header array
    private array $appleHeaderParameters = [
        'apple_transactionId',
        'apple_ephemeralPublicKey',
        'apple_publicKeyHash',
    ];

    // only allow these in the apple_paymentMethod array
    private array $applePaymentMethodParameters = [
        'apple_displayName',
        'apple_network',
        'apple_type',
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
        switch ($parameter) {
            case 'digital_wallet':
                return APSConstants::DIGITAL_WALLET_APPLE;

            case 'apple_header':
                if ($isStrict && !is_array($possibleValue)) {
                    throw new APSException(
                        APSI18n::getText('aps_parameter_not_array_type', [
                            'parameter'     => $parameter,
                        ]),
	                    APSExceptionCodes::APS_INVALID_TYPE,
                    );
                }

                // only these parameters should be in the Apple Header array
                foreach ($possibleValue as $keyName => $keyValue) {
                    if (!in_array($keyName, $this->appleHeaderParameters, true)) {
                        // remove the unwanted parameter
                        unset($possibleValue[$keyName]);
                    }
                }
                if (count($possibleValue) !== count($this->appleHeaderParameters)) {
                    throw new APSException(
                        APSI18n::getText('aps_parameter_missing_array_keys', [
                            'parameter'     => $parameter,
                        ]),
                        APSExceptionCodes::APS_PARAMETER_MISSING,
                    );
                }

                return $possibleValue;

            case 'apple_paymentMethod':
                if ($isStrict && !is_array($possibleValue)) {
                    throw new APSException(
                        APSI18n::getText('aps_parameter_not_array_type', [
                            'parameter'     => $parameter,
                        ]),
	                    APSExceptionCodes::APS_INVALID_TYPE,
                    );
                }

                // only these parameters should be in the Apple Header array
                foreach ($possibleValue as $keyName => $keyValue) {
                    if (!in_array($keyName, $this->applePaymentMethodParameters, true)) {
                        // remove the unwanted parameter
                        unset($possibleValue[$keyName]);
                    }
                }
                if (count($possibleValue) !== count($this->applePaymentMethodParameters)) {
                    throw new APSException(
                        APSI18n::getText('aps_parameter_missing_array_keys', [
                            'parameter'     => $parameter,
                        ]),
						APSExceptionCodes::APS_PARAMETER_MISSING,
                    );
                }

                return $possibleValue;

            default:
                return parent::getPaymentParameter($parameter, $possibleValue, $isStrict);
        }
    }
}
