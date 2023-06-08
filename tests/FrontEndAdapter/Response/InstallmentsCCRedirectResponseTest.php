<?php

namespace Tests\FrontEndAdapter\Response;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConstants;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\FrontEndAdapter\Response\InstallmentsCCRedirectResponse;
use Tests\APSTestCase;

/**
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\APSResponse
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCRedirectAuthorization
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\InstallmentsCCRedirectPurchase
 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\ResponseAdapter
 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Response\InstallmentsCCRedirectResponse
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSCore
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n
 */
class InstallmentsCCRedirectResponseTest extends APSTestCase
{
    private InstallmentsCCRedirectResponse $installmentsCCRedirectResponse;

    public function setUp(): void
    {
        parent::setUp();

        try {
            APSMerchant::setMerchantParams($this->merchantConfig);

            $fakeResponseData = $this->normalPaymentParams;
            $fakeResponseData['signature'] = (new APSSignature())->calculateSignature($fakeResponseData, false, $this->merchantConfig);
            $this->installmentsCCRedirectResponse = new InstallmentsCCRedirectResponse($fakeResponseData);
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Response\InstallmentsCCRedirectResponse::process
     *
     * @return void
     */
    public function testProcess()
    {
        $this->assertNull($this->installmentsCCRedirectResponse->process());
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Response\InstallmentsCCRedirectResponse::getSuccessStatusCodes
     *
     * @return void
     */
    public function testGetSuccessStatusCodes(): void
    {
        $statusCodes = $this->installmentsCCRedirectResponse->getSuccessStatusCodes();

        $this->assertIsArray($statusCodes);
        $this->assertTrue(
            in_array('02', $statusCodes, true)
        );
        $this->assertTrue(
            in_array('14', $statusCodes, true)
        );
        $this->assertTrue(
            in_array('44', $statusCodes, true)
        );
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Response\InstallmentsCCRedirectResponse::getPaymentOptions
     *
     * @return void
     */
    public function testGetPaymentOptions()
    {
        $paymentOptions = $this->installmentsCCRedirectResponse->getPaymentOptions();

        $this->assertIsArray($paymentOptions);
        $this->assertEquals(
            APSConstants::PAYMENT_TYPE_INSTALMENTS,
            $paymentOptions['payment_type'] ?? ''
        );
        $this->assertEquals(
            APSConstants::INTEGRATION_TYPE_REDIRECT,
            $paymentOptions['integration_type'] ?? ''
        );
    }
}
