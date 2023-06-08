<?php
namespace Tests;

use PHPUnit\Framework\TestCase;

class APSTestCase extends TestCase
{
    protected array $merchantConfig;
    protected array $normalPaymentParams;

    public function setUp(): void
    {
        parent::setUp();

        $this->merchantConfig = [
            'merchant_identifier'   => '123456',
            'access_code'           => '654321',
            'SHAType'               => 'sha256',
            'SHARequestPhrase'      => 'test1',
            'SHAResponsePhrase'     => 'test2',
            'sandbox_mode'          => true,

            'Apple_AccessCode'          => '654321',
            'Apple_SHARequestPhrase'    => 'test1[$',
            'Apple_SHAResponsePhrase'   => 'test2*!',
            'Apple_SHAType'             => 'sha256',
            'Apple_DisplayName'         => 'Test Apple store',
            'Apple_DomainName'          => 'https://localhost',
            'Apple_MerchantUid'         => '34728942398742398',
            'Apple_SupportedNetworks'   => ["visa", "masterCard", "amex", "mada"],
            'Apple_SupportedCountries'  => ['RO'],
            'Apple_CertificatePath'     => __DIR__ . '/cert/apple.crt',
            'Apple_CertificateKeyPath'  => __DIR__ . '/cert/apple.key',
            'Apple_CertificateKeyPass'  => 'edusaiou',

            'log_path'                  => null,
            'locale'                    => 'en',
        ];

        $this->normalPaymentParams = [
            'command'               => 'AUTHORIZATION',
            'access_code'           => '43287943278432',
            'merchant_identifier'   => '343278h432',
            'amount'                => 1,
            'merchant_reference'    => 'test 2121',
            'currency'              => 'USD',
            'language'              => 'en',
            'customer_email'        => 'test@aps.com',
        ];
    }
}
