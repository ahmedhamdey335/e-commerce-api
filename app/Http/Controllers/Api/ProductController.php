<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Http\Resources\ProductResource;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Product::query();
        $query->with('categories');

        // search filter (partial match)
        $query->when($request->search, function (Builder $q, $search) {
            $q->where('name', 'like', "%{$search}%");
        });

        // filter by category
        $query->when($request->category, function (Builder $q, $slug) {
            $q->whereHas('categories', function (Builder $q) use ($slug) {
                $q->where('slug', $slug);
            });
        });

        // price range filter
        $query->when($request->min_price, function (Builder $q, $min ) {
            $q->where('price', '>=', $min * 100);
        });
        $query->when($request->max_price, function (Builder $q, $max ) {
            $q->where('price', '<=', $max * 100);
        });

        // sorting
        if ($request->sort === 'price_asc') {
            $query->orderBy('price', 'asc');
        } elseif ($request->sort === 'price_desc') {
            $query->orderBy('price', 'desc');
        } else {
            $query->latest();
        }

        // pagination
        return ProductResource::collection($query->paginate(10));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        // Validation is already done by StoreProductRequest!
        $validatedData = $request->validated();
        $validatedData['slug'] = Str::slug($validatedData['name']);
        $validatedData['price'] = $validatedData['price'] * 100;

        // Handle image upload
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $validatedData['image'] = $path;
        }

        // Handle categories separately
        $categoryIds = $request->input('categories');
        $productData = \Illuminate\Support\Arr::except($validatedData, ['categories']);

        $product = Product::create($productData);

        if ($categoryIds) {
            $product->categories()->attach($categoryIds);
        }

        return new ProductResource($product);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return new ProductResource($product->load('categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $validatedData = $request->validated();
        
        if (isset($validatedData['name'])) {
            $validatedData['slug'] = Str::slug($validatedData['name']);
        }
        if (isset($validatedData['price'])) {
            $validatedData['price'] = $validatedData['price'] * 100;
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $path = $request->file('image')->store('products', 'public');
            $validatedData['image'] = $path;
        }

        // Handle categories separately
        $categoryIds = $request->input('categories');
        $productData = \Illuminate\Support\Arr::except($validatedData, ['categories']);

        $product->update($productData);

        if ($request->has('categories')) {
            $product->categories()->sync($categoryIds);
        }
        return new ProductResource($product);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();
        return response()->noContent();
    }
}
