<?php

namespace Tests\FrontEndAdapter\Maintenance;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSCore;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSExceptionCodes;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\FrontEndAdapter\Maintenance\PaymentRefund;
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
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\APSLogger
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n
 */
class PaymentRefundTest extends APSTestCase
{

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Maintenance\PaymentRefund::paymentRefund
     *
     * @return void
     */
    public function testPaymentRefund_noMerchantData(): void
    {
        try {
            $data = (new PaymentRefund())->paymentRefund($this->normalPaymentParams);

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
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Maintenance\PaymentRefund::paymentRefund
     *
     * @return void
     */
    public function testPaymentRefund_withMerchantData(): void
    {
        try {
            APSMerchant::setMerchantParams($this->merchantConfig);

            $paymentRefund = new PaymentRefund();

            $returnData = [
                'test1' => 'test2',
            ];

            $mockCore = $this->createMock(APSCore::class);
            $mockCore
                ->method('callToRefundPayment')
                ->willReturn($returnData);

            $replaceApsConnector = function () use ($mockCore) {
                $this->amazonPaymentServicesCore = $mockCore;
            };
            $doReplaceApsConnector = $replaceApsConnector->bindTo($paymentRefund, PaymentRefund::class);
            $doReplaceApsConnector();

            $this->assertEquals(
                $returnData,
                $paymentRefund->paymentRefund($this->normalPaymentParams)
            );
        } catch (APSException|Exception $e) {
            $this->fail($e->getMessage());
        }
    }
}
