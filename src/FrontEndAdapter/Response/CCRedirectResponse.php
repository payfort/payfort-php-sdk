<?php

namespace AmazonPaymentServicesSdk\FrontEndAdapter\Response;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConstants;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\APSResponse;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCRedirectAuthorization;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCRedirectPurchase;
use AmazonPaymentServicesSdk\FrontEndAdapter\Core\ResponseAdapter;

class CCRedirectResponse extends ResponseAdapter
{
    public function getSuccessStatusCodes(): array
    {
        return [
            '02', '14', '44',
        ];
    }

    public function getPaymentOptions(): array
    {
        return [
            'payment_type'      => APSConstants::PAYMENT_TYPE_CREDIT_CARD,
            'integration_type'  => APSConstants::INTEGRATION_TYPE_REDIRECT,
        ];
    }

    /**
     * At Credit Card Redirect, we have just one action:
     * - authorization
     * If we are at the process method, authorization was already made.
     * We don't have to do anything here
     *
     * @return string|null|APSResponse
     */
    public function process(): null|string|APSResponse
    {
        if (!$this->paymentResponseModel->isSuccess()) {
            // if the transaction failed, don't do further actions
            return null;
        }

        // for this method, we don't need to add more
        return $this->paymentResponseModel;
    }


    /**
     * @return string
     */
    public function getDiscriminator(): string
    {
        return $this->isPurchase
            ? (new CCRedirectPurchase())->getDiscriminator()
            : (new CCRedirectAuthorization())->getDiscriminator();
    }
}