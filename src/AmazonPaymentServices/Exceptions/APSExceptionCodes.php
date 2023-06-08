<?php

namespace AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions;

class APSExceptionCodes
{
    const APS_S2S_CALL_FAILED                                   = 1001;
    const APS_S2S_CALL_RESPONSE_SIGNATURE_FAILED                = 1002;
    const APS_PARAMETER_MISSING                                 = 1003;
    const APS_PAYMENT_ADAPTER_MISSING                           = 1004;
    const APS_TEMPLATE_FILE_MISSING                             = 1005;
    const APS_CALLBACK_MISSING                                  = 1006;
    const APS_TOKEN_NAME_MISSING                                = 1007;
    const APS_RESPONSE_SIGNATURE_FAILED                         = 1008;
    const APS_PAYMENT_METHOD_NOT_AVAILABLE                      = 1009;
    const APS_INVALID_TYPE                                      = 1010;
    const APS_INVALID_PARAMETER                                 = 1011;

    const APPLE_PAY_URL_MISSING                                 = 2001;
    const APPLE_PAY_URL_INVALID                                 = 2002;
    const APPLE_PAY_VALIDATION_CALLBACK_URL_MISSING             = 2003;
    const APPLE_PAY_COMMAND_CALLBACK_URL_MISSING                = 2004;

    const RESPONSE_NO_SIGNATURE                                 = 3001;

    const MERCHANT_CONFIG_MISSING                               = 4001;
    const MERCHANT_CONFIG_MERCHANT_ID_MISSING                   = 4002;
    const MERCHANT_CONFIG_ACCESS_CODE_MISSING                   = 4003;
    const MERCHANT_CONFIG_SHA_REQUEST_PHRASE_MISSING            = 4004;
    const MERCHANT_CONFIG_SHA_RESPONSE_PHRASE_MISSING           = 4005;
    const MERCHANT_CONFIG_SHA_TYPE_MISSING                      = 4006;

    const PAYMENT_DATA_CONFIG_MISSING                           = 5001;
    const PAYMENT_DATA_MERCHANT_REFERENCE_MISSING               = 5002;
    const PAYMENT_DATA_AMOUNT_MISSING                           = 5003;
    const PAYMENT_DATA_CURRENCY_CODE_MISSING                    = 5004;
    const PAYMENT_DATA_LANGUAGE_MISSING                         = 5005;
    const PAYMENT_DATA_CUSTOMER_EMAIL_MISSING                   = 5006;
    const PAYMENT_DATA_COUNTRY_CODE_MISSING                     = 5007;
    const PAYMENT_DATA_SUBTOTAL_MISSING                         = 5008;
    const PAYMENT_DATA_SHIPPING_MISSING                         = 5009;
    const PAYMENT_DATA_DISCOUNT_MISSING                         = 5010;
    const PAYMENT_DATA_TAX_MISSING                              = 5011;

    const MERCHANT_CONFIG_APPLE_MERCHANT_ID_MISSING             = 6001;
    const MERCHANT_CONFIG_APPLE_ACCESS_CODE_MISSING             = 6002;
    const MERCHANT_CONFIG_APPLE_SUPPORTED_NETWORKS_MISSING      = 6003;
    const MERCHANT_CONFIG_APPLE_SUPPORTED_COUNTRIES_MISSING     = 6004;
    const MERCHANT_CONFIG_APPLE_SHA_REQUEST_PHRASE_MISSING      = 6005;
    const MERCHANT_CONFIG_APPLE_SHA_RESPONSE_PHRASE_MISSING     = 6006;
    const MERCHANT_CONFIG_APPLE_SHA_TYPE_MISSING                = 6007;
    const MERCHANT_CONFIG_APPLE_DISPLAY_NAME_MISSING            = 6008;
    const MERCHANT_CONFIG_APPLE_DOMAIN_NAME_MISSING             = 6009;
    const MERCHANT_CONFIG_APPLE_CERTIFICATE_PATH_MISSING        = 6010;
    const MERCHANT_CONFIG_APPLE_CERTIFICATE_KEY_PATH_MISSING    = 6011;
    const MERCHANT_CONFIG_APPLE_CERTIFICATE_KEY_PASS_MISSING    = 6012;
    const MERCHANT_CONFIG_SANDBOX_NOT_SPECIFIED                 = 6013;

    const WEBHOOK_PARAMETERS_EMPTY                              = 7001;
    const WEBHOOK_JSON_INVALID                                  = 7002;
    const WEBHOOK_SIGNATURE_INVALID                             = 7003;
}
