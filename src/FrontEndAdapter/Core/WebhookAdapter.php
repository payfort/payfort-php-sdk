<?php

namespace AmazonPaymentServicesSdk\FrontEndAdapter\Core;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSCore;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSExceptionCodes;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\Logger;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\APSResponse;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter;

class WebhookAdapter
{
    /**
     * Get parameters sent to webhook, process them,
     * validate and return them
     *
     * @param string|null $webhookParamsString
     *
     * @return APSResponse
     *
     * @throws APSException
     */
    public static function getWebhookData(string $webhookParamsString = null): APSResponse
    {
        Logger::getInstance()->info('Webhook accessed');

        if (null === $webhookParamsString) {
            $webhookParamsString = file_get_contents('php://input');
        }

        if (APSMerchant::isDebugMode()) {
            Logger::getInstance()->debug( 'Webhook params string: ' . $webhookParamsString );
        }

        if (empty($webhookParamsString)) {
            throw new APSException(
                APSI18n::getText('webhook_parameters_empty'),
                APSExceptionCodes::WEBHOOK_PARAMETERS_EMPTY
            );
        }

        $webhookParamsArray = json_decode(
            filter_var($webhookParamsString, FILTER_UNSAFE_RAW, FILTER_FLAG_NO_ENCODE_QUOTES),
            true
        );
        if (!is_array($webhookParamsArray)) {
            throw new APSException(
                APSI18n::getText('webhook_json_invalid'),
                APSExceptionCodes::WEBHOOK_JSON_INVALID
            );
        }

        if (APSMerchant::isDebugMode()) {
            Logger::getInstance()->debug( 'Webhook params: ' . json_encode($webhookParamsArray, true) );
        }
        Logger::getInstance()->info(
            'Webhook params for merchant_reference: ' . ($webhookParamsArray['merchant_reference'] ?? '-')
        );

        $apsCore = new APSCore();

        if ($apsCore->isResponseValid($webhookParamsArray)) {
            // the response from the webhook is valid!
            Logger::getInstance()->info( 'Webhook signature is VALID!' );

            return new APSResponse($webhookParamsArray,
                PaymentTypeAdapter::getAllResponseSuccessStatusCodes());
        }

        throw new APSException(
            APSI18n::getText('webhook_signature_invalid'),
            APSExceptionCodes::WEBHOOK_SIGNATURE_INVALID
        );
    }
}
