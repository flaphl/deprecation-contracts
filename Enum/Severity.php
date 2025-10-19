<?php

/**
 * This file is part of the Flaphl package.
 *
 * (c) Jade Phyressi <jade@flaphl.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaphl\Contracts\Deprecation\Enum;

/**
 * Severity levels for deprecations.
 *
 * Defines the urgency and impact of deprecated functionality:
 * - NOTICE: Informational deprecation, no immediate action required
 * - WARNING: Deprecation that should be addressed soon
 * - ERROR: Critical deprecation requiring immediate attention
 */
enum Severity: int
{
    case NOTICE = 1;
    case WARNING = 2;
    case ERROR = 3;

    /**
     * Get a human-readable label for the severity level.
     */
    public function label(): string
    {
        return match($this) {
            self::NOTICE => 'Notice',
            self::WARNING => 'Warning',
            self::ERROR => 'Error',
        };
    }
}
