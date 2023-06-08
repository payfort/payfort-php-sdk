<?php

namespace AmazonPaymentServicesSdk\AmazonPaymentServices\Core;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\Logger;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class APSConnector
{
    private Client $client;

    public function __construct()
    {
        $this->client = $this->getClient();
    }

    /**
     * Return the Guzzle Client
     *
     * @return Client
     */
    private function getClient(): Client
    {
        // create the client object and call
        return new Client();
    }

    /**
     * Handles the actual api call to the APS server via GuzzleHttp
     *
     * @param string $url
     * @param array $paymentParams
     * @param array|null $paymentOptions
     * @param bool $dontDecode
     *
     * @return array|string
     *
     * @throws GuzzleException
     */
    public function callToAps(
        string $url,
        array $paymentParams,
        array $paymentOptions = null,
        bool $dontDecode = false
    ): array|string
    {
        if (null === $paymentOptions) {
            $paymentOptions = [
                'headers'           => [
                    'Content-Type'  => 'application/json',
                    'charset'       => 'UTF-8',
                ],
                'user_Agent'        => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:20.0) Gecko/20100101 Firefox/20.0',
                'blocking'          => true,
                'sslverify'         => true,
                'httpversion'       => '1.0',
                'curl'              => [
                    CURLOPT_FAILONERROR     => 1,
                    CURLOPT_ENCODING        => "compress, gzip",
                    CURLOPT_RETURNTRANSFER  => true,
                    CURLOPT_FOLLOWLOCATION  => false,
                    CURLOPT_CONNECTTIMEOUT  => 60,
                    CURLOPT_PROTOCOLS       => CURLPROTO_HTTPS,
                ],
            ];
        }

        if (!isset($paymentOptions['body'])) {
            $paymentOptions['body'] = json_encode($paymentParams);
        }

        if (APSMerchant::isDebugMode()) {
            Logger::getInstance()->debug('Parameters before being sent: ' . $paymentOptions['body']);
        }

        $response = $this->client->post($url, $paymentOptions);
        $responseString = $response->getBody()->getContents();

        if (APSMerchant::isDebugMode()) {
            Logger::getInstance()->debug('Response string: ' . $responseString);
            Logger::getInstance()->debug('Response status code: ' . $response->getStatusCode());
        }

        if ($dontDecode) {
            return $responseString;
        }

        return json_decode(
            $responseString,
            true
        );
    }
}
