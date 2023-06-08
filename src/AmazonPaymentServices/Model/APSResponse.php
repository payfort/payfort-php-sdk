<?php

namespace AmazonPaymentServicesSdk\AmazonPaymentServices\Model;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConstants;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCStandardAuthorization;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCStandardPurchase;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\InstallmentsCCStandardPurchase;

class APSResponse
{
    private array $responseData = [];
    private array $successStatusCodes = [];

    private array $getParameters = [
        'merchant_reference',
        'status',
        'response_message',
        'amount',
        'currency',
        '3ds_url',
    ];

    public string $discriminator;

    private string $responseMessage = '';

    public function __construct(array $responseData, array $successStatusCodes, string $discriminator = '')
    {
        $this->responseData = $responseData;
        $this->successStatusCodes = $successStatusCodes;

        $this->discriminator = $discriminator;
    }

    public function isSuccess(): bool
    {
        return in_array($this->responseData['status'] ?? '', $this->successStatusCodes, true);
    }

    public function getDiscriminator(): string
    {
        return $this->discriminator;
    }

    public function requires3dsValidation(): bool
    {
        return !empty($this->responseData['3ds_url'] ?? '');
    }

    public function get3dsUrl(): ?string
    {
        return $this->responseData['3ds_url'] ?? null;
    }

    public function isTokenization(): bool
    {
        return
            APSConstants::PAYMENT_COMMAND_TOKENIZATION
                === ($this->responseData['service_command'] ?? null);
    }

    public function getTokenName(): ?string
    {
        return $this->responseData['token_name'] ?? null;
    }


    public function getErrorMessage(): string
    {
        return $this->responseData['response_message'] ?? '-';
    }

    public function getResponseData(): array
    {
        return $this->responseData;
    }

    public function setResponseMessage(string $message): void
    {
        $this->responseMessage = $message;
    }

    public function getResponseMessage(): string
    {
        return $this->responseMessage;
    }

    public function getResponseStatus(): string
    {
        return $this->responseData['status'] ?? '-';
    }

    public function isStandardImplementation(): bool
    {
        return $this->isSuccess()
            && in_array( $this->getDiscriminator(), [
                (new CCStandardAuthorization())->getDiscriminator(),
                (new CCStandardPurchase())->getDiscriminator(),
                (new InstallmentsCCStandardPurchase())->getDiscriminator(),
            ], true);
    }

    /**
     * Get the redirect params in a URL ready form
     *
     * @return string
     */
    public function getRedirectParams(): string
    {
        $redirectParamsString = '';

        foreach ($this->getSafeToShowParams() as $parameter => $value) {
            if ($parameter === 'amount') {
                $value = PaymentDTO::convertIntegerToAmount($value, $this->responseData['currency'] ?? '');
            }
            $redirectParamsString .= urlencode($parameter) . '=' . urlencode($value) . '&';
        }

        return $redirectParamsString;
    }

    private function getSafeToShowParams(): array
    {
        $safeArray = [];

        foreach ($this->responseData as $parameter => $value) {
            if (in_array($parameter, $this->getParameters, true)) {
                $safeArray[$parameter] = $value;
            }
        }

        return $safeArray;
    }
}
