<?php

/**
 * This file is part of the Flaphl package.
 * 
 * (c) Jade Phyressi <jade@flaphl.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


if (!function_exists('trigger_deprecation')) {

    /**
     * Builds a deprecation message.
     *
     * @param string $package The package name.
     * @param string $version The version number.
     * @param string $message The deprecation message.
     * @param array $args The arguments to format the message.
     *
     * @return string The formatted deprecation message.
     */
    function _build_deprecation_message(string $package, string $version, string $message, array $args): string
    {
        // Build prefix only if we have meaningful package/version info
        $prefix = '';
        if ($package && $version) {
            $prefix = "Since $package $version: ";
        } elseif ($package) {
            $prefix = "Since $package: ";
        } elseif ($version) {
            $prefix = "Since version $version: ";
        }
        
        // Safely format message with vsprintf, falling back to raw message on error
        $formattedMessage = $message;
        if ($args) {
            try {
                $formattedMessage = vsprintf($message, $args);
            } catch (\ValueError $e) {
                // If formatting fails (mismatched placeholders/args), use raw message
                // In debug mode (via env var), include detailed error; otherwise use generic marker
                $debugMode = getenv('FLAPHL_DEBUG') !== false;
                $errorDetail = $debugMode ? ' [Warning: Failed to format message - ' . $e->getMessage() . ']' : ' [Formatting Error]';
                $formattedMessage = $message . $errorDetail;
            }
        }
        
        return $prefix . $formattedMessage;
    }

    /**
     * Triggers a deprecation notice.
     *
     * @param string $package The package name.
     * @param string $version The version number.
     * @param string $message The deprecation message.
     * @param mixed ...$args The arguments to format the message.
     *
     * @return void
     */

    function trigger_deprecation(string $package, string $version, string $message, mixed ...$args): void
    {
        trigger_error(
            _build_deprecation_message($package, $version, $message, $args),
            \E_USER_DEPRECATED
        );
    }
}

if (!function_exists('get_deprecation_backtrace')) {
    /**
     * Retrieves a formatted backtrace for deprecation notices.
     *
     * Filters out internal deprecation functions from the backtrace to show
     * only the relevant application call stack.
     *
     * @param int $limit The maximum number of stack frames to include.
     *
     * @return string The formatted backtrace.
     */
    function get_deprecation_backtrace(int $limit = 10): string
    {
        $trace = debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS, $limit + 5);
        
        // Filter out internal deprecation functions by name and file path
        $internalFunctions = [
            'get_deprecation_backtrace',
            'trigger_deprecation',
            'log_deprecation',
            '_build_deprecation_message',
            '_get_deprecation_log_file'
        ];
        
        $filteredTrace = [];
        foreach ($trace as $step) {
            $function = $step['function'] ?? '';
            $file = $step['file'] ?? '';
            
            // Skip if function name matches internal functions
            if (in_array($function, $internalFunctions, true)) {
                continue;
            }
            
            // Skip if file path contains deprecation-contracts (optional stricter filtering)
            if ($file && strpos($file, '/deprecation-contracts/functions.php') !== false) {
                continue;
            }
            
            $filteredTrace[] = $step;
        }
        
        // Limit the filtered trace
        $filteredTrace = array_slice($filteredTrace, 0, $limit);
        
        $backtrace = [];
        foreach ($filteredTrace as $i => $step) {
            $class = $step['class'] ?? '';
            $type = $step['type'] ?? '';
            $function = $step['function'] ?? '';
            $file = $step['file'] ?? 'unknown';
            $line = $step['line'] ?? 0;
            
            // Use %s for both file and line to handle 'unknown' gracefully
            $location = ($file !== 'unknown' && $line > 0) 
                ? sprintf('%s:%d', $file, $line)
                : $file;
            
            $backtrace[] = sprintf(
                '#%d %s%s%s() called at [%s]',
                $i,
                $class,
                $type,
                $function,
                $location
            );
        }
        
        return implode("\n", $backtrace);
    }
}


