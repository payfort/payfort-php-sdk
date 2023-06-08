<?php

namespace AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\Logger;
use Throwable;

class APSException extends \Exception
{
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        Logger::getInstance()->error('APS Exception: '. '(' . $code . ') ' . $message);
    }
}
