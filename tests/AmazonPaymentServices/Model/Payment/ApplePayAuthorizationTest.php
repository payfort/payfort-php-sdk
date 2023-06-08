<?php

namespace Tests\AmazonPaymentServices\Model\Payment;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConstants;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSExceptionCodes;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\ApplePayAuthorization;
use Tests\APSTestCase;

/**
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\Logger
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\APSLogger
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n
 */
class ApplePayAuthorizationTest extends APSTestCase
{
    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\ApplePayAuthorization::getPaymentParameter
     *
     * @return void
     */
    public function testGetPaymentParameter_digitalWallet(): void
    {
        try {
            APSMerchant::setMerchantParams($this->merchantConfig);

            $applePayAuthorization = new ApplePayAuthorization();

            $this->assertEquals(
                APSConstants::DIGITAL_WALLET_APPLE,
                $applePayAuthorization->getPaymentParameter('digital_wallet', null, true)
            );
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\ApplePayAuthorization::getPaymentParameter
     *
     * @return void
     */
    public function testGetPaymentParameter_appleHeader_badType(): void
    {
        try {
            APSMerchant::setMerchantParams($this->merchantConfig);

            $applePayAuthorization = new ApplePayAuthorization();

            $applePayAuthorization->getPaymentParameter(
                'apple_header',
                null,
                true
            );
        } catch (APSException $e) {
            $this->assertEquals(
                APSExceptionCodes::APS_INVALID_TYPE,
                $e->getCode()
            );
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\ApplePayAuthorization::getPaymentParameter
     *
     * @return void
     */
    public function testGetPaymentParameter_appleHeader_missingParam(): void
    {
        try {
            APSMerchant::setMerchantParams($this->merchantConfig);

            $applePayAuthorization = new ApplePayAuthorization();

            $applePayAuthorization->getPaymentParameter(
                'apple_header',
                [],
                true
            );
        } catch (APSException $e) {
            $this->assertEquals(
                APSExceptionCodes::APS_PARAMETER_MISSING,
                $e->getCode()
            );
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\ApplePayAuthorization::getPaymentParameter
     *
     * @return void
     */
    public function testGetPaymentParameter_appleHeader_allGoodParams(): void
    {
        try {
            APSMerchant::setMerchantParams($this->merchantConfig);

            $applePayAuthorization = new ApplePayAuthorization();

            $appleHeaderData = [
                'apple_transactionId'       => 'erwkjrlewjrklew',
                'apple_ephemeralPublicKey'  => '3242rwdsfdsf',
                'apple_publicKeyHash'       => '0d9dsakjdlksa',
            ];

            $this->assertEquals(
                $appleHeaderData,
                $applePayAuthorization->getPaymentParameter(
                    'apple_header',
                    $appleHeaderData,
                    true
                )
            );

        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\ApplePayAuthorization::getPaymentParameter
     *
     * @return void
     */
    public function testGetPaymentParameter_applePaymentMethod_badType(): void
    {
        try {
            APSMerchant::setMerchantParams($this->merchantConfig);

            $applePayAuthorization = new ApplePayAuthorization();

            $applePayAuthorization->getPaymentParameter(
                'apple_paymentMethod',
                null,
                true
            );
        } catch (APSException $e) {
            $this->assertEquals(
                APSExceptionCodes::APS_INVALID_TYPE,
                $e->getCode()
            );
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\ApplePayAuthorization::getPaymentParameter
     *
     * @return void
     */
    public function testGetPaymentParameter_applePaymentMethod_missingParam(): void
    {
        try {
            APSMerchant::setMerchantParams($this->merchantConfig);

            $applePayAuthorization = new ApplePayAuthorization();

            $applePayAuthorization->getPaymentParameter(
                'apple_paymentMethod',
                [],
                true
            );
        } catch (APSException $e) {
            $this->assertEquals(
                APSExceptionCodes::APS_PARAMETER_MISSING,
                $e->getCode()
            );
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\ApplePayAuthorization::getPaymentParameter
     *
     * @return void
     */
    public function testGetPaymentParameter_applePaymentMethod_goodParam(): void
    {
        try {
            APSMerchant::setMerchantParams($this->merchantConfig);

            $applePayAuthorization = new ApplePayAuthorization();

            $applePaymentMethodData = [
                'apple_displayName' => 'dsjalkdjklsajdsa',
                'apple_network'     => 'dsjalkdjklsajdsa',
                'apple_type'        => 'dsjalkdjklsajdsa',
            ];
            $this->assertEquals(
                $applePaymentMethodData,
                $applePayAuthorization->getPaymentParameter(
                    'apple_paymentMethod',
                    $applePaymentMethodData,
                    true
                )
            );
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\ApplePayAuthorization::getPaymentParameter
     *
     * @return void
     */
    public function testGetPaymentParameter_defaultGoodParam(): void
    {
        try {
            APSMerchant::setMerchantParams($this->merchantConfig);

            $applePayAuthorization = new ApplePayAuthorization();

            $this->assertNull(
                $applePayAuthorization->getPaymentParameter(
                    'random_param',
                    null,
                    false
                )
            );
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }
}
