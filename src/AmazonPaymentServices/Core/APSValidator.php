<?php

namespace AmazonPaymentServicesSdk\AmazonPaymentServices\Core;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSExceptionCodes;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\Logger;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;

class APSValidator
{
    /**
     * Validate the merchant configuration parameters,
     * throw an exception if something is missing
     *
     * @param array $merchantParams
     * @param ?array $paymentOptions
     *
     * @throws APSException
     */
    public function validateMerchantParams(array $merchantParams, array $paymentOptions = null): void
    {
        if (empty($merchantParams)) {
            throw new APSException(
                APSI18n::getText('merchant_config_missing'),
                APSExceptionCodes::MERCHANT_CONFIG_MISSING,
            );
        }

        $this->validateParameterInArray(
            $merchantParams,
            'merchant_identifier',
            APSI18n::getText('merchant_config_merchant_id_missing'),
            APSExceptionCodes::MERCHANT_CONFIG_MERCHANT_ID_MISSING
        );

        $this->validateParameterInArray(
            $merchantParams,
            'access_code',
            APSI18n::getText('merchant_config_access_code_missing'),
            APSExceptionCodes::MERCHANT_CONFIG_ACCESS_CODE_MISSING
        );

        $this->validateParameterInArray(
            $merchantParams,
            'SHARequestPhrase',
            APSI18n::getText('merchant_config_sha_request_phrase_missing'),
            APSExceptionCodes::MERCHANT_CONFIG_SHA_REQUEST_PHRASE_MISSING
        );

        $this->validateParameterInArray(
            $merchantParams,
            'SHAResponsePhrase',
            APSI18n::getText('merchant_config_sha_request_phrase_missing'),
            APSExceptionCodes::MERCHANT_CONFIG_SHA_RESPONSE_PHRASE_MISSING
        );

        $this->validateParameterInArray(
            $merchantParams,
            'SHAType',
            APSI18n::getText('merchant_config_sha_type_missing'),
            APSExceptionCodes::MERCHANT_CONFIG_SHA_TYPE_MISSING
        );

        if (null === ($merchantParams['sandbox_mode'] ?? null) || !is_bool($merchantParams['sandbox_mode'])) {
            throw new APSException(
                APSI18n::getText('merchant_config_sandbox_not_specified'),
                APSExceptionCodes::MERCHANT_CONFIG_SANDBOX_NOT_SPECIFIED,
            );
        }

        if (($paymentOptions['payment_type'] ?? 0) === APSConstants::PAYMENT_TYPE_APPLE_PAY) {
            $this->validateAppleMerchantParams($merchantParams);
        }
    }

    /**
     * Calculates and compares response signature
     * to the signature calculated by us
     *
     * @param array $merchantParams
     * @param array $responseData
     *
     * @return bool
     * @throws APSException
     */
    public static function isResponseValid(array $merchantParams, array $responseData): bool
    {
        $apsSignature = ($responseData['signature'] ?? null);
        if (!$apsSignature) {
            throw new APSException(
                APSI18n::getText('response_no_signature'),
                APSExceptionCodes::RESPONSE_NO_SIGNATURE,
            );
        }
        Logger::getInstance()->info('Response signature: ' . $apsSignature);

        unset($responseData['signature']);
		if (isset($responseData['discriminator'])) {
            unset($responseData['discriminator']);
		}

        $responseSignature =
            (new APSSignature())
                ->calculateSignature($responseData, false, $merchantParams);
        Logger::getInstance()->info('Calculated signature: ' . $responseSignature);

        return $apsSignature === $responseSignature;
    }

    /**
     * Validate the apple url before accessing it
     * with sensible information
     *
     * @param string|null $appleUrl
     *
     * @return void
     *
     * @throws APSException
     */
    public static function validateAppleUrl(?string $appleUrl = null): void
    {
        if (APSMerchant::isDebugMode()) {
            Logger::getInstance()->debug('Apple Pay session URL: ' . $appleUrl);
        }

        if (empty($appleUrl)) {
            throw new APSException(
                APSI18n::getText('apple_pay_url_missing'),
                APSExceptionCodes::APPLE_PAY_URL_MISSING,
            );
        }

        if ( ! filter_var( $appleUrl, FILTER_VALIDATE_URL ) ) {
            throw new APSException(
                APSI18n::getText('apple_pay_url_invalid'),
                APSExceptionCodes::APPLE_PAY_URL_INVALID,
            );
        }

        $isAppleUrlValid = preg_match('/^https\:\/\/[^\.\/]+\.apple\.com\//', $appleUrl);
        if (!$isAppleUrlValid) {
            throw new APSException(
                APSI18n::getText('apple_pay_url_invalid'),
                APSExceptionCodes::APPLE_PAY_URL_INVALID,
            );
        }
    }

