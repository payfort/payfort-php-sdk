<?php

namespace Tests\FrontEndAdapter\Maintenance;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSCore;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSExceptionCodes;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\FrontEndAdapter\Maintenance\PaymentMoto;
use Tests\APSTestCase;

/**
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSCore
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\Logger
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Maintenance\PaymentMotoModel
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentDTO
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter
 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\MaintenanceAdapter
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n
 */
class PaymentMotoTest extends APSTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        try {
            APSMerchant::setMerchantParams($this->merchantConfig);

            $this->normalMotoParams =[
                'merchant_reference' => 'O-00001-63934',
                'amount' => '3197',
                'currency' => 'USD',
                'language' => 'en',
                'customer_email' => 'test@aps.com',
                'token_name' => '5540cb3b9e6a40e38227ab9141e7342a',
                'customer_ip' => '127.0.0.1',
            ];
            $this->normalPaymentParams['signature'] = (new APSSignature())->calculateSignature($this->normalMotoParams, true, $this->merchantConfig);
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Maintenance\PaymentMoto::paymentMoto
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\APSLogger
     *
     * @return void
     */
    public function testPaymentMoto_noMerchantData(): void
    {
        try {
            $data = (new PaymentMoto())->paymentMoto($this->normalPaymentParams);

            $this->fail();
        }  catch (APSException $e) {
            $this->assertEquals(
                APSExceptionCodes::APS_PARAMETER_MISSING,
                $e->getCode()
            );
            $this->assertTrue(true, $e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Maintenance\PaymentMoto::paymentMoto
     *
     * @return void
     */
    public function testPaymentMoto_withMerchantData(): void
    {
        try {
            APSMerchant::setMerchantParams($this->merchantConfig);

            $paymentMoto = new PaymentMoto();

            $returnData = [
                'test1' => 'test2',
            ];

            $mockCore = $this->createMock(APSCore::class);
            $mockCore
                ->method('callToMotoPayment')
                ->willReturn($returnData);

            $replaceApsConnector = function () use ($mockCore) {
                $this->amazonPaymentServicesCore = $mockCore;
            };
            $doReplaceApsConnector = $replaceApsConnector->bindTo($paymentMoto, PaymentMoto::class);
            $doReplaceApsConnector();

            $this->assertEquals(
                $returnData,
                $paymentMoto->paymentMoto($this->normalMotoParams)
            );
        } catch (APSException|\Exception $e) {
            $this->fail($e->getMessage());
        }
    }
}
