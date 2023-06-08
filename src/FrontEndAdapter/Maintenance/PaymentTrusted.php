<?php

namespace AmazonPaymentServicesSdk\FrontEndAdapter\Maintenance;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\APSResponse;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\PaymentTrustedModel;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentDTO;
use AmazonPaymentServicesSdk\FrontEndAdapter\Core\MaintenanceAdapter;
use AmazonPaymentServicesSdk\FrontEndAdapter\Response\TrustedChannelResponse;

class PaymentTrusted extends MaintenanceAdapter
{
    /**
     * @param array $paymentData
     *
     * @return APSResponse|string|null
     *
     * @throws APSException
     */
    public function paymentTrusted(array $paymentData): array|string|null
    {
        $callResult = $this->processTrustedPayment(
            new PaymentDTO($paymentData, new PaymentTrustedModel())
        );

        // initiate the response mechanism
        $responseHandler = new TrustedChannelResponse($callResult, [], true);

        // process the result
        $processResult = $responseHandler->process();

        // 3ds secure?
        if (is_string($processResult)) {
            // at this point we need to approve the transaction with 3d secure
            // we have the iframe modal in the string, BUT if the 3ds_modal setting is false
            // we need to redirect the user in the browser, instead of the modal

            // get the merchant parameters
            $merchantParams = APSMerchant::getMerchantParams();

            if (($merchantParams['3ds_modal'] ?? false)) {
                return $processResult;
            }

            // retrieve the response model (we have iframe string in the response now)
            $responseModel = $responseHandler->getPaymentResponseModel();
            if (empty($responseModel->get3dsUrl())) {
                return $processResult;
            }

            // we simply redirect the user to the 3ds verification page
            header('Location: ' . $responseModel->get3dsUrl());
            exit;
        }

        return $processResult->getResponseData();
    }
}