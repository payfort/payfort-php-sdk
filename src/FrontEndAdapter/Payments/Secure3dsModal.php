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
        // Validate/sanitize the 3ds URL and then HTML-encode it before
        // injecting it into the iframe's src attribute. This prevents
        // XSS through attribute breakout or unsafe URL schemes
        // (e.g. javascript:, data:) if a malicious/malformed value ever
        // reaches this sink.
        $rawUrl     = $options['3ds_url'] ?? '';
        $safeUrl    = self::validateAndSanitizeUrl(is_string($rawUrl) ? $rawUrl : '');
        $encodedUrl = htmlspecialchars($safeUrl, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return str_replace(
            [
                '{action_url}',
            ],
            [
                $encodedUrl,
            ],
            file_get_contents($this->templateFilePath)
        );

    }

    /**
     * Validate and sanitize a URL that will be placed into an iframe
     * `src` attribute. Only absolute http(s) URLs are allowed.
     *
     * Any of the following results in an empty string being returned,
     * which produces an inert `src=""` in the rendered template:
     *   - null / empty / whitespace-only input
     *   - URLs containing control characters, whitespace or quote-like
     *     characters that could be used for HTML attribute breakout
     *   - URLs that cannot be parsed
     *   - URLs whose scheme is not http or https (e.g. javascript:, data:,
     *     vbscript:, file:, about:)
     *   - URLs missing a host component (e.g. scheme-relative "//evil"
     *     or relative paths)
     *   - URLs that fail PHP's FILTER_VALIDATE_URL check
     *
     * @param string|null $url
     * @return string
     */
    private static function validateAndSanitizeUrl(?string $url): string
    {
        if ($url === null) {
            return '';
        }

        $url = trim($url);
        if ($url === '') {
            return '';
        }

        // Reject anything containing control characters, whitespace, or
        // quote/angle/backtick characters. A legitimate 3DS URL from the
        // APS API will not contain any of these.
        if (preg_match('/[\x00-\x1F\x7F\s"\'<>`]/', $url) === 1) {
            return '';
        }

        $parts = parse_url($url);
        if ($parts === false
            || empty($parts['scheme'])
            || empty($parts['host'])
        ) {
            return '';
        }

        $scheme = strtolower($parts['scheme']);
        if (!in_array($scheme, ['http', 'https'], true)) {
            return '';
        }

        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return '';
        }

        return $url;
    }

}
