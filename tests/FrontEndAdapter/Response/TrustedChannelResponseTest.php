<?php

namespace Tests\FrontEndAdapter\Response;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConstants;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\APSResponse;
use AmazonPaymentServicesSdk\FrontEndAdapter\Response\TrustedChannelResponse;
use Tests\APSTestCase;

/**
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\APSResponse
 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\ResponseAdapter
 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Response\TrustedChannelResponse
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSCore
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\APSLogger
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\Logger
 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\FrontEndAdapter
 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Payments\Secure3dsModal
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n
 */
class TrustedChannelResponseTest extends APSTestCase
{
    private TrustedChannelResponse $trustedChannelResponse;

    public function setUp(): void
    {
        parent::setUp();

        try {
            $this->merchantConfig['3ds_modal'] = true;
            APSMerchant::setMerchantParams($this->merchantConfig);

            $fakeResponseData = $this->normalPaymentParams;
            $fakeResponseData['signature'] = (new APSSignature())->calculateSignature($fakeResponseData, false, $this->merchantConfig);
            $this->trustedChannelResponse = new TrustedChannelResponse($fakeResponseData);
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Response\TrustedChannelResponse::getSuccessStatusCodes
     *
     * @return void
     */
    public function testGetSuccessStatusCodes(): void
    {
        $statusCodes = $this->trustedChannelResponse->getSuccessStatusCodes();

        $this->assertIsArray($statusCodes);
        $this->assertTrue(
            in_array('18', $statusCodes, true)
        );
        $this->assertTrue(
            in_array('02', $statusCodes, true)
        );
        $this->assertTrue(
            in_array('14', $statusCodes, true)
        );
        $this->assertTrue(
            in_array('44', $statusCodes, true)
        );
        $this->assertTrue(
            in_array('20', $statusCodes, true)
        );
    }


    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Response\TrustedChannelResponse::getDiscriminator
     * @return void
     */
    public function testGetDiscriminator()
    {
        $this->assertIsString($this->trustedChannelResponse->getDiscriminator());
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Response\TrustedChannelResponse::getPaymentOptions
     *
     * @return void
     */
    public function testGetPaymentOptions(): void
    {
        $paymentOptions = $this->trustedChannelResponse->getPaymentOptions();

        $this->assertIsArray($paymentOptions);
        $this->assertEquals(
            APSConstants::PAYMENT_TYPE_CREDIT_CARD,
            $paymentOptions['payment_type'] ?? ''
        );
        $this->assertEquals(
            APSConstants::INTEGRATION_TYPE_CUSTOM,
            $paymentOptions['integration_type'] ?? ''
        );
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Response\TrustedChannelResponse::process
     *
     * @return void
     */
    public function testProcess_3dSecureModal(): void
    {
        try {
            $fakeResponseData = $this->normalPaymentParams;
            $fakeResponseData['status'] = '02';
            $fakeResponseData['3ds_url'] = 'http://test.com';
            $fakeResponseData['signature'] = (new APSSignature())->calculateSignature($fakeResponseData, false, $this->merchantConfig);
            $this->trustedChannelResponse = new TrustedChannelResponse($fakeResponseData);

            $this->assertIsString(
                $this->trustedChannelResponse->process()
            );

            $this->assertEquals(
                APSResponse::class,
                $this->trustedChannelResponse->getPaymentResponseModel()::class
            );
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Response\TrustedChannelResponse::process
     *
     * @return void
     */
    public function testProcess_responseObject(): void
    {
        try {
            $fakeResponseData = $this->normalPaymentParams;
            $fakeResponseData['status'] = '02';
            $fakeResponseData['signature'] = (new APSSignature())->calculateSignature($fakeResponseData, false, $this->merchantConfig);
            $this->trustedChannelResponse = new TrustedChannelResponse($fakeResponseData);

            $this->assertEquals(
                APSResponse::class,
                ($this->trustedChannelResponse->process())::class
            );
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }
}
