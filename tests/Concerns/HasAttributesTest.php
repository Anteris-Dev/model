<?php

namespace Anteris\Tests\Model\Concerns;

use Anteris\Model\Concerns\HasAttributes;
use Anteris\Tests\Model\Stubs\ModelMutationStub;
use Anteris\Tests\Model\Stubs\ModelStub;
use PHPUnit\Framework\TestCase;

/**
 * @group traits
 * @group attributes
 */
class HasAttributesTest extends TestCase
{
    /**
     * Sets up the test with an instance of the trait.
     *
     * @return HasAttributes
     */
    protected function getTrait()
    {
        return $this->getObjectForTrait(HasAttributes::class);
    }

    public function testAttributeManipulation()
    {
        $model = $this->getTrait();

        $this->assertNull($model->getAttribute('title'));

        $model->setAttribute('title', 'Mademoiselle');

        $this->assertSame('Mademoiselle', $model->getAttribute('title'));
        $this->assertSame(['title' => 'Mademoiselle'], $model->getAttributes());
    }

    public function testAttributeMutation()
    {
        $model = new ModelMutationStub;

        $model->setAttribute('greeting', 'Hello there!');

        $this->assertSame('Oh! Hello there!', $model->getAttribute('greeting'));

        $model->setAttribute('name', 'Aidan');

        $this->assertSame('Mr. Aidan', $model->getAttribute('name'));
    }

    public function testSetRawAttributesBypassesMutation()
    {
        $model = new ModelMutationStub;

        $model->setRawAttributes([
            'greeting' => 'Hello there!',
        ]);

        $this->assertNotSame('Oh! Hello there!', $model->getAttribute('greeting'));
        $this->assertSame('Hello there!', $model->getAttribute('greeting'));
    }

    public function testOnly()
    {
        $model = $this->getTrait();

        $model->setAttribute('name', 'Aidan');
        $model->setAttribute('occupation', 'Lead Developer');
        $model->setAttribute('location', 'VS Code');

        $this->assertSame(['name' => 'Aidan', 'location' => 'VS Code'], $model->only('name', 'location'));
        $this->assertSame(['name' => 'Aidan', 'occupation' => 'Lead Developer'], $model->only(['name', 'occupation']));
    }

    public function testAttributesToArrayReturnsVisibleAttributes()
    {
        $model = new ModelStub;
        $model->setVisible(['name', 'email']);

        $model->setAttribute('name', 'Aidan');
        $model->setAttribute('email', 'aidan@example.com');
        $model->setAttribute('password', 'Test123');

        $this->assertSame(
            ['name' => 'Aidan', 'email' => 'aidan@example.com'],
            $model->attributesToArray()
        );
    }

    public function testKeepingTrackOfChanges()
    {
        $model = $this->getTrait();

        $this->assertFalse($model->hasChanges([]));

        $model->setRawAttributes([
            'name'       => 'Aidan',
            'occupation' => 'Lead Developer',
            'project'    => 'models',
        ], true);

        $model->syncChanges();

        // Make sure it knows when we are clean.
        $this->assertTrue($model->isClean(['name', 'occupation', 'project']));
        $this->assertTrue($model->isClean('name', 'occupation'));
        $this->assertTrue($model->isClean('name'));
        $this->assertFalse($model->isDirty(['name', 'occupation', 'project']));
        $this->assertFalse($model->isDirty('name', 'occupation'));
        $this->assertFalse($model->isDirty('name'));

        $this->assertFalse($model->wasChanged(['name', 'occupation', 'project']));

        // Make sure it knows when we are dirty.
        $model->setAttribute('name', 'Bob');
        $model->syncChanges();

        $this->assertTrue($model->isDirty('name'));
        $this->assertFalse($model->isClean('name'));
        $this->assertNotSame($model->getOriginal('name'), $model->getAttribute('name'));
        $this->assertNotSame($model->getOriginal(), $model->getAttributes());
        $this->assertTrue($model->isClean('occupation', 'project'));
        $this->assertFalse($model->isDirty(['occupation', 'project']));
    }

    public function testAttributesToArrayDoesNotReturnHiddenAttributes()
    {
        $model = new ModelStub;
        $model->setHidden(['password']);

        $model->setAttribute('name', 'Aidan');
        $model->setAttribute('email', 'aidan@example.com');
        $model->setAttribute('password', 'Test123');

        $this->assertSame(
            ['name' => 'Aidan', 'email' => 'aidan@example.com'],
            $model->attributesToArray()
        );
    }
}