if (!function_exists('configure_deprecation_log_file')) {
    /**
     * Configures the log file path for deprecation logging.
     *
     * Pass null to reset to default behavior (environment variable or temp dir fallback).
     *
     * @param string|null $path The path to the log file, or null to reset to defaults.
     *
     * @return void
     */
    function configure_deprecation_log_file(?string $path): void
    {
        // Note: Passing null explicitly clears the configuration and falls back to
        // environment variable or temp directory default
        $GLOBALS['_flaphl_deprecation_log_file'] = $path;
    }
}

if (!function_exists('_get_deprecation_log_file')) {
    /**
     * Gets the configured deprecation log file path.
     *
     * Priority order:
     * 1. Explicitly configured via configure_deprecation_log_file()
     * 2. FLAPHL_DEPRECATION_LOG environment variable
     * 3. System temp directory + deprecation.log
     *
     * @return string The log file path.
     */
    function _get_deprecation_log_file(): string
    {
        // Check explicit configuration
        if (isset($GLOBALS['_flaphl_deprecation_log_file']) && $GLOBALS['_flaphl_deprecation_log_file']) {
            return $GLOBALS['_flaphl_deprecation_log_file'];
        }
        
        // Check environment variable
        $envPath = getenv('FLAPHL_DEPRECATION_LOG');
        if ($envPath !== false && $envPath !== '') {
            return $envPath;
        }
        
        // Default to temp directory
        return sys_get_temp_dir() . \DIRECTORY_SEPARATOR . 'flaphl_deprecation.log';
    }
}

if (!function_exists('configure_deprecation_logger')) {
    /**
     * Configures a custom logger callable for deprecation logging.
     *
     * The callable should accept: (string $message, string $backtrace)
     *
     * @param callable|null $logger The logger callable, or null to use default file logging.
     *
     * @return void
     */
    function configure_deprecation_logger(?callable $logger): void
    {
        $GLOBALS['_flaphl_deprecation_logger'] = $logger;
    }
}

if (!function_exists('log_deprecation')) {
    /**
     * Logs a deprecation notice with full backtrace.
     *
     * By default, logs to a file. Can be customized via configure_deprecation_logger()
     * to integrate with PSR-3 loggers or other logging systems.
     *
     * @param string $package The package name.
     * @param string $version The version number.
     * @param string $message The deprecation message.
     * @param mixed ...$args The arguments to format the message.
     *
     * @return void
     */
    function log_deprecation(string $package, string $version, string $message, mixed ...$args): void
    {
        $message = _build_deprecation_message($package, $version, $message, $args);
        $backtrace = get_deprecation_backtrace();
        
        // Use custom logger if configured
        if (isset($GLOBALS['_flaphl_deprecation_logger']) && is_callable($GLOBALS['_flaphl_deprecation_logger'])) {
            $GLOBALS['_flaphl_deprecation_logger']($message, $backtrace);
            return;
        }
        
        // Default file logging
        $logFile = _get_deprecation_log_file();
        $logEntry = sprintf(
            "[%s] DEPRECATION: %s\nBacktrace:\n%s\n\n",
            date('Y-m-d H:i:s'),
            $message,
            $backtrace
        );
        
        // Use LOCK_EX to avoid race conditions when multiple processes write simultaneously
        // Suppress warnings if file cannot be written (graceful degradation)
        // Fall back to error_log if file writing fails
        if (@file_put_contents($logFile, $logEntry, \FILE_APPEND | \LOCK_EX) === false) {
            error_log("DEPRECATION: $message");
        }
    }
}


if (!function_exists('configure_deprecation_handler')) {
    /**
     * Configures a custom deprecation handler.
     *
     * The handler will be called for all E_USER_DEPRECATED errors.
     * Returns the previous error handler so it can be restored later.
     *
     * Note: The return value may be a callable, a string (internal handler), or null.
     * Treat it opaquely and restore using set_error_handler($previous) if needed.
     *
     * @param callable $handler The custom handler function with signature:
     *                          function(string $errstr, string $errfile, int $errline): void
     *
     * @return callable|string|null The previous error handler (may be callable, string, or null).
     */
    function configure_deprecation_handler(callable $handler): callable|string|null
    {
        return set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline) use ($handler): bool {
            if ($errno === \E_USER_DEPRECATED) {
                $handler($errstr, $errfile, $errline);
                return true;
            }
            return false;
        }, \E_USER_DEPRECATED);
    }
}
