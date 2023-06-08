<?php

namespace Tests\FrontEndAdapter\Maintenance;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSCore;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSExceptionCodes;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\FrontEndAdapter\Maintenance\PaymentRecurring;
use Exception;
use Tests\APSTestCase;

/**
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSCore
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\Logger
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Maintenance\PaymentRecurringModel
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentDTO
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter
 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\MaintenanceAdapter
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n
 */
class PaymentRecurringTest extends APSTestCase
{

    public function setUp(): void
    {
        parent::setUp();

        try {
            APSMerchant::setMerchantParams($this->merchantConfig);

            $this->normalRecurringParams = [
                'merchant_reference' => 'O-00001-63934',
                'amount' => '3197',
                'currency' => 'USD',
                'language' => 'en',
                'token_name' => '5540cb3b9e6a40e38227ab9141e7342a',
                'customer_email' => 'test@aps.com',
            ];
            $this->normalPaymentParams['signature'] = (new APSSignature())->calculateSignature($this->normalRecurringParams, true, $this->merchantConfig);
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }
    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Maintenance\PaymentRecurring::paymentRecurring
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\APSLogger
     *
     * @return void
     */
    public function testPaymentRecurring_noMerchantData(): void
    {
        try {
            $data = (new PaymentRecurring())->paymentRecurring($this->normalPaymentParams);

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
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Maintenance\PaymentRecurring::paymentRecurring
     *
     * @return void
     */
    public function testPaymentRecurring_withMerchantData(): void
    {
        try {
            APSMerchant::setMerchantParams($this->merchantConfig);

            $paymentRecurring = new PaymentRecurring();

            $returnData = [
                'test1' => 'test2',
            ];

            $mockCore = $this->createMock(APSCore::class);
            $mockCore
                ->method('callToRecurringPayment')
                ->willReturn($returnData);

            $replaceApsConnector = function () use ($mockCore) {
                $this->amazonPaymentServicesCore = $mockCore;
            };
            $doReplaceApsConnector = $replaceApsConnector->bindTo($paymentRecurring, PaymentRecurring::class);
            $doReplaceApsConnector();

            $this->assertEquals(
                $returnData,
                $paymentRecurring->paymentRecurring($this->normalRecurringParams)
            );
        } catch (APSException|Exception $e) {
            $this->fail($e->getMessage());
        }
    }
}
