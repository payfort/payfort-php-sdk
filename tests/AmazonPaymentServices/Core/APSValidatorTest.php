<?php

namespace Tests\AmazonPaymentServices\Core;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConstants;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSExceptionCodes;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\Logger;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use Psr\Log\LogLevel;
use Tests\APSTestCase;
use Tests\APSTestLogging;

/**
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\APSLogger
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\Logger
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n
 */
class APSValidatorTest extends APSTestCase
{
    private ?APSValidator $amazonPaymentServicesValidator;

    public function setUp(): void
    {
        parent::setUp();

        $this->amazonPaymentServicesValidator = new \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator();
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator::validateMerchantParams
     *
     * @return void
     */
    public function testValidateMerchantParams_basicMerchantParams(): void
    {
        try {
            $this->amazonPaymentServicesValidator->validateMerchantParams(
                $this->merchantConfig,
                []
            );

            $this->assertTrue(true);
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator::validateMerchantParams
     *
     * @return void
     */
    public function testValidateMerchantParams_validateParameterInArray_exception(): void
    {
        try {
            $merchantConfig = $this->merchantConfig;
            unset($merchantConfig['merchant_identifier']);

            $this->amazonPaymentServicesValidator->validateMerchantParams(
                $merchantConfig,
                []
            );

            $this->fail('no exception thrown');
        } catch (APSException $e) {
            $this->assertEquals(
                APSExceptionCodes::MERCHANT_CONFIG_MERCHANT_ID_MISSING,
                $e->getCode()
            );
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator::validateMerchantParams
     *
     * @return void
     */
    public function testValidateMerchantParams_missingSandBoxMode(): void
    {
        try {
            $merchantConfig = $this->merchantConfig;
            unset($merchantConfig['sandbox_mode']);

            $this->amazonPaymentServicesValidator->validateMerchantParams(
                $merchantConfig,
                []
            );

            $this->fail('SandBox mode not checked!');
        } catch (APSException $e) {
            $this->assertEquals(
                APSExceptionCodes::MERCHANT_CONFIG_SANDBOX_NOT_SPECIFIED,
                $e->getCode()
            );
            $this->assertTrue(true, $e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator::validateMerchantParams
     *
     * @return void
     */
    public function testValidateMerchantParams_badSandBoxMode(): void
    {
        try {
            $merchantConfig = $this->merchantConfig;
            $merchantConfig['sandbox_mode'] = 'djksjd';

            $this->amazonPaymentServicesValidator->validateMerchantParams(
                $merchantConfig,
                []
            );

            $this->fail('SandBox mode not correct and still validating!');
        } catch (APSException $e) {
            $this->assertEquals(
                APSExceptionCodes::MERCHANT_CONFIG_SANDBOX_NOT_SPECIFIED,
                $e->getCode()
            );
            $this->assertTrue(true, $e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator::validateMerchantParams
     */
    public function testValidateMerchantParams_missingAppleParams(): void
    {
        try {
            $noAppleParams = $this->merchantConfig;
            foreach ($noAppleParams as $keyName => $keyValue) {
                if (str_starts_with($keyName, 'Apple_')) {
                    unset($noAppleParams[$keyName]);
                }
            }
            $this->amazonPaymentServicesValidator->validateMerchantParams(
                $noAppleParams,
                [
                    'payment_type' => APSConstants::PAYMENT_TYPE_APPLE_PAY,
                ]
            );

            $this->fail('No apple params and still validated!');
        } catch (APSException $e) {
            $this->assertIsInt($e->getCode());
            $this->assertTrue(true, $e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator::validateMerchantParams
     */
    public function testValidateMerchantParams_withAppleParams(): void
    {
        try {
            $this->amazonPaymentServicesValidator->validateMerchantParams(
                $this->merchantConfig,
                [
                    'payment_type' => APSConstants::PAYMENT_TYPE_APPLE_PAY,
                ]
            );

            $this->assertTrue(true);
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator::isResponseValid
     */
    public function testIsResponseValid_noSignature(): void
    {
        try {
            // build up a fake response
            $responseData = $this->normalPaymentParams;

            $this->amazonPaymentServicesValidator->isResponseValid(
                $this->merchantConfig,
                $responseData
            );

            $this->fail();
        } catch (APSException $e) {
            $this->assertEquals(
                APSExceptionCodes::RESPONSE_NO_SIGNATURE,
                $e->getCode()
            );
            $this->assertTrue(true, $e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator::isResponseValid
     */
    public function testIsResponseValid_badSignature(): void
    {
        // build up a fake response
        $responseData = $this->normalPaymentParams;
        $responseData['signature'] = (new \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature())->calculateSignature($responseData, true, $this->merchantConfig);

        $this->assertFalse(
            $this->amazonPaymentServicesValidator->isResponseValid(
                $this->merchantConfig,
                $responseData
            )
        );
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator::isResponseValid
     */
    public function testIsResponseValid_goodSignature(): void
    {
        // build up a fake response
        $responseData = $this->normalPaymentParams;
        $responseData['signature'] = (new \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature())->calculateSignature($responseData, false, $this->merchantConfig);

        $this->assertTrue(
            $this->amazonPaymentServicesValidator->isResponseValid(
                $this->merchantConfig,
                $responseData
            )
        );
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator::isResponseValid
     */
    public function testIsResponseValid_goodSignature_withDiscriminator(): void
    {
        // build up a fake response
        $responseData = $this->normalPaymentParams;
        $responseData['signature'] = (new \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature())->calculateSignature($responseData, false, $this->merchantConfig);
        $responseData['discriminator'] = 'test-discriminator';

        $this->assertTrue(
            $this->amazonPaymentServicesValidator->isResponseValid(
                $this->merchantConfig,
                $responseData
            )
        );
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator::validateAppleUrl
     */
    public function testValidateAppleUrl_emptyUrl(): void
    {
        try {
            $this->amazonPaymentServicesValidator->validateAppleUrl('');

            $this->fail();
        } catch (APSException $e) {
            $this->assertEquals(
                APSExceptionCodes::APPLE_PAY_URL_MISSING,
                $e->getCode()
            );
            $this->assertTrue(true, $e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator::validateAppleUrl
     */
    public function testValidateAppleUrl_httpUrl(): void
    {
        try {
            $this->amazonPaymentServicesValidator->validateAppleUrl('http://pay.apple.com/processPayment');

            $this->fail();
        } catch (APSException $e) {
            $this->assertEquals(
                APSExceptionCodes::APPLE_PAY_URL_INVALID,
                $e->getCode()
            );
            $this->assertTrue(true, $e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator::validateAppleUrl
     */
    public function testValidateAppleUrl_httpsNonAppleUrl(): void
    {
        try {
            $this->amazonPaymentServicesValidator->validateAppleUrl('https://pay.apple5.com/processPayment');

            $this->fail();
        } catch (APSException $e) {
            $this->assertEquals(
                APSExceptionCodes::APPLE_PAY_URL_INVALID,
                $e->getCode()
            );
            $this->assertTrue(true, $e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator::validateAppleUrl
     */
    public function testValidateAppleUrl_httpsAppleUrl(): void
    {
        $this->merchantConfig['debug_mode'] = true;
        APSMerchant::setMerchantParams($this->merchantConfig);
        Logger::setLogger(new APSTestLogging());

        try {
            $this->amazonPaymentServicesValidator->validateAppleUrl('https://pay.apple.com/processPayment');

            $this->assertTrue(true);

            $this->assertTrue(
                Logger::getInstance()->isMessageStartInLogData('Apple Pay session URL', LogLevel::DEBUG)
            );

        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator::validateAppleUrl
     */
    public function testValidateAppleUrl_httpsAppleUrl_invalid(): void
    {
        try {
            $this->amazonPaymentServicesValidator->validateAppleUrl('https://pay.apple.com/processPayment @ $#@$#@');

            $this->fail();
        } catch (APSException $e) {
            $this->assertEquals(
                APSExceptionCodes::APPLE_PAY_URL_INVALID,
                $e->getCode()
            );
            $this->assertTrue(true, $e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator::validateApplePaymentParams
     *
     * @return void
     */
    public function testValidateApplePaymentParams_missingAmount(): void
    {
        try {
            $paymentData = [];

            $this->amazonPaymentServicesValidator->validateApplePaymentParams($paymentData);

            $this->fail();
        } catch (APSException $e) {
            $this->assertEquals(
                APSExceptionCodes::PAYMENT_DATA_AMOUNT_MISSING,
                $e->getCode()
            );
            $this->assertTrue(true, $e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator::validateApplePaymentParams
     *
     * @return void
     */
    public function testValidateApplePaymentParams_missingSubtotal(): void
    {
        try {
            $paymentData = [
                'amount'    => 29.99,
            ];

            $this->amazonPaymentServicesValidator->validateApplePaymentParams($paymentData);

            $this->fail();
        } catch (APSException $e) {
            $this->assertEquals(
                APSExceptionCodes::PAYMENT_DATA_SUBTOTAL_MISSING,
                $e->getCode()
            );
            $this->assertTrue(true, $e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator::validateApplePaymentParams
     *
     * @return void
     */
    public function testValidateApplePaymentParams_missingShipping(): void
    {
        try {
            $paymentData = [
                'amount'    => 29.99,
                'subtotal'  => 20.99,
            ];

            $this->amazonPaymentServicesValidator->validateApplePaymentParams($paymentData);

            $this->fail();
        } catch (APSException $e) {
            $this->assertEquals(
                APSExceptionCodes::PAYMENT_DATA_SHIPPING_MISSING,
                $e->getCode()
            );
            $this->assertTrue(true, $e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator::validateApplePaymentParams
     *
     * @return void
     */
    public function testValidateApplePaymentParams_missingDiscount(): void
    {
        try {
            $paymentData = [
                'amount'    => 29.99,
                'subtotal'  => 20.99,
                'shipping'  => 5,
            ];

            $this->amazonPaymentServicesValidator->validateApplePaymentParams($paymentData);

            $this->fail();
        } catch (APSException $e) {
            $this->assertTrue(true);
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator::validateApplePaymentParams
     *
     * @return void
     */
    public function testValidateApplePaymentParams_missingTax(): void
    {
        try {
            $paymentData = [
                'amount'    => 29.99,
                'subtotal'  => 20.99,
                'shipping'  => 5,
                'discount'  => 2,
            ];

            $this->amazonPaymentServicesValidator->validateApplePaymentParams($paymentData);

            $this->fail();
        } catch (APSException $e) {
            $this->assertEquals(
                APSExceptionCodes::PAYMENT_DATA_TAX_MISSING,
                $e->getCode()
            );
            $this->assertTrue(true, $e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator::validateApplePaymentParams
     *
     * @return void
     */
    public function testValidateApplePaymentParams_good(): void
    {
        try {
            $paymentData = [
                'amount'    => 29.99,
                'subtotal'  => 20.99,
                'shipping'  => 5,
                'discount'  => 2,
                'tax'       => 2,
            ];

            $this->amazonPaymentServicesValidator->validateApplePaymentParams($paymentData);

            $this->assertTrue(true);
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }
}
