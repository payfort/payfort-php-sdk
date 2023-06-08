<?php

namespace Tests\AmazonPaymentServices\Model;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConstants;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSExceptionCodes;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Maintenance\PaymentCheckStatusModel;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCRedirectAuthorization;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentDTO;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter;
use Tests\APSTestCase;

/**
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCRedirectAuthorization
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\Logger
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\APSLogger
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentDTO
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n
 */
class PaymentTypeAdapterTest extends APSTestCase
{
    private PaymentTypeAdapter $paymentTypeAdapter;

    public function setUp(): void
    {
        parent::setUp();

        try {
            APSMerchant::setMerchantParams($this->merchantConfig);
            // instantiate a child class
            $this->paymentTypeAdapter = new CCRedirectAuthorization();
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter::getPaymentType
     *
     * @return void
     */
    public function testGetPaymentType(): void
    {
        $this->assertEquals(
            APSConstants::PAYMENT_TYPE_CREDIT_CARD,
            $this->paymentTypeAdapter->getPaymentType()
        );
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter::getIntegrationType
     *
     * @return void
     */
    public function testGetIntegrationType(): void
    {
        $this->assertEquals(
            APSConstants::INTEGRATION_TYPE_REDIRECT,
            $this->paymentTypeAdapter->getIntegrationType()
        );
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter::getRequiredParameters
     *
     * @return void
     */
    public function testGetRequiredParameters(): void
    {
        $this->assertTrue(
            in_array(
                'merchant_reference',
                $this->paymentTypeAdapter->getRequiredParameters(),
                true
            )
        );
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter::getOptionalParameters
     *
     * @return void
     */
    public function testGetOptionalParameters(): void
    {
        $this->assertTrue(
            in_array(
                'order_description',
                $this->paymentTypeAdapter->getOptionalParameters(),
                true
            )
        );
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter::generateMainParameters
     *
     * @return void
     */
    public function testGenerateMainParameters_withMerchantParams(): void
    {
        try {
            APSMerchant::setMerchantParams($this->merchantConfig);
            $this->assertArrayHasKey(
                'access_code',
                $this->paymentTypeAdapter->generateMainParameters(),
            );
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter::generateParameters
     *
     * @return void
     */
    public function testGenerateParameters(): void
    {
        try {
            APSMerchant::setMerchantParams($this->merchantConfig);
            $this->assertArrayHasKey(
                'command',
                $this->paymentTypeAdapter->generateParameters(new PaymentDTO($this->normalPaymentParams, $this->paymentTypeAdapter)),
            );
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter::generateMainParameters
     *
     * @return void
     */
    public function testGenerateMainParameters_noMerchantConfig(): void
    {
        try {
            APSMerchant::reset();
            $this->paymentTypeAdapter->generateMainParameters();

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
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter::getEndpoint
     *
     * @return void
     */
    public function testGetEndpoint(): void
    {
        try {
            $this->assertIsString(
                $this->paymentTypeAdapter->getEndpoint()
            );
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter::getEndpoint
     *
     * @return void
     */
    public function testGetEndpoint_noSandboxMode(): void
    {
        try {
            $merchantConfig = $this->merchantConfig;
            $merchantConfig['sandbox_mode'] = false;
            APSMerchant::setMerchantParams($merchantConfig);

            $this->assertStringContainsString(
                '//checkout',
                $this->paymentTypeAdapter->getEndpoint()
            );
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter::isValid
     *
     * @return void
     */
    public function testIsValid_nullParam(): void
    {
        try {
            $paymentDTO = new PaymentDTO(
                [],
                new PaymentCheckStatusModel()
            );
            $paymentDTO->validate();

            $this->fail();
        } catch (APSException $e) {
            $this->assertEquals(
                APSExceptionCodes::APS_PARAMETER_MISSING,
                $e->getCode()
            );
            $this->assertTrue(true, $e->getMessage());
        }
    }
}
