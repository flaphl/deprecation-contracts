<?php

/**
 * This file is part of the Flaphl package.
 *
 * (c) Jade Phyressi <jade@flaphl.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaphl\Contracts\Deprecation\Tests\Enum;

use Flaphl\Contracts\Deprecation\Enum\Lifecycle;
use PHPUnit\Framework\TestCase;

class LifecycleTest extends TestCase
{
    public function testLifecycleEnumHasCorrectCases(): void
    {
        $cases = Lifecycle::cases();
        
        $this->assertCount(3, $cases);
        $this->assertContains(Lifecycle::DEPRECATED, $cases);
        $this->assertContains(Lifecycle::SCHEDULED_FOR_REMOVAL, $cases);
        $this->assertContains(Lifecycle::REMOVED, $cases);
    }

    public function testLifecycleEnumHasCorrectValues(): void
    {
        $this->assertSame('deprecated', Lifecycle::DEPRECATED->value);
        $this->assertSame('scheduled_for_removal', Lifecycle::SCHEDULED_FOR_REMOVAL->value);
        $this->assertSame('removed', Lifecycle::REMOVED->value);
    }

    public function testLifecycleEnumCanBeCreatedFromValue(): void
    {
        $this->assertSame(Lifecycle::DEPRECATED, Lifecycle::from('deprecated'));
        $this->assertSame(Lifecycle::SCHEDULED_FOR_REMOVAL, Lifecycle::from('scheduled_for_removal'));
        $this->assertSame(Lifecycle::REMOVED, Lifecycle::from('removed'));
    }

    public function testLifecycleEnumTryFromReturnsNullForInvalidValue(): void
    {
        $this->assertNull(Lifecycle::tryFrom('invalid'));
        $this->assertNull(Lifecycle::tryFrom(''));
        $this->assertNull(Lifecycle::tryFrom('active'));
    }

    public function testIsRemovalImminentReturnsFalseForDeprecated(): void
    {
        $this->assertFalse(Lifecycle::DEPRECATED->isRemovalImminent());
    }

    public function testIsRemovalImminentReturnsTrueForScheduledForRemoval(): void
    {
        $this->assertTrue(Lifecycle::SCHEDULED_FOR_REMOVAL->isRemovalImminent());
    }

    public function testIsRemovalImminentReturnsTrueForRemoved(): void
    {
        $this->assertTrue(Lifecycle::REMOVED->isRemovalImminent());
    }

    public function testLifecycleEnumIsComparable(): void
    {
        $this->assertTrue(Lifecycle::DEPRECATED === Lifecycle::DEPRECATED);
        $this->assertFalse(Lifecycle::DEPRECATED === Lifecycle::REMOVED);
        $this->assertTrue(Lifecycle::SCHEDULED_FOR_REMOVAL !== Lifecycle::REMOVED);
    }

    public function testLifecycleEnumCanBeUsedInMatch(): void
    {
        $lifecycle = Lifecycle::SCHEDULED_FOR_REMOVAL;
        
        $result = match($lifecycle) {
            Lifecycle::DEPRECATED => 'warning',
            Lifecycle::SCHEDULED_FOR_REMOVAL => 'urgent',
            Lifecycle::REMOVED => 'error',
        };
        
        $this->assertSame('urgent', $result);
    }

    public function testLifecycleEnumCanBeUsedInSwitch(): void
    {
        $lifecycle = Lifecycle::REMOVED;
        $result = '';
        
        switch($lifecycle) {
            case Lifecycle::DEPRECATED:
                $result = 'still available';
                break;
            case Lifecycle::SCHEDULED_FOR_REMOVAL:
                $result = 'will be removed';
                break;
            case Lifecycle::REMOVED:
                $result = 'no longer available';
                break;
        }
        
        $this->assertSame('no longer available', $result);
    }

    public function testLifecycleEnumCanBeSerializedToJson(): void
    {
        $data = [
            'lifecycle' => Lifecycle::DEPRECATED,
        ];
        
        $json = json_encode($data);
        $decoded = json_decode($json, true);
        
        $this->assertSame('deprecated', $decoded['lifecycle']);
    }

    public function testLifecycleProgressionLogic(): void
    {
        $deprecated = Lifecycle::DEPRECATED;
        $scheduled = Lifecycle::SCHEDULED_FOR_REMOVAL;
        $removed = Lifecycle::REMOVED;
        
        // Test that progression makes sense
        $this->assertFalse($deprecated->isRemovalImminent());
        $this->assertTrue($scheduled->isRemovalImminent());
        $this->assertTrue($removed->isRemovalImminent());
        
        // Removed is most severe
        $this->assertTrue($removed->isRemovalImminent());
    }

    public function testLifecycleEnumInArray(): void
    {
        $lifecycles = [
            Lifecycle::DEPRECATED,
            Lifecycle::SCHEDULED_FOR_REMOVAL,
            Lifecycle::REMOVED,
        ];
        
        $this->assertCount(3, $lifecycles);
        $this->assertContains(Lifecycle::DEPRECATED, $lifecycles);
        $this->assertTrue(in_array(Lifecycle::REMOVED, $lifecycles, true));
    }
}
