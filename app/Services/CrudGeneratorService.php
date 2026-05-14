<?php

namespace App\Services;

use App\Models\Entity;
use App\Generators\MigrationGenerator;
use App\Generators\ModelGenerator;
use App\Generators\ControllerGenerator;
use App\Generators\RequestGenerator;
use App\Generators\VueGenerator;
use App\Generators\RouteGenerator;
use App\Generators\FactoryGenerator;

class CrudGeneratorService
{
    public function generate(Entity $entity)
    {
        // 1. Generate Migration
        (new MigrationGenerator())->generate($entity);

        // 2. Generate Model
        (new ModelGenerator())->generate($entity);

        // 3. Generate Controller
        (new ControllerGenerator())->generate($entity);

        // 4. Generate Form Requests
        (new RequestGenerator())->generate($entity);

        // 5. Generate Vue Pages
        (new VueGenerator())->generate($entity);

        // 6. Append Routes
        (new RouteGenerator())->generate($entity);
        (new FactoryGenerator())->generate($entity);

        
        
        // 7. Mark as generated
        $entity->update(['is_generated' => true]);
        
        // Let's run migrations automatically
        \Artisan::call('migrate', ['--force' => true]);
    }
}
