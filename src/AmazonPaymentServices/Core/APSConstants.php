<?php

namespace AmazonPaymentServicesSdk\AmazonPaymentServices\Core;

class APSConstants
{
    const SDK_VERSION = '2.0.0';

    const PAYMENT_TYPE_CREDIT_CARD      = 'credit_card';
    const PAYMENT_TYPE_APPLE_PAY        = 'apple_pay';
    const PAYMENT_TYPE_INSTALMENTS      = 'installments';

    const INTEGRATION_TYPE_REDIRECT     = 'redirect';
    const INTEGRATION_TYPE_STANDARD     = 'standard';
    const INTEGRATION_TYPE_CUSTOM       = 'custom';
    const INTEGRATION_TYPE_TRUSTED       = 'trusted';

    const PAYMENT_COMMAND_AUTHORIZATION = 'AUTHORIZATION';
    const PAYMENT_COMMAND_PURCHASE      = 'PURCHASE';
    const PAYMENT_COMMAND_TOKENIZATION  = 'TOKENIZATION';
    const PAYMENT_COMMAND_CAPTURE       = 'CAPTURE';
    const PAYMENT_COMMAND_VOID          = 'VOID_AUTHORIZATION';
    const PAYMENT_COMMAND_REFUND        = 'REFUND';
    const PAYMENT_COMMAND_CHECK_STATUS  = 'CHECK_STATUS';
    const PAYMENT_COMMAND_RECURRING     = 'RECURRING';

    const PAYMENT_ECI_MOTO              = 'MOTO';
    const PAYMENT_ECI_RECURRING         = 'RECURRING';
    const PAYMENT_ECI_ECOMMERCE         = 'ECOMMERCE';

	const INSTALLMENTS_TYPE_STANDALONE  = 'STANDALONE';
	const INSTALLMENTS_TYPE_HOSTED      = 'HOSTED';
	const INSTALLMENTS_TYPE_PURCHASE    = 'YES';

	const INSTALLMENTS_PLANS            = 'GET_INSTALLMENTS_PLANS';

	const DIGITAL_WALLET_APPLE          = 'APPLE_PAY';
}
