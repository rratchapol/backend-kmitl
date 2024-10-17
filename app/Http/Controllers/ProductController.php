<?php

namespace App\Http\Controllers;

use App\Models\Product;
use OpenApi\Annotations as OA;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/products",
     *     summary="Get a list of products",
     *     tags={"Products"},
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=400, description="Invalid request")
     * )
     */
    public function index(Request $request)
    {
        $columns = $request->input('columns', []);
        $length = $request->input('length', 10);
        $order = $request->input('order', []);
        $search = $request->input('search', []);
        $start = $request->input('start', 0);
        $page = ($start / $length) + 1;

        $col = ['id', 'product_name', 'product_qty', 'product_price', 'product_description', 'product_category', 'product_type', 'seller_id', 'date_exp', 'location', 'condition'];
        $orderby = ['id', 'product_name', 'product_qty', 'product_price' , 'product_description', 'product_category', 'product_type', 'seller_id', 'date_exp', 'location', 'condition'];

        $products = Product::select($col);

        if (isset($order[0]['column']) && isset($orderby[$order[0]['column']])) {
            $products->orderBy($orderby[$order[0]['column']], $order[0]['dir']);
        }

        if (!empty($search['value'])) {
            $products->where(function ($query) use ($search, $col) {
                foreach ($col as $c) {
                    $query->orWhere($c, 'like', '%' . $search['value'] . '%');
                }
            });
        }

        $d = $products->paginate($length, ['*'], 'page', $page);

        if ($d->isNotEmpty()) {
            $d->transform(function ($item, $key) use ($page, $length) {
                $item->No = ($page - 1) * $length + $key + 1;
                return $item;
            });
        }

        return response()->json([
            'status' => 'success',
            'message' => 'เรียกดูข้อมูลสำเร็จ',
            'data' => $d
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/products/{id}",
     *     summary="Get a specific product",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=404, description="Product not found")
     * )
     */
    public function show(string $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json($product);
    }

    /**
     * @OA\Post(
     *     path="/api/products",
     *     summary="Create a new product",
     *     tags={"Products"},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="product_name", type="string"),
     *             @OA\Property(property="product_images", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="product_qty", type="integer"),
     *             @OA\Property(property="product_price", type="number"),
     *             @OA\Property(property="product_description", type="string"),
     *             @OA\Property(property="product_category", type="string"),
     *             @OA\Property(property="product_type", type="string"),
     *             @OA\Property(property="seller_id", type="integer"),
     *             @OA\Property(property="date_exp", type="string", format="date"),
     *             @OA\Property(property="location", type="string"),
     *             @OA\Property(property="condition", type="string")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Product created successfully"),
     *     @OA\Response(response=400, description="Invalid input")
     * )
     */
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
        return response()->json($product, 201);
    }

    /**
     * @OA\Put(
     *     path="/api/products/{id}",
     *     summary="Update a specific product",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="product_name", type="string"),
     *             @OA\Property(property="product_images", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="product_qty", type="integer"),
     *             @OA\Property(property="product_price", type="number"),
     *             @OA\Property(property="product_description", type="string"),
     *             @OA\Property(property="product_category", type="string"),
     *             @OA\Property(property="product_type", type="string"),
     *             @OA\Property(property="seller_id", type="integer"),
     *             @OA\Property(property="date_exp", type="string", format="date"),
     *             @OA\Property(property="location", type="string"),
     *             @OA\Property(property="condition", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Product updated successfully"),
     *     @OA\Response(response=404, description="Product not found"),
     *     @OA\Response(response=400, description="Invalid input")
     * )
     */
    public function update(Request $request, string $id)
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

    /**
     * @OA\Delete(
     *     path="/api/products/{id}",
     *     summary="Delete a specific product",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="Product deleted successfully"),
     *     @OA\Response(response=404, description="Product not found")
     * )
     */
    public function destroy(string $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $product->delete();
        return response()->json(['message' => 'Product deleted']);
    }
}
