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
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCCustomAuthorization;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCCustomPurchase;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCRedirectAuthorization;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCRedirectPurchase;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCStandardAuthorization;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCStandardPurchase;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\InstallmentsCCCustomPurchase;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\InstallmentsCCRedirectPurchase;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\InstallmentsCCStandardPurchase;
use AmazonPaymentServicesSdk\FrontEndAdapter\Response\CCCustomResponse;
use AmazonPaymentServicesSdk\FrontEndAdapter\Response\CCRedirectResponse;
use AmazonPaymentServicesSdk\FrontEndAdapter\Response\CCStandardResponse;
use AmazonPaymentServicesSdk\FrontEndAdapter\Response\InstallmentsCCCustomResponse;
use AmazonPaymentServicesSdk\FrontEndAdapter\Response\InstallmentsCCRedirectResponse;
use AmazonPaymentServicesSdk\FrontEndAdapter\Response\InstallmentsCCStandardResponse;
use Closure;

class ResponseHandler
{
    private ?array $paymentData;

    private ?array $paymentResponseData;

    private ?Closure $onSuccessFunction = null;
    private ?Closure $onErrorFunction = null;
    private ?Closure $onHtmlFunction = null;

    private bool $validated = false;
    private bool $processed = false;

    protected null|string|APSResponse $resultObject = null;
    protected ?APSResponse $apsResponse = null;

    /**
     * @param array|null $paymentData
     * @param array|null $paymentResponseData
     */
    public function __construct(array $paymentData = null, array $paymentResponseData = null)
    {
        $this->paymentData = $paymentData;
        $this->paymentResponseData = $paymentResponseData;

        $this->onHtml(function(string $htmlContent) {
            return $htmlContent;
        });
    }

    /**
     * Validate the response and return the hydrated Response model
     *
     * @return ResponseHandler
     *
     * @throws APSException
     */
    public function validate(): self
    {
        Logger::getInstance()->info('Arrived on the redirect page');
        if (null === $this->paymentResponseData) {
            $this->paymentResponseData = filter_input_array(INPUT_POST);
            if (APSMerchant::isDebugMode()) {
                Logger::getInstance()->debug('Received POST: ' . json_encode($this->paymentResponseData));
            }

			if (!isset($this->paymentResponseData['signature'])) {
				$this->paymentResponseData = filter_input_array(INPUT_GET);
                if (APSMerchant::isDebugMode()) {
                    Logger::getInstance()->debug('Received GET: ' . json_encode($this->paymentResponseData));
                }
			}
        }


        $apsCore = new APSCore();
        if (!$apsCore->isResponseValid((array)$this->paymentResponseData)) {
            throw new APSException(
                APSI18n::getText('aps_response_signature_failed'),
                APSExceptionCodes::APS_RESPONSE_SIGNATURE_FAILED,
            );
        }

        $this->validated = true;

        return $this;
    }

