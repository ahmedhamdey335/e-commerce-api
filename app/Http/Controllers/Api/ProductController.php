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

/**
 * @group Products
 *
 * Endpoints for browsing and managing products.
 */
class ProductController extends Controller
{
    /**
     * List products
     *
     * Returns a paginated list of products with filtering and sorting options.
     *
     * @unauthenticated
     */
    public function index(Request $request){
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
        return $this->success(ProductResource::collection($query->paginate(10)));
    }

    /**
     * Create product
     *
     * Creates a new product for the authenticated seller or admin. Requires seller or admin role.
     */
    public function store(StoreProductRequest $request){
        $this->authorize('create', Product::class);
        
        // Validation is already done by StoreProductRequest!
        $validatedData = $request->validated();
        $validatedData['slug'] = Str::slug($validatedData['name']);
        $validatedData['price'] = $validatedData['price'] * 100;
        $validatedData['user_id'] = auth()->id();

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
        return $this->success(new ProductResource($product), 'Product created successfully', 201);
    }

    /**
     * Show product
     *
     * Returns the details of a single product including its categories.
     *
     * @unauthenticated
     */
    public function show(Product $product){
        return $this->success(new ProductResource($product->load('categories')));
    }

    /**
     * Update product
     *
     * Updates an existing product owned by the authenticated seller or managed by an admin. Requires seller or admin role.
     */
    public function update(UpdateProductRequest $request, Product $product){
        $this->authorize('update', $product);

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
            $product->categories()->syncWithoutDetaching($categoryIds);
        }
        return $this->success(new ProductResource($product), 'Product updated successfully');
    }

    /**
     * Delete product
     *
     * Deletes a product owned by the authenticated seller or managed by an admin. Requires seller or admin role.
     */
    public function destroy(Product $product){
        $this->authorize('delete', $product);

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();
        return $this->success(null, 'Product deleted successfully', 204);
    }

    /**
     * Search products
     *
     * Searches products by query, category, and price range with pagination.
     *
     * @unauthenticated
     */
    public function search(Request $request) {
        $query = $request->input('q');
        $productsQuery = Product::query();

        if ($query) {
            $productsQuery->where(function($q) use ($query) {
                $q->where('name', 'like', '%' . $query . '%')
                ->orWhere('description', 'like', '%' . $query . '%');
            });
        }

        // Category
        if ($request->has('category_id')) {
            $productsQuery->whereHas('categories', function ($q) use ($request) {
                $q->where('categories.id', $request->input('category_id'));
            });
        }

        // Sorting
        if ($request->has('min_price')) {
            $productsQuery->where('price', '>=', $request->input('min_price') * 100);
        }
        if ($request->has('max_price')) {
            $productsQuery->where('price', '<=', $request->input('max_price') * 100);
        }

        return $this->success(ProductResource::collection($productsQuery->with('categories')->paginate(10)));
    }
}
