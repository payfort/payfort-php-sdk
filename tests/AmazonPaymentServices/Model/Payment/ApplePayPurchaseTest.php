<?php

namespace Tests\AmazonPaymentServices\Model\Payment;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConstants;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSExceptionCodes;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\ApplePayPurchase;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentDTO;
use Tests\APSTestCase;
/**
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\Logger
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\ApplePayAuthorization
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\APSLogger
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentDTO
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n
 */
class ApplePayPurchaseTest extends APSTestCase
{
    private ?ApplePayPurchase $applePayPurchase;

    public function setUp(): void
    {
        parent::setUp();

        try {
            APSMerchant::setMerchantParams($this->merchantConfig);

            // instantiate a child class
            $this->applePayPurchase = new ApplePayPurchase();
        } catch (APSException|\Exception $e) {
            $this->fail();
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\ApplePayPurchase::getPaymentParameter
     *
     * @return void
     */
    public function testGetPaymentParameter_command(): void
    {
        try {
            $this->assertEquals(
                APSConstants::PAYMENT_COMMAND_PURCHASE,
                $this->applePayPurchase->getPaymentParameter('command', null, false)
            );
        } catch (APSException|\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\ApplePayPurchase::getPaymentParameter
     *
     * @return void
     */
    public function testGetPaymentParameter_appleHeader_bad(): void
    {
        try {
            $appleHeaderArray = [
                'apple_transactionId'       => 'test1',
                'apple_ephemeralPublicKey'  => 'test2',
                'apple_hash'                => 'test3=',
            ];

            $this->applePayPurchase->getPaymentParameter('apple_header', $appleHeaderArray, true);

            $this->fail();
        } catch (APSException|\Exception $e) {
            $this->assertEquals(
                APSExceptionCodes::APS_PARAMETER_MISSING,
                $e->getCode()
            );
            $this->assertTrue(true, $e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\ApplePayPurchase::getPaymentParameter
     *
     * @return void
     */
    public function testGetPaymentParameter_appleHeader_good(): void
    {
        try {
            $appleHeaderArray = [
                'apple_transactionId'       => 'test1',
                'apple_ephemeralPublicKey'  => 'test2',
                'apple_publicKeyHash'       => 'test3=',
            ];

            $this->assertEquals(
                $appleHeaderArray,
                $this->applePayPurchase->getPaymentParameter('apple_header', $appleHeaderArray, true)
            );
        } catch (APSException|\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\ApplePayPurchase::getPaymentParameter
     *
     * @return void
     */
    public function testGetPaymentParameter_applePaymentMethod_bad(): void
    {
        try {
            $applePaymentMethodArray = [
                'apple_displayName'     => 'test1',
                'apple_network'         => 'test2',
                'apple_'                => 'test3=',
            ];

            $this->applePayPurchase->getPaymentParameter('apple_paymentMethod', $applePaymentMethodArray, true);

            $this->fail();
        } catch (APSException|\Exception $e) {
            $this->assertEquals(
                APSExceptionCodes::APS_PARAMETER_MISSING,
                $e->getCode()
            );
            $this->assertTrue(true, $e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\ApplePayPurchase::getPaymentParameter
     *
     * @return void
     */
    public function testGetPaymentParameter_applePaymentMethod_good(): void
    {
        try {
            $applePaymentMethodArray = [
                'apple_displayName'     => 'test1',
                'apple_network'         => 'test2',
                'apple_type'            => 'test3=',
            ];

            $this->applePayPurchase->getPaymentParameter('apple_paymentMethod', $applePaymentMethodArray, true);

            $this->assertTrue(true);
        } catch (APSException|\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\ApplePayPurchase::generateParameters
     *
     * @return void
     */
    public function testGenerateParameters_noDigitalWallet(): void
    {
        try {
            $this->applePayPurchase
                ->generateParameters(new PaymentDTO($this->normalPaymentParams, $this->applePayPurchase));

            $this->fail();
        } catch (APSException $e) {
            $this->assertEquals(
                APSExceptionCodes::APS_PARAMETER_MISSING,
                $e->getCode()
            );
            $this->assertTrue(true, $e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\ApplePayPurchase::generateParameters
     *
     * @return void
     */
    public function testGenerateParameters_missingAppleData(): void
    {
        try {
            $applePayParams = array_merge($this->normalPaymentParams, [
                'digital_wallet'    => 'APPLE_PAY'
            ]);

            $this->applePayPurchase
                ->generateParameters(new PaymentDTO($applePayParams, $this->applePayPurchase));

            $this->fail();
        } catch (APSException $e) {
            $this->assertEquals(
                APSExceptionCodes::APS_PARAMETER_MISSING,
                $e->getCode()
            );
            $this->assertTrue(true, $e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\ApplePayPurchase::generateParameters
     *
     * @return void
     */
    public function testGenerateParameters_missingAppleSignature(): void
    {
        try {
            $applePayParams = array_merge($this->normalPaymentParams, [
                'digital_wallet'    => 'APPLE_PAY',
                'apple_data'        => 'dsajdlsakjkldsjalkdsajkljdsakljdl',
            ]);

            $this->applePayPurchase
                ->generateParameters(new PaymentDTO($applePayParams, $this->applePayPurchase));

            $this->fail();
        } catch (APSException $e) {
            $this->assertEquals(
                APSExceptionCodes::APS_PARAMETER_MISSING,
                $e->getCode()
            );
            $this->assertTrue(true, $e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\ApplePayPurchase::generateParameters
     *
     * @return void
     */
    public function testGenerateParameters_missingAppleHeader(): void
    {
        try {
            $applePayParams = array_merge($this->normalPaymentParams, [
                'digital_wallet'    => 'APPLE_PAY',
                'apple_data'        => 'dsajdlsakjkldsjalkdsajkljdsakljdl',
                'apple_signature'   => 'dsajdlsakjkldsjalkdsajkljdsakljdl',
            ]);

            $this->applePayPurchase
                ->generateParameters(new PaymentDTO($applePayParams, $this->applePayPurchase));

            $this->fail();
        } catch (APSException $e) {
            $this->assertEquals(
                APSExceptionCodes::APS_INVALID_TYPE,
                $e->getCode()
            );
            $this->assertTrue(true, $e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\ApplePayPurchase::generateParameters
     *
     * @return void
     */
    public function testGenerateParameters_missingApplePaymentMethod(): void
    {
        try {
            $applePayParams = array_merge($this->normalPaymentParams, [
                'digital_wallet'    => 'APPLE_PAY',
                'apple_data'        => 'dsajdlsakjkldsjalkdsajkljdsakljdl',
                'apple_signature'   => 'dsajdlsakjkldsjalkdsajkljdsakljdl',
                'apple_header'      => [
                    'apple_transactionId'      => '93eec76cbedaedca44648e3d5c314766906e4e78ce33cd3b8396f105a1c0daed',
                    'apple_ephemeralPublicKey' => 'MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAEM9JqF04vDlGIHEzWsaDm4bGBlTJdCn3+DH8ptlAmOSwVddD7/FN93A2o+l7i2U6Lmjb8WhKJcz6ZB+2MabcF4g==',
                    'apple_publicKeyHash'      => 'bVTUiyTv0uCJgQz8SNYHBHOlHMD6sR1qDuCqTaETzkw=',
                ],
            ]);

            $this->applePayPurchase
                ->generateParameters(new PaymentDTO($applePayParams, $this->applePayPurchase) );

            $this->fail();
        } catch (APSException $e) {
            $this->assertEquals(
                APSExceptionCodes::APS_INVALID_TYPE,
                $e->getCode()
            );
            $this->assertTrue(true, $e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\ApplePayPurchase::generateParameters
     *
     * @return void
     */
    public function testGenerateParameters_allGood(): void
    {
        try {
            $applePayParams = array_merge($this->normalPaymentParams, [
                'digital_wallet'    => 'APPLE_PAY',
                'apple_data'        => 'dsajdlsakjkldsjalkdsajkljdsakljdl',
                'apple_signature'   => 'dsajdlsakjkldsjalkdsajkljdsakljdl',
                'apple_header'      => [
                    'apple_transactionId'      => '93eec76cbedaedca44648e3d5c314766906e4e78ce33cd3b8396f105a1c0daed',
                    'apple_ephemeralPublicKey' => 'MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAEM9JqF04vDlGIHEzWsaDm4bGBlTJdCn3+DH8ptlAmOSwVddD7/FN93A2o+l7i2U6Lmjb8WhKJcz6ZB+2MabcF4g==',
                    'apple_publicKeyHash'      => 'bVTUiyTv0uCJgQz8SNYHBHOlHMD6sR1qDuCqTaETzkw=',
                ],
                'apple_paymentMethod'   => [
                    'apple_displayName'     => 'Visa 0492',
                    'apple_network'         => 'Visa',
                    'apple_type'            => 'debit',
                ],
                'customer_ip'           => '127.0.0.1',
            ]);

            $this->applePayPurchase
                ->generateParameters(new PaymentDTO($applePayParams, $this->applePayPurchase));

            $this->assertTrue(true);
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }
}
