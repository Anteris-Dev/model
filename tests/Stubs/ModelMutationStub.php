<?php

namespace Anteris\Tests\Model\Stubs;

use Anteris\Model\Model;

class ModelMutationStub extends Model
{
    public function getNameAttribute($name)
    {
        return "Mr. $name";
    }

    public function setGreetingAttribute($greeting)
    {
        $this->attributes['greeting'] = 'Oh! ' . $greeting;
    }
}
