<?php

namespace Tests\AmazonPaymentServices\Model\Payment;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConstants;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSExceptionCodes;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCRedirectAuthorization;
use Tests\APSTestCase;

/**
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCRedirectAuthorization
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\Logger
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n
 */
class CCRedirectAuthorizationTest extends APSTestCase
{
    private CCRedirectAuthorization $creditCardRedirectAuthorization;

    public function setUp(): void
    {
        parent::setUp();

        try {
            APSMerchant::setMerchantParams($this->merchantConfig);
            // instantiate a child class
            $this->creditCardRedirectAuthorization = new CCRedirectAuthorization();
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCRedirectAuthorization::getPaymentParameter
     *
     * @return void
     */
    public function testGetPaymentParameter_command(): void
    {
        try {
            $this->assertEquals(
                APSConstants::PAYMENT_COMMAND_AUTHORIZATION,
                $this->creditCardRedirectAuthorization->getPaymentParameter('command', null, true)
            );
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCRedirectAuthorization::getPaymentParameter
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\APSLogger
     *
     * @return void
     */
    public function testGetPaymentParameter_badAmount(): void
    {
        try {
            $this->creditCardRedirectAuthorization->getPaymentParameter('amount', 0, true);

            $this->fail();
        } catch (APSException $e) {
            $this->assertEquals(
                APSExceptionCodes::APS_INVALID_PARAMETER,
                $e->getCode()
            );
            $this->assertTrue(true, $e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCRedirectAuthorization::getPaymentParameter
     *
     * @return void
     */
    public function testGetPaymentParameter_goodAmount(): void
    {
        try {
            $this->assertIsInt(
                $this->creditCardRedirectAuthorization->getPaymentParameter('amount', 1249, true)
            );
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCRedirectAuthorization::getPaymentParameter
     *
     * @return void
     */
    public function testGetPaymentParameter_orderDescriptionCut(): void
    {
        try {
            $this->assertLessThanOrEqual(
                150,
                strlen(
                    $this->creditCardRedirectAuthorization->getPaymentParameter(
                    'order_description',
                    str_repeat('1234567890', 20),
                    false
                    )
                )
            );
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }
}
