<?php
spl_autoload_register(function ($name) {
    require_once str_replace("AmazonPaymentServicesSdk/", '../src/',
            str_replace('\\', '/', $name)) . '.php';
});

require_once __DIR__ . '/../vendor/autoload.php';

