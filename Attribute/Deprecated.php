<?php

/**
 * This file is part of the Flaphl package.
 *
 * (c) Jade Phyressi <jade@flaphl.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaphl\Contracts\Deprecation\Attribute;

use Flaphl\Contracts\Deprecation\Enum\Lifecycle;
use Flaphl\Contracts\Deprecation\Enum\Severity;

/**
 * Marks deprecated code with comprehensive metadata.
 *
 * This attribute provides structured deprecation information that can be
 * extracted by static analysis tools, IDEs, and documentation generators.
 *
 * @example
 * ```php
 * #[Deprecated(
 *     id: 'CACHE-001',
 *     deprecatedIn: '2.1',
 *     alternative: 'Use Cache::get() instead',
 *     removalVersion: '3.0',
 *     severity: Severity::WARNING
 * )]
 * function old_cache_method() { }
 * ```
 */
#[\Attribute(\Attribute::TARGET_ALL)]
class Deprecated
{
    /**
     * @param string|null $id Unique deprecation identifier (e.g., 'CACHE-001')
     * @param string|null $deprecatedIn Version when deprecated
     * @param string|null $alternative Recommended replacement or migration path
     * @param string|null $removalVersion Version when this will be removed
     * @param string|null $docsUrl URL to detailed deprecation documentation
     * @param Severity $severity Impact level of this deprecation
     * @param Lifecycle $lifecycle Current stage in deprecation journey
     * @param int|null $createdAt Unix timestamp when deprecation was added
     * @param array<string, mixed> $context Additional context-specific metadata
     */
    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $deprecatedIn = null,
        public readonly ?string $alternative = null,
        public readonly ?string $removalVersion = null,
        public readonly ?string $docsUrl = null,
        public readonly Severity $severity = Severity::NOTICE,
        public readonly Lifecycle $lifecycle = Lifecycle::DEPRECATED,
        public readonly ?int $createdAt = null,
        public readonly array $context = [],
    ) {
    }
}
