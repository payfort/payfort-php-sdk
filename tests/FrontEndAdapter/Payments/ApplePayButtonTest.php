<?php

namespace Tests\FrontEndAdapter\Payments;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSExceptionCodes;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\FrontEndAdapter\Payments\ApplePayButton;
use Tests\APSTestCase;

/**
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSCore
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\ApplePayInitialization
 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\FrontEndAdapter
 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Payments\ApplePayButton
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\Logger
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\APSLogger
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentDTO
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n
 */
class ApplePayButtonTest extends APSTestCase
{
    private ApplePayButton $applePayButton;

    /**
     * @throws APSException
     */
    public function setUp(): void
    {
        parent::setUp();

        APSMerchant::setMerchantParams($this->merchantConfig);

        $this->applePayButton = new ApplePayButton();
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Payments\ApplePayButton::setPaymentData
     *
     * @return void
     */
    public function testSetPaymentData(): void
    {
        try {
            $applePayButtonPaymentParameters = [
                'amount'        => 39.99,
                'subtotal'      => 39.99,
                'shipping'      => 0,
                'discount'      => 0,
                'tax'           => 0,
                'currency'      => 'USD',
            ];
            $this->assertEquals(
                ApplePayButton::class,
                $this->applePayButton->setPaymentData($applePayButtonPaymentParameters)::class
            );
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Payments\ApplePayButton::setDisplayName
     *
     * @return void
     */
    public function testSetDisplayName(): void
    {
        $this->assertEquals(
            ApplePayButton::class,
            $this->applePayButton->setDisplayName('Test store')::class
        );
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Payments\ApplePayButton::setCountryCode
     *
     * @return void
     */
    public function testSetCountryCode(): void
    {
        $this->assertEquals(
            ApplePayButton::class,
            $this->applePayButton->setCountryCode('UAE')::class
        );
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Payments\ApplePayButton::setCurrencyCode
     *
     * @return void
     */
    public function testSetCurrencyCode(): void
    {
        $this->assertEquals(
            ApplePayButton::class,
            $this->applePayButton->setCurrencyCode('AED')::class
        );
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Payments\ApplePayButton::setSupportedCountries
     *
     * @return void
     */
    public function testSetSupportedCountries(): void
    {
        $this->assertEquals(
            ApplePayButton::class,
            $this->applePayButton->setSupportedCountries([])::class
        );
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Payments\ApplePayButton::setSupportedNetworks
     *
     * @return void
     */
    public function testSetSupportedNetworks(): void
    {
        $this->assertEquals(
            ApplePayButton::class,
            $this->applePayButton->setSupportedNetworks([])::class
        );
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Payments\ApplePayButton::setValidationCallbackUrl
     *
     * @return void
     */
    public function testSetValidationCallbackUrl(): void
    {
        $this->assertEquals(
            ApplePayButton::class,
            $this->applePayButton->setValidationCallbackUrl($this->merchantConfig['Apple_DomainName'])::class
        );
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Payments\ApplePayButton::setCommandCallbackUrl
     *
     * @return void
     */
    public function testSetCommandCallbackUrl(): void
    {
        $this->assertEquals(
            ApplePayButton::class,
            $this->applePayButton->setCommandCallbackUrl($this->merchantConfig['Apple_DomainName'])::class
        );
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Payments\ApplePayButton::getCallbackUrlAddon
     *
     * @return void
     */
    public function testGetCallbackUrlAddon(): void
    {
        $this->assertIsString(
            $this->applePayButton->getCallbackUrlAddon()
        );
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Payments\ApplePayButton::render
     *
     * @return void
     */
    public function testRender_noSdkValidationUrl(): void
    {
        try {
            $this->applePayButton->render();

            $this->fail();
        } catch (APSException $e) {
            $this->assertEquals(
                APSExceptionCodes::APPLE_PAY_VALIDATION_CALLBACK_URL_MISSING,
                $e->getCode()
            );
            $this->assertTrue(true, $e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Payments\ApplePayButton::render
     *
     * @return void
     */
    public function testRender_noSdkCommandUrl(): void
    {
        try {
            $this->applePayButton
                ->setValidationCallbackUrl($this->merchantConfig['Apple_DomainName'])
                ->render();

            $this->fail();
        } catch (APSException $e) {
            $this->assertEquals(
                APSExceptionCodes::APPLE_PAY_COMMAND_CALLBACK_URL_MISSING,
                $e->getCode()
            );
            $this->assertTrue(true, $e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Payments\ApplePayButton::render
     *
     * @return void
     */
    public function testRender_noCurrencyCode(): void
    {
        try {
            $this->applePayButton
                ->setValidationCallbackUrl($this->merchantConfig['Apple_DomainName'])
                ->setCommandCallbackUrl($this->merchantConfig['Apple_DomainName'])
                ->render();

            $this->fail();
        } catch (APSException $e) {
            $this->assertEquals(
                APSExceptionCodes::PAYMENT_DATA_CURRENCY_CODE_MISSING,
                $e->getCode()
            );
            $this->assertTrue(true, $e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Payments\ApplePayButton::render
     *
     * @return void
     */
    public function testRender_noCountryCode(): void
    {
        try {
            $paymentData = $this->normalPaymentParams;
            $paymentData['subtotal'] = 1245.00;
            $paymentData['discount'] = 200;
            $paymentData['shipping'] = 50;
            $paymentData['tax'] = 5;
            $paymentData['currency'] = 'AED';
            $paymentData['country'] = 'AE';

            $this->applePayButton
                ->setValidationCallbackUrl($this->merchantConfig['Apple_DomainName'])
                ->setCommandCallbackUrl($this->merchantConfig['Apple_DomainName'])
                ->setPaymentData($paymentData)
                ->render();

            $this->fail();
        } catch (APSException $e) {
            $this->assertEquals(
                APSExceptionCodes::PAYMENT_DATA_COUNTRY_CODE_MISSING,
                $e->getCode()
            );
            $this->assertTrue(true, $e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Payments\ApplePayButton::render
     *
     * @return void
     */
    public function testRender_scriptGenerated(): void
    {
        try {
            $applePayButtonPaymentParameters = [
                'MerchantIdentifier'    => '3428943028432',
                'amount'         => 39.99,
                'subtotal'      => 39.99,
                'shipping'      => 0,
                'discount'      => 0,
                'tax'           => 0,
                'currency'      => 'USD',
            ];

            $this->assertIsString(
                $this->applePayButton
                    ->setValidationCallbackUrl($this->merchantConfig['Apple_DomainName'])
                    ->setCommandCallbackUrl($this->merchantConfig['Apple_DomainName'])
                    ->setPaymentData($applePayButtonPaymentParameters)
                    ->setCountryCode('UAE')
                    ->render()
            );
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }
}
