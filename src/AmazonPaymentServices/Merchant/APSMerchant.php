<?php

namespace AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSExceptionCodes;

class APSMerchant
{
    private static ?array $merchantParams = null;

    /**
     * Get the previously set merchant config parameters
     *
     * @return array
     *
     * @throws APSException
     */
    public static function getMerchantParams(): array
    {
        if (null === self::$merchantParams) {
            throw new APSException(
                APSI18n::getText('merchant_config_missing'),
                APSExceptionCodes::MERCHANT_CONFIG_MISSING,
            );
        }

        return self::$merchantParams;
    }

    /**
     * Get the previously set merchant config parameters
     *
     * @return array
     */
    public static function getMerchantParamsNoException(): array
    {
        if (null === self::$merchantParams) {
            return [];
        }

        return self::$merchantParams;
    }

    /**
     * Set the merchant config parameters
     * and validate them
     *
     * @param array $merchantParams
     *
     * @throws APSException
     */
    public static function setMerchantParams(array $merchantParams): void
    {
        self::$merchantParams = $merchantParams;

        (new APSValidator())->validateMerchantParams(self::$merchantParams);
    }

    /**
     * Whether debug mode is activated or not
     *
     * @return bool
     */
    public static function isSandboxMode(): bool
    {
        return self::$merchantParams['sandbox_mode'] ?? false;
    }

    /**
     * Whether debug mode is activated or not
     *
     * @return bool
     */
    public static function isDebugMode(): bool
    {
        return self::$merchantParams['debug_mode'] ?? false;
    }

    public static function reset(): void
    {
        self::$merchantParams = null;
    }
}
