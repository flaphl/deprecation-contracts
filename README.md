# Flaphl Deprecation Contracts

> **Graceful evolution through transparent communication**

Flaphl's deprecation system is designed around the philosophy of *evolution over abandonment*. Instead of breaking changes that leave developers stranded, we provide clear migration paths with comprehensive tooling for tracking and managing deprecated functionality.

## Philosophy

Deprecation in Flaphl isn't just about marking old code—it's about:
- **Transparency**: Every deprecation tells you exactly what's changing and why
- **Guidance**: Clear migration paths to modern alternatives
- **Control**: Full visibility into your deprecation landscape
- **Grace**: Reasonable timelines for migration planning

## Quick Start

```php
// Basic deprecation notice
trigger_deprecation('flaphl/element', '2.1', 'FridgeManager::coolDown() is deprecated. Use FridgeManager::chill() instead.');

// With dynamic content
trigger_deprecation('flaphl/fridge', '1.8', 'Method %s() will be removed in v3.0. Migrate to %s().', 'freeze', 'preserve');
```

## Available Tools

| Function | Purpose |
|----------|---------|
| `trigger_deprecation()` | Issues standardized deprecation notices |
| `log_deprecation()` | Persists deprecations with full context and backtraces |
| `get_deprecation_backtrace()` | Provides detailed call stack analysis |
| `configure_deprecation_handler()` | Customize how your app responds to deprecations |
| `configure_deprecation_log_file()` | Set custom log file path for deprecation logging |
| `configure_deprecation_logger()` | Integrate with PSR-3 or custom logging systems |

## Advanced Usage

### Custom Deprecation Logging

**File-based logging (default)**
```php
// Configure custom log file path
configure_deprecation_log_file('/var/log/app/deprecations.log');

// Or use environment variable
putenv('FLAPHL_DEPRECATION_LOG=/var/log/app/deprecations.log');

// Log deprecations to configured file
log_deprecation('flaphl/contracts', '2.0', 'Legacy contract %s is deprecated', 'PaymentInterface');
```

**Integration with PSR-3 Logger**
```php
use Psr\Log\LoggerInterface;

// Configure custom logger callable
configure_deprecation_logger(function(string $message, string $backtrace) use ($logger) {
    $logger->warning($message, ['backtrace' => $backtrace]);
});

// Now log_deprecation() will use your PSR-3 logger
log_deprecation('flaphl/element', '2.1', 'Deprecated method called');
```

**Custom logging backends**
```php
// Log to multiple destinations
configure_deprecation_logger(function($message, $backtrace) {
    // Send to monitoring service
    Sentry::captureMessage($message, ['extra' => ['backtrace' => $backtrace]]);
    
    // Also write to database
    DB::table('deprecations')->insert([
        'message' => $message,
        'backtrace' => $backtrace,
        'created_at' => now()
    ]);
});
```

### Backtrace Analysis
```php
// Get detailed context about deprecation usage
$trace = get_deprecation_backtrace(15); // Include up to 15 stack frames

// Backtrace automatically filters internal deprecation functions
// and handles edge cases gracefully
```

### Custom Handling
```php
// Route deprecations through your monitoring system
$previousHandler = configure_deprecation_handler(function($message, $file, $line) {
    YourMonitoring::trackDeprecation($message, $file, $line);
});

// Restore previous handler when needed
set_error_handler($previousHandler);
```

### Log File Configuration Priority

The log file path is determined in this order:
1. **Explicit configuration**: `configure_deprecation_log_file('/path/to/log')`
2. **Environment variable**: `FLAPHL_DEPRECATION_LOG=/path/to/log`
3. **Default fallback**: `sys_get_temp_dir() . '/flaphl_deprecation.log'`

Pass `null` to `configure_deprecation_log_file()` to reset to default behavior:
```php
configure_deprecation_log_file(null); // Resets to env var or temp dir
```

If file writing fails, the system gracefully falls back to PHP's `error_log()`.

### Debug Mode

Enable detailed error messages for deprecation formatting issues:
```php
// Set environment variable for verbose error details
putenv('FLAPHL_DEBUG=1');

// Without debug mode, formatting errors show: [Formatting Error]
// With debug mode, you get: [Warning: Failed to format message - ValueError details]
```

### Thread Safety

Log file writes use `LOCK_EX` to prevent race conditions when multiple processes write simultaneously.

## Migration Strategy

Flaphl deprecations follow a predictable lifecycle:
1. **Notice Phase**: Feature marked deprecated, alternative provided
2. **Migration Phase**: Documentation and tooling to assist transition  
3. **Warning Phase**: Increased visibility of deprecation notices
4. **Removal Phase**: Deprecated feature removed in next major version

## Development & Testing

This package includes comprehensive unit tests for all functionality:

```bash
# Install development dependencies
composer install

# Run tests
composer test

# Generate coverage report
composer test-coverage
```

### Test Coverage

The test suite includes:
- **Function tests**: All deprecation functions including `_build_deprecation_message`, `get_deprecation_backtrace`, `log_deprecation`, and handler configuration
- **Enum tests**: `Severity` and `Lifecycle` enums with all methods and edge cases
- **Attribute tests**: `Deprecated` attribute with all properties and target types
- **Integration tests**: Error handler restoration, file fallback, and backtrace filtering

Tests verify:
- Message formatting with vsprintf and error handling
- Backtrace filtering (internal function and file path filtering)
- Log file configuration priority (explicit → env var → default)
- Custom logger integration
- Error handler return value and restoration
- Thread safety with LOCK_EX
- Enum value validation and comparison
- Attribute application to all PHP elements

## Silencing Deprecations

For legacy applications that cannot immediately migrate:

```php
// Temporarily disable all deprecation notices (not recommended)
function trigger_deprecation() {}
```

> ⚠️ **Warning**: Silencing deprecations prevents you from preparing for future breaking changes.

