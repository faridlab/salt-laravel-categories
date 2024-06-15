<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use SaltCategories\Controllers\CategoriesResourcesController;
use SaltCategories\Controllers\ProvincesResourcesController;
use SaltCategories\Controllers\NestedProvincesResourcesController;
use SaltCategories\Controllers\CitiesResourcesController;
use SaltCategories\Controllers\NestedCitiesResourcesController;
use SaltCategories\Controllers\DistrictsResourcesController;
use SaltCategories\Controllers\NestedDistrictsResourcesController;
use SaltCategories\Controllers\SubdistrictsResourcesController;
use SaltCategories\Controllers\NestedSubdistrictsResourcesController;
use SaltCategories\Controllers\PostalcodeResourcesController;

$version = config('app.API_VERSION', 'v1');

Route::middleware(['api'])
    ->prefix("api/{$version}")
    ->group(function () {

    // API: CATEGORIES RESOURCES
    Route::get("categories", [CategoriesResourcesController::class, 'index']); // get entire collection
    Route::post("categories", [CategoriesResourcesController::class, 'store'])->middleware(['auth:api']); // create new collection

    Route::get("categories/trash", [CategoriesResourcesController::class, 'trash'])->middleware(['auth:api']); // trash of collection

    Route::post("categories/import", [CategoriesResourcesController::class, 'import'])->middleware(['auth:api']); // import collection from external
    Route::post("categories/export", [CategoriesResourcesController::class, 'export'])->middleware(['auth:api']); // export entire collection
    Route::get("categories/report", [CategoriesResourcesController::class, 'report'])->middleware(['auth:api']); // report collection

    Route::get("categories/{id}/trashed", [CategoriesResourcesController::class, 'trashed'])->where('id', '[a-zA-Z0-9-]+')->middleware(['auth:api']); // get collection by ID from trash

    // RESTORE data by ID (id), selected IDs (selected), and All data (all)
    Route::post("categories/{id}/restore", [CategoriesResourcesController::class, 'restore'])->where('id', '[a-zA-Z0-9-]+')->middleware(['auth:api']); // restore collection by ID

    // DELETE data by ID (id), selected IDs (selected), and All data (all)
    Route::delete("categories/{id}/delete", [CategoriesResourcesController::class, 'delete'])->where('id', '[a-zA-Z0-9-]+')->middleware(['auth:api']); // hard delete collection by ID

    Route::get("categories/{id}", [CategoriesResourcesController::class, 'show'])->where('id', '[a-zA-Z0-9-]+'); // get collection by ID
    Route::put("categories/{id}", [CategoriesResourcesController::class, 'update'])->where('id', '[a-zA-Z0-9-]+')->middleware(['auth:api']); // update collection by ID
    Route::patch("categories/{id}", [CategoriesResourcesController::class, 'patch'])->where('id', '[a-zA-Z0-9-]+')->middleware(['auth:api']); // patch collection by ID
    // DESTROY data by ID (id), selected IDs (selected), and All data (all)
    Route::delete("categories/{id}", [CategoriesResourcesController::class, 'destroy'])->where('id', '[a-zA-Z0-9-]+')->middleware(['auth:api']); // soft delete a collection by ID

    Route::resource('categories.provinces', NestedProvincesResourcesController::class);

});
