<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return CategoryResource::collection(Category::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request)
    {
        $validatedData = $request->validated();
        $validatedData['slug'] = Str::slug($validatedData['name']);
        $category = Category::create($validatedData);
        return new CategoryResource($category);
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        return new CategoryResource($category->load('products'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $validatedData = $request->validated();
        
        if (isset($validatedData['name'])) {
            $validatedData['slug'] = Str::slug($validatedData['name']);
        }
        $category->update($validatedData);
        return new CategoryResource($category);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        $category->delete();
        return response()->noContent();
    }
}
