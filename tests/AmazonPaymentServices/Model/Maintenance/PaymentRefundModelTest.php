<?php

namespace Tests\AmazonPaymentServices\Model\Maintenance;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConstants;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Maintenance\PaymentRefundModel;
use Tests\APSTestCase;

/**
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Maintenance\PaymentCaptureModel
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\APSLogger
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\Logger
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Maintenance\PaymentRefundModel
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n
 */
class PaymentRefundModelTest extends APSTestCase
{
    private ?PaymentRefundModel $paymentRefund;

    public function setUp(): void
    {
            parent::setUp();

        try {
            APSMerchant::setMerchantParams($this->merchantConfig);

            $this->normalRefundParams = [
                'command' => 'CAPTURE',
                'amount' => 1,
                'merchant_reference' => 'O-00001-857',
                'currency' => 'USD',
                'language' => 'en',
            ];
            $this->normalPaymentParams['signature'] = (new APSSignature())->calculateSignature($this->normalRefundParams, true, $this->merchantConfig);

            $this->paymentRefund = new PaymentRefundModel();
        }   catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testGetPaymentParameter(): void
    {
        try {
            $this->assertEquals(
                APSConstants::PAYMENT_COMMAND_REFUND,
                $this->paymentRefund->getPaymentParameter('command', $this->normalRefundParams, true)
            );
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }
}
