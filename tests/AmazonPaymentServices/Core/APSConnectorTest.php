<?php

namespace Tests\AmazonPaymentServices\Core;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConnector;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\Logger;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use Psr\Log\LogLevel;
use Tests\APSTestCase;
use Tests\APSTestLogging;

/**
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConnector
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\Logger
 */
class APSConnectorTest extends APSTestCase
{
    private APSConnector $amazonPaymentServicesConnector;

    public function setUp(): void
    {
        parent::setUp();

        $this->amazonPaymentServicesConnector = new APSConnector();
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConnector::callToAps
     *
     * @return void
     */
    public function testCallToAps_array(): void
    {
        try {
            $this->merchantConfig['debug_mode'] = true;
            APSMerchant::setMerchantParams($this->merchantConfig);
            Logger::setLogger(new APSTestLogging());

            $returnData = [
               'test'   => 'test',
               'test2'  => 'test2',
            ];
            $returnString = json_encode($returnData);
            $guzzleHttpResponse = new Response(200, [], $returnString);

            $mockClient = $this->createMock(Client::class);
            $mockClient
                ->method('post')
                ->willReturn($guzzleHttpResponse);

            $replaceClientInConnector = function() use($mockClient) {
                $this->client = $mockClient;
            };
            $doReplaceClientInConnector = $replaceClientInConnector->bindTo($this->amazonPaymentServicesConnector, APSConnector::class);
            $doReplaceClientInConnector();

            $this->assertEquals(
                $returnData,
                $this->amazonPaymentServicesConnector->callToAps('http://127.0.0.1/test', [])
            );

            $this->assertTrue(
                Logger::getInstance()->isMessageStartInLogData('Parameters before being sent', LogLevel::DEBUG)
            );

            $this->assertTrue(
                Logger::getInstance()->isMessageStartInLogData('Response string', LogLevel::DEBUG)
            );

            $this->assertTrue(
                Logger::getInstance()->isMessageStartInLogData('Response status code', LogLevel::DEBUG)
            );
        } catch (APSException|GuzzleException|\PHPUnit\Framework\MockObject\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConnector::callToAps
     *
     * @return void
     */
    public function testCallToAps_string(): void
    {
        try {
            $returnData = [
               'test'   => 'test',
               'test2'  => 'test2',
            ];
            $returnString = json_encode($returnData);
            $guzzleHttpResponse = new Response(200, [], $returnString);

            $mockClient = $this->createMock(Client::class);
            $mockClient
                ->method('post')
                ->willReturn($guzzleHttpResponse);

            $replaceClientInConnector = function() use($mockClient) {
                $this->client = $mockClient;
            };
            $doReplaceClientInConnector = $replaceClientInConnector->bindTo($this->amazonPaymentServicesConnector, APSConnector::class);
            $doReplaceClientInConnector();

            $this->assertEquals(
                $returnString,
                $this->amazonPaymentServicesConnector->callToAps('http://127.0.0.1/test', [], [], true)
            );
        } catch (GuzzleException|\PHPUnit\Framework\MockObject\Exception $e) {
            $this->fail($e->getMessage());
        }
    }
}
