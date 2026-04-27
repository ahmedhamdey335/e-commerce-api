<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use Illuminate\Http\Request;

/**
 * @group Categories
 *
 * Endpoints for browsing and managing product categories.
 */
class CategoryController extends Controller
{
    /**
     * List categories
     *
     * Returns all available product categories.
     *
     * @unauthenticated
     */
    public function index()
    {
        return $this->success(CategoryResource::collection(Category::all()));
    }

    /**
     * Create category
     *
     * Creates a new product category. Requires admin role.
     */
    public function store(StoreCategoryRequest $request)
    {
        $validatedData = $request->validated();
        $validatedData['slug'] = Str::slug($validatedData['name']);
        $category = Category::create($validatedData);
        return $this->success(new CategoryResource($category), 'Category created successfully', 201);
    }

    /**
     * Show category
     *
     * Returns a category with its related products.
     *
     * @unauthenticated
     */
    public function show(Category $category)
    {
        return $this->success(new CategoryResource($category->load('products')));
    }

    /**
     * Update category
     *
     * Updates an existing product category. Requires admin role.
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $validatedData = $request->validated();
        
        if (isset($validatedData['name'])) {
            $validatedData['slug'] = Str::slug($validatedData['name']);
        }
        $category->update($validatedData);
        return $this->success(new CategoryResource($category), 'Category updated successfully');
    }

    /**
     * Delete category
     *
     * Deletes a product category. Requires admin role.
     */
    public function destroy(Category $category)
    {
        $category->delete();
        return $this->success(null, 'Category deleted successfully', 204);
    }
}
