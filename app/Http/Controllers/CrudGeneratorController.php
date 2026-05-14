<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Entity;

class CrudGeneratorController extends Controller
{
    public function generate(Entity $entity, \App\Services\CrudGeneratorService $service)
    {
        $service->generate($entity);
        return redirect()->back()->with('success', 'CRUD generated successfully!');
    }
}
