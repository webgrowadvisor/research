<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Seller;
use App\Models\Category;
use App\Models\ProductVariant;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    
    // Show all products
    public function index()
    {
        $products = Product::with(['seller', 'variants'])->latest()->paginate(10);
        return view('admin.products.product', compact('products'));
    }

    // Show form to create product
    public function create()
    {
        $sellers = Seller::all();
        $categories = Category::whereNull('parent_id')->with('children')->get();
        return view('admin.products.add-product', compact('sellers', 'categories'));
    }

    // Store product
    public function store(Request $request)
    {
        $validated = $request->validate([
            'seller_id'   => 'required|exists:sellers,id',
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'base_price'  => 'required|numeric',
            'status'      => 'required|boolean',
            'brand'    => 'required|string',
            'product_specail'    => 'nullable',
            'thumbnail'   => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'variants.*.sku'     => 'required|string',
            'variants.*.price'   => 'required|numeric',
            'variants.*.stock'   => 'required|integer',
            'variants.*.options' => 'nullable|string',
            'variant_images.*'   => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Upload thumbnail if present
        $thumbnailPath = null;
        if ($request->hasFile('thumbnail')) {
            $paths = uploadWebp($request->file('thumbnail'), 'product_thumbnails');
            $thumbnailPath = $paths['webp']; // or use 'original' if you want
        }

        $product = Product::create([
            'seller_id'   => $validated['seller_id'] ?? '',
            'category_id'   => $validated['category_id'] ?? '',
            'name'        => $validated['name'] ?? '',
            'description' => $validated['description'] ?? null,
            'base_price'  => $validated['base_price'] ?? '',
            'status'      => $validated['status'] ?? '',
            'brand'      => $validated['brand'] ?? '',
            'product_specail' => $validated['product_specail'] ?? '',
            'thumbnail'   => $thumbnailPath,
        ]);

        foreach ($request->variants as $i => $variant) {
            
            $imagePath = null;

            if ($request->hasFile('variant_images') && isset($request->variant_images[$i])) {
                $imagePath = uploadWebp($request->variant_images[$i], 'variant_images');
            }
            
            $product->variants()->create([
                'sku'     => $variant['sku'] ?? rand(999,9999),
                'price'   => $variant['price'] ?? '',
                'stock'   => $variant['stock'] ?? '1',
                'options' => $variant['options'] ?? null,
                'image'   => $imagePath['original'] ?? '',
                'webp'   => $imagePath['webp'] ?? '',
            ]);
        }

        return redirect()->route('ad.products.index')->with('success_msg', 'Product and variants created successfully.');
    }

    // Delete product
    public function destroy($id)
    {
        $product = Product::with('variants')->findOrFail($id);

        // Delete product thumbnail if it exists
        if ($product->thumbnail && Storage::disk('public')->exists($product->thumbnail)) {
            Storage::disk('public')->delete($product->thumbnail);
        }

        // Loop through variants and delete images
        foreach ($product->variants as $variant) {
            if ($variant->image && Storage::disk('public')->exists($variant->image)) {
                Storage::disk('public')->delete($variant->image);
            }

            if ($variant->webp && Storage::disk('public')->exists($variant->webp)) {
                Storage::disk('public')->delete($variant->webp);
            }
        }

        // Delete variants from DB
        $product->variants()->delete();

        // Delete product from DB
        $product->delete();

        return redirect()->back()->with('success_msg', 'Product and images deleted successfully.');
    }

    public function edit($id)
    {
        $sellers = Seller::all();
        $categories = Category::whereNull('parent_id')->with('children')->get();
        $product = Product::with('variants')->findOrFail($id);
        return view('admin.products.product-edit', compact('product', 'sellers', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'seller_id'   => 'required|exists:sellers,id',
            'category_id' => 'required|exists:categories,id',
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_price'  => 'required|numeric',
            'status'      => 'required|boolean',
            'brand'    => 'required|string',
            'product_specail'    => 'nullable',
            'thumbnail'   => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'variants.*.sku'     => 'required|string',
            'variants.*.price'   => 'required|numeric',
            'variants.*.stock'   => 'required|integer',
            'variants.*.options' => 'nullable|string',
            'variant_images.*'   => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $product = Product::findOrFail($id);

        // Handle thumbnail update
        if ($request->hasFile('thumbnail')) {
            $paths = uploadWebp($request->file('thumbnail'), 'product_thumbnails');
            $product->thumbnail = $paths['webp'];
        }

        $product->update([
            'seller_id'   => $validated['seller_id'],
            'category_id'   => $validated['category_id'],
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
            'base_price'  => $validated['base_price'],
            'status'      => $validated['status'],
            'brand'      => $validated['brand'],
            'product_specail' => $validated['product_specail'],
        ]);

        // Step 1: Get old variants (you must have loaded them before this)
        $oldVariants = $product->variants;

        // Delete old variants and recreate
        $product->variants()->delete();

        foreach ($request->variants as $i => $variant) {
            
            $imagePath = [
                'original' => '',
                'webp' => '',
            ];

            // Check if image uploaded for this variant
            if ($request->hasFile('variant_images') && isset($request->variant_images[$i])) {

                // Delete old image if exists for this index
                if (isset($oldVariants[$i])) {
                    $old = $oldVariants[$i];

                    if ($old->image && Storage::disk('public')->exists($old->image)) {
                        Storage::disk('public')->delete($old->image);
                    }
                    if ($old->webp && Storage::disk('public')->exists($old->webp)) {
                        Storage::disk('public')->delete($old->webp);
                    }
                }

                // Upload new image
                $imagePath = uploadWebp($request->variant_images[$i], 'variant_images');
            } else {
                // No new image uploaded — reuse old image paths if available
                if (isset($oldVariants[$i])) {
                    $imagePath['original'] = $oldVariants[$i]->image;
                    $imagePath['webp'] = $oldVariants[$i]->webp;
                }
            }

            $product->variants()->create([
                'sku'     => $variant['sku'],
                'price'   => $variant['price'],
                'stock'   => $variant['stock'],
                'options' => $variant['options'] ?? null,
                'image'   => $imagePath['original'] ?? '',
                'webp'    => $imagePath['webp'] ?? '',
            ]);
        }

        return redirect()->route('ad.products.index')->with('success_msg', 'Product updated successfully.');
    }

    public function Status(Request $request, $id)
    {
        $user = Product::findOrFail($id);
        $user->status = $request->status;
        $user->save();

        return response()->json(['success_msg' => 'Status updated successfully.']);
    }

}
