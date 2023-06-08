<?php

return [
    'merchant_identifier'       => '**********',
    'access_code'               => '**********',
    'SHARequestPhrase'          => '**********',
    'SHAResponsePhrase'         => '**********',
    'SHAType'                   => '**********',
    'sandbox_mode'              => true,

    'Apple_AccessCode'          => '**********',
    'Apple_SHARequestPhrase'    => '**********[$',
    'Apple_SHAResponsePhrase'   => '***********!',
    'Apple_SHAType'             => '**********',
    'Apple_DisplayName'         => 'Test Apple store',
    'Apple_DomainName'          => 'https://store.local.com',
    'Apple_SupportedNetworks'   => ["visa", "masterCard", "amex", "mada"],
    'Apple_SupportedCountries'  => [],
    'Apple_CertificatePath'     => '**path**to**certificate**',
    'Apple_CertificateKeyPath'  => '**path**to**certificate**key**',
    'Apple_CertificateKeyPass'  => 'apple*certificate*password',

    // folder must be created before
    'log_path'                  => __DIR__ . '/tmp/aps.log',
    '3ds_modal'                 => true,
    'debug_mode'                => false,
    'locale'                    => 'en',
];

