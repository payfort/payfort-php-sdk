<?php

namespace AmazonPaymentServicesSdk\AmazonPaymentServices\Model;

class PaymentTypeAdapterApi extends PaymentTypeAdapter
{
    protected string $environmentUrl = 'https://paymentservices.payfort.com/FortAPI/';
    protected string $sandboxEnvironmentUrl = 'https://sbpaymentservices.payfort.com/FortAPI/';
    protected string $endpointUri = 'paymentApi';
}