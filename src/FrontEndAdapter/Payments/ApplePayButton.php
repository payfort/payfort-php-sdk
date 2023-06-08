<?php

namespace AmazonPaymentServicesSdk\FrontEndAdapter\Payments;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSExceptionCodes;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\ApplePayInitialization;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\ApplePayPurchase;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter;
use AmazonPaymentServicesSdk\FrontEndAdapter\Core\FrontEndAdapter;

class ApplePayButton extends FrontEndAdapter
{
    protected string $appleScriptPath = __DIR__ . '/../Templates/ApplePayHandlerTemplate.template';

    protected array $supportedNetworks;
    protected ?string $currencyCode = null;
    protected ?string $countryCode = null;
    protected string $displayName;
    protected array $supportedCountries;
    protected ?string $sdkValidationUrl = null;
    protected ?string $sdkCommandUrl = null;

    protected ?PaymentTypeAdapter $apsModelObject;

    /**
     * @throws APSException
     */
    public function __construct()
    {
        $this->templateFilePath = __DIR__ . '/../Templates/apple_pay.html';

        $this->apsModelObject = new ApplePayInitialization();

        $merchantConfig = APSMerchant::getMerchantParams();
        $this->displayName = $merchantConfig['Apple_DisplayName'] ?? [];
        $this->supportedNetworks = $merchantConfig['Apple_SupportedNetworks'] ?? [];
        $this->supportedCountries = $merchantConfig['Apple_SupportedCountries'] ?? [];

        //set a default callback url, we don't need it at this payment type
        $this->callbackUrl = $merchantConfig['Apple_DomainName'] ?? 'placeholder';

        parent::__construct();
    }

    public function setPaymentData(array $paymentData): self
    {
        if (isset($paymentData['currency'])) {
            $this->setCurrencyCode($paymentData['currency']);
        }

        $this->amazonPaymentServicesCore->getValidator()->validateApplePaymentParams($paymentData);

        parent::setPaymentData($paymentData);

        return $this;
    }

    /**
     * Set Apple Store Display Name
     *
     * @param string $displayName
     *
     * @return $this
     */
    public function setDisplayName(string $displayName): self
    {
        $this->displayName = $displayName;

        return $this;
    }

    /**
     * Set Payment Country code
     *
     * @param string $countryCode
     *
     * @return $this
     */
    public function setCountryCode(string $countryCode): self
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    /**
     * Set Payment Currency code
     *
     * @param string $currencyCode
     *
     * @return $this
     */
    public function setCurrencyCode(string $currencyCode): self
    {
        $this->currencyCode = $currencyCode;

        return $this;
    }

    /**
     * Specify the supported country list
     * This can be left empty
     *
     * @param array $countryList
     *
     * @return $this
     */
    public function setSupportedCountries(array $countryList): self
    {
        $this->supportedCountries = $countryList;

        return $this;
    }

    /**
     * Specify the supported network list
     * This can be left empty
     *
     * @param array $networks
     *
     * @return $this
     */
    public function setSupportedNetworks(array $networks): self
    {
        $this->supportedNetworks = $networks;

        return $this;
    }

    /**
     * Set up the url where we send the
     * apple_url to be validated by the SDK
     *
     * @param string $url
     *
     * @return $this
     */
    public function setValidationCallbackUrl(string $url): self
    {
        $this->sdkValidationUrl = $url;

        return $this;
    }

    /**
     * Set up the Amazon Payment Services
     * purchase callback url
     *
     * The purchase command will be initiated
     * when arriving to this URL
     *
     * @param string $url
     *
     * @return $this
     */
    public function setCommandCallbackUrl(string $url): self
    {
        $this->sdkCommandUrl = $url;

        return $this;
    }

    public function getCallbackUrlAddon(): string
    {
        return '?discriminator=' . (new ApplePayPurchase())->getDiscriminator();
    }

    public function render(array $options = null): string
    {
        if (!$this->sdkValidationUrl) {
            throw new APSException(
                APSI18n::getText('apple_pay_validation_callback_url_missing'),
                APSExceptionCodes::APPLE_PAY_VALIDATION_CALLBACK_URL_MISSING,
            );
        }
        if (!$this->sdkCommandUrl) {
            throw new APSException(
                APSI18n::getText('apple_pay_command_callback_url_missing'),
                APSExceptionCodes::APPLE_PAY_COMMAND_CALLBACK_URL_MISSING,
            );
        }
        if (!$this->currencyCode) {
            throw new APSException(
                APSI18n::getText('payment_data_currency_code_missing'),
                APSExceptionCodes::PAYMENT_DATA_CURRENCY_CODE_MISSING,
            );
        }
        if (!$this->countryCode) {
            throw new APSException(
                APSI18n::getText('payment_data_country_code_missing'),
                APSExceptionCodes::PAYMENT_DATA_COUNTRY_CODE_MISSING,
            );
        }

        $appleInitData = [
            'paymentData'           => $this->paymentData,
            'supportedNetworks'     => (array)($this->supportedNetworks ?? []),
            'supportedCountries'    => (array)($this->supportedCountries ?? []),
            'currencyCode'          => $this->currencyCode,
            'countryCode'           => $this->countryCode,
            'displayName'           => $this->displayName,
            'sdkValidationUrl'      => $this->sdkValidationUrl,
            'sdkCommandUrl'        => $this->sdkCommandUrl,
            'generated'             => date('Y-m-d H:i:s'),
        ];

        $applePaymentHandlerJavascript = file_get_contents($this->appleScriptPath);

        $options['apple_payment_script'] = str_replace(
            '[apple_init_data]',
            json_encode($appleInitData),
            $applePaymentHandlerJavascript
        );

        if (!($options['button_text'] ?? null)) {
            $options['button_text'] = 'Pay with Apple';
        }

        return parent::render($options);
    }

    /**
     * Overwrite the parent function,
     * this is not a conventional Form/Input method
     *
     * @param array|null $options
     *
     * @return string
     */
    protected function renderHtmlContent(array $options = null): string
    {
        $options['order_now_button_text'] = $options['button_text'] ?? 'Place order';

        return $this->prepareTemplateContent($options);
    }
}
