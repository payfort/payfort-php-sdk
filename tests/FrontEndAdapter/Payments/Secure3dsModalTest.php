<?php

namespace Tests\FrontEndAdapter\Payments;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\FrontEndAdapter\Payments\Secure3dsModal;
use Tests\APSTestCase;

/**
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSCore
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant
 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\FrontEndAdapter
 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Payments\Secure3dsModal
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n
 */
class Secure3dsModalTest extends APSTestCase
{
    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Payments\Secure3dsModal::render
     *
     * @return void
     */
    public function testRender(): void
    {
        try {
            APSMerchant::setMerchantParams($this->merchantConfig);

            $secure3ds = new Secure3dsModal();

            $this->assertNull(
                $secure3ds->getCallbackUrl()
            );

            $this->assertEquals(
                '',
                $secure3ds->getCallbackUrlAddon()
            );

            $this->assertEquals(
                Secure3dsModal::class,
                $secure3ds->usePurchaseCommand()::class
            );

            $this->assertIsString(
                $secure3ds->render([])
            );
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * A well-formed https URL must be preserved verbatim inside src="...".
     *
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Payments\Secure3dsModal::render
     *
     * @return void
     * @throws APSException
     */
    public function testRenderPreservesValidHttpsUrl(): void
    {
        APSMerchant::setMerchantParams($this->merchantConfig);

        $url    = 'https://example.com/3ds?token=abc123';
        $output = (new Secure3dsModal())->render(['3ds_url' => $url]);

        $this->assertStringContainsString('src="' . $url . '"', $output);
    }

    /**
     * A well-formed http URL must be preserved verbatim inside src="...".
     *
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Payments\Secure3dsModal::render
     *
     * @return void
     * @throws APSException
     */
    public function testRenderPreservesValidHttpUrl(): void
    {
        APSMerchant::setMerchantParams($this->merchantConfig);

        $url    = 'http://example.com/3ds';
        $output = (new Secure3dsModal())->render(['3ds_url' => $url]);

        $this->assertStringContainsString('src="' . $url . '"', $output);
    }

    /**
     * Empty and missing input must produce an inert `src=""` without
     * generating any error.
     *
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Payments\Secure3dsModal::render
     *
     * @return void
     * @throws APSException
     */
    public function testRenderWithMissingOrEmptyUrlProducesEmptySrc(): void
    {
        APSMerchant::setMerchantParams($this->merchantConfig);

        $modal = new Secure3dsModal();

        $this->assertStringContainsString('src=""', $modal->render([]));
        $this->assertStringContainsString('src=""', $modal->render(['3ds_url' => '']));
        $this->assertStringContainsString('src=""', $modal->render(['3ds_url' => null]));
    }

    /**
     * Provider of malicious 3ds_url payloads that must be neutralized
     * (rejected → inert `src=""`), including the two payloads from the
     * original issue report.
     *
     * @return array<string, array{0:string}>
     */
    public function maliciousUrlProvider(): array
    {
        return [
            'attribute break + onload'  => ['x" onload="alert(document.domain)'],
            'close iframe + script tag' => ['x"></iframe><script>alert(document.domain)</script><iframe src="y'],
            'javascript scheme'         => ['javascript:alert(1)'],
            'javascript scheme upper'   => ['JavaScript:alert(1)'],
            'data scheme'               => ['data:text/html,<script>alert(1)</script>'],
            'vbscript scheme'           => ['vbscript:msgbox(1)'],
            'file scheme'               => ['file:///etc/passwd'],
            'about scheme'              => ['about:blank'],
            'scheme relative'           => ['//evil.example.com/x'],
            'relative path'             => ['/foo/bar'],
            'plain word'                => ['not-a-url'],
            'with CR LF'                => ["https://example.com/\r\n<script>alert(1)</script>"],
            'with null byte'            => ["https://example.com/\x00<script>alert(1)</script>"],
            'with space'                => ['https://example.com/ onload=alert(1)'],
            'with single quote'         => ["https://example.com/'onmouseover='alert(1)"],
            'with backtick'             => ['https://example.com/`alert(1)`'],
        ];
    }

    /**
     * @test
     * @dataProvider maliciousUrlProvider
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Payments\Secure3dsModal::render
     *
     * @param string $maliciousUrl
     * @return void
     * @throws APSException
     */
    public function testRenderRejectsMaliciousUrls(string $maliciousUrl): void
    {
        APSMerchant::setMerchantParams($this->merchantConfig);

        $output = (new Secure3dsModal())->render(['3ds_url' => $maliciousUrl]);

        // The malicious URL must not have been reflected verbatim into
        // the iframe src attribute.
        $this->assertStringNotContainsString(
            'src="' . $maliciousUrl . '"',
            $output,
            'Malicious URL was reflected verbatim into src attribute'
        );

        // No raw script tag, event handler attribute, or javascript:
        // scheme should ever appear in the rendered output.
        $this->assertStringNotContainsString('<script', $output);
        $this->assertStringNotContainsString('</script>', $output);
        $this->assertStringNotContainsString('onload=', $output);
        $this->assertStringNotContainsString('onerror=', $output);
        $this->assertStringNotContainsString('onmouseover=', $output);
        $this->assertStringNotContainsString('javascript:', strtolower($output));
        $this->assertStringNotContainsString('vbscript:', strtolower($output));
        // No data: scheme should appear as a src value.
        $this->assertStringNotContainsString('src="data:', $output);

        // The template still renders and the iframe still exists.
        $this->assertStringContainsString('<iframe', $output);
    }

    /**
     * Regression test for the exact reproduction payloads from the
     * original issue write-up.
     *
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Payments\Secure3dsModal::render
     *
     * @return void
     * @throws APSException
     */
    public function testRenderNeutralizesOriginalIssuePayloads(): void
    {
        APSMerchant::setMerchantParams($this->merchantConfig);

        $modal = new Secure3dsModal();

        // Payload A: attribute break + onload handler
        $payloadA = 'x" onload="alert(document.domain)';
        $outputA  = $modal->render(['3ds_url' => $payloadA]);
        $this->assertStringNotContainsString('onload="alert', $outputA);
        $this->assertStringContainsString('src=""', $outputA);

        // Payload B: close iframe, inject inline script
        $payloadB = 'x"></iframe><script>alert(document.domain)</script><iframe src="y';
        $outputB  = $modal->render(['3ds_url' => $payloadB]);
        $this->assertStringNotContainsString('<script>alert', $outputB);
        $this->assertStringNotContainsString('</iframe><', $outputB);
        $this->assertStringContainsString('src=""', $outputB);
    }
}
