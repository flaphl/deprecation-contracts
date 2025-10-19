# Changelog

All notable changes to the Flaphl Deprecation Contracts package will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.4.0] - 2025-10-19

### Added
- Comprehensive unit test suite with 53 tests and 143 assertions
- PHPUnit 10.5 as dev dependency
- Tests for `_build_deprecation_message()` with all formatting scenarios
- Tests for `get_deprecation_backtrace()` including file path filtering
- Tests for `log_deprecation()` fallback behavior
- Tests for `configure_deprecation_handler()` return value and restoration
- Tests for `Severity` enum with all cases and methods
- Tests for `Lifecycle` enum including `isRemovalImminent()` logic
- Tests for `Deprecated` attribute with all properties and target types
- Integration tests for error handling, file writing, and backtrace filtering
- Composer test scripts: `composer test` and `composer test-coverage`
- Development section in README with testing instructions

### Technical
- PSR-4 autoload-dev configuration for test namespace
- PHPUnit XML configuration with strict settings
- Test coverage for vsprintf error handling (debug vs production)
- Test coverage for environment variable configuration
- Test coverage for thread safety with LOCK_EX
- Test coverage for enum serialization and comparison
- Test coverage for attribute application to functions, classes, methods, properties, and constants

## [2.3.1] - 2025-10-19

### Added
- `FLAPHL_DEBUG` environment variable for verbose error details in formatting failures
- `LOCK_EX` flag to `file_put_contents()` for thread-safe concurrent writes
- Additional file path filtering in `get_deprecation_backtrace()` for stricter internal function removal
- Documentation about null resetting behavior in `configure_deprecation_log_file()`

### Changed
- Return type of `configure_deprecation_handler()` changed from `?callable` to `callable|string|null` for accuracy
- vsprintf error messages now show generic `[Formatting Error]` marker in production; detailed errors only with `FLAPHL_DEBUG=1`
- Enhanced backtrace filtering to also check file paths, not just function names

### Improved
- Thread safety: File writes now use `LOCK_EX` to prevent race conditions
- Debug ergonomics: Less noisy error messages in production logs
- Documentation: Added notes about opaque return type handling for error handlers
- Documentation: Clarified that passing `null` to `configure_deprecation_log_file()` resets to defaults

### Technical
- Added `_get_deprecation_log_file` to internal function filter list
- File path filtering includes check for `/deprecation-contracts/functions.php`
- Formatting error verbosity controlled by `FLAPHL_DEBUG` environment variable

## [2.3.0] - 2025-10-19

### Added
- `configure_deprecation_log_file()` function to set custom log file path
- `configure_deprecation_logger()` function for PSR-3 logger integration
- `_get_deprecation_log_file()` internal helper for log path resolution
- Environment variable support: `FLAPHL_DEPRECATION_LOG` for log file configuration
- Graceful fallback to `error_log()` when file writing fails

### Changed
- **BREAKING (minor)**: `configure_deprecation_handler()` now returns previous error handler
- Removed `@` silence operator from `trigger_error()` call in `trigger_deprecation()`
- Improved `_build_deprecation_message()` with safer prefix construction
- Enhanced `get_deprecation_backtrace()` to filter by function name instead of array shifting
- Added `ValueError` exception handling in `vsprintf()` calls for safer message formatting
- Improved backtrace output format to handle missing file/line information gracefully
- Log file path now follows priority: explicit config → env var → temp dir

### Fixed
- Fixed hard-coded log path issue in `log_deprecation()` - now configurable
- Fixed fragile backtrace trimming that could remove incorrect frames
- Fixed potential `%d` format issues with 'n/a' values in backtrace output
- Fixed empty package/version producing malformed deprecation prefixes
- Fixed unhandled `vsprintf()` errors when placeholder count mismatches arguments
- Fixed missing return value from `configure_deprecation_handler()`

### Technical
- Backtrace filtering now uses function name matching for reliability
- Message formatting errors are caught and reported inline
- File logging has error suppression with `error_log()` fallback
- Global state used for logger/log file configuration (`$GLOBALS['_flaphl_*']`)

## [2.2.0] - 2025-01-20

### Added
- `Severity` enum with three levels: NOTICE (1), WARNING (2), ERROR (3)
- `Lifecycle` enum with states: DEPRECATED, SCHEDULED_FOR_REMOVAL, REMOVED
- `Deprecated` attribute for marking deprecated code with comprehensive metadata
- `Severity::label()` method for human-readable severity levels
- `Lifecycle::isRemovalImminent()` method to check if removal is scheduled or complete
- PSR-4 autoloading support for namespace classes

### Technical
- Added comprehensive metadata fields to `Deprecated` attribute:
  - `id`: Unique deprecation identifier
  - `deprecatedIn`: Version when deprecated
  - `alternative`: Recommended replacement
  - `removalVersion`: Planned removal version
  - `docsUrl`: Link to detailed documentation
  - `severity`: Impact level (Severity enum)
  - `lifecycle`: Current stage (Lifecycle enum)
  - `createdAt`: Unix timestamp
  - `context`: Additional metadata array
- Enums utilize PHP 8.2+ backed enum features
- Attribute can be applied to all targets (TARGET_ALL)

## [1.0.0] - 2025-10-17

### Added
- Initial stable release of Flaphl Deprecation Contracts
- `trigger_deprecation()` function for standardized deprecation notices
- `log_deprecation()` function for persistent logging with full backtraces
- `get_deprecation_backtrace()` function for detailed call stack analysis
- `configure_deprecation_handler()` function for custom deprecation handling
- `_build_deprecation_message()` internal helper function for message formatting
- Comprehensive README with philosophy and usage examples
- MIT License for open source distribution
- Composer package configuration with PHP 8.2+ requirement
- Autoloading configuration for seamless function availability

### Documentation
- Detailed README with Flaphl-specific deprecation philosophy
- Code examples for all available functions
- Migration strategy documentation
- Advanced usage patterns and best practices
- Function reference table with clear descriptions

### Technical
- PHP 8.2+ compatibility with modern type declarations
- Proper error handling with `E_USER_DEPRECATED` notices
- Configurable backtrace depth for performance optimization
- Support for `vsprintf()` formatting in deprecation messages
- Graceful degradation when functions are redefined

