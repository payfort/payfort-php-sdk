<?php

namespace AmazonPaymentServicesSdk\AmazonPaymentServices\Model;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConstants;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSExceptionCodes;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;

abstract class PaymentTypeAdapter implements PaymentTypeInterface
{
    protected string $discriminator = '';

    protected string $paymentType;
    protected string $integrationType;
    protected string $command;
    protected array $requiredParameters;
    protected array $optionalParameters;

    protected string $environmentUrl = 'https://checkout.payfort.com/FortAPI/';
    protected string $sandboxEnvironmentUrl = 'https://sbcheckout.payfort.com/FortAPI/';

    protected string $endpointUri = 'paymentPage';

    public function getPaymentType(): string
    {
        return $this->paymentType;
    }

    public function getIntegrationType(): string
    {
        return $this->integrationType;
    }

    public function getRequiredParameters(): array
    {
        return $this->requiredParameters;
    }

    public function getOptionalParameters(): array
    {
        return $this->optionalParameters;
    }

    public function getDiscriminator(): string
    {
        return $this->doGetDiscriminator($this->discriminator);
    }

    public static function doGetDiscriminator(string $discriminator): string
    {
        return hash('sha256', $discriminator);
    }

    /**
     * Generate the main merchant parameters
     *
     * @throws APSException
     */
    public function generateMainParameters(): array
    {
        $merchantParams = APSMerchant::getMerchantParams();

        return [
            'access_code'           => $merchantParams['access_code'],
            'merchant_identifier'   => $merchantParams['merchant_identifier'],
        ];
    }

    /**
     * @param PaymentDTO $paymentParams
     * @param bool $addMainParameters
     *
     * @return array
     *
     * @throws APSException
     */
    public function generateParameters(PaymentDTO $paymentParams, bool $addMainParameters = true): array
    {
        $paymentTypeParams = [];
        if ($addMainParameters) {
            $paymentTypeParams = $this->generateMainParameters();
        }

        foreach ($this->requiredParameters as $parameter) {
	        $parameterValue = $this->getPaymentParameter(
                $parameter,
                $paymentParams->get($parameter, false),
                true
            );
	        if (!is_null($parameterValue)) {
		        $paymentTypeParams[$parameter] = $parameterValue;
	        }
        }

        foreach ($this->optionalParameters as $parameter) {
            $parameterValue = $this->getPaymentParameter(
                $parameter,
                $paymentParams->get($parameter, false) ?? null,
                false
            );
            if ($parameterValue) {
	            $paymentTypeParams[$parameter] = $parameterValue;
            }
        }

        return $paymentTypeParams;
    }

	/**
	 * @param array $paymentData
	 *
	 * @return bool
	 * @throws APSException
	 */
	public function isValid(array $paymentData): bool
	{
		foreach ($this->requiredParameters as $parameter) {
			$this->getPaymentParameter(
                $parameter,
                $paymentData[$parameter] ?? null,
                true
            );
		}

		return true;
	}

    /**
     * Return the endpoint for APS
     *
     * @return string
     */
    public function getEndpoint(): string
    {
        if (APSMerchant::isSandboxMode()) {
            // sandbox mode activated
            return $this->sandboxEnvironmentUrl . $this->endpointUri;
        }

        return $this->environmentUrl . $this->endpointUri;
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
            case 'command':
            case 'service_command':
            case 'query_command':
                return $this->command;

            case 'amount':
                if ((int)$possibleValue > 0) {
                    return (int)$possibleValue;
                }
                throw new APSException(
                    APSI18n::getText('aps_amount_invalid_parameter'),
	                APSExceptionCodes::APS_INVALID_PARAMETER,
                );

            case 'order_description':
                if ($possibleValue) {
                    return substr($possibleValue, 0, 150);
                }
                return null;

            case 'customer_ip':
                if (!empty($_SERVER['REMOTE_ADDR'])) {
                    return $_SERVER['REMOTE_ADDR'];
                }
                return $possibleValue;

            case 'installments':
                // default values for:
                // credit card redirect authorization
                // credit card standard tokenization
                return APSConstants::INSTALLMENTS_TYPE_STANDALONE;

            default:
                if ($possibleValue) {
                    return $possibleValue;
                }
        }

