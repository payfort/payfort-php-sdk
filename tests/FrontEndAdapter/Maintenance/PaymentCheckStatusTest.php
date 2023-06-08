<?php

namespace Tests\FrontEndAdapter\Maintenance;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSCore;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSExceptionCodes;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\FrontEndAdapter\Maintenance\PaymentCheckStatus;
use PHPUnit\Framework\Constraint\ExceptionCode;
use PHPUnit\Framework\MockObject\Exception;
use Tests\APSTestCase;

/**
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSCore
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\Logger
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\APSLogger
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant
 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\MaintenanceAdapter
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentDTO
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConnector
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n
 */
class PaymentCheckStatusTest extends APSTestCase
{
    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Maintenance\PaymentCheckStatus::paymentCheckStatus
     *
     * @return void
     */
    public function testPaymentCheckStatus_noMerchantData(): void
    {
        try {
            $data = (new PaymentCheckStatus())->paymentCheckStatus($this->normalPaymentParams);

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
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Maintenance\PaymentCheckStatus::paymentCheckStatus
     *
     * @return void
     */
    public function testPaymentCheckStatus_withMerchantData(): void
    {
        try {
            APSMerchant::setMerchantParams($this->merchantConfig);

            $paymentCheckStatus = new PaymentCheckStatus();

            $returnData = [
                'test1' => 'test2',
            ];

            $mockCore = $this->createMock(APSCore::class);
            $mockCore
                ->method('callToCheckPaymentStatus')
                ->willReturn($returnData);

            $replaceApsConnector = function() use($mockCore) {
                $this->amazonPaymentServicesCore = $mockCore;
            };
            $doReplaceApsConnector = $replaceApsConnector->bindTo($paymentCheckStatus, PaymentCheckStatus::class);
            $doReplaceApsConnector();

            $this->assertEquals(
                $returnData,
                $paymentCheckStatus->paymentCheckStatus($this->normalPaymentParams)
            );
        } catch (APSException|Exception $e) {
            $this->fail($e->getMessage());
        }
    }
}
