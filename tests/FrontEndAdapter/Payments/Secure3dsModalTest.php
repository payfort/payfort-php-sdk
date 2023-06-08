<?php

namespace Tests\FrontEndAdapter\Payments;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\FrontEndAdapter\Payments\Secure3dsModal;
use Tests\APSTestCase;

/**
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSCore
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant
 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\FrontEndAdapter
 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Payments\Secure3dsModal
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n
 */
class Secure3dsModalTest extends APSTestCase
{
    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Payments\Secure3dsModal::render
     *
     * @return void
     */
    public function testRender(): void
    {
        try {
            APSMerchant::setMerchantParams($this->merchantConfig);

            $secure3ds = new Secure3dsModal();

            $this->assertNull(
                $secure3ds->getCallbackUrl()
            );

            $this->assertEquals(
                '',
                $secure3ds->getCallbackUrlAddon()
            );

            $this->assertEquals(
                Secure3dsModal::class,
                $secure3ds->usePurchaseCommand()::class
            );

            $this->assertIsString(
                $secure3ds->render([])
            );
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }
}
