<?php

namespace AmazonPaymentServicesSdk\AmazonPaymentServices\Core;

class APSSignature
{
    /**
     * Main function for calculating signature strings from payment data
     *
     * @param array $paymentParams              Payment data params
     * @param bool  $isRequest                  TRUE or FALSE for Request or Response SHA calculation
     * @param array $configParams               The configuration array data
     *
     * @return string
     */
    public function calculateSignature(array $paymentParams, bool $isRequest, array $configParams): string
    {
        $isApplePay = $this->isApplePay($paymentParams);

        $signatureSHA = $isRequest ?
            $configParams[$isApplePay ? 'Apple_SHARequestPhrase' : 'SHARequestPhrase'] :
            $configParams[$isApplePay ? 'Apple_SHAResponsePhrase' : 'SHAResponsePhrase'];

        ksort($paymentParams);

        $shaString = $signatureSHA . $this->implodeParamsToString($paymentParams) . $signatureSHA;

        return hash($configParams[$isApplePay ? 'Apple_SHAType' : 'SHAType'], $shaString);
    }

    /**
     * Resolve if this is an Apple Pay payment or not
     *
     * @param array $paymentParams
     *
     * @return bool
     */
    private function isApplePay(array $paymentParams): bool
    {
        if (isset($paymentParams['apple_header'])) {
            return true;
        }

        if (isset($paymentParams['apple_paymentMethod'])) {
            return true;
        }

        if (APSConstants::DIGITAL_WALLET_APPLE === ($paymentParams['digital_wallet'] ?? null)) {
            return true;
        }

        return false;
    }

    /**
     * This function appends all array elements one after the other,
     * differently based on their type
     * (products and apple have special concatenation agreements)
     *
     * @param array $arrayData          The array based on which the SHA string is calculated
     *
     * @return string
     */
    private function implodeParamsToString(array $arrayData): string
    {
        $shaString = '';
        foreach ($arrayData as $index => $value) {
            $shaString .= match ($index) {
                'apple_header', 'apple_paymentMethod'   => $index . '={' . $this->getAppleShaString($value) . '}',
                'installment_detail'                    => '',

                default => $index . '=' . $value,
            };
        }

        return $shaString;
    }

    /**
     * Special handling helper for Apple data
     *
     * @param array $appleParams
     *
     * @return string
     */
    private function getAppleShaString(array $appleParams): string
    {
        $appleShaString = '';

        foreach ($appleParams as $index => $value) {
            if ($appleShaString) {
                $appleShaString .= ', ';
            }
            $appleShaString .= $index .'=' . $value;
        }

        return $appleShaString;
    }
}
