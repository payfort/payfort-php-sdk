<?php

namespace AmazonPaymentServicesSdk\AmazonPaymentServices\Core;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;

class APSI18n
{
    private static ?array $apsTexts = null;

    private static string $fallbackLocale = 'en';

    /**
     * Get the I18n text
     *
     * @param string|int $key
     * @param array|null $parameters
     *
     * @return string
     */
    public static function getText(string|int $key, array $parameters = null): string
    {
        if (null === self::$apsTexts) {
            $merchantConfig = APSMerchant::getMerchantParamsNoException();
            $locale = $merchantConfig['locale'] ?? self::$fallbackLocale;

            self::$apsTexts = self::populateTextsArray($locale);
        }

        $string = self::$apsTexts[(string)$key] ?? '';

        if (null === $parameters) {
            return $string;
        }

        foreach ($parameters as $parameter => $value) {
            $string = str_replace('{' . $parameter . '}', $value, $string);
        }

        return $string;
    }

    /**
     * Populate the internal texts cache
     *
     * @param string $locale
     *
     * @return array
     */
    private static function populateTextsArray(string $locale): array
    {
        $fallBackTexts = self::loadTexts(self::$fallbackLocale);

        $localeTexts = self::loadTexts($locale);

        foreach ($localeTexts as $index => $value) {
            $fallBackTexts[$index] = $value;
        }

        return $fallBackTexts;
    }

    /**
     * Load the contents of the text files
     *
     * @param string $locale
     *
     * @return array
     */
    private static function loadTexts(string $locale): array
    {
        $localeFilePath = __DIR__ . '/../I18n/' . $locale . '.json';

        $langArray = [];

        if (file_exists($localeFilePath)) {
            $langArray = json_decode(file_get_contents($localeFilePath), true);
        }

        return $langArray;
    }
}