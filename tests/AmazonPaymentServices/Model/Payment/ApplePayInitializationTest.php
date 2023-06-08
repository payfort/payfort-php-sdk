<?php

namespace Tests\AmazonPaymentServices\Model\Payment;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSExceptionCodes;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\ApplePayInitialization;
use Tests\APSTestCase;

/**
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\APSLogger
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\Logger
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\ApplePayInitialization
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n
 */
class ApplePayInitializationTest extends APSTestCase
{
    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\ApplePayInitialization::getPaymentParameter
     *
     * @return void
     */
    public function testGetPaymentParameter_noMerchantIdentifier(): void
    {
        try {
            $merchantConfig = $this->merchantConfig;
            unset($merchantConfig['Apple_CertificatePath']);

            APSMerchant::setMerchantParams($merchantConfig);
            $applePayInitialization = new ApplePayInitialization();

            $applePayInitialization->getPaymentParameter('merchantIdentifier', null, true);

            $this->fail('no exception thrown!');
        } catch (APSException $e) {
            $this->assertEquals(
                APSExceptionCodes::MERCHANT_CONFIG_APPLE_CERTIFICATE_PATH_MISSING,
                $e->getCode()
            );
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\ApplePayInitialization::getPaymentParameter
     *
     * @return void
     */
    public function testGetPaymentParameter_noMerchantUID(): void
    {
        try {
            $merchantConfig = $this->merchantConfig;
            unset($merchantConfig['Apple_MerchantUid']);

            APSMerchant::setMerchantParams($merchantConfig);
            $applePayInitialization = new ApplePayInitialization();

            $applePayInitialization->getPaymentParameter('merchantIdentifier', null, true);

            $this->fail('no exception thrown!');
        } catch (APSException $e) {
            $this->assertEquals(
                APSExceptionCodes::MERCHANT_CONFIG_APPLE_MERCHANT_ID_MISSING,
                $e->getCode()
            );
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\ApplePayInitialization::getPaymentParameter
     *
     * @return void
     */
    public function testGetPaymentParameter_hasMerchantUID(): void
    {
        try {
            $merchantConfig = $this->merchantConfig;

            APSMerchant::setMerchantParams($merchantConfig);
            $applePayInitialization = new ApplePayInitialization();

            $this->assertEquals(
                $merchantConfig['Apple_MerchantUid'],
                $applePayInitialization->getPaymentParameter('merchantIdentifier', null, true)
            );
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\ApplePayInitialization::getPaymentParameter
     *
     * @return void
     */
    public function testGetPaymentParameter_otherParameter(): void
    {
        try {
            $merchantConfig = $this->merchantConfig;

            APSMerchant::setMerchantParams($merchantConfig);
            $applePayInitialization = new ApplePayInitialization();

            $test = 'string';

            $this->assertEquals(
                $test,
                $applePayInitialization->getPaymentParameter('otherParameter', $test, true)
            );

            $this->assertNull(
                $applePayInitialization->getPaymentParameter('otherParameter', null, false)
            );
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\ApplePayInitialization::getPaymentParameter
     *
     * @return void
     */
    public function testGetPaymentParameter_badOtherParameter(): void
    {
        try {
            $merchantConfig = $this->merchantConfig;

            APSMerchant::setMerchantParams($merchantConfig);
            $applePayInitialization = new ApplePayInitialization();

            $applePayInitialization->getPaymentParameter('otherParameter', null, true);
        } catch (APSException $e) {
            $this->assertEquals(
                APSExceptionCodes::APS_PARAMETER_MISSING,
                $e->getCode()
            );
        }
    }
}
