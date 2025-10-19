# Changelog

All notable changes to the Flaphl Deprecation Contracts package will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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

