<?php

namespace AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSExceptionCodes;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter;

class ApplePayHeaderOptions extends PaymentTypeAdapter
{
    private array $merchantParams;

    protected array $requiredParameters = [
        'user_Agent',
        'headers',
        'timeout',
        'redirection',
        'blocking',
        'sslverify',
        'sslcertificates',
        'ssl_cert',
        'ssl_key',
        'httpversion',
        'data_format',

        'curl',
    ];

    protected array $optionalParameters = [
    ];

    /**
     * @throws APSException
     */
    public function __construct()
    {
        $this->merchantParams = APSMerchant::getMerchantParams();
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
        switch ($parameter) {
            case 'user_Agent':
                return 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:20.0) Gecko/20100101 Firefox/20.0';

            case 'headers':
                return [
                    'Content-Type'      => 'application/json',
                    'charset'           => 'UTF-8',
                ];

            case 'timeout':
                return 60;

            case 'redirection':
                return 0;
//                return 5;

            case 'sslverify':
            case 'blocking':
                return true;

            case 'ssl_cert':
            case 'sslcertificates':
                return $this->merchantParams['Apple_CertificatePath'] ?? null;

            case 'httpversion':
                return '1.0';

            case 'data_format':
                return 'body';

            case 'ssl_key':
                return [
                    $this->merchantParams['Apple_CertificateKeyPath'] ?? null,
                    $this->merchantParams['Apple_CertificateKeyPass'] ?? null
                ];

            case 'curl':
                return [
                    CURLOPT_SSLCERT         => $this->merchantParams['Apple_CertificatePath'],
                    CURLOPT_SSLKEY          => $this->merchantParams['Apple_CertificateKeyPath'],
                    CURLOPT_SSLKEYPASSWD    => $this->merchantParams['Apple_CertificateKeyPass'],
                ];

            default:
                if ($possibleValue) {
                    return $possibleValue;
                }
        }

        if ($isStrict) {
            throw new APSException(
                APSI18n::getText('aps_parameter_missing_from_payment_data', [
                    'parameter' => $parameter
                ]),
	            APSExceptionCodes::APS_PARAMETER_MISSING,
            );
        }

        return null;
    }
}
