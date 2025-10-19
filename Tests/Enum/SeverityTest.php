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

use Flaphl\Contracts\Deprecation\Enum\Severity;
use PHPUnit\Framework\TestCase;

class SeverityTest extends TestCase
{
    public function testSeverityEnumHasCorrectCases(): void
    {
        $cases = Severity::cases();
        
        $this->assertCount(3, $cases);
        $this->assertContains(Severity::NOTICE, $cases);
        $this->assertContains(Severity::WARNING, $cases);
        $this->assertContains(Severity::ERROR, $cases);
    }

    public function testSeverityEnumHasCorrectValues(): void
    {
        $this->assertSame(1, Severity::NOTICE->value);
        $this->assertSame(2, Severity::WARNING->value);
        $this->assertSame(3, Severity::ERROR->value);
    }

    public function testSeverityEnumCanBeCreatedFromValue(): void
    {
        $this->assertSame(Severity::NOTICE, Severity::from(1));
        $this->assertSame(Severity::WARNING, Severity::from(2));
        $this->assertSame(Severity::ERROR, Severity::from(3));
    }

    public function testSeverityEnumTryFromReturnsNullForInvalidValue(): void
    {
        $this->assertNull(Severity::tryFrom(99));
        $this->assertNull(Severity::tryFrom(0));
        $this->assertNull(Severity::tryFrom(-1));
    }

    public function testSeverityLabelReturnsCorrectStrings(): void
    {
        $this->assertSame('Notice', Severity::NOTICE->label());
        $this->assertSame('Warning', Severity::WARNING->label());
        $this->assertSame('Error', Severity::ERROR->label());
    }

    public function testSeverityEnumIsComparable(): void
    {
        $this->assertTrue(Severity::NOTICE === Severity::NOTICE);
        $this->assertFalse(Severity::NOTICE === Severity::WARNING);
        $this->assertTrue(Severity::ERROR !== Severity::WARNING);
    }

    public function testSeverityEnumCanBeUsedInMatch(): void
    {
        $severity = Severity::WARNING;
        
        $result = match($severity) {
            Severity::NOTICE => 'low',
            Severity::WARNING => 'medium',
            Severity::ERROR => 'high',
        };
        
        $this->assertSame('medium', $result);
    }

    public function testSeverityEnumCanBeUsedInSwitch(): void
    {
        $severity = Severity::ERROR;
        $result = '';
        
        switch($severity) {
            case Severity::NOTICE:
                $result = 'info';
                break;
            case Severity::WARNING:
                $result = 'warn';
                break;
            case Severity::ERROR:
                $result = 'error';
                break;
        }
        
        $this->assertSame('error', $result);
    }

    public function testSeverityEnumCanBeSerializedToJson(): void
    {
        $data = [
            'severity' => Severity::WARNING,
        ];
        
        $json = json_encode($data);
        $decoded = json_decode($json, true);
        
        $this->assertSame(2, $decoded['severity']);
    }

    public function testSeverityEnumValuesAreOrdered(): void
    {
        $this->assertTrue(Severity::NOTICE->value < Severity::WARNING->value);
        $this->assertTrue(Severity::WARNING->value < Severity::ERROR->value);
        $this->assertTrue(Severity::NOTICE->value < Severity::ERROR->value);
    }
}
