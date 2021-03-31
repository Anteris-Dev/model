<?php

namespace Anteris\Tests\Model\Concerns;

use Anteris\Model\Concerns\GuardsAttributes;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @group traits
 * @group guard
 * @group attributes
 */
class GuardsAttributesTest extends TestCase
{
    public function testFillableManipulation()
    {
        /** @var GuardsAttributes $model */
        $model = $this->getObjectForTrait(GuardsAttributes::class);

        $this->assertEmpty($model->getFillable());

        $model->fillable(['name', 'email']);
        $this->assertTrue($model->isFillable('name'));
        $this->assertTrue($model->isFillable('email'));

        $this->assertSame(['name', 'email'], $model->getFillable());

        $model->mergeFillable(['password']);
        $this->assertTrue($model->isFillable('password'));

        $this->assertSame(['name', 'email', 'password'], $model->getFillable());

        $model->fillable([]);

        $this->assertEmpty($model->getFillable());
    }

    public function testGuardedManipulation()
    {
        /** @var GuardsAttributes $model */
        $model = $this->getObjectForTrait(GuardsAttributes::class);

        $this->assertEmpty($model->getGuarded());
        $this->assertTrue($model->isFillable('name'));
        $this->assertTrue($model->isFillable('email'));
        $this->assertTrue($model->isFillable('password'));

        $model->guard(['name', 'email']);

        $this->assertSame(['name', 'email'], $model->getGuarded());
        $this->assertTrue($model->isGuarded('name'));
        $this->assertFalse($model->isFillable('name'));
        $this->assertTrue($model->isGuarded('email'));
        $this->assertFalse($model->isFillable('email'));
        $this->assertFalse($model->isGuarded('password'));
        $this->assertTrue($model->isFillable('password'));

        $model->mergeGuarded(['password']);

        $this->assertSame(['name', 'email', 'password'], $model->getGuarded());
        $this->assertTrue($model->isGuarded('password'));

        $model->guard([]);

        $this->assertEmpty($model->getGuarded());
    }

    public function testUnguarding()
    {
        /** @var GuardsAttributes $model */
        $model = $this->getObjectForTrait(GuardsAttributes::class);

        $this->assertFalse($model->isUnguarded());

        $model->unguard();

        $this->assertTrue($model->isUnguarded());

        $model->reguard();

        $this->assertFalse($model->isUnguarded());
    }

    public function testUnguardingWithinCallback()
    {
        /** @var GuardsAttributes $model */
        $model = $this->getObjectForTrait(GuardsAttributes::class);
        $model->guard(['name']);

        $this->assertFalse($model->isUnguarded());
        $this->assertFalse($model->isFillable('name'));

        $model->unguarded(function () use ($model) {
            $this->assertTrue($model->isUnguarded());
            $this->assertTrue($model->isFillable('name'));
        });

        $this->assertFalse($model->isUnguarded());
    }

    public function testUnguardingWhenUnguarded()
    {
        /** @var GuardsAttributes $model */
        $model = $this->getObjectForTrait(GuardsAttributes::class);
        $model->unguard();

        $this->assertTrue($model->isUnguarded());

        $model->unguarded(function () use ($model) {
            $this->assertTrue($model->isUnguarded());
        });

        $this->assertTrue($model->isUnguarded());
    }

    public function testTotallyGuarded()
    {
        /** @var GuardsAttributes $model */
        $model = $this->getObjectForTrait(GuardsAttributes::class);

        $this->assertFalse($model->totallyGuarded());

        $model->guard(['*']);

        $this->assertTrue($model->totallyGuarded());

        $model->fillable(['name']);

        $this->assertFalse($model->totallyGuarded());
    }

    public function testFillableFromArray()
    {
        /** @var GuardsAttributes $model */
        $model = $this->getObjectForTrait(GuardsAttributes::class);

        // Start by setting our method to public.
        $reflection = new ReflectionClass($model);
        $method     = $reflection->getMethod('fillableFromArray');
        $method->setAccessible(true);

        // Test nothing guarded.
        $attributes = [
            'name'     => 'John Doe',
            'email'    => 'john.doe@anteris.com',
            'password' => 'Test123',
        ];

        $this->assertSame($attributes, $method->invoke($model, $attributes));

        // Test something guarded.
        $model->fillable(['email', 'password']);
        $model->guard(['name']);

        $this->assertSame([
            'email'    => 'john.doe@anteris.com',
            'password' => 'Test123',
        ], $method->invoke($model, $attributes));
    }
}
