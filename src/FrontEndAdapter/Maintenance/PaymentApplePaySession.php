<?php

namespace AmazonPaymentServicesSdk\FrontEndAdapter\Maintenance;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\Logger;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\ApplePayHeaderOptions;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\ApplePayInitialization;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentDTO;
use AmazonPaymentServicesSdk\FrontEndAdapter\Core\MaintenanceAdapter;

class PaymentApplePaySession extends MaintenanceAdapter
{
    /**
     * @param string $appleUrl
     *
     * @return array|string
     *
     * @throws APSException
     */
    public function applePayValidateSession(string $appleUrl): array|string
    {
        Logger::getInstance()->info('Apple Pay Session Validation call build-up');

        APSValidator::validateAppleUrl($appleUrl);

        $appleValidationParameters = $this->getAppleValidationParameters();

        $appleValidationOptions = $this->getAppleValidationHeaderOptions();

        Logger::getInstance()->info('Calling Apple Pay Url for Session Validation');

        return $this
            ->getApsCore()
            ->getAppleCustomerSession(
                $appleUrl,
                $appleValidationParameters,
                $appleValidationOptions
            );
    }

    /**
     * Build Apple Pay Session Validation parameters
     *
     * @return array
     *
     * @throws APSException
     */
    private function getAppleValidationParameters(): array
    {
        $validationPaymentType = new ApplePayInitialization();
        $parameters = $validationPaymentType->generateParameters(
            new PaymentDTO([], $validationPaymentType),
            false
        );

        if (APSMerchant::isDebugMode()) {
            Logger::getInstance()->debug(
                'Apple Pay session Data sent: ' . json_encode($parameters)
            );
        }

        return $parameters;
    }

    /**
     * Build Apple Pay Session Validation parameters
     *
     * @return array
     *
     * @throws APSException
     */
    private function getAppleValidationHeaderOptions(): array
    {
        $optionsType = new ApplePayHeaderOptions();

        $options = $optionsType->generateParameters(
            new PaymentDTO([], $optionsType),
            false
        );

        if (APSMerchant::isDebugMode()) {
            Logger::getInstance()->debug(
                'Apple Pay session connector options: ' . json_encode($options)
            );
        }

        return $options;
    }
}