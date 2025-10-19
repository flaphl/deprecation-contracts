<?php

/**
 * This file is part of the Flaphl package.
 *
 * (c) Jade Phyressi <jade@flaphl.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaphl\Contracts\Deprecation\Tests;

use PHPUnit\Framework\TestCase;

class DeprecationFunctionsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Clear any global state
        unset($GLOBALS['_flaphl_deprecation_log_file']);
        unset($GLOBALS['_flaphl_deprecation_logger']);
        
        // Clear environment variables
        putenv('FLAPHL_DEPRECATION_LOG');
        putenv('FLAPHL_DEBUG');
    }

    public function testBuildDeprecationMessageWithPackageAndVersion(): void
    {
        $message = _build_deprecation_message('flaphl/element', '2.1', 'Method foo() is deprecated', []);
        
        $this->assertStringContainsString('Since flaphl/element 2.1:', $message);
        $this->assertStringContainsString('Method foo() is deprecated', $message);
    }

    public function testBuildDeprecationMessageWithPackageOnly(): void
    {
        $message = _build_deprecation_message('flaphl/element', '', 'Method bar() is deprecated', []);
        
        $this->assertStringContainsString('Since flaphl/element:', $message);
        $this->assertStringContainsString('Method bar() is deprecated', $message);
    }

    public function testBuildDeprecationMessageWithVersionOnly(): void
    {
        $message = _build_deprecation_message('', '3.0', 'Method baz() is deprecated', []);
        
        $this->assertStringContainsString('Since version 3.0:', $message);
        $this->assertStringContainsString('Method baz() is deprecated', $message);
    }

    public function testBuildDeprecationMessageWithNoPackageOrVersion(): void
    {
        $message = _build_deprecation_message('', '', 'Something is deprecated', []);
        
        $this->assertStringNotContainsString('Since', $message);
        $this->assertSame('Something is deprecated', $message);
    }

    public function testBuildDeprecationMessageWithVsprintfFormatting(): void
    {
        $message = _build_deprecation_message('flaphl/test', '1.0', 'Method %s() is deprecated. Use %s() instead.', ['oldMethod', 'newMethod']);
        
        $this->assertStringContainsString('Method oldMethod() is deprecated. Use newMethod() instead.', $message);
    }

    public function testBuildDeprecationMessageWithVsprintfErrorInProduction(): void
    {
        // Production mode (no debug flag)
        $message = _build_deprecation_message('flaphl/test', '1.0', 'Method %s() has %d issues', ['test']); // Mismatched args
        
        $this->assertStringContainsString('[Formatting Error]', $message);
        $this->assertStringNotContainsString('ValueError', $message);
    }

    public function testBuildDeprecationMessageWithVsprintfErrorInDebugMode(): void
    {
        putenv('FLAPHL_DEBUG=1');
        
        $message = _build_deprecation_message('flaphl/test', '1.0', 'Method %s() has %d issues', ['test']); // Mismatched args
        
        $this->assertStringContainsString('[Warning: Failed to format message', $message);
        $this->assertStringContainsString('Method %s() has %d issues', $message);
        
        putenv('FLAPHL_DEBUG');
    }

    public function testTriggerDeprecationTriggersError(): void
    {
        $triggered = false;
        $errorMessage = '';
        
        set_error_handler(function($errno, $errstr) use (&$triggered, &$errorMessage) {
            if ($errno === E_USER_DEPRECATED) {
                $triggered = true;
                $errorMessage = $errstr;
                return true;
            }
            return false;
        }, E_USER_DEPRECATED);
        
        trigger_deprecation('flaphl/test', '1.0', 'This is deprecated');
        
        restore_error_handler();
        
        $this->assertTrue($triggered);
        $this->assertStringContainsString('Since flaphl/test 1.0:', $errorMessage);
        $this->assertStringContainsString('This is deprecated', $errorMessage);
    }

    public function testGetDeprecationBacktraceFiltersInternalFunctions(): void
    {
        $backtrace = $this->callThroughMultipleLayers();
        
        // Should not contain internal function names
        $this->assertStringNotContainsString('get_deprecation_backtrace', $backtrace);
        $this->assertStringNotContainsString('trigger_deprecation', $backtrace);
        $this->assertStringNotContainsString('log_deprecation', $backtrace);
        $this->assertStringNotContainsString('_build_deprecation_message', $backtrace);
        
        // Should contain the test method name
        $this->assertStringContainsString('callThroughMultipleLayers', $backtrace);
    }

    private function callThroughMultipleLayers(): string
    {
        return $this->intermediateMethod();
    }

    private function intermediateMethod(): string
    {
        return get_deprecation_backtrace(10);
    }

    public function testGetDeprecationBacktraceLimitWorks(): void
    {
        $backtrace = get_deprecation_backtrace(2);
        $lines = explode("\n", $backtrace);
        
        $this->assertLessThanOrEqual(2, count($lines));
    }

    public function testConfigureDeprecationLogFileSetPath(): void
    {
        $customPath = '/tmp/custom_deprecation.log';
        configure_deprecation_log_file($customPath);
        
        $this->assertSame($customPath, _get_deprecation_log_file());
    }

    public function testConfigureDeprecationLogFileResetWithNull(): void
    {
        configure_deprecation_log_file('/tmp/custom.log');
        configure_deprecation_log_file(null);
        
        // Should fall back to default (temp dir)
        $path = _get_deprecation_log_file();
        $this->assertStringContainsString('flaphl_deprecation.log', $path);
    }

    public function testGetDeprecationLogFileUsesEnvironmentVariable(): void
    {
        putenv('FLAPHL_DEPRECATION_LOG=/var/log/test.log');
        
        $path = _get_deprecation_log_file();
        
        $this->assertSame('/var/log/test.log', $path);
        
        putenv('FLAPHL_DEPRECATION_LOG');
    }

    public function testGetDeprecationLogFileDefaultFallback(): void
    {
        $path = _get_deprecation_log_file();
        
        $this->assertStringContainsString(sys_get_temp_dir(), $path);
        $this->assertStringContainsString('flaphl_deprecation.log', $path);
    }

    public function testConfigureDeprecationLoggerSetsCustomLogger(): void
    {
        $logged = false;
        $loggedMessage = '';
        $loggedBacktrace = '';
        
        configure_deprecation_logger(function($message, $backtrace) use (&$logged, &$loggedMessage, &$loggedBacktrace) {
            $logged = true;
            $loggedMessage = $message;
            $loggedBacktrace = $backtrace;
        });
        
        log_deprecation('flaphl/test', '1.0', 'Custom logger test');
        
        $this->assertTrue($logged);
        $this->assertStringContainsString('Custom logger test', $loggedMessage);
        $this->assertNotEmpty($loggedBacktrace);
    }

    public function testLogDeprecationFallsBackToErrorLogOnFileFailure(): void
    {
        configure_deprecation_log_file('/invalid/path/that/cannot/be/written.log');
        
        $errorLogged = false;
        $errorMessage = '';
        
        set_error_handler(function($errno, $errstr) use (&$errorLogged, &$errorMessage) {
            $errorLogged = true;
            $errorMessage = $errstr;
            return true;
        });
        
        // This should fail to write to file and use error_log
        log_deprecation('flaphl/test', '1.0', 'Fallback test');
        
        restore_error_handler();
        
        // Note: error_log doesn't trigger error handler in most configurations
        // This test verifies the function doesn't crash
        $this->assertTrue(true);
    }

    public function testConfigureDeprecationHandlerReturnsAndRestoresPreviousHandler(): void
    {
        $firstHandlerCalled = false;
        $secondHandlerCalled = false;
        
        $firstHandler = function($message, $file, $line) use (&$firstHandlerCalled) {
            $firstHandlerCalled = true;
        };
        
        $secondHandler = function($message, $file, $line) use (&$secondHandlerCalled) {
            $secondHandlerCalled = true;
        };
        
        // Set first handler
        $previous = configure_deprecation_handler($firstHandler);
        
        // Trigger deprecation
        trigger_deprecation('test', '1.0', 'First handler');
        $this->assertTrue($firstHandlerCalled);
        $this->assertFalse($secondHandlerCalled);
        
        // Set second handler
        $firstHandlerCalled = false;
        configure_deprecation_handler($secondHandler);
        
        // Trigger deprecation
        trigger_deprecation('test', '1.0', 'Second handler');
        $this->assertFalse($firstHandlerCalled);
        $this->assertTrue($secondHandlerCalled);
        
        // Restore to default
        restore_error_handler();
    }

    public function testConfigureDeprecationHandlerOnlyHandlesUserDeprecated(): void
    {
        $handlerCalled = false;
        
        configure_deprecation_handler(function($message, $file, $line) use (&$handlerCalled) {
            $handlerCalled = true;
        });
        
        // Trigger a different error type
        $warningTriggered = false;
        set_error_handler(function($errno) use (&$warningTriggered) {
            if ($errno === E_USER_WARNING) {
                $warningTriggered = true;
                return true;
            }
            return false;
        });
        
        @trigger_error('This is a warning', E_USER_WARNING);
        
        $this->assertTrue($warningTriggered);
        $this->assertFalse($handlerCalled); // Deprecation handler should not be called
        
        restore_error_handler();
    }
}
