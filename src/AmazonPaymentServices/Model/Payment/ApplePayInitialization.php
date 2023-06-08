<?php

namespace AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConstants;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSExceptionCodes;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter;

class ApplePayInitialization extends PaymentTypeAdapter
{
    protected string $paymentType = APSConstants::PAYMENT_TYPE_APPLE_PAY;
    protected string $integrationType = APSConstants::INTEGRATION_TYPE_STANDARD;
    protected string $command = APSConstants::PAYMENT_COMMAND_TOKENIZATION;

    private array $merchantParams;

    protected array $requiredParameters = [
        'merchantIdentifier',
        'initiative',
        'initiativeContext',
        'displayName',
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
            case 'merchantIdentifier':
                $certificatePath = $this->merchantParams['Apple_CertificatePath'];
                if (!$certificatePath) {
                    throw new APSException(
                        APSI18n::getText('merchant_config_apple_certificate_path_missing'),
                        APSExceptionCodes::MERCHANT_CONFIG_APPLE_CERTIFICATE_PATH_MISSING,
                    );
                }

                $certificateContent = openssl_x509_parse( file_get_contents( $certificatePath ) );
                $merchantUID = $certificateContent['subject']['UID'] ?? null;
                if (!$merchantUID) {
                    $merchantUID = $this->merchantParams['Apple_MerchantUid'] ?? null;
                    if (!$merchantUID) {
                        throw new APSException(
                            APSI18n::getText('merchant_config_apple_merchant_id_missing'),
                            APSExceptionCodes::MERCHANT_CONFIG_APPLE_MERCHANT_ID_MISSING,
                        );
                    }
                }

                return $merchantUID;

            case 'initiative':
                return 'web';

            case 'initiativeContext':
                return $this->merchantParams['Apple_DomainName'];

            case 'displayName':
                return $this->merchantParams['Apple_DisplayName'];

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