    /**
     * Validate a special set of payment data
     * destined for the Apple Pay Button
     *
     * @param array $paymentData
     *
     * @return void
     *
     * @throws APSException
     */
    public static function validateApplePaymentParams(array $paymentData): void
    {
        self::validateParameterExistenceInArray(
            $paymentData,
            'amount',
            APSI18n::getText('apple_pay_payment_data_amount_missing'),
            APSExceptionCodes::PAYMENT_DATA_AMOUNT_MISSING
        );

        self::validateParameterExistenceInArray(
            $paymentData,
            'subtotal',
            APSI18n::getText('apple_pay_payment_data_subtotal_missing'),
            APSExceptionCodes::PAYMENT_DATA_SUBTOTAL_MISSING
        );

        self::validateParameterExistenceInArray(
            $paymentData,
            'shipping',
            APSI18n::getText('apple_pay_payment_data_shipping_missing'),
            APSExceptionCodes::PAYMENT_DATA_SHIPPING_MISSING
        );

        self::validateParameterExistenceInArray(
            $paymentData,
            'discount',
            APSI18n::getText('apple_pay_payment_data_discount_missing'),
            APSExceptionCodes::PAYMENT_DATA_DISCOUNT_MISSING
        );

        self::validateParameterExistenceInArray(
            $paymentData,
            'tax',
            APSI18n::getText('apple_pay_payment_data_tax_missing'),
            APSExceptionCodes::PAYMENT_DATA_TAX_MISSING
        );
    }

    /**
     * Validate merchant apple config parameters
     *
     * @param array $merchantParams
     *
     * @return void
     *
     * @throws APSException
     */
    private function validateAppleMerchantParams(array $merchantParams): void
    {
        // additional fields for Apple Pay
        $needToCheck = [
            'Apple_AccessCode'          => [
				'message' => APSI18n::getText('merchant_config_apple_access_code_missing'),
                'code' => APSExceptionCodes::MERCHANT_CONFIG_APPLE_ACCESS_CODE_MISSING
	            ],
            'Apple_SupportedNetworks'   => [
                'message' => APSI18n::getText('merchant_config_apple_supported_networks_missing'),
                'code' => APSExceptionCodes::MERCHANT_CONFIG_APPLE_SUPPORTED_NETWORKS_MISSING
	            ],
            'Apple_SupportedCountries'  => [
                'message' => APSI18n::getText('merchant_config_apple_supported_countries_missing'),
                'code' => APSExceptionCodes::MERCHANT_CONFIG_APPLE_SUPPORTED_COUNTRIES_MISSING
            ],
            'Apple_SHARequestPhrase'    => [
                'message' => APSI18n::getText('merchant_config_apple_sha_request_phrase_missing'),
                'code' => APSExceptionCodes::MERCHANT_CONFIG_APPLE_SHA_REQUEST_PHRASE_MISSING
            ],
            'Apple_SHAResponsePhrase'   => [
                'message' => APSI18n::getText('merchant_config_apple_sha_response_phrase_missing'),
                'code' => APSExceptionCodes::MERCHANT_CONFIG_APPLE_SHA_RESPONSE_PHRASE_MISSING
            ],
            'Apple_SHAType'             => [
                'message' => APSI18n::getText('merchant_config_apple_sha_type_missing'),
                'code' => APSExceptionCodes::MERCHANT_CONFIG_APPLE_SHA_TYPE_MISSING
            ],
            'Apple_DisplayName'         => [
                'message' => APSI18n::getText('merchant_config_apple_display_name_missing'),
                'code' => APSExceptionCodes::MERCHANT_CONFIG_APPLE_DISPLAY_NAME_MISSING
            ],
            'Apple_DomainName'          => [
                'message' => APSI18n::getText('merchant_config_apple_domain_name_missing'),
                'code' => APSExceptionCodes::MERCHANT_CONFIG_APPLE_DOMAIN_NAME_MISSING
            ],
            'Apple_CertificatePath'     => [
                'message' => APSI18n::getText('merchant_config_apple_certificate_path_missing'),
                'code' => APSExceptionCodes::MERCHANT_CONFIG_APPLE_CERTIFICATE_PATH_MISSING
            ],
            'Apple_CertificateKeyPath'  => [
                'message' => APSI18n::getText('merchant_config_apple_certificate_key_path_missing'),
                'code' => APSExceptionCodes::MERCHANT_CONFIG_APPLE_CERTIFICATE_KEY_PATH_MISSING
            ],
            'Apple_CertificateKeyPass'  => [
                'message' => APSI18n::getText('merchant_config_apple_certificate_key_pass_missing'),
                'code' => APSExceptionCodes::MERCHANT_CONFIG_APPLE_CERTIFICATE_KEY_PASS_MISSING
            ],
        ];

        foreach ($needToCheck as $parameter => $errorDetails) {
            self::validateParameterExistenceInArray(
				$merchantParams,
				$parameter,
				$errorDetails['message'],
	            $errorDetails['code']
            );
        }
    }

    /**
     * Validate one parameter
     *
     * @param array $array
     * @param string $parameter
     * @param string $message
     * @param int $exceptionCode
     *
     * @return void
     *
     * @throws APSException
     */
    private function validateParameterInArray(
        array $array,
        string $parameter,
        string $message,
        int $exceptionCode = 0
    ): void
    {
        if (!($array[$parameter] ?? null)) {
            throw new APSException($message, $exceptionCode);
        }
    }

    /**
     * Validate one parameter
     *
     * @param array $array
     * @param string $parameter
     * @param string $message
     * @param int $exceptionCode
     *
     * @return void
     *
     * @throws APSException
     */
    private static function validateParameterExistenceInArray(
        array $array,
        string $parameter,
        string $message,
        int $exceptionCode = 0
    ): void
    {
        if (!isset($array[$parameter])) {
            throw new APSException($message, $exceptionCode);
        }
    }
}
