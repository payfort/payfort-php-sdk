<?php

namespace Tests\AmazonPaymentServices\Model\Payment;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConstants;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCStandardAuthorization;
use Tests\APSTestCase;

/**
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCStandardAuthorization
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n
 */
class CCStandardAuthorizationTest extends APSTestCase
{
    private CCStandardAuthorization $creditCardStandardAuthorization;

    public function setUp(): void
    {
        parent::setUp();

        try {
            APSMerchant::setMerchantParams($this->merchantConfig);

            // instantiate a child class
            $this->creditCardStandardAuthorization = new CCStandardAuthorization();
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCStandardAuthorization::getPaymentParameter
     *
     * @return void
     */
    public function testGetPaymentParameter_command(): void
    {
        try {
            $this->assertEquals(
                APSConstants::PAYMENT_COMMAND_AUTHORIZATION,
                $this->creditCardStandardAuthorization->getPaymentParameter('command', null, true)
            );
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCStandardAuthorization::getPaymentParameter
     *
     * @return void
     */
    public function testGetPaymentParameter_installments(): void
    {
        try {
            $this->assertEquals(
                APSConstants::INSTALLMENTS_TYPE_PURCHASE,
                $this->creditCardStandardAuthorization->getPaymentParameter('installments', 'YES', true)
            );
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCStandardAuthorization::getPaymentParameter
     *
     * @return void
     */
    public function testGetPaymentParameter_defaultReturn(): void
    {
        $this->assertTrue(
            in_array(
                'merchant_reference',
                $this->creditCardStandardAuthorization->getRequiredParameters(),
                true
            )
        );
    }

}
