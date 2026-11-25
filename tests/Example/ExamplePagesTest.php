<?php

namespace Tests\Example;

use PHPUnit\Framework\TestCase;

/**
 * Black-box tests for the sample app result pages (order_success.php,
 * order_failed.php), which are loaded inside the checkout / 3DS iframes after 3DS.
 *
 * The pages are served by PHP's built-in web server on a random loopback port.
 * A test merchant_config.php is put first on the include_path, so the tests do
 * not depend on (or read) a developer's local, gitignored merchant_config.php.
 * No request leaves the machine.
 *
 * @coversNothing
 */
class ExamplePagesTest extends TestCase
{
    /** The only URL paths the test server serves. */
    private const ALLOWED_PATHS = [
        '/order_success.php',
        '/order_failed.php',
        '/css/site.css',
    ];

    /** @var resource|null */
    private static $server = null;
    private static string $baseUrl = '';
    private static string $configDir = '';

    public static function setUpBeforeClass(): void
    {
        if (!function_exists('proc_open')) {
            self::markTestSkipped('proc_open is not available');
        }

        // test merchant configuration (never the developer's real one)
        self::$configDir = sys_get_temp_dir() . '/aps-sdk-example-test-' . bin2hex(random_bytes(4));
        mkdir(self::$configDir);
        file_put_contents(self::$configDir . '/merchant_config.php', '<?php return ' . var_export([
            'merchant_identifier' => 'test_merchant',
            'access_code'         => 'test_access_code',
            'SHARequestPhrase'    => 'test_request_phrase',
            'SHAResponsePhrase'   => 'test_response_phrase',
            'SHAType'             => 'sha256',
            'sandbox_mode'        => true,
            'log_path'            => null,
            '3ds_modal'           => true,
            'debug_mode'          => false,
            'locale'              => 'en',
        ], true) . ';');

        // Router: only the pages under test (and their stylesheet) are served.
        // Everything else in example/ (e.g. a developer's tmp/aps.log, which can
        // contain request data) gets a 404, even though the server is loopback-only.
        file_put_contents(self::$configDir . '/router.php', '<?php
$path = parse_url($_SERVER["REQUEST_URI"] ?? "/", PHP_URL_PATH);
if (in_array($path, ' . var_export(self::ALLOWED_PATHS, true) . ', true)) {
    return false; // let the built-in server serve/execute the file
}
http_response_code(404);
echo "Not found";
');

        // free loopback port
        $socket = stream_socket_server('tcp://127.0.0.1:0', $errno, $errstr);
        $port   = (int)substr(strrchr(stream_socket_get_name($socket, false), ':'), 1);
        fclose($socket);

        $exampleDir = realpath(__DIR__ . '/../../example');
        self::$server = proc_open(
            [
                PHP_BINARY,
                '-d', 'include_path=' . self::$configDir . PATH_SEPARATOR . '.',
                '-d', 'display_errors=1',
                '-d', 'error_reporting=' . (E_ALL & ~E_DEPRECATED),
                '-S', '127.0.0.1:' . $port,
                '-t', $exampleDir,
                self::$configDir . '/router.php',
            ],
            [0 => ['pipe', 'r'], 1 => ['file', '/dev/null', 'w'], 2 => ['file', '/dev/null', 'w']],
            $pipes
        );
        self::$baseUrl = 'http://127.0.0.1:' . $port . '/';

        // wait until the server accepts connections
        for ($i = 0; $i < 50; $i++) {
            $connection = @fsockopen('127.0.0.1', $port, $errno, $errstr, 0.1);
            if ($connection) {
                fclose($connection);
                return;
            }
            usleep(100000);
        }
        self::fail('PHP built-in server did not start');
    }

    public static function tearDownAfterClass(): void
    {
        if (is_resource(self::$server)) {
            proc_terminate(self::$server);
            proc_close(self::$server);
        }
        if (self::$configDir !== '' && is_dir(self::$configDir)) {
            @unlink(self::$configDir . '/merchant_config.php');
            @unlink(self::$configDir . '/router.php');
            @rmdir(self::$configDir);
        }
    }

    private function get(string $page, string $query = ''): string
    {
        $body = file_get_contents(
            self::$baseUrl . $page . ($query !== '' ? '?' . $query : ''),
            false,
            stream_context_create(['http' => ['ignore_errors' => true, 'timeout' => 10]])
        );
        $this->assertIsString($body, "no response from $page");
        $this->assertStringNotContainsString('Fatal error', $body);
        $this->assertStringNotContainsString('Warning', $body);

        return $body;
    }

    public static function resultPageProvider(): array
    {
        return [
            'success page' => ['order_success.php'],
            'failed page'  => ['order_failed.php'],
        ];
    }

    /**
     * After 3DS the result page is loaded two (sandboxed) iframes deep. It must
     * navigate window.top: sandboxed frames may only navigate the top window,
     * so the old `window.parent.location = ...` breakout was blocked and the
     * result stayed inside the modal.
     *
     * @dataProvider resultPageProvider
     */
    public function testResultPageBreaksOutToTopWindow(string $page): void
    {
        $body = $this->get($page, 'merchant_reference=O-1');

        $this->assertStringContainsString('if (window.top !== window.self)', $body);
        $this->assertStringContainsString('window.top.location.href = window.location.href;', $body);
        $this->assertStringNotContainsString('window.parent.location =', $body);
    }

    /**
     * The response values are still shown.
     *
     * @dataProvider resultPageProvider
     */
    public function testResultPageShowsResponseParameters(string $page): void
    {
        $body = $this->get($page, http_build_query([
            'merchant_reference' => 'O-00001-12345',
            'amount'             => '1500',
            'currency'           => 'AED',
            'response_message'   => 'Success',
        ]));

        $this->assertStringContainsString('Order number: O-00001-12345', $body);
        $this->assertStringContainsString('1500 AED', $body);
        $this->assertStringContainsString('Response message: Success', $body);
    }

    public static function blockedPathProvider(): array
    {
        return [
            'debug log'             => ['/tmp/aps.log'],
            'merchant config'       => ['/merchant_config.php'],
            'default config'        => ['/merchant_config.default.php'],
            'other sample page'     => ['/credit_card_standard.php'],
            'directory listing'     => ['/'],
            'path traversal'        => ['/../composer.json'],
            'encoded traversal'     => ['/%2e%2e/composer.json'],
        ];
    }

    /**
     * The test server must not expose anything except the pages under test.
     *
     * @dataProvider blockedPathProvider
     */
    public function testServerOnlyServesPagesUnderTest(string $path): void
    {
        $body = file_get_contents(
            self::$baseUrl . ltrim($path, '/'),
            false,
            stream_context_create(['http' => ['ignore_errors' => true, 'timeout' => 10]])
        );

        $headers = function_exists('http_get_last_response_headers')
            ? (http_get_last_response_headers() ?? [])
            : ($http_response_header ?? []);

        $this->assertSame('Not found', $body);
        $this->assertStringContainsString('404', $headers[0] ?? '');
    }
}
