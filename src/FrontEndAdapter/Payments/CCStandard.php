<?php

namespace AmazonPaymentServicesSdk\FrontEndAdapter\Payments;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCStandardAuthorization;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCStandardPurchase;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCStandardTokenization;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter;
use AmazonPaymentServicesSdk\FrontEndAdapter\Core\FrontEndAdapter;

class CCStandard extends FrontEndAdapter
{
    protected ?PaymentTypeAdapter $apsModelObject;

    protected bool $isPurchase = false;

    /**
     * @throws APSException
     */
    public function __construct()
    {
        $this->templateFilePath = __DIR__ . '/../Templates/standard.html';

        // set up this payment type
        $this->apsModelObject = new CCStandardTokenization();

        parent::__construct();
    }

    public function getCallbackUrlAddon(): string
    {
        return '?discriminator=' . ($this->isPurchase
                ? (new CCStandardPurchase())->getDiscriminator()
                : (new CCStandardAuthorization())->getDiscriminator());
    }

    public function render(array $options = null): string
    {
        if (!($options['button_text'] ?? null)) {
            $options['button_text'] = 'Place order';
        }

        return parent::render($options);
    }
}
