<?php

namespace Tests\AmazonPaymentServices\Model\Maintenance;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConstants;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Maintenance\PaymentCaptureModel;
use Tests\APSTestCase;

/**
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Maintenance\PaymentCaptureModel
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\APSLogger
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\Logger
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n
 */
class PaymentCaptureModelTest extends APSTestCase
{
    private ?PaymentCaptureModel $paymentCapture;
	private array $normalCaptureParams;

    public function setUp(): void
    {
        parent::setUp();

        try {
            APSMerchant::setMerchantParams($this->merchantConfig);

            $this->normalCaptureParams = [
                'command' => 'CAPTURE',
                'amount' => 1,
                'merchant_reference' => 'O-00001-596',
                'currency' => 'USD',
                'language' => 'en',
            ];
            $this->normalPaymentParams['signature'] = (new APSSignature())->calculateSignature($this->normalCaptureParams, true, $this->merchantConfig);

            $this->paymentCapture = new PaymentCaptureModel();
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Maintenance\PaymentCaptureModel::getPaymentParameter
     *
     * @return void
     */
    public function testGetPaymentParameter(): void
    {
        try {
            $this->assertEquals(
                APSConstants::PAYMENT_COMMAND_CAPTURE,
                $this->paymentCapture->getPaymentParameter('command', $this->normalCaptureParams, true)
            );
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

}
