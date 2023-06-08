<?php

namespace Tests\AmazonPaymentServices\Model\Maintenance;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConstants;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSExceptionCodes;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Maintenance\PaymentCheckStatusModel;
use Tests\APSTestCase;

/**
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Maintenance\PaymentCheckStatusModel
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\APSLogger
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\Logger
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n
 */
class PaymentCheckStatusModelTest extends APSTestCase
{
    private ?PaymentCheckStatusModel $paymentCheckStatus;

    public function setUp(): void
    {
        parent::setUp();

        try {
            APSMerchant::setMerchantParams($this->merchantConfig);

            $this->normalStatusParams = [
                'query_command' => 'CHECK_STATUS',
                'merchant_reference' => 'O-00001-27297',
                'language' => 'en',
            ];

            $this->normalPaymentParams['signature'] = (new APSSignature())->calculateSignature($this->normalStatusParams, true, $this->merchantConfig);

            $this->paymentCheckStatus = new PaymentCheckStatusModel();
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Maintenance\PaymentCheckStatusModel::getPaymentParameter
     *
     * @return void
     */
    public function testGetPaymentParameter(): void
    {
        try {
            $this->assertEquals(
                APSConstants::PAYMENT_COMMAND_CHECK_STATUS,
                $this->paymentCheckStatus->getPaymentParameter('query_command', $this->normalStatusParams, true)
            );
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Maintenance\PaymentCheckStatusModel::isValid
     *
     * @return void
     */
    public function testIsValid(): void
    {
        try {
            $paymentCheckStatusModel = new PaymentCheckStatusModel();

            $paymentCheckStatusModel->isValid([
                'merchant_reference'    => null,
            ]);
        } catch (APSException $e) {
            $this->assertEquals(
                APSExceptionCodes::APS_PARAMETER_MISSING,
                $e->getCode()
            );
        }
    }
}
