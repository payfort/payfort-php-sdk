<?php

namespace AmazonPaymentServicesSdk\AmazonPaymentServices\Model;

interface PaymentTypeInterface
{
    public function getPaymentType(): string;
    public function getIntegrationType(): string;
    public function getRequiredParameters(): array;
    public function getOptionalParameters(): array;

    public function generateMainParameters(): array;
    public function generateParameters(PaymentDTO $paymentParams): array;

    public function getDiscriminator(): string;

    public function getPaymentParameter(string $parameter, mixed $possibleValue = null, bool $isStrict = false): mixed;
}
