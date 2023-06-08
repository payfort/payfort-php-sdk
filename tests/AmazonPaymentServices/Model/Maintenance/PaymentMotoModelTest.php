<?php

namespace Tests\AmazonPaymentServices\Model\Maintenance;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConstants;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Maintenance\PaymentMotoModel;
use Tests\APSTestCase;

/**
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Maintenance\PaymentMotoModel
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n
 */
class PaymentMotoModelTest extends APSTestCase
{
    private PaymentMotoModel $paymentMotoModel;

    public function setUp(): void
    {
        parent::setUp();

        try {
            APSMerchant::setMerchantParams($this->merchantConfig);

            $this->paymentMotoModel = new PaymentMotoModel();
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Maintenance\PaymentMotoModel::getPaymentParameter
     *
     * @return void
     */
    public function testGetPaymentParameter_eciCommand(): void
    {
        try {
            $this->assertEquals(
                APSConstants::PAYMENT_ECI_MOTO,
                $this->paymentMotoModel->getPaymentParameter('eci', null, true)
            );
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Maintenance\PaymentMotoModel::getPaymentParameter
     *
     * @return void
     */
    public function testGetPaymentParameter_defaultReturn(): void
    {
        $this->assertTrue(
            in_array(
                'merchant_reference',
                $this->paymentMotoModel->getRequiredParameters(),
                true
            )
        );

        $this->assertTrue(
            in_array(
                'merchant_reference',
                $this->paymentMotoModel->getRequiredParameters(),
                true
            )
        );
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Maintenance\PaymentMotoModel::getPaymentParameter
     *
     * @return void
     */
    public function testGetPaymentParameter_customerIp(): void
    {
        try {
            $ipAddress = '127.0.0.1';
            $this->assertEquals(
                $ipAddress,
                $this->paymentMotoModel->getPaymentParameter('customer_ip', $ipAddress, true)
            );
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }
}
