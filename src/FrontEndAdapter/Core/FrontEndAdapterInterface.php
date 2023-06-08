<?php

namespace AmazonPaymentServicesSdk\FrontEndAdapter\Core;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter;

interface FrontEndAdapterInterface
{
    public function setPaymentData(array $paymentData): self;
    public function setCallbackUrl(string $callbackUrl): self;
    public function getCallbackUrl(): ?string;
    public function getCallbackUrlAddon(): string;

    public function getApsModelObject(): PaymentTypeAdapter;


    public function render(array $options = null): string;
}
