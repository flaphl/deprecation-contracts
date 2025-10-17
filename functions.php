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
        $prefix = ($package || $version) ? "Since $package $version: " : '';
        $formattedMessage = $args ? vsprintf($message, $args) : $message;
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
        @trigger_error(
            _build_deprecation_message($package, $version, $message, $args),
            \E_USER_DEPRECATED
        );
    }
}

if (!function_exists('get_deprecation_backtrace')) {
    /**
     * Retrieves a formatted backtrace for deprecation notices.
     *
     * @param int $limit The maximum number of stack frames to include.
     *
     * @return string The formatted backtrace.
     */
    function get_deprecation_backtrace(int $limit = 10): string
    {
        $trace = debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS, $limit);
        array_shift($trace); // Remove the call to this function
        array_shift($trace); // Remove the call to trigger_deprecation
        
        $backtrace = [];
        foreach ($trace as $i => $step) {
            $class = $step['class'] ?? '';
            $type = $step['type'] ?? '';
            $function = $step['function'] ?? '';
            $file = $step['file'] ?? 'n/a';
            $line = $step['line'] ?? 'n/a';
            $backtrace[] = sprintf('#%d %s%s%s() called at [%s:%d]', $i, $class, $type, $function, $file, $line);
        }
        
        return implode("\n", $backtrace);
    }
}


if (!function_exists('log_deprecation')) {
    /**
     * Logs a deprecation notice to a log file.
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
        $logFile = '/path/to/your/deprecation.log';
        $message = _build_deprecation_message($package, $version, $message, $args);
        $backtrace = get_deprecation_backtrace();
        
        $logEntry = sprintf(
            "[%s] DEPRECATION: %s\nBacktrace:\n%s\n\n",
            date('Y-m-d H:i:s'),
            $message,
            $backtrace
        );
        
        file_put_contents($logFile, $logEntry, \FILE_APPEND);
    }
}


if (!function_exists('configure_deprecation_handler')) {
    /**
     * Configures a custom deprecation handler.
     *
     * @param callable $handler The custom handler function.
     *
     * @return void
     */
    function configure_deprecation_handler(callable $handler): void
    {
        set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline) use ($handler): bool {
            if ($errno === \E_USER_DEPRECATED) {
                $handler($errstr, $errfile, $errline);
                return true;
            }
            return false;
        }, \E_USER_DEPRECATED);
    }
}
