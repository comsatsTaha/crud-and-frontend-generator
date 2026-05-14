<?php

namespace App\Generators;

use App\Models\Entity;
use Illuminate\Support\Str;

class RouteGenerator extends BaseGenerator
{
    public function generate(Entity $entity)
    {
        $controllerName = $this->buildClassName($entity->name) . 'Controller';
        $routeName = Str::kebab($this->buildCollectionName($entity->name));
        
        $webPath = base_path('routes/web.php');
        $webContents = file_get_contents($webPath);
        
        $useStatement = "use App\Http\Controllers\\{$controllerName};";
        
        if (strpos($webContents, $useStatement) === false) {
            $webContents = str_replace(
                "use App\Http\Controllers\BuilderController;",
                "use App\Http\Controllers\BuilderController;\n{$useStatement}",
                $webContents
            );
        }
        
        $bulkRoute = "Route::delete('{$routeName}/bulk-delete', [{$controllerName}::class, 'bulkDestroy'])->name('{$routeName}.bulk-destroy');";
        $resourceRoute = "Route::resource('{$routeName}', {$controllerName}::class);";

        $changed = false;

        // Check if resource route already exists
        $hasResource = strpos($webContents, "Route::resource('{$routeName}'") !== false;
        $hasBulk = strpos($webContents, "name('{$routeName}.bulk-destroy')") !== false;

        if (!$hasBulk) {
            if ($hasResource) {
                // If resource exists, insert bulk route ABOVE it
                $webContents = str_replace(
                    "Route::resource('{$routeName}'",
                    "{$bulkRoute}\n    Route::resource('{$routeName}'",
                    $webContents
                );
            } else {
                // If neither exists, insert both at the top of the group
                $searchString = "Route::middleware(['auth', 'verified'])->group(function () {";
                $webContents = str_replace(
                    $searchString,
                    $searchString . "\n    " . $bulkRoute . "\n    " . $resourceRoute,
                    $webContents
                );
                // Mark resource as "added" so we don't add it again below
                $hasResource = true;
            }
            $changed = true;
        }

        // Add Resource Route if still missing
        if (!$hasResource) {
            $searchString = "Route::middleware(['auth', 'verified'])->group(function () {";
            $webContents = str_replace(
                $searchString,
                $searchString . "\n    " . $resourceRoute,
                $webContents
            );
            $changed = true;
        }

        if ($changed) {
            file_put_contents($webPath, $webContents);
        }
    }
}