    /**
     * Process actions that are specific
     * to the initiated payment type
     *
     * @param ?string $discriminator
     *
     * @return ResponseHandler
     *
     * @throws APSException
     */
    public function process(string $discriminator = null): self
    {
        if (null === $discriminator) {
            $discriminator = $_REQUEST['discriminator'] ?? '';
        }

        if (empty($discriminator)) {
            // fallback option
            $discriminator = (new CCCustomAuthorization())->getDiscriminator();
            if (APSConstants::PAYMENT_COMMAND_PURCHASE ===
                ($this->paymentResponseData['command'] ?? null)
            ) {
                $discriminator = (new CCCustomPurchase())->getDiscriminator();
            }
        }

        Logger::getInstance()->info('Received discriminator: ' . $discriminator);

        // match payment type with the correct response handler
        /** @var ResponseAdapter $paymentMethodResponseHandler */
        $paymentMethodResponseHandler = match ($discriminator) {
            (new CCRedirectAuthorization())->getDiscriminator()
                => new CCRedirectResponse(
                    $this->paymentResponseData, null, false),
            (new CCRedirectPurchase())->getDiscriminator()
                => new CCRedirectResponse($this->paymentResponseData, null, true),

            (new CCStandardAuthorization())->getDiscriminator()
                => new CCStandardResponse($this->paymentResponseData, $this->paymentData, false),
            (new CCStandardPurchase())->getDiscriminator()
                => new CCStandardResponse($this->paymentResponseData, $this->paymentData, true),

            (new CCCustomAuthorization())->getDiscriminator()
                => new CCCustomResponse($this->paymentResponseData, $this->paymentData, false),
            (new CCCustomPurchase())->getDiscriminator()
                => new CCCustomResponse($this->paymentResponseData, $this->paymentData, true),

            (new InstallmentsCCRedirectPurchase())->getDiscriminator()
                => new InstallmentsCCRedirectResponse(
                    $this->paymentResponseData, $this->paymentData, true
            ),
            (new InstallmentsCCStandardPurchase())->getDiscriminator()
                => new InstallmentsCCStandardResponse(
                    $this->paymentResponseData, $this->paymentData, true
            ),
            (new InstallmentsCCCustomPurchase())->getDiscriminator()
                => new InstallmentsCCCustomResponse(
                    $this->paymentResponseData, $this->paymentData, true
            ),

            default => $this->guessPaymentImplementationType($discriminator),
        };

        Logger::getInstance()->info('Matched response handler: ' . $paymentMethodResponseHandler::class);

	    // process after response tasks (like authorization, secure 3ds validation, etc)
        $this->resultObject = $paymentMethodResponseHandler->process();

        $this->apsResponse = $paymentMethodResponseHandler->paymentResponseModel;

        Logger::getInstance()->info('Returned resultObject is '
            . (is_string($this->resultObject) ? 'string' : 'ResponseModel'));

        // we can have two types of response from the process method:
        // - string => html code to write to the browser (ex. secure 3ds verification modal)
        // - null   => no html code to write to the browser, so we return the APS response model
        if (!is_string($this->resultObject)) {
            $this->resultObject = $paymentMethodResponseHandler->paymentResponseModel;
            Logger::getInstance()->info('Result discriminator = ' . $this->resultObject->getDiscriminator());
        }

        $this->processed = true;

        return $this;
    }

    /**
     * Try to guess, based on response from APS,
     * which implementation type response is this
     *
     * @param string $discriminator
     *
     * @return ResponseAdapter
     *
     * @throws APSException
     */
    private function guessPaymentImplementationType(string $discriminator): ResponseAdapter
    {
        throw new APSException(
            APSI18n::getText('aps_payment_method_not_available', [
                'discriminator' => $discriminator
            ]),
            APSExceptionCodes::APS_PAYMENT_METHOD_NOT_AVAILABLE,
        );
    }

    public function onSuccess(Closure $onSuccessFunction): self
    {
        $this->onSuccessFunction = $onSuccessFunction;

        return $this;
    }

    public function onError(Closure $onErrorFunction): self
    {
        $this->onErrorFunction = $onErrorFunction;

        return $this;
    }

    public function onHtml(Closure $onHtmlFunction): self
    {
        $this->onHtmlFunction = $onHtmlFunction;

        return $this;
    }

    /**
     * @throws APSException
     */
    public function handleResponse(): null|string|APSResponse
    {
        if (!$this->validated) {
            $this->validate();
        }

        if (!$this->processed) {
            $this->process();
        }

        if ($this->onHtmlFunction && is_string($this->resultObject)) {
            $apsResponseModel =
            call_user_func($this->onHtmlFunction, $this->resultObject, $this->apsResponse);

            return $this->resultObject;
        }

        Logger::getInstance()->info('Response status = '
            . $this->resultObject->getResponseStatus() . ' which is '
            . ($this->resultObject->isSuccess() ? 'success' : 'fail'));

        if ($this->onSuccessFunction && $this->resultObject->isSuccess()) {
            call_user_func($this->onSuccessFunction, $this->resultObject);

            return $this->resultObject;
        }

        if ($this->onErrorFunction) {
            call_user_func($this->onErrorFunction, $this->resultObject);
        }

        return $this->resultObject;
    }

    /**
     * Return the process result
     *
     * @return string|APSResponse
     */
    public function getResult(): string|APSResponse
    {
        return $this->resultObject;
    }
}
