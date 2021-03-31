<?php

namespace Anteris\Tests\Model;

use Anteris\Model\Exceptions\MassAssignmentException;
use Anteris\Tests\Model\Stubs\ModelStub;
use PHPUnit\Framework\TestCase;

/**
 * @group model
 */
class ModelTest extends TestCase
{
    public function testAttributeManipulation()
    {
        $model = new ModelStub;

        $model->project = 'Models';

        $this->assertSame('Models', $model->project);
        $this->assertTrue(isset($model->project));

        unset($model->project);

        $this->assertNull($model->project);
        $this->assertFalse(isset($model->project));
    }

    public function testOffsetInteraction()
    {
        $model = new ModelStub([
            0 => 'Hello',
        ]);

        $model['first_name'] = 'Aidan';

        $this->assertSame('Hello', $model[0]);
        $this->assertSame('Aidan', $model['first_name']);
        $this->assertTrue(isset($model[0]));
        $this->assertTrue(isset($model['first_name']));

        unset($model[0]);
        unset($model['first_name']);

        $this->assertFalse(isset($model[0]));
        $this->assertNull($model[0]);
        $this->assertFalse(isset($model['first_name']));
        $this->assertNull($model['first_name']);
    }

    public function testNewInstance()
    {
        $model = new ModelStub([
            'first_name' => 'Aidan',
            'project'    => 'Models',
        ]);

        $this->assertSame('Aidan', $model->first_name);
        $this->assertSame('Models', $model->project);

        $model2 = $model->newInstance();

        $this->assertNull($model2->first_name);
        $this->assertNull($model2->project);
    }

    public function testFillWhenTotallyGuarded()
    {
        $this->expectException(MassAssignmentException::class);
        $this->expectExceptionMessage(
            'Add [name] to the fillable property to allow mass assignment on [Anteris\Tests\Model\Stubs\ModelStub].'
        );

        $model = new ModelStub;
        $model->guard(['*']);

        $model->fill([
            'name' => 'Aidan',
        ]);
    }

    public function testForceFillWhenTotallyGuarded()
    {
        $model = new ModelStub;
        $model->guard(['*']);

        $this->assertEmpty($model->getAttributes());

        $model->forceFill([
            'name' => 'Aidan',
        ]);

        $this->assertSame(['name' => 'Aidan'], $model->getAttributes());
    }

    public function testToArray()
    {
        $model = new ModelStub([
            'first_name' => 'Aidan',
            'project'    => 'Models',
        ]);

        $array = $model->toArray();

        $this->assertIsArray($array);
        $this->assertSame([
            'first_name' => 'Aidan',
            'project'    => 'Models',
        ], $array);
    }

    public function testToJson()
    {
        $model = new ModelStub([
            'greeting' => 'Hello!',
            'farewell' => 'Goodbye!',
        ]);

        $json = $model->toJson(JSON_PRETTY_PRINT);

        $this->assertJson($json);
        $this->assertSame(
            <<<EOF
{
    "greeting": "Hello!",
    "farewell": "Goodbye!"
}
EOF,
            $json
        );
        // $this->assertSame('{"greeting":"Hello!","farewell":"Goodbye!"}', $json);
    }

    public function testToString()
    {
        $model = new ModelStub([
            'greeting' => 'Hello!',
            'farewell' => 'Goodbye!',
        ]);

        $string = $model->__toString();

        $this->assertIsString($string);
        $this->assertJson($string);
        $this->assertSame('{"greeting":"Hello!","farewell":"Goodbye!"}', $string);
    }
}
