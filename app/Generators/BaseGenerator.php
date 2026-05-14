<?php

namespace App\Generators;

use App\Models\Entity;
use Illuminate\Support\Str;

abstract class BaseGenerator
{
    protected function getStub($type)
    {
        return file_get_contents(base_path("stubs/crud/{$type}.stub"));
    }

    protected function buildClassName($name)
    {
        return Str::studly(Str::singular($name));
    }
    
    protected function buildVariableName($name)
    {
        return Str::camel(Str::singular($name));
    }

    protected function buildCollectionName($name)
    {
        return Str::camel(Str::plural($name));
    }

    abstract public function generate(Entity $entity);
}
