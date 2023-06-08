<?php

namespace AmazonPaymentServicesSdk\FrontEndAdapter\Core;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConstants;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSCore;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSExceptionCodes;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\Logger;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\APSResponse;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentDTO;
use AmazonPaymentServicesSdk\FrontEndAdapter\Payments\Secure3dsModal;

abstract class ResponseAdapter implements ResponseAdapterInterface
{
    public APSResponse $paymentResponseModel;
    protected array $merchantParams;
    protected array $responseData;
    protected ?array $authorizationPaymentData = null;

    protected bool $isPurchase = false;

    protected APSCore $amazonPaymentServicesCore;

    abstract public function getSuccessStatusCodes(): array;
    abstract public function getPaymentOptions(): array;

    abstract public function getDiscriminator(): string;

    /**
     * @throws APSException
     */
    public function __construct(array $responseData, array $authorizationPaymentData = null, bool $isPurchase = false)
    {
        $this->isPurchase = $isPurchase;

        $this->setResponseData($responseData);
        if (null !== $authorizationPaymentData) {
            $this->authorizationPaymentData = $authorizationPaymentData;
        }

        $this->amazonPaymentServicesCore = new APSCore();

        $this->merchantParams = APSMerchant::getMerchantParams();
    }

    protected function setResponseData(array $responseData): void
    {
        $this->responseData = $responseData;
        $this->paymentResponseModel = new APSResponse(
            $this->responseData,
            $this->getSuccessStatusCodes(),
	        $this->getDiscriminator()
        );
    }


    /**
     * @param PaymentTypeAdapter $paymentTypeAdapter
     * @return array|null
     *
     * @throws APSException
     */
    protected function handleAuthorization(PaymentTypeAdapter $paymentTypeAdapter): null|array
    {
        Logger::getInstance()->info('Authorization: Check');

        // if this is not the TOKENIZATION response, don't do anything
        if (!$this->paymentResponseModel->isTokenization()) {
            Logger::getInstance()->info('Authorization: not the case');
            return null;
        }

        // So, this is tokenization, we need authorization
        Logger::getInstance()->info('Authorization: preparing authorization call');

        // check if payment data is set
        if (null === $this->authorizationPaymentData) {
            throw new APSException(
                APSI18n::getText('payment_data_config_missing'),
                APSExceptionCodes::PAYMENT_DATA_CONFIG_MISSING,
            );
        }

        $tokenName = $this->paymentResponseModel->getTokenName();
        if (!$tokenName) {
            throw new APSException(
                APSI18n::getText('aps_token_name_missing'),
                APSExceptionCodes::APS_TOKEN_NAME_MISSING,
            );
        }

		// Transfer token name from response to authorize payment
		$this->authorizationPaymentData['token_name'] = $tokenName;

        // add additional variables that identifies this implementation
		$this->authorizationPaymentData['app_programming']      = 'PHP';
		$this->authorizationPaymentData['app_framework']        = 'SDK';
		$this->authorizationPaymentData['app_ver']              = phpversion();
		$this->authorizationPaymentData['app_plugin']           = 'PHP_SDK';
		$this->authorizationPaymentData['app_plugin_version']   = APSConstants::SDK_VERSION;

        Logger::getInstance()->info('Authorization: authorizing now');

        $paymentDTO = new PaymentDTO($this->authorizationPaymentData, $paymentTypeAdapter);

        return $this->amazonPaymentServicesCore->callToAuthorizePayment($paymentDTO);
    }

    /**
     * @return string|null
     *
     * @throws APSException
     */
    protected function handleSecure3ds(): ?string
    {
        Logger::getInstance()->info('3ds Validation: Check');

        // if there is no 3ds request, do nothing
        if (!$this->paymentResponseModel->requires3dsValidation()) {
            Logger::getInstance()->info('3ds Validation: not requested!');
            return null;
        }

        Logger::getInstance()->info('3ds Validation: needed!');

        // if the parameter is there, but it is empty,
        // it might be a glitch, do nothing
        $redirect3dsUrl = $this->paymentResponseModel->get3dsUrl();
        if (APSMerchant::isDebugMode()) {
            Logger::getInstance()->debug('3ds Validation: URL=' . $redirect3dsUrl);
        }

        Logger::getInstance()->info('3ds Validation: output iframe html');
        return (new Secure3dsModal())->render([
            '3ds_url'   => $redirect3dsUrl,
        ]);
    }

    /**
     * Return the response model in cases
     * when it is not returned automatically
     *
     * @return APSResponse
     */
    public function getPaymentResponseModel(): APSResponse
    {
        return $this->paymentResponseModel;
    }
}
