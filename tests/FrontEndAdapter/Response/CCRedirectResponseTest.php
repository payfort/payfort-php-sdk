<?php

namespace Tests\FrontEndAdapter\Response;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConstants;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\FrontEndAdapter\Response\CCRedirectResponse;
use Tests\APSTestCase;

/**
 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Response\CCRedirectResponse
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\APSLogger
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\APSResponse
 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\ResponseAdapter
 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Response\CCRedirectResponse
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\Logger
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCRedirectAuthorization
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSCore
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n
 */
class CCRedirectResponseTest extends APSTestCase
{
    private CCRedirectResponse $creditCardRedirectResponse;

    public function setUp(): void
    {
        parent::setUp();

        try {
            APSMerchant::setMerchantParams($this->merchantConfig);

            $fakeResponseData = $this->normalPaymentParams;
            $fakeResponseData['signature'] = (new APSSignature())->calculateSignature($fakeResponseData, false, $this->merchantConfig);
            $this->creditCardRedirectResponse = new CCRedirectResponse($fakeResponseData);
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Response\CCRedirectResponse::getSuccessStatusCodes
     *
     * @return void
     */
    public function testGetSuccessStatusCodes(): void
    {
        $statusCodes = $this->creditCardRedirectResponse->getSuccessStatusCodes();

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
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Response\CCRedirectResponse::getPaymentOptions
     *
     * @return void
     */
    public function testGetPaymentOptions(): void
    {
        $paymentOptions = $this->creditCardRedirectResponse->getPaymentOptions();

        $this->assertIsArray($paymentOptions);
        $this->assertEquals(
            APSConstants::PAYMENT_TYPE_CREDIT_CARD,
            $paymentOptions['payment_type'] ?? ''
        );
        $this->assertEquals(
            APSConstants::INTEGRATION_TYPE_REDIRECT,
            $paymentOptions['integration_type'] ?? ''
        );
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Response\CCRedirectResponse::process
     *
     * @return void
     */
    public function testProcess(): void
    {
        $this->assertNull(
            $this->creditCardRedirectResponse->process()
        );
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Response\CCRedirectResponse::getDiscriminator
     *
     * @return void
     */
    public function testGetDiscriminator(): void
    {
        try {
            $fakeResponseData = $this->normalPaymentParams;
            $fakeResponseData['signature'] = (new APSSignature())->calculateSignature($fakeResponseData, false, $this->merchantConfig);
            $creditCardRedirectResponse = new CCRedirectResponse($fakeResponseData);

            $authorizationDiscriminator = $creditCardRedirectResponse->getDiscriminator();

            $creditCardRedirectResponse = new CCRedirectResponse($fakeResponseData, [], true);
            $purchaseDiscriminator = $creditCardRedirectResponse->getDiscriminator();

            $this->assertNotEquals(
                $authorizationDiscriminator,
                $purchaseDiscriminator
            );
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

}
