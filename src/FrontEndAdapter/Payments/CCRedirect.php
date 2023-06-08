<?php

namespace AmazonPaymentServicesSdk\FrontEndAdapter\Payments;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCRedirectAuthorization;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCRedirectPurchase;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter;
use AmazonPaymentServicesSdk\FrontEndAdapter\Core\FrontEndAdapter;

class CCRedirect extends FrontEndAdapter
{
    protected ?PaymentTypeAdapter $apsModelObject;

    protected bool $isPurchase = false;

    /**
     * @throws APSException
     */
    public function __construct()
    {
        $this->templateFilePath = __DIR__ . '/../Templates/redirect.html';

        // set up this payment type
        $this->useAuthorizationCommand();

        parent::__construct();
    }

    /**
     * Use the PURCHASE command if this is called
     *
     * @return $this
     *
     * @throws APSException
     */
    public function usePurchaseCommand(): self
    {
        $this->apsModelObject = new CCRedirectPurchase();

        parent::usePurchaseCommand();

        return $this;
    }

    /**
     * Use the AUTHORIZATION command if this is called
     *
     * @return $this
     *
     * @throws APSException
     */
    public function useAuthorizationCommand(): self
    {
        $this->apsModelObject = new CCRedirectAuthorization();

        parent::useAuthorizationCommand();

        return $this;
    }

    /**
     * Get the callback URL addon
     *
     * @return string
     */
    public function getCallbackUrlAddon(): string
    {
        return '?discriminator=' . ($this->isPurchase ? (new CCRedirectPurchase())->getDiscriminator()
                : (new CCRedirectAuthorization())->getDiscriminator());
    }

    /**
     * Render the template HTML code
     *
     * @param array|null $options
     *
     * @return string
     *
     * @throws APSException
     */
    public function render(array $options = null): string
    {
        if (!($options['button_text'] ?? null)) {
            $options['button_text'] = 'Place order';
        }

        return parent::render($options);
    }
}
