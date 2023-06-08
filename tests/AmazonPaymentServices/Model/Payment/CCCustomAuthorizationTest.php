<?php

namespace Tests\AmazonPaymentServices\Model\Payment;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConstants;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCCustomAuthorization;
use Tests\APSTestCase;

/**
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCCustomAuthorization
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n
 */
class CCCustomAuthorizationTest extends APSTestCase
{
    private CCCustomAuthorization $creditCardCustomAuthorization;

    public function setUp(): void
    {
        parent::setUp();

        try {
            APSMerchant::setMerchantParams($this->merchantConfig);

            $this->creditCardCustomAuthorization = new CCCustomAuthorization();
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCCustomAuthorization::getPaymentParameter
     * @return void
     */
    public function testGetPaymentParameter_command(): void
    {
        try {
            $this->assertEquals(
                APSConstants::PAYMENT_COMMAND_AUTHORIZATION,
                $this->creditCardCustomAuthorization->getPaymentParameter('command', null, true)
            );
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCCustomAuthorization::getPaymentParameter
     * @return void
     */
    public function testGetPaymentParameter_installments(): void
    {
        try {
            $this->assertEquals(
                APSConstants::INSTALLMENTS_TYPE_HOSTED,
                $this->creditCardCustomAuthorization->getPaymentParameter('installments', 'HOSTED', true)
            );
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCCustomAuthorization::getPaymentParameter
     * @return void
     */
    public function testGetPaymentParameter_defaultReturn(): void
    {
        $this->assertTrue(
            in_array(
                'merchant_reference',
                $this->creditCardCustomAuthorization->getRequiredParameters(),
                true
            )
        );
    }

}
