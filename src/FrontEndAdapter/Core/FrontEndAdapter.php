<?php

namespace AmazonPaymentServicesSdk\FrontEndAdapter\Core;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSCore;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSExceptionCodes;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentDTO;

abstract class FrontEndAdapter implements FrontEndAdapterInterface
{
    protected ?array $merchantParams = null;
    protected ?array $paymentData = null;
    protected ?PaymentDTO $paymentDTO = null;
    protected ?string $callbackUrl = null;

    protected bool $isPurchase = false;

    protected ?string $templateFilePath = null;
    protected ?PaymentTypeAdapter $apsModelObject = null;

    protected APSCore $amazonPaymentServicesCore;

    /**
     * @throws APSException
     */
    public function __construct()
    {
        if (!file_exists($this->templateFilePath)) {
            throw new APSException(
                APSI18n::getText('aps_template_file_missing'),
                APSExceptionCodes::APS_TEMPLATE_FILE_MISSING,
            );
        }

        $this->merchantParams = APSMerchant::getMerchantParams();
        $this->amazonPaymentServicesCore = new APSCore();

        $this->useAuthorizationCommand();
    }

    /**
     * Set the APS Model object
     * this is the model for the transaction requirements
     *
     * @return PaymentTypeAdapter
     */
    public function getApsModelObject(): PaymentTypeAdapter
    {
        return $this->apsModelObject;
    }

    /**
     * Set the payment data DTO
     *
     * @param array $paymentData
     *
     * @return $this
     *
     * @throws APSException
     */
    public function setPaymentData(array $paymentData): self
    {
        $this->paymentData = $paymentData;
        $this->paymentDTO = new PaymentDTO($this->paymentData, $this->getApsModelObject());

        return $this;
    }

    /**
     * Set the callback URL
     *
     * @param string $callbackUrl
     *
     * @return $this
     */
    public function setCallbackUrl(string $callbackUrl): self
    {
        $this->callbackUrl = $callbackUrl;

        return $this;
    }

    /**
     * Get the callback URL
     *
     * @return string|null
     */
    public function getCallbackUrl(): ?string
    {
        if (!$this->callbackUrl) {
            return null;
        }

        return $this->callbackUrl . $this->getCallbackUrlAddon();
    }

    /**
     * Get the addon that is appended to the callback URL
     *
     * @return string
     */
    public function getCallbackUrlAddon(): string
    {
        return '';
    }

    /**
     * Use the PURCHASE command for the transaction
     *
     * @return $this
     *
     * @throws APSException
     */
    public function usePurchaseCommand(): self
    {
        $this->isPurchase = true;

        if ($this->paymentData) {
            // set paymentDTO again
            $this->setPaymentData($this->paymentData);
        }

        return $this;
    }

    /**
     * Use the AUTHORIZATION command for the transaction
     *
     * @return $this
     *
     * @throws APSException
     */
    public function useAuthorizationCommand(): self
    {
        $this->isPurchase = false;

        if ($this->paymentData) {
            // set paymentDTO again
            $this->setPaymentData($this->paymentData);
        }

        return $this;
    }

    /**
     * Render and return the HTML code
     *
     * @throws APSException()
     */
    public function render(array $options = null): string
    {
        if (!$this->merchantParams) {
            throw new APSException(
                APSI18n::getText('merchant_config_missing'),
                APSExceptionCodes::MERCHANT_CONFIG_MISSING,
            );
        }
        if (!$this->paymentData || !$this->paymentDTO) {
            throw new APSException(
                APSI18n::getText('payment_data_config_missing'),
                APSExceptionCodes::PAYMENT_DATA_CONFIG_MISSING,
            );
        }
        if (!$this->callbackUrl) {
            throw new APSException(
                APSI18n::getText('aps_callback_missing'),
                APSExceptionCodes::APS_CALLBACK_MISSING,
            );
        }

        return $this->renderHtmlContent($options);
    }

    /**
     * Replace template variables with corresponding content
     * and return the template HTML code
     *
     * @param array|null $options
     *
     * @return string
     *
     * @throws APSException
     */
    protected function renderHtmlContent(array $options = null): string
    {
        $apsPaymentParams = $this->preparePaymentData();

        $options['hidden_inputs'] = $this->prepareHiddenHtmlInputString($apsPaymentParams);
        $options['action_url'] = $this->getApsModelObject()->getEndpoint();
        $options['order_now_button_text'] = $options['button_text'] ?? 'Place order';

        return $this->prepareTemplateContent($options);
    }

    /**
     * Set return url,
     * generate all required and available optional parameters
     * and calculate signature
     *
     * @return array
     *
     * @throws APSException
     */
    private function preparePaymentData(): array
    {
        $this->paymentDTO->set('return_url', $this->getCallbackUrl());

	    $apsPaymentParams = $this->paymentDTO->getPaymentTypeAdapter()->generateParameters($this->paymentDTO);

	    $apsPaymentParams['signature'] = $this->amazonPaymentServicesCore->calculateRequestSignature($apsPaymentParams);

        return $apsPaymentParams;
    }

    /**
     * Parse all payment data,
     * and generate HTML hidden input fields for form
     *
     * @param array $apsPaymentParams
     *
     * @return string
     */
    private function prepareHiddenHtmlInputString(array $apsPaymentParams): string
    {
        $hiddenInputContent = '';
        foreach ($apsPaymentParams as $fieldName => $fieldValue) {
            if (is_array($fieldValue) || is_null($fieldValue)) {
                // if the value is null, don't show it in the hidden section

                continue;
            }

            $hiddenInputContent .=
                '<input type="hidden" '
                . 'name="' . htmlspecialchars($fieldName) . '" '
                . 'value="' . htmlspecialchars($fieldValue) . '" />';
        }

        return $hiddenInputContent;
    }

    /**
     * Get the template content
     * and return the processed content
     *
     * @param array $options
     *
     * @return string
     */
    protected function prepareTemplateContent(array $options): string
    {
        $templateContent = $this->getTemplateContent();

        return $this->findAndReplaceHtmlVariables($templateContent, $options);
    }

    /**
     * Return the content of the set template
     *
     * @return string
     */
    protected function getTemplateContent(): string
    {
        return file_get_contents($this->templateFilePath);
    }

    /**
     * Find all template variables
     * and replace them with the matching value
     * from the $options array
     *
     * @param string $fileContent
     * @param array $options
     *
     * @return string
     */
    protected function findAndReplaceHtmlVariables(string $fileContent, array $options): string
    {
        $templateReplaceSearch = [];
        $templateReplaceWith = [];
        $variableMatches = [];
        preg_match_all('/{([a-zA-z0-9\-_]+)}/', $fileContent, $variableMatches);

        if (isset($variableMatches[0]) && isset($variableMatches[1])) {
            foreach ($variableMatches[0] as $matchIndex => $matchVariable) {
                $attribute = $variableMatches[1][$matchIndex] ?? null;
                $templateReplaceSearch[] = $matchVariable;
                $templateReplaceWith[] = $attribute ? ($options[$attribute] ?? '') : '';
            }
        }

        return str_replace(
            $templateReplaceSearch,
            $templateReplaceWith,
            $fileContent
        );
    }
}
