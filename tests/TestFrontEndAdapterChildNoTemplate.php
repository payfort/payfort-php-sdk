<?php

namespace Tests;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\FrontEndAdapter\Core\FrontEndAdapter;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter;

class TestFrontEndAdapterChildNoTemplate extends FrontEndAdapter
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
        $this->usePurchaseCommand();

        $this->apsModelObject = new TestPaymentTypeAdapterChild();

        parent::__construct();
    }
}