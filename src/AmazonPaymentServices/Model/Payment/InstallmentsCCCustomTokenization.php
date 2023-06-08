<?php

namespace AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConstants;

class InstallmentsCCCustomTokenization extends CCCustomTokenization
{
	protected string $discriminator = APSConstants::PAYMENT_TYPE_INSTALMENTS . '_'
    . APSConstants::INTEGRATION_TYPE_CUSTOM . '_'
    . APSConstants::PAYMENT_COMMAND_TOKENIZATION;

	protected string $paymentType = APSConstants::PAYMENT_TYPE_INSTALMENTS;
}
