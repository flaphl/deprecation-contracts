# Changelog

All notable changes to the Flaphl Deprecation Contracts package will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.1.0] - 2025-10-17

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

## [Unreleased]

### Planned
- Additional logging backends (Monolog, PSR-3 compatible loggers)
- Integration helpers for popular PHP frameworks
- Performance optimizations for high-volume applications
- Extended backtrace filtering options

