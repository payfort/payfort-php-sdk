<?php

namespace AmazonPaymentServicesSdk\FrontEndAdapter\Response;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConstants;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\APSResponse;
use AmazonPaymentServicesSdk\FrontEndAdapter\Core\ResponseAdapter;

class TrustedChannelResponse extends ResponseAdapter
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
            return $this->paymentResponseModel;
        }

        // redirect / show modal for Secure 3DS verification (if this is needed)
        $secure3dsString = $this->handleSecure3ds();
        if (null !== $secure3dsString) {
            return $secure3dsString;
        }

        return $this->paymentResponseModel;
    }

    public function getDiscriminator(): string
    {
        return '';
    }
}
