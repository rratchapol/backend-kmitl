<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        return Product::all(); // แสดงสินค้าทั้งหมด
    }

    public function show($id)
    {
        $product = Product::find($id);
        if ($product) {
            return $product;
        }
        return response()->json(['message' => 'Product not found'], 404);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_name' => 'required|string',
            'product_images' => 'nullable|array',
            'product_qty' => 'required|integer',
            'product_price' => 'required|numeric',
            'product_description' => 'nullable|string',
            'product_category' => 'required|string',
            'product_type' => 'required|string',
            'seller_id' => 'required|exists:users,id',
            'date_exp' => 'nullable|date',
            'location' => 'nullable|string',
            'condition' => 'required|string',
        ]);

        $product = Product::create($validated);
        return response()->json($product, 201); // ส่งกลับข้อมูลสินค้าที่เพิ่มใหม่
    }

    public function update(Request $request, $id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $validated = $request->validate([
            'product_name' => 'required|string',
            'product_images' => 'nullable|array',
            'product_qty' => 'required|integer',
            'product_price' => 'required|numeric',
            'product_description' => 'nullable|string',
            'product_category' => 'required|string',
            'product_type' => 'required|string',
            'seller_id' => 'required|exists:users,id',
            'date_exp' => 'nullable|date',
            'location' => 'nullable|string',
            'condition' => 'required|string',
        ]);

        $product->update($validated);
        return response()->json($product);
    }

    public function destroy($id)
    {
        $product = Product::find($id);
        if ($product) {
            $product->delete();
            return response()->json(['message' => 'Product deleted']);
        }
        return response()->json(['message' => 'Product not found'], 404);
    }
}
