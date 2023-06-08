<?php

namespace Tests\FrontEndAdapter\Payments;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\FrontEndAdapter\Payments\CCRedirect;
use Tests\APSTestCase;

/**
 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Payments\CCRedirect
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSCore
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\APSLogger
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCRedirectAuthorization
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter
 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\FrontEndAdapter
 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Payments\CCRedirect
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConnector
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\Logger
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentDTO
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n
 */
class CCRedirectTest extends APSTestCase
{
    private CCRedirect $creditCardRedirect;

    public function setUp(): void
    {
        parent::setUp();

        try {
            APSMerchant::setMerchantParams($this->merchantConfig);
            $this->creditCardRedirect = new CCRedirect();
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Payments\CCRedirect::render
     *
     * @return void
     */
    public function testRender(): void
    {
        try {
            $this->creditCardRedirect->setPaymentData($this->normalPaymentParams);
            $this->creditCardRedirect->setCallbackUrl('https://test.com');

            $this->assertIsString($this->creditCardRedirect->render([]));
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Payments\CCRedirect::render
     *
     * @return void
     */
    public function testRender_edgeCases(): void
    {
        try {
            $paymentParameters = $this->normalPaymentParams;
            $paymentParameters['random_parameter'] = [
                'test'  => 'test1',
            ];

            $this->creditCardRedirect->setPaymentData($paymentParameters);

            $this->assertNull(
                $this->creditCardRedirect->getCallbackUrl()
            );

            $this->assertEquals(
                CCRedirect::class,
                $this->creditCardRedirect->usePurchaseCommand()::class
            );

            $this->creditCardRedirect->setCallbackUrl('https://test.com');

            $this->assertIsString(
                $this->creditCardRedirect->render([])
            );
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Payments\CCRedirect::render
     *
     * @return void
     */
    public function testRender_purchase(): void
    {
        try {
            $this->creditCardRedirect->usePurchaseCommand();

            $this->creditCardRedirect->setPaymentData($this->normalPaymentParams);
            $this->creditCardRedirect->setCallbackUrl('https://test.com');

            $this->assertIsString($this->creditCardRedirect->render([]));
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }
}
