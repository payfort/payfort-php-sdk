<?php

namespace AmazonPaymentServicesSdk\FrontEndAdapter\Core;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\APSResponse;

interface ResponseAdapterInterface
{
    public function getSuccessStatusCodes(): array;

    public function process(): null|string|APSResponse;
}