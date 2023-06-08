<?php

namespace Tests\AmazonPaymentServices\Core;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConstants;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature;
use Tests\APSTestCase;

/**
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\APSLogger
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\Logger
 */
class APSSignatureTest extends APSTestCase
{
    private ?\AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature $amazonPaymentServicesSignature;

    public function setUp(): void
    {
        parent::setUp();

        $this->merchantConfig['Apple_SHARequestPhrase'] = $this->merchantConfig['SHARequestPhrase'];
        $this->merchantConfig['Apple_SHAResponsePhrase'] = $this->merchantConfig['SHAResponsePhrase'];
        $this->merchantConfig['Apple_SHAType'] = $this->merchantConfig['SHAType'];

        $this->APSSignature = new \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature();
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature::calculateSignature
     *
     * @return void
     */
    public function testCalculateSignature_normalPaymentRequest(): void
    {
        $this->assertEquals(
            '6b73011e4eda060bbbdf206ad110b5208690b57d9e39bec1a548fe8bf3029439',
            $this->APSSignature->calculateSignature($this->normalPaymentParams, true, $this->merchantConfig)
        );
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature::calculateSignature
     *
     * @return void
     */
    public function testCalculateSignature_normalPaymentResponse(): void
    {
        $this->assertEquals(
            '916b0c4952b7d072fe6cbe15fad9b5509660b482aaa952d90096c9094ca87478',
            $this->APSSignature->calculateSignature($this->normalPaymentParams, false, $this->merchantConfig)
        );
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature::calculateSignature
     *
     * @return void
     */
    public function testCalculateSignature_applePaymentRequest(): void
    {
        $applePaymentParams = $this->getApplePaymentParams();

        $this->assertEquals(
            'aa8da5763c86290b87dd2583c26540fa3cbba621d2a9afa32d0180542cf2d400',
            $this->APSSignature->calculateSignature($applePaymentParams, true, $this->merchantConfig)
        );
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature::calculateSignature
     *
     * @return void
     */
    public function testCalculateSignature_applePaymentResponse(): void
    {
        $applePaymentParams = $this->getApplePaymentParams();

        $this->assertEquals(
            'f341b4c2be2cf4e12a8af9b7ac7a7885c3b3fe5fd142f9eb82dcc92a8a1ad688',
            $this->APSSignature->calculateSignature($applePaymentParams, false, $this->merchantConfig)
        );
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature::calculateSignature
     *
     * @return void
     */
    public function testCalculateSignature_isApplePay(): void
    {
        $applePaymentParams = $this->getApplePaymentParams();
        unset($applePaymentParams['apple_header']);

        $this->assertIsString(
            $this->APSSignature->calculateSignature($applePaymentParams, true, $this->merchantConfig)
        );

        unset($applePaymentParams['apple_paymentMethod']);

        $this->assertIsString(
            $this->APSSignature->calculateSignature($applePaymentParams, true, $this->merchantConfig)
        );
    }


    private function getApplePaymentParams(): array
    {
        $applePaymentParams = $this->normalPaymentParams;

        $applePaymentParams['digital_wallet'] = APSConstants::DIGITAL_WALLET_APPLE;
        $applePaymentParams['apple_signature'] = 'MIAGCSqGSIb3DQEHAqCAMIACAQExDzANBglghkgBZQMEAgEFADCABgkqhkiG9w0BBwEAAKCAMIID5jCC';
        $applePaymentParams['apple_paymentMethod'] = [
            'apple_displayName'     => 'Visa 0492',
            'apple_network'         => 'Visa',
            'apple_type'            => 'debit',
        ];
        $applePaymentParams['apple_data'] = 'C0QcNob17qrbYmBX63UxsfLOp3iqNU7ieMz1fmSlAYEG8gbkXsukzymwy7E3cqFZHD4UCZRL5uXcSfOIqT99c4xsqalQ3gIZgwhqcLZL6m/xqOuxqx1j9XQ9C54nmZJyAh6//zQWjeJhIeybGKS1zHlNRbaOScMp+hLMcvBnoL3EYkfbQiPJrxWUqXxGx/lxeo9G72Yp5QfsuQ74RW/mwBmKXtirFq7UsUt/Mh/KGgw';
        $applePaymentParams['apple_header'] = [
            'apple_transactionId'      => '93eec76cbedaedca44648e3d5c314766906e4e78ce33cd3b8396f105a1c0daed',
            'apple_ephemeralPublicKey' => 'MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAEM9JqF04vDlGIHEzWsaDm4bGBlTJdCn3+DH8ptlAmOSwVddD7/FN93A2o+l7i2U6Lmjb8WhKJcz6ZB+2MabcF4g==',
            'apple_publicKeyHash'      => 'bVTUiyTv0uCJgQz8SNYHBHOlHMD6sR1qDuCqTaETzkw=',
        ];

        return $applePaymentParams;
    }
}