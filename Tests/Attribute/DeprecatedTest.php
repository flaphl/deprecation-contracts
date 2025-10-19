<?php

/**
 * This file is part of the Flaphl package.
 *
 * (c) Jade Phyressi <jade@flaphl.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaphl\Contracts\Deprecation\Tests\Attribute;

use Flaphl\Contracts\Deprecation\Attribute\Deprecated;
use Flaphl\Contracts\Deprecation\Enum\Lifecycle;
use Flaphl\Contracts\Deprecation\Enum\Severity;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionFunction;

class DeprecatedTest extends TestCase
{
    public function testDeprecatedAttributeCanBeInstantiated(): void
    {
        $attr = new Deprecated(
            id: 'TEST-001',
            deprecatedIn: '1.0',
            alternative: 'Use newMethod()',
            removalVersion: '2.0'
        );
        
        $this->assertSame('TEST-001', $attr->id);
        $this->assertSame('1.0', $attr->deprecatedIn);
        $this->assertSame('Use newMethod()', $attr->alternative);
        $this->assertSame('2.0', $attr->removalVersion);
    }

    public function testDeprecatedAttributeHasDefaultValues(): void
    {
        $attr = new Deprecated();
        
        $this->assertNull($attr->id);
        $this->assertNull($attr->deprecatedIn);
        $this->assertNull($attr->alternative);
        $this->assertNull($attr->removalVersion);
        $this->assertNull($attr->docsUrl);
        $this->assertSame(Severity::NOTICE, $attr->severity);
        $this->assertSame(Lifecycle::DEPRECATED, $attr->lifecycle);
        $this->assertNull($attr->createdAt);
        $this->assertSame([], $attr->context);
    }

    public function testDeprecatedAttributeAcceptsAllParameters(): void
    {
        $timestamp = time();
        $context = ['reason' => 'security', 'ticket' => 'JIRA-123'];
        
        $attr = new Deprecated(
            id: 'SEC-042',
            deprecatedIn: '2.5',
            alternative: 'Use secureMethod() instead',
            removalVersion: '3.0',
            docsUrl: 'https://docs.example.com/deprecations/sec-042',
            severity: Severity::ERROR,
            lifecycle: Lifecycle::SCHEDULED_FOR_REMOVAL,
            createdAt: $timestamp,
            context: $context
        );
        
        $this->assertSame('SEC-042', $attr->id);
        $this->assertSame('2.5', $attr->deprecatedIn);
        $this->assertSame('Use secureMethod() instead', $attr->alternative);
        $this->assertSame('3.0', $attr->removalVersion);
        $this->assertSame('https://docs.example.com/deprecations/sec-042', $attr->docsUrl);
        $this->assertSame(Severity::ERROR, $attr->severity);
        $this->assertSame(Lifecycle::SCHEDULED_FOR_REMOVAL, $attr->lifecycle);
        $this->assertSame($timestamp, $attr->createdAt);
        $this->assertSame($context, $attr->context);
    }

    public function testDeprecatedAttributeCanBeAppliedToFunction(): void
    {
        $reflection = new ReflectionFunction($this->getDeprecatedFunction(...));
        $attributes = $reflection->getAttributes(Deprecated::class);
        
        $this->assertCount(1, $attributes);
        
        $attr = $attributes[0]->newInstance();
        $this->assertInstanceOf(Deprecated::class, $attr);
        $this->assertSame('FUNC-001', $attr->id);
    }

    #[Deprecated(id: 'FUNC-001', deprecatedIn: '1.0')]
    private function getDeprecatedFunction(): void
    {
        // Intentionally empty
    }

    public function testDeprecatedAttributeCanBeAppliedToClass(): void
    {
        $reflection = new ReflectionClass(DeprecatedTestClass::class);
        $attributes = $reflection->getAttributes(Deprecated::class);
        
        $this->assertCount(1, $attributes);
        
        $attr = $attributes[0]->newInstance();
        $this->assertInstanceOf(Deprecated::class, $attr);
        $this->assertSame('CLASS-001', $attr->id);
        $this->assertSame(Severity::WARNING, $attr->severity);
    }

    public function testDeprecatedAttributeCanBeAppliedToMethod(): void
    {
        $reflection = new ReflectionClass(DeprecatedTestClass::class);
        $method = $reflection->getMethod('deprecatedMethod');
        $attributes = $method->getAttributes(Deprecated::class);
        
        $this->assertCount(1, $attributes);
        
        $attr = $attributes[0]->newInstance();
        $this->assertInstanceOf(Deprecated::class, $attr);
        $this->assertSame('METHOD-001', $attr->id);
    }

    public function testDeprecatedAttributeCanBeAppliedToProperty(): void
    {
        $reflection = new ReflectionClass(DeprecatedTestClass::class);
        $property = $reflection->getProperty('deprecatedProperty');
        $attributes = $property->getAttributes(Deprecated::class);
        
        $this->assertCount(1, $attributes);
        
        $attr = $attributes[0]->newInstance();
        $this->assertInstanceOf(Deprecated::class, $attr);
        $this->assertSame('PROP-001', $attr->id);
    }

    public function testDeprecatedAttributeCanBeAppliedToConstant(): void
    {
        $reflection = new ReflectionClass(DeprecatedTestClass::class);
        $constant = $reflection->getReflectionConstant('DEPRECATED_CONST');
        $attributes = $constant->getAttributes(Deprecated::class);
        
        $this->assertCount(1, $attributes);
        
        $attr = $attributes[0]->newInstance();
        $this->assertInstanceOf(Deprecated::class, $attr);
        $this->assertSame('CONST-001', $attr->id);
    }

    public function testDeprecatedAttributePropertiesAreReadonly(): void
    {
        $attr = new Deprecated(id: 'TEST-001');
        
        $reflection = new ReflectionClass($attr);
        $property = $reflection->getProperty('id');
        
        $this->assertTrue($property->isReadOnly());
    }

    public function testDeprecatedAttributeWithMinimalInfo(): void
    {
        $attr = new Deprecated(
            alternative: 'Use new API'
        );
        
        $this->assertNull($attr->id);
        $this->assertSame('Use new API', $attr->alternative);
        $this->assertSame(Severity::NOTICE, $attr->severity);
        $this->assertSame(Lifecycle::DEPRECATED, $attr->lifecycle);
    }

    public function testDeprecatedAttributeContextCanStoreArbitraryData(): void
    {
        $attr = new Deprecated(
            context: [
                'migrationGuide' => 'https://example.com/migration',
                'affectedVersions' => ['1.0', '1.1', '1.2'],
                'estimatedMigrationTime' => 30,
                'breaking' => true
            ]
        );
        
        $this->assertArrayHasKey('migrationGuide', $attr->context);
        $this->assertArrayHasKey('affectedVersions', $attr->context);
        $this->assertArrayHasKey('estimatedMigrationTime', $attr->context);
        $this->assertArrayHasKey('breaking', $attr->context);
        $this->assertTrue($attr->context['breaking']);
    }

    public function testDeprecatedAttributeCanExpressUrgency(): void
    {
        $urgent = new Deprecated(
            severity: Severity::ERROR,
            lifecycle: Lifecycle::SCHEDULED_FOR_REMOVAL,
            removalVersion: '2.0'
        );
        
        $this->assertSame(Severity::ERROR, $urgent->severity);
        $this->assertTrue($urgent->lifecycle->isRemovalImminent());
        $this->assertSame('2.0', $urgent->removalVersion);
    }
}

#[Deprecated(id: 'CLASS-001', severity: Severity::WARNING)]
class DeprecatedTestClass
{
    #[Deprecated(id: 'CONST-001')]
    public const DEPRECATED_CONST = 'old_value';
    
    #[Deprecated(id: 'PROP-001')]
    public string $deprecatedProperty = 'test';
    
    #[Deprecated(id: 'METHOD-001', alternative: 'Use newMethod()')]
    public function deprecatedMethod(): void
    {
        // Intentionally empty
    }
}