        if ($isStrict) {
            throw new APSException(
                APSI18n::getText('aps_parameter_missing_from_payment_data', [
                    'parameter'     => $parameter,
                ]),
	            APSExceptionCodes::APS_PARAMETER_MISSING,
            );
        }

        return null;
    }

    protected function buildRequiredParameters(string $discriminator): array
    {
        if (in_array($discriminator, [
            APSConstants::PAYMENT_TYPE_CREDIT_CARD
            . '_' . APSConstants::INTEGRATION_TYPE_STANDARD
            . '_' . APSConstants::PAYMENT_COMMAND_TOKENIZATION,

            APSConstants::PAYMENT_TYPE_CREDIT_CARD
            . '_' . APSConstants::INTEGRATION_TYPE_CUSTOM
            . '_' . APSConstants::PAYMENT_COMMAND_TOKENIZATION,

            APSConstants::PAYMENT_TYPE_INSTALMENTS
            . '_' . APSConstants::INTEGRATION_TYPE_STANDARD
            . '_' . APSConstants::PAYMENT_COMMAND_TOKENIZATION,

            APSConstants::PAYMENT_TYPE_INSTALMENTS
            . '_' . APSConstants::INTEGRATION_TYPE_CUSTOM
            . '_' . APSConstants::PAYMENT_COMMAND_TOKENIZATION,
        ])) {
            // credit card standard tokenization
            // credit card custom tokenization
            return [
                'service_command',
                'merchant_reference',
                'language',
            ];
        }

        // credit card redirect authorization
        $requiredParameters = [
            'command',
            'merchant_reference',
            'amount',
            'currency',
            'language',
            'customer_email',
        ];

        if ($this->isCCStandardAuthorizationPurchase($discriminator)) {
            // credit card standard authorization
            // credit card custom authorization
            $requiredParameters = array_merge($requiredParameters, [
                'customer_ip',
                'token_name',
            ]);
        }

        if (APSConstants::PAYMENT_TYPE_CREDIT_CARD
            . '_' . APSConstants::INTEGRATION_TYPE_TRUSTED
            . '_' . APSConstants::PAYMENT_COMMAND_PURCHASE
            === $discriminator
        ) {
            // payment trusted model
            $requiredParameters = array_merge($requiredParameters, [
                'eci',
                'customer_ip',
            ]);
        }

        return $requiredParameters;
    }

    protected function buildOptionalParameters(string $discriminator): array
    {
        if (in_array($discriminator, [
            APSConstants::PAYMENT_TYPE_CREDIT_CARD
            . '_' . APSConstants::INTEGRATION_TYPE_STANDARD
            . '_' . APSConstants::PAYMENT_COMMAND_TOKENIZATION,

            APSConstants::PAYMENT_TYPE_INSTALMENTS
            . '_' . APSConstants::INTEGRATION_TYPE_STANDARD
            . '_' . APSConstants::PAYMENT_COMMAND_TOKENIZATION,
        ], true)) {
            // credit card standard tokenization
            return [
                'token_name',
                'return_url',
            ];
        }

        if (in_array($discriminator, [
            APSConstants::PAYMENT_TYPE_CREDIT_CARD
            . '_' . APSConstants::INTEGRATION_TYPE_CUSTOM
            . '_' . APSConstants::PAYMENT_COMMAND_TOKENIZATION,

            APSConstants::PAYMENT_TYPE_INSTALMENTS
            . '_' . APSConstants::INTEGRATION_TYPE_CUSTOM
            . '_' . APSConstants::PAYMENT_COMMAND_TOKENIZATION,
        ], true)) {
            // credit card custom tokenization
            return [
                'expiry_date',
                'card_number',
                'card_security_code',
                'token_name',
                'card_holder_name',
                'remember_me',
                'return_url',
            ];
        }

        $optionalParameters = [
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
            'return_url',
            'billing_stateProvince',
            'billing_provinceCode',
            'billing_street',
            'billing_street2',
            'billing_postcode',
            'billing_country',
            'billing_company',
            'billing_city',
            'shipping_stateProvince',
            'shipping_provinceCode',
            'shipping_street',
            'shipping_street2',
            'shipping_source',
            'shipping_sameAsBilling',
            'shipping_postcode',
            'shipping_country',
            'shipping_company',
            'shipping_city',
            'agreement_id',
            'recurring_mode',
            'recurring_transactions_count',
        ];

        if (in_array($discriminator, [
            APSConstants::PAYMENT_TYPE_CREDIT_CARD
            . '_' . APSConstants::INTEGRATION_TYPE_REDIRECT
            . '_' . APSConstants::PAYMENT_COMMAND_AUTHORIZATION,

            APSConstants::PAYMENT_TYPE_CREDIT_CARD
            . '_' . APSConstants::INTEGRATION_TYPE_REDIRECT
            . '_' . APSConstants::PAYMENT_COMMAND_PURCHASE,
        ], true)) {
            // credit card redirect authorization
            return array_merge($optionalParameters, [
                'token_name',
                'sadad_olp',
                'customer_ip',
                'remember_me',
                'eci',
            ]);
        }

        if ($this->isCCStandardAuthorizationPurchase($discriminator)) {
            // credit card standard authorization
            // credit card custom authorization
            return array_merge($optionalParameters, [
                'card_security_code',
                'remember_me',
                'eci',
            ]);
        }

        if (APSConstants::PAYMENT_TYPE_CREDIT_CARD
            . '_' . APSConstants::INTEGRATION_TYPE_TRUSTED
            . '_' . APSConstants::PAYMENT_COMMAND_PURCHASE
            === $discriminator
        ) {
            // payment trusted model
            return array_merge($optionalParameters, [
                'card_number',
                'expiry_date',
                'card_security_code',
                'card_holder_name',
                'token_name',
            ]);
        }

        return $optionalParameters;
    }

    public static function getAllResponseSuccessStatusCodes(): array
    {
        return [
            '01', '02', '04', '06', '08', '12', '14', '18', '20', '22',
            '24', '28', '30', '32', '42', '44', '46', '48', '50', '52',
            '54', '56', '58', '62', '66', '70', '72', '74', '76', '80',
        ];
    }

    private function isCCStandardAuthorizationPurchase(string $discriminator): bool
    {
        return in_array($discriminator, [
            APSConstants::PAYMENT_TYPE_CREDIT_CARD
            . '_' . APSConstants::INTEGRATION_TYPE_STANDARD
            . '_' . APSConstants::PAYMENT_COMMAND_AUTHORIZATION,

            APSConstants::PAYMENT_TYPE_CREDIT_CARD
            . '_' . APSConstants::INTEGRATION_TYPE_STANDARD
            . '_' . APSConstants::PAYMENT_COMMAND_PURCHASE,

            APSConstants::PAYMENT_TYPE_INSTALMENTS
            . '_' . APSConstants::INTEGRATION_TYPE_STANDARD
            . '_' . APSConstants::PAYMENT_COMMAND_PURCHASE,

            APSConstants::PAYMENT_TYPE_CREDIT_CARD
            . '_' . APSConstants::INTEGRATION_TYPE_CUSTOM
            . '_' . APSConstants::PAYMENT_COMMAND_AUTHORIZATION,

            APSConstants::PAYMENT_TYPE_CREDIT_CARD
            . '_' . APSConstants::INTEGRATION_TYPE_CUSTOM
            . '_' . APSConstants::PAYMENT_COMMAND_PURCHASE,

            APSConstants::PAYMENT_TYPE_INSTALMENTS
            . '_' . APSConstants::INTEGRATION_TYPE_CUSTOM
            . '_' . APSConstants::PAYMENT_COMMAND_PURCHASE,
        ], true);
    }
}
