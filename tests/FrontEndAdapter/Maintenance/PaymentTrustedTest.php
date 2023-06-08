<?php

namespace Tests\FrontEndAdapter\Maintenance;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSCore;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSExceptionCodes;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\FrontEndAdapter\Maintenance\PaymentTrusted;
use Tests\APSTestCase;

/**
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSCore
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\Logger
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentDTO
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\PaymentTrustedModel
 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\MaintenanceAdapter
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\APSResponse
 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\ResponseAdapter
 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Response\TrustedChannelResponse
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\APSLogger
 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\FrontEndAdapter
 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Payments\Secure3dsModal
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n
 */
class PaymentTrustedTest extends APSTestCase
{

    public function setUp(): void
    {
        parent::setUp();

        try {
            APSMerchant::setMerchantParams($this->merchantConfig);

            $this->normalTrustedParams = [
                'merchant_reference' => 'O-00001-63934',
                'amount' => '3197',
                'currency' => 'AED',
                'language' => 'en',
                'token_name' => '5540cb3b9e6a40e38227ab9141e7342a',
                'customer_email' => 'test@aps.com',
                'card_security_code' => '123',
                'customer_ip' => '127.0.0.1',
                'eci' => 'MOTO',
            ];
            $this->normalPaymentParams['signature'] = (new APSSignature())->calculateSignature($this->normalTrustedParams, true, $this->merchantConfig);

            $this->paymentTrusted = new PaymentTrusted();
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }
    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Maintenance\PaymentTrusted::paymentTrusted
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\APSLogger
     *
     * @return void
     */
    public function testPaymentTrusted_noMerchantParams(): void
    {
        try {
            $data = (new PaymentTrusted())->paymentTrusted($this->normalPaymentParams);

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
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Maintenance\PaymentTrusted::paymentTrusted
     *
     * @return void
     */
    public function testPaymentTrusted_withMerchantParams(): void
    {
        try {
            APSMerchant::setMerchantParams($this->merchantConfig);

            $paymentTrusted = new PaymentTrusted();

            $returnData = [
                'test1' => 'test2',
            ];

            $mockCore = $this->createMock(APSCore::class);
            $mockCore
                ->method('callToTrustedPayment')
                ->willReturn($returnData);

            $replaceApsConnector = function () use ($mockCore) {
                $this->amazonPaymentServicesCore = $mockCore;
            };
            $doReplaceApsConnector = $replaceApsConnector->bindTo($paymentTrusted, PaymentTrusted::class);
            $doReplaceApsConnector();

            $this->assertEquals(
                $returnData,
                $paymentTrusted->paymentTrusted($this->normalTrustedParams)
            );
        }catch (APSException|\PHPUnit\Framework\MockObject\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Maintenance\PaymentTrusted::paymentTrusted
     *
     * @return void
     */
    public function testPaymentTrusted_3ds(): void
    {
        try {
            $merchantConfig = $this->merchantConfig;
            $merchantConfig['3ds_modal'] = true;
            APSMerchant::setMerchantParams($merchantConfig);

            $paymentTrusted = new PaymentTrusted();

            $returnData = [
                'status'    => '02',
                'test1'     => 'test2',
                '3ds_url'   => 'https://test.com',
            ];

            $mockCore = $this->createMock(APSCore::class);
            $mockCore
                ->method('callToTrustedPayment')
                ->willReturn($returnData);

            $replaceApsConnector = function () use ($mockCore) {
                $this->amazonPaymentServicesCore = $mockCore;
            };
            $doReplaceApsConnector = $replaceApsConnector->bindTo($paymentTrusted, PaymentTrusted::class);
            $doReplaceApsConnector();

            $this->assertIsString(
                $paymentTrusted->paymentTrusted($this->normalTrustedParams)
            );
        }catch (APSException|\PHPUnit\Framework\MockObject\Exception $e) {
            $this->fail($e->getMessage());
        }
    }
}
