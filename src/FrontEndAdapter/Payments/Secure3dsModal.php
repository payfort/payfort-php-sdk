<?php

namespace AmazonPaymentServicesSdk\FrontEndAdapter\Payments;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\FrontEndAdapter\Core\FrontEndAdapter;

class Secure3dsModal extends FrontEndAdapter
{
    /**
     * @throws APSException
     */
    public function __construct()
    {
        $this->templateFilePath = __DIR__ . '/../Templates/3ds_modal.html';

        parent::__construct();
    }

    public function render(array $options = null): string
    {
        // skip the checks, just render the HTML
        return str_replace(
            [
                '{action_url}',
            ],
            [
                $options['3ds_url'] ?? '',
            ],
            file_get_contents($this->templateFilePath)
        );

    }

}