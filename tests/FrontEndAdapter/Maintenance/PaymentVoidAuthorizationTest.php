<?php

namespace Tests\FrontEndAdapter\Maintenance;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSCore;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSExceptionCodes;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\FrontEndAdapter\Maintenance\PaymentVoidAuthorization;
use PHPUnit\Framework\MockObject\Exception;
use Tests\APSTestCase;

/**
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConnector
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSCore
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\Logger
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentDTO
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter
 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\MaintenanceAdapter
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n
 */
class PaymentVoidAuthorizationTest extends APSTestCase
{

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Maintenance\PaymentVoidAuthorization::paymentVoid
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\APSLogger
     *
     * @return void
     */
    public function testPaymentVoidAuthorization_noMerchantData(): void
    {
        try {
            $data = (new PaymentVoidAuthorization())->paymentVoid($this->normalPaymentParams);

            $this->fail();
        } catch (APSException $e) {
            $this->assertContains(
                $e->getCode(),
                [
                    APSExceptionCodes::RESPONSE_NO_SIGNATURE,
                    APSExceptionCodes::MERCHANT_CONFIG_MISSING,
                ]
            );
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Maintenance\PaymentVoidAuthorization::paymentVoid
     *
     * @return void
     */
    public function testPaymentVoidAuthorization_withMerchantData(): void
    {
        try {
            APSMerchant::setMerchantParams($this->merchantConfig);

            $paymentVoidAuthorization = new PaymentVoidAuthorization();

            $returnData = [
                'test1' => 'test2',
            ];

            $mockCore = $this->createMock(APSCore::class);
            $mockCore
                ->method('callToVoidPayment')
                ->willReturn($returnData);

            $replaceApsConnector = function() use($mockCore) {
                $this->amazonPaymentServicesCore = $mockCore;
            };
            $doReplaceApsConnector = $replaceApsConnector->bindTo($paymentVoidAuthorization, PaymentVoidAuthorization::class);
            $doReplaceApsConnector();

            $this->assertEquals(
                $returnData,
                $paymentVoidAuthorization->paymentVoid($this->normalPaymentParams)
            );
        } catch (APSException|Exception $e) {
            $this->fail($e->getMessage());
        }
    }
}
