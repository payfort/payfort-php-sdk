# Amazon Payment Services PHP SDK
<a href="https://paymentservices.amazon.com/" target="_blank">Amazon Payment Services</a> SDK offers seamless payments for PHP platform merchants.  If you don't have an APS account click [here](https://paymentservices.amazon.com/) to sign up for Amazon Payment Services account.


## Getting Started
We know that payment processing is critical to your business. With this plugin we aim to increase your payment processing capabilities. Do you have a business-critical questions? View our quick reference [documentation](https://paymentservices.amazon.com/docs/EN/index.html) for key insights covering payment acceptance, integration, and reporting. For SDK Guide refer [wiki](https://github.com/payfort/payfort-php-sdk/wiki)


## Payment Options

* Integration Types
    * Redirection
    * Merchant Page
    * Hosted Merchant Page
    * Apple Pay
    * Installments
    * Recurring
    * MOTO
    * Trusted

* Maintenance Operations
    * Signature Calculation and Validation
    * Refund
    * Capture
    * Void
    * Check Status
    * Feedback Notification handling
 
# Integrations steps

## Install PHP SDK Package

Install the PHP SDK Package of your solution with composer or
download it from the GitHub repository and then run the composer update
command in terminal to install all the dependencies.

## Merchant configuration

As a merchant you need to send to the gateway some properties. These properties must be put into an array and set with the
following method. If you want integration with Apple Pay all
the properties that contains "Apple\_" must be added, otherwise those
properties are not required.

```php
php  
return [     'merchant_identifier'       => '**********',    
 'access_code'               => '**********',     
'SHARequestPhrase'          => '**********',    
 'SHAResponsePhrase'         => '**********',     
'SHAType'                   => '**********',    
 'sandbox_mode'              => true,     
 'Apple_AccessCode'          => '**********',    
 'Apple_SHARequestPhrase'    => '**********',    
 'Apple_SHAResponsePhrase'   => '**********',     
'Apple_SHAType'             => '**********',     
'Apple_DisplayName'         => 'Test Apple store',    
 'Apple_DomainName'          => 'https://store.local.com',     
'Apple_SupportedNetworks'   => ["visa", "masterCard", "amex", "mada"],     
'Apple_SupportedCountries'  => [],    
 'Apple_CertificatePath'     => '**path**to**certificate**',    
 'Apple_CertificateKeyPath'  => '**path**to**certificate**key**',    
 'Apple_CertificateKeyPass'  => 'apple*certificate*password',     
 // folder must be created before     
'log_path'                  => __DIR__ . '/tmp/aps.log',    
'3ds_modal'                 => true,     
'debug_mode'                => false,     
'locale'                    => 'en', ];

```



All the merchant configuration properties
```
// load merchant configuration
$merchantParams = include 'merchant_config.php';

// set merchant configuration one time
APSMerchant::setMerchantParams($merchantParams);
```

## Payment data configuration

As a merchant you need to send to the gateway the payment details. These details must be put into an array and set within the "setPaymentData" method below. The "merchant_reference" is the customer order number.

```
<?php

return  [
    'merchant_reference'=> 'O-00001-'.rand(1000, 99999),
    'amount'            => 3197.00,
    'currency'          => 'AED',
    'language'          => 'en',
    'customer_email'    => 'test@aps.com',

    'order_description' => 'Test product 1',
];
```
You can see below how the credit card redirect payment method issued. Payment data is set with the payment details, then set the
authorization/purchase command, set your callback URL and render the information needed for your client page.

```<div>
    <?php
    try {
        echo (new CCRedirect())
            ->setPaymentData($paymentData)
            ->useAuthorizationCommand()
            ->setCallbackUrl(‘callback-url.php’)
            ->render([
                    ‘button_text’   => ‘Place order with Authorization’
            ]);
    } catch (APSException $e) {
        echo ‘SETUP ERROR: ‘ . $e->getMessage();
    }
    ?>
</div>

    <?php
    try {
        echo (new CCRedirect())
            ->setPaymentData($paymentData)
            ->usePurchaseCommand()
            ->setCallbackUrl(‘callback-url.php’)
            ->render([
                    ‘button_text’   => ‘Place order with Purchase’
            ]);
    } catch (APSException $e) {
        echo ‘SETUP ERROR: ‘ . $e->getMessage();
    }
    ?>

```

## Changelog

| Plugin Version | Release Notes |
| :---: | :--- |
| 2.0.0 |   * Integrated checkout experience options: Redirection, Merchant Page, Hosted Merchant Page, Apple Pay, Installments, Recurring, MOTO, Trusted <br/> * Partial/Full Refund, Single/Multiple Capture and Void events <br/> * Signature calculation and validation <br/> * Check Status as a function <br/> Feedback notification handling| 

## API Documentation
This SDK has been implemented by using following [API library](https://paymentservices-reference.payfort.com/docs/api/build/index.html)

## Further Questions
Have any questions? Just get in [touch](https://paymentservices.amazon.com/get-in-touch)

## License
Released under the [MIT License](/LICENSE).
