<?php

namespace Tests\FrontEndAdapter\Core;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConstants;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSExceptionCodes;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\Logger;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\APSResponse;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCCustomAuthorization;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCCustomPurchase;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCRedirectAuthorization;
use AmazonPaymentServicesSdk\FrontEndAdapter\Core\ResponseHandler;
use Psr\Log\LogLevel;
use Tests\APSTestCase;
use Tests\APSTestLogging;

/**
 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\ResponseHandler
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSCore
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\APSLogger
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\APSResponse
 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\ResponseAdapter
 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\ResponseHandler
 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Response\CCRedirectResponse
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConnector
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\Logger
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCCustomAuthorization
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCCustomPurchase
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCRedirectAuthorization
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCRedirectPurchase
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCStandardAuthorization
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCStandardPurchase
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\InstallmentsCCRedirectPurchase
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\InstallmentsCCStandardPurchase
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter
 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Response\CCCustomResponse
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\InstallmentsCCCustomPurchase
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n
 */
class ResponseHandlerTest extends APSTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        try {
            $this->merchantConfig['debug_mode'] = true;
            APSMerchant::setMerchantParams($this->merchantConfig);
            Logger::setLogger(new APSTestLogging());
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\ResponseHandler::validate
     *
     * @return void
     */
    public function testValidate_noSignature(): void
    {
        try {
            $fakeResponseData = $this->normalPaymentParams;
            $responseHandler = new ResponseHandler(null, $fakeResponseData);
            $responseHandler->validate();

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
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\ResponseHandler::validate
     *
     * @return void
     */
    public function testValidate_badSignature(): void
    {
        try {
            $fakeResponseData = $this->normalPaymentParams;
            $fakeResponseData['signature'] = (new APSSignature())->calculateSignature($fakeResponseData, true, $this->merchantConfig);
            $responseHandler = new ResponseHandler(null, $fakeResponseData);
            $responseHandler->validate();

            $this->fail();
        } catch (APSException $e) {
            $this->assertEquals(
                APSExceptionCodes::APS_RESPONSE_SIGNATURE_FAILED,
                $e->getCode()
            );
            $this->assertTrue(true, $e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\ResponseHandler::validate
     *
     * @return void
     */
    public function testValidate_goodSignature(): void
    {
        try {
            $fakeResponseData = $this->normalPaymentParams;
            $fakeResponseData['signature'] = (new APSSignature())->calculateSignature($fakeResponseData, false, $this->merchantConfig);
            $responseHandler = new ResponseHandler(null, $fakeResponseData);
            $responseHandler->validate();

            $this->assertTrue(true);
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\ResponseHandler::validate
     *
     * @return void
     */
    public function testValidate_debugData(): void
    {
        try {
            $responseHandler = new ResponseHandler(null, null);
            $responseHandler->validate();

            $this->fail();
        } catch (APSException $e) {
            $this->assertEquals(
                APSExceptionCodes::RESPONSE_NO_SIGNATURE,
                $e->getCode()
            );

            $this->assertTrue(
                Logger::getInstance()->isMessageStartInLogData('Received POST:', LogLevel::DEBUG)
            );

            $this->assertTrue(
                Logger::getInstance()->isMessageStartInLogData('Received GET:', LogLevel::DEBUG)
            );
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\ResponseHandler::process
     *
     * @return void
     */
    public function testProcess_noDiscriminator(): void
    {
        try {
            $fakeResponseData = $this->normalPaymentParams;
            $fakeResponseData['signature'] = (new APSSignature())->calculateSignature($fakeResponseData, false, $this->merchantConfig);
            $responseHandler = new ResponseHandler(null, $fakeResponseData);
            $responseHandler
                ->validate()
                ->process('');

            $responseHandler
                ->validate()
                ->process();

            // default discriminator is credit card custom
            $this->assertTrue(true);
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\ResponseHandler::process
     *
     * @return void
     */
    public function testProcess_badDiscriminator(): void
    {
        try {
            $fakeResponseData = $this->normalPaymentParams;
            $fakeResponseData['signature'] = (new APSSignature())->calculateSignature($fakeResponseData, false, $this->merchantConfig);
            $responseHandler = new ResponseHandler(null, $fakeResponseData);
            $responseHandler
                ->validate()
                ->process('just testing');

            $this->fail();
        } catch (APSException $e) {
            $this->assertEquals(
                APSExceptionCodes::APS_PAYMENT_METHOD_NOT_AVAILABLE,
                $e->getCode()
            );
            $this->assertTrue(true, $e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\ResponseHandler::process
     *
     * @return void
     */
    public function testProcess_CCRDiscriminator(): void
    {
        try {
            $fakeResponseData = $this->normalPaymentParams;
            $fakeResponseData['signature'] = (new APSSignature())->calculateSignature($fakeResponseData, false, $this->merchantConfig);
            $responseHandler = new ResponseHandler(null, $fakeResponseData);
            $responseHandler
                ->validate()
                ->process((new CCRedirectAuthorization())->getDiscriminator());

            $this->assertTrue(true);
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\ResponseHandler::onSuccess
     *
     * @return void
     */
    public function testOnSuccess(): void
    {
        try {
            $fakeResponseData = $this->normalPaymentParams;
            $fakeResponseData['signature'] = (new APSSignature())->calculateSignature($fakeResponseData, false, $this->merchantConfig);
            $responseHandler = new ResponseHandler(null, $fakeResponseData);
            $responseHandler
                ->validate()
                ->process((new CCRedirectAuthorization())->getDiscriminator())
                ->onSuccess(function() {})
            ;

            $this->assertTrue(true);
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\ResponseHandler::onError
     *
     * @return void
     */
    public function testOnError(): void
    {
        try {
            $fakeResponseData = $this->normalPaymentParams;
            $fakeResponseData['signature'] = (new APSSignature())->calculateSignature($fakeResponseData, false, $this->merchantConfig);
            $responseHandler = new ResponseHandler(null, $fakeResponseData);
            $responseHandler
                ->validate()
                ->process((new CCRedirectAuthorization())->getDiscriminator())
                ->onError(function() {})
            ;

            $this->assertTrue(true);
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\ResponseHandler::onHtml
     *
     * @return void
     */
    public function testOnHtml(): void
    {
        try {
            $fakeResponseData = $this->normalPaymentParams;
            $fakeResponseData['signature'] = (new APSSignature())->calculateSignature($fakeResponseData, false, $this->merchantConfig);
            $responseHandler = new ResponseHandler(null, $fakeResponseData);
            $responseHandler
                ->validate()
                ->process((new CCRedirectAuthorization())->getDiscriminator())
                ->onHtml(function() {})
            ;

            $this->assertTrue(true);
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\ResponseHandler::handleResponse
     *
     * @return void
     */
    public function testHandleResponse_notValidatedYet(): void
    {
        try {
            $_GET['discriminator'] = (new CCRedirectAuthorization())->getDiscriminator();

            $fakeResponseData = $this->normalPaymentParams;
            $fakeResponseData['signature'] = (new APSSignature())->calculateSignature($fakeResponseData, false, $this->merchantConfig);
            $responseHandler = new ResponseHandler(null, $fakeResponseData);

            $this->assertEquals(
                APSResponse::class,
                $responseHandler
                    ->handleResponse()::class
            );
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }


            /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\ResponseHandler::handleResponse
     *
     * @return void
     */
    public function testHandleResponse_html_defaultHtmlFunctionSet(): void
    {
        try {
            $fakeResponseData = $this->normalPaymentParams;
            $fakeResponseData['signature'] = (new APSSignature())->calculateSignature($fakeResponseData, false, $this->merchantConfig);
            $responseHandler = new ResponseHandler(null, $fakeResponseData);
            $responseHandler
                ->validate()
                ->process((new CCRedirectAuthorization())->getDiscriminator())
            ;

            $htmlString = 'html string';
            $changeResultObjectToString = function() use ($htmlString) {
                $this->resultObject = $htmlString;
            };
            $doChangeResultObjectToString = $changeResultObjectToString->bindTo($responseHandler, ResponseHandler::class);
            $doChangeResultObjectToString();

            $this->assertEquals(
                $htmlString,
                $responseHandler->handleResponse()
            );
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\ResponseHandler::handleResponse
     *
     * @return void
     */
    public function testHandleResponse_html_withFunctionSet(): void
    {
        try {
            $fakeResponseData = $this->normalPaymentParams;
            $fakeResponseData['signature'] = (new APSSignature())->calculateSignature($fakeResponseData, false, $this->merchantConfig);
            $responseHandler = new ResponseHandler(null, $fakeResponseData);
            $responseHandler
                ->validate()
                ->process((new CCRedirectAuthorization())->getDiscriminator())
                ->onHtml(function(string $value) {
                    return true;
                })
            ;

            $htmlString = 'html string';
            $changeResultObjectToString = function() use($htmlString) {
                $this->resultObject = $htmlString;
            };
            $doChangeResultObjectToString = $changeResultObjectToString->bindTo($responseHandler, ResponseHandler::class);
            $doChangeResultObjectToString();

            $this->assertEquals(
                $htmlString,
                $responseHandler->handleResponse()
            );
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\ResponseHandler::handleResponse
     *
     * @return void
     */
    public function testHandleResponse_error_withoutFunctionSet(): void
    {
        try {
            $fakeResponseData = $this->normalPaymentParams;
            $fakeResponseData['status'] = '99999';
            $fakeResponseData['signature'] = (new APSSignature())->calculateSignature($fakeResponseData, false, $this->merchantConfig);
            $responseHandler = new ResponseHandler(null, $fakeResponseData);
            $responseHandler
                ->validate()
                ->process((new CCRedirectAuthorization())->getDiscriminator())
            ;

            $this->assertEquals(
                APSResponse::class,
                ($responseHandler->handleResponse())::class
            );

            $this->assertEquals(
                '',
                ($responseHandler->handleResponse())->getResponseMessage()
            );
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\ResponseHandler::handleResponse
     *
     * @return void
     */
    public function testHandleResponse_error_withFunctionSet(): void
    {
        try {
            $fakeResponseData = $this->normalPaymentParams;
            $fakeResponseData['status'] = '99999';
            $fakeResponseData['signature'] = (new APSSignature())->calculateSignature($fakeResponseData, false, $this->merchantConfig);
            $responseHandler = new ResponseHandler(null, $fakeResponseData);
            $responseHandler
                ->validate()
                ->process((new CCRedirectAuthorization())->getDiscriminator())
                ->onError(function(APSResponse $amazonPaymentServicesResponse) {
                    $amazonPaymentServicesResponse->setResponseMessage('error');
                });
            ;

            $this->assertEquals(
                APSResponse::class,
                ($responseHandler->handleResponse())::class
            );

            $this->assertEquals(
                'error',
                ($responseHandler->handleResponse())->getResponseMessage()
            );
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\ResponseHandler::handleResponse
     *
     * @return void
     */
    public function testHandleResponse_success_withoutFunctionSet(): void
    {
        try {
            $fakeResponseData = $this->normalPaymentParams;
            $fakeResponseData['status'] = '02';
            $fakeResponseData['signature'] = (new APSSignature())->calculateSignature($fakeResponseData, false, $this->merchantConfig);
            $responseHandler = new ResponseHandler(null, $fakeResponseData);
            $responseHandler
                ->validate()
                ->process((new CCRedirectAuthorization())->getDiscriminator())
            ;

            $this->assertEquals(
                APSResponse::class,
                ($responseHandler->handleResponse())::class
            );

            $this->assertEquals(
                '',
                ($responseHandler->handleResponse())->getResponseMessage()
            );
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\ResponseHandler::handleResponse
     *
     * @return void
     */
    public function testHandleResponse_success_withFunctionSet(): void
    {
        try {
            $fakeResponseData = $this->normalPaymentParams;
            $fakeResponseData['status'] = '02';
            $fakeResponseData['signature'] = (new APSSignature())->calculateSignature($fakeResponseData, false, $this->merchantConfig);
            $responseHandler = new ResponseHandler(null, $fakeResponseData);
            $responseHandler
                ->validate()
                ->process((new CCRedirectAuthorization())->getDiscriminator())
                ->onSuccess(function(APSResponse $amazonPaymentServicesResponse) {
                    $amazonPaymentServicesResponse->setResponseMessage('success');
                });
            ;

            $this->assertEquals(
                APSResponse::class,
                ($responseHandler->handleResponse())::class
            );

            $this->assertEquals(
                'success',
                ($responseHandler->handleResponse())->getResponseMessage()
            );
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\ResponseHandler::getResult
     *
     * @return void
     */
    public function testGetResult(): void
    {
        try {
            $fakeResponseData = $this->normalPaymentParams;
            $fakeResponseData['status'] = '02';
            $fakeResponseData['signature'] = (new APSSignature())->calculateSignature($fakeResponseData, false, $this->merchantConfig);
            $responseHandler = new ResponseHandler(null, $fakeResponseData);
            $responseHandler
                ->validate()
                ->process((new CCRedirectAuthorization())->getDiscriminator())
                ->onSuccess(function(APSResponse $amazonPaymentServicesResponse) {
                    $amazonPaymentServicesResponse->setResponseMessage('success');
                });
            ;

            $this->assertEquals(
                APSResponse::class,
                ($responseHandler->getResult())::class
            );
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\ResponseHandler::process
     *
     * @return void
     */
    public function testProcess_discriminatorFallbackOptions(): void
    {
        try {
            $this->merchantConfig['debug_mode'] = true;
            APSMerchant::setMerchantParams($this->merchantConfig);
            Logger::setLogger(new APSTestLogging());

            $responseData = $this->normalPaymentParams;
            $responseData['status'] = '02';
            $responseData['signature'] = (new APSSignature())->calculateSignature($responseData, false, $this->merchantConfig);

            $responseHandler = new ResponseHandler(null, $responseData);
            $responseHandler->process();

            $this->assertTrue(
                Logger::getInstance()->isMessageStartInLogData('Received discriminator: ' . (new CCCustomAuthorization())->getDiscriminator(), LogLevel::INFO)
            );

            Logger::getInstance()->clearLogData();

            $responseData['command'] = APSConstants::PAYMENT_COMMAND_PURCHASE;
            $responseData['signature'] = (new APSSignature())->calculateSignature($responseData, false, $this->merchantConfig);

            $responseHandler = new ResponseHandler(null, $responseData);
            $responseHandler->process();

            $this->assertTrue(
                Logger::getInstance()->isMessageStartInLogData('Received discriminator: ' . (new CCCustomPurchase())->getDiscriminator(), LogLevel::INFO)
            );

        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }
}
