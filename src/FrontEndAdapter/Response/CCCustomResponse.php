<?php

namespace AmazonPaymentServicesSdk\FrontEndAdapter\Response;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConstants;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\APSResponse;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCCustomAuthorization;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCCustomPurchase;
use AmazonPaymentServicesSdk\FrontEndAdapter\Core\ResponseAdapter;

class CCCustomResponse extends ResponseAdapter
{
    public function getSuccessStatusCodes(): array
    {
        return [
            '18', '02', '14', '44', '20',
        ];
    }

    public function getPaymentOptions(): array
    {
        return [
            'payment_type'      => APSConstants::PAYMENT_TYPE_CREDIT_CARD,
            'integration_type'  => APSConstants::INTEGRATION_TYPE_CUSTOM,
        ];
    }

    /**
     * @return string|null|APSResponse
     *
     * @throws APSException
     */
    public function process(): null|string|APSResponse
    {
        if (!$this->paymentResponseModel->isSuccess()) {
            // if the transaction failed, don't do further actions
            return null;
        }

        // ask for the authorization (if this is a tokenization response)
        $authorizationResult = $this->handleAuthorization($this->isPurchase
            ? new CCCustomPurchase() : new CCCustomAuthorization());
        if (null !== $authorizationResult) {
            $this->setResponseData($authorizationResult);
            return $this->process();
        }

        // redirect / show modal for Secure 3DS verification (if this is needed)
        return $this->handleSecure3ds();
    }

    /**
     * @return string
     */
    public function getDiscriminator(): string
    {
        return $this->isPurchase ?
            (new CCCustomPurchase())->getDiscriminator() :
            (new CCCustomAuthorization())->getDiscriminator();
    }
}
