<?php

namespace Tests\AmazonPaymentServices\Merchant;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSExceptionCodes;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use Tests\APSTestCase;

/**
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\Logger
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n
 */
class APSMerchantTest extends APSTestCase
{
    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant::getMerchantParamsNoException
     *
     * @return void
     */
    public function testGetMerchantParamsNoException(): void
    {
        APSMerchant::reset();
        APSMerchant::getMerchantParamsNoException();

        $this->assertTrue(true);
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant::getMerchantParams
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\APSLogger
     *
     * @return void
     */
    public function testGetMerchantParams_noParamsSet(): void
    {
        try {
            APSMerchant::reset();
            APSMerchant::getMerchantParams();

            $this->fail();
        } catch (APSException $e) {
            $this->assertEquals(
                APSExceptionCodes::MERCHANT_CONFIG_MISSING,
                $e->getCode()
            );
            $this->assertTrue(true, $e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant::getMerchantParams
     *
     * @return void
     */
    public function testGetMerchantParams_noParamsSet_allowUnset(): void
    {
        try {
            APSMerchant::reset();

            $this->assertEquals(
                [],
                APSMerchant::getMerchantParamsNoException()
            );
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant::getMerchantParams
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\APSLogger
     *
     * @return void
     */
    public function testReset(): void
    {
        try {
            APSMerchant::reset();
            APSMerchant::getMerchantParams();

            $this->fail();
        } catch (APSException $e) {
            $this->assertEquals(
                APSExceptionCodes::MERCHANT_CONFIG_MISSING,
                $e->getCode()
            );
            $this->assertTrue(true, $e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant::setMerchantParams
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\APSLogger
     *
     * @return void
     */
    public function testSetMerchantParams_badParams(): void
    {
        try {
            APSMerchant::setMerchantParams([]);

            $this->fail();
        } catch (APSException $e) {
            $this->assertEquals(
                APSExceptionCodes::MERCHANT_CONFIG_MISSING,
                $e->getCode()
            );
            $this->assertTrue(true, $e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant::setMerchantParams
     *
     * @return void
     */
    public function testSetMerchantParams_goodParams(): void
    {
        try {
            APSMerchant::setMerchantParams($this->merchantConfig);

            $this->assertTrue(true);
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant::getMerchantParams
     *
     * @return void
     */
    public function testGetMerchantParams_goodParams(): void
    {
        try {
            APSMerchant::setMerchantParams($this->merchantConfig);

            $this->assertArrayHasKey(
                'access_code',
                APSMerchant::getMerchantParams()
            );
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }
}
