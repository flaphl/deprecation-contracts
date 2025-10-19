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
 * Lifecycle stages for deprecated functionality.
 *
 * Tracks the deprecation journey from initial notice to removal:
 * - DEPRECATED: Functionality is deprecated but still available
 * - SCHEDULED_FOR_REMOVAL: Removal date/version has been announced
 * - REMOVED: Functionality has been removed from the codebase
 */
enum Lifecycle: string
{
    case DEPRECATED = 'deprecated';
    case SCHEDULED_FOR_REMOVAL = 'scheduled_for_removal';
    case REMOVED = 'removed';

    /**
     * Check if removal is imminent or has occurred.
     */
    public function isRemovalImminent(): bool
    {
        return $this === self::SCHEDULED_FOR_REMOVAL || $this === self::REMOVED;
    }
}
