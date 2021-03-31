<?php

namespace Anteris\Tests\Model\Concerns;

use Anteris\Model\Concerns\HidesAttributes;
use PHPUnit\Framework\TestCase;

/**
 * @group traits
 * @group hide
 */
class HidesAttributesTest extends TestCase
{
    /**
     * Sets up the test with an instance of the trait.
     *
     * @return HidesAttributes
     */
    protected function getTrait()
    {
        return $this->getObjectForTrait(HidesAttributes::class);
    }

    public function testHiddenManipulation()
    {
        $model = $this->getTrait();

        $this->assertEmpty($model->getHidden());

        $model->setHidden(['name', 'email']);

        $this->assertSame(['name', 'email'], $model->getHidden());
    }

    public function testVisibleManipulation()
    {
        $model = $this->getTrait();

        $this->assertEmpty($model->getVisible());

        $model->setVisible(['name', 'email']);

        $this->assertSame(['name', 'email'], $model->getVisible());
    }

    public function testMakeVisible()
    {
        $model = $this->getTrait();
        $model->setHidden(['name']);

        $this->assertSame(['name'], $model->getHidden());

        $model->makeVisible('name');

        $this->assertEmpty($model->getHidden());
        $this->assertEmpty($model->getVisible());

        $model->setVisible(['name']);

        $this->assertSame(['name'], $model->getVisible());

        $model->makeVisible(['email']);

        $this->assertSame(['name', 'email'], $model->getVisible());
    }

    public function testMakeVisibleIf()
    {
        $model = $this->getTrait();
        $model->setVisible(['address']);

        $model->makeVisibleIf(function () {
            return false;
        }, 'name');

        $this->assertSame(['address'], $model->getVisible());

        $model->makeVisibleIf(true, 'name');

        $this->assertSame(['address', 'name'], $model->getVisible());
    }

    public function testMakeHidden()
    {
        $model = $this->getTrait();

        $model->setHidden(['name']);

        $this->assertSame(['name'], $model->getHidden());

        $model->makeHidden(['email']);

        $this->assertSame(['name', 'email'], $model->getHidden());
    }

    public function testMakeHiddenIf()
    {
        $model = $this->getTrait();

        $model->makeHiddenIf(function () {
            return false;
        }, 'name');

        $this->assertSame([], $model->getHidden());

        $model->makeHiddenIf(true, 'name');

        $this->assertSame(['name'], $model->getHidden());
    }
}
