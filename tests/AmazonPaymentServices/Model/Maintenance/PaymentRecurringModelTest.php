<?php

namespace Tests\AmazonPaymentServices\Model\Maintenance;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConstants;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Maintenance\PaymentRecurringModel;
use Tests\APSTestCase;

/**
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n
 */
class PaymentRecurringModelTest extends APSTestCase
{
    private PaymentRecurringModel $paymentRecurringModel;

    public function setUp(): void
    {
        parent::setUp();

        try {

            APSMerchant::setMerchantParams($this->merchantConfig);

            $this->paymentRecurringModel = new PaymentRecurringModel();
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }


    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Maintenance\PaymentRecurringModel::getPaymentParameter
     *
     * @return void
     */
    public function testGetPaymentParameter_eciCommand(): void
    {
        try {
            $this->assertEquals(
                APSConstants::PAYMENT_COMMAND_RECURRING,
                $this->paymentRecurringModel->getPaymentParameter('eci', null, true)
            );
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Maintenance\PaymentRecurringModel::getPaymentParameter
     *
     * @return void
     */
    public function testGetPaymentParameter_defaultReturn(): void
    {
        $this->assertTrue(
            in_array(
                'merchant_reference',
                $this->paymentRecurringModel->getRequiredParameters(),
                true
            )
        );
    }
}
