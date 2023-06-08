<?php

namespace Tests\FrontEndAdapter\Core;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSExceptionCodes;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCRedirectAuthorization;
use AmazonPaymentServicesSdk\FrontEndAdapter\Core\FrontEndAdapter;
use AmazonPaymentServicesSdk\FrontEndAdapter\Payments\CCRedirect;
use Tests\APSTestCase;
use Tests\TestFrontEndAdapterChildNoTemplate;
use Tests\TestPaymentTypeAdapterChild;

/**
 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\FrontEndAdapter
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCRedirectAuthorization
 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\FrontEndAdapter
 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Payments\CCRedirect
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSCore
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\APSLogger
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConnector
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\Logger
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentDTO
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n
 */
class FrontEndAdapterTest extends APSTestCase
{
    private FrontEndAdapter $frontEndAdapter;

    public function setUp(): void
    {
        parent::setUp();

        try {
            APSMerchant::setMerchantParams($this->merchantConfig);
            $this->frontEndAdapter = new CCRedirect();
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\FrontEndAdapter::getApsModelObject
     *
     * @return void
     */
    public function testGetApsModelObject(): void
    {
        $this->assertEquals(
            CCRedirectAuthorization::class,
            ($this->frontEndAdapter->getApsModelObject())::class
        );
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\FrontEndAdapter::setPaymentData
     *
     * @return void
     */
    public function testSetPaymentData_noPaymentData(): void
    {
        try {
            $this->frontEndAdapter->setPaymentData([]);
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
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\FrontEndAdapter::setPaymentData
     *
     * @return void
     */
    public function testSetPaymentData_badPaymentData(): void
    {
        try {
            $badPaymentData = $this->normalPaymentParams;
            unset($badPaymentData['merchant_reference']);

            $this->frontEndAdapter->setPaymentData($badPaymentData);
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
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\FrontEndAdapter::setPaymentData
     *
     * @return void
     */
    public function testSetPaymentData_correctPaymentData(): void
    {
        try {
            $this->frontEndAdapter->setPaymentData($this->normalPaymentParams);
            $this->assertTrue(true);
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\FrontEndAdapter::setPaymentData
     *
     * @return void
     */
    public function testSetCallbackUrl(): void
    {
        $this->frontEndAdapter->setCallbackUrl('https://test.com');
        $this->assertTrue(true);
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\FrontEndAdapter::render
     *
     * @return void
     */
    public function testRender_noMerchantParams(): void
    {
        try {
            $removeMerchantParams = function() {
                $this->merchantParams = null;
            };
            $doRemoveMerchantParams = $removeMerchantParams->bindTo($this->frontEndAdapter, FrontEndAdapter::class);
            $doRemoveMerchantParams();

            $this->frontEndAdapter->render([]);
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
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\FrontEndAdapter::render
     *
     * @return void
     */
    public function testRender_noPaymentParams(): void
    {
        try {
            $this->frontEndAdapter->render([]);
        } catch (APSException $e) {
            $this->assertEquals(
                APSExceptionCodes::PAYMENT_DATA_CONFIG_MISSING,
                $e->getCode()
            );
            $this->assertTrue(true, $e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\FrontEndAdapter::render
     *
     * @return void
     */
    public function testRender_noCallbackUrl(): void
    {
        try {
            $this->frontEndAdapter->setPaymentData($this->normalPaymentParams);
            $this->frontEndAdapter->render([]);
        } catch (APSException $e) {
            $this->assertEquals(
                APSExceptionCodes::APS_CALLBACK_MISSING,
                $e->getCode()
            );
            $this->assertTrue(true, $e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\FrontEndAdapter::renderHtmlContent
     *
     * @return void
     */
    public function testRenderHtmlContent(): void
    {
        try {
            $this->frontEndAdapter->setPaymentData($this->normalPaymentParams);
            $this->frontEndAdapter->setCallbackUrl('https://test.com');

            $this->assertIsString($this->frontEndAdapter->render([]));
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\FrontEndAdapter::__construct
     *
     * @return void
     */
    public function testConstruct_noTemplatePath(): void
    {
        try {
            $testFrontEndAdapterChild = new TestFrontEndAdapterChildNoTemplate();

            $testFrontEndAdapterChild->render([]);
        } catch (APSException $e) {
            $this->assertEquals(
                APSExceptionCodes::APS_TEMPLATE_FILE_MISSING,
                $e->getCode()
            );
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\FrontEndAdapter::__construct
     *
     * @return void
     */
    public function testRender_arrayParameter(): void
    {
        try {
            $testFrontEndAdapterChild = new CCRedirect();
            $testPaymentTypeAdapter = new TestPaymentTypeAdapterChild();

            $replaceApsConnector = function() use($testPaymentTypeAdapter) {
                $this->apsModelObject = $testPaymentTypeAdapter;
            };
            $doReplaceApsConnector = $replaceApsConnector->bindTo($testFrontEndAdapterChild, CCRedirect::class);
            $doReplaceApsConnector();

            $testFrontEndAdapterChild
                ->setPaymentData($this->normalPaymentParams)
                ->setCallbackUrl('https://test.com')
            ;

            $this->assertIsString(
                $testFrontEndAdapterChild->render([])
            );
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }
}
