<?php

namespace Tests\AmazonPaymentServices\Model\Payment;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCCustomTokenization;
use Tests\APSTestCase;

/**
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCCustomTokenization
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n
 */
class CCCustomTokenizationTest extends APSTestCase
{
    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCCustomTokenization::getPaymentParameter
     *
     * @return void
     */
    public function testGetPaymentParameter(): void
    {
        try {
            APSMerchant::setMerchantParams($this->merchantConfig);

            $this->assertNull(
                (new CCCustomTokenization())->getPaymentParameter('card_number', null, true)
            );
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }
}
