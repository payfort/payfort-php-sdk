<?php

namespace AmazonPaymentServicesSdk\FrontEndAdapter\Payments;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCCustomAuthorization;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCCustomPurchase;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCCustomTokenization;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter;
use AmazonPaymentServicesSdk\FrontEndAdapter\Core\FrontEndAdapter;

class CCCustom extends FrontEndAdapter
{
    protected ?PaymentTypeAdapter $apsModelObject;

    protected bool $isPurchase = false;

    /**
     * @throws APSException
     */
    public function __construct()
    {
        $this->templateFilePath = __DIR__ . '/../Templates/custom.html';

        // set up this payment type
        $this->apsModelObject = new CCCustomTokenization();

        parent::__construct();
    }

    public function getCallbackUrlAddon(): string
    {
        return '?discriminator=' . ($this->isPurchase
                ? (new CCCustomPurchase())->getDiscriminator()
                : (new CCCustomAuthorization())->getDiscriminator());
    }

    public function render(array $options = null): string
    {
        if (!($options['button_text'] ?? null)) {
            $options['button_text'] = 'Place order';
        }

        return parent::render($options);
    }
}
