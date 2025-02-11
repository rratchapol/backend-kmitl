<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\CheckProduct;
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
    // public function index(Request $request)
    // {
    //     $columns = $request->input('columns', []);
    //     $length = $request->input('length', 10);
    //     $order = $request->input('order', []);
    //     $search = $request->input('search', []);
    //     $start = $request->input('start', 0);
    //     $page = ($start / $length) + 1;

    //     $col = ['id', 'product_name', 'product_qty', 'product_price', 'product_description', 'product_category', 'product_type', 'seller_id', 'date_exp', 'location', 'condition'];
    //     $orderby = ['id', 'product_name', 'product_qty', 'product_price' , 'product_description', 'product_category', 'product_type', 'seller_id', 'date_exp', 'location', 'condition'];

    //     $products = Product::select($col);

    //     if (isset($order[0]['column']) && isset($orderby[$order[0]['column']])) {
    //         $products->orderBy($orderby[$order[0]['column']], $order[0]['dir']);
    //     }

    //     if (!empty($search['value'])) {
    //         $products->where(function ($query) use ($search, $col) {
    //             foreach ($col as $c) {
    //                 $query->orWhere($c, 'like', '%' . $search['value'] . '%');
    //             }
    //         });
    //     }

    //     $d = $products->paginate($length, ['*'], 'page', $page);

    //     if ($d->isNotEmpty()) {
    //         $d->transform(function ($item, $key) use ($page, $length) {
    //             $item->No = ($page - 1) * $length + $key + 1;
    //             return $item;
    //         });
    //     }

    //     return response()->json([
    //         'status' => 'success',
    //         'message' => 'เรียกดูข้อมูลสำเร็จ',
    //         'data' => $d
    //     ]);
    // }


    public function index(Request $request)
{
    $columns = $request->input('columns', []);
    $length = $request->input('length', 10);
    $order = $request->input('order', []);
    $search = $request->input('search', []);
    $start = $request->input('start', 0);
    $page = ($start / $length) + 1;

    $col = ['id', 'product_name', 'product_images', 'product_qty', 'product_price', 'product_description', 'product_category', 'product_type', 'seller_id', 'date_exp', 'product_location', 'product_condition', 'product_years', 'product_defect', 'tag','status'];
    $orderby = ['id', 'product_name', 'product_images', 'product_qty', 'product_price', 'product_description', 'product_category', 'product_type', 'seller_id', 'date_exp', 'product_location', 'product_condition', 'product_years', 'product_defect', 'tag', 'status'];

    $products = Product::select($col);

    // กรองตาม column ที่ส่งมา
    if ($productstatus = $request->input('status', '')) {
        $products->where('status', 'like', "%$productstatus%");
    }

    if ($productType = $request->input('product_type', '')) {
        $products->where('product_type', 'like', "%$productType%");
    }

    if ($productCategory = $request->input('product_category', '')) {
        $products->where('product_category', 'like', "%$productCategory%");
    }

    if ($productCondition = $request->input('product_condition', '')) {
        $products->where('product_condition', 'like', "%$productCondition%");
    }

    if ($priceOrder = $request->input('price_order', '')) {
        // กรองราคาแบบจากน้อยไปมากหรือตามที่เลือก
        if ($priceOrder == 'asc') {
            $products->orderBy('product_price', 'asc'); // จากน้อยไปมาก
        } elseif ($priceOrder == 'desc') {
            $products->orderBy('product_price', 'desc'); // จากมากไปน้อย
        }
    }

    // การจัดเรียงข้อมูลตาม column ที่เลือก
    if (isset($order[0]['column']) && isset($orderby[$order[0]['column']])) {
        $products->orderBy($orderby[$order[0]['column']], $order[0]['dir']);
    }

    // การค้นหาสินค้าทั้งหมดตามคำค้น
    if (!empty($search['value'])) {
        $products->where(function ($query) use ($search, $col) {
            foreach ($col as $c) {
                $query->orWhere($c, 'like', '%' . $search['value'] . '%');
            }
        });
    }

    // การแสดงผลข้อมูลสินค้า
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
        // $product = Product::find($id);
        $product = Product::with('seller')->find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json($product);
    }


    public function look(string $seller_id)
    {
        // ดึงสินค้าทั้งหมดที่มี seller_id ตรงกับที่ระบุ
        $products = Product::where('seller_id', $seller_id)->get();
    
        // ตรวจสอบว่ามีสินค้าหรือไม่
        if ($products->isEmpty()) {
            return response()->json(['message' => 'No products found for this seller'], 404);
        }
    
        return response()->json($products);
    }


    // public function store(Request $request)
    // {
    //     $validated = $request->validate([
    //         'product_name' => 'required|string',
    //         'product_images' => 'nullable|array',
    //         'product_qty' => 'required|integer',
    //         'product_price' => 'required|numeric',
    //         'product_description' => 'nullable|string',
    //         'product_category' => 'required|string',
    //         'product_type' => 'required|string',
    //         'seller_id' => 'required|exists:users,id',
    //         'date_exp' => 'nullable|date',
    //         'product_location' => 'nullable|string',
    //         'product_condition' => 'required|string',
    //         'product_defect' => 'nullable|string',
    //         'product_years' => 'nullable|string',
    //         'tag' => 'required|string'
    //     ]);

    //     $product = Product::create($validated);
    //     return response()->json($product, 201);
    // }

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
            'product_location' => 'nullable|string',
            'product_condition' => 'required|string',
            'product_defect' => 'nullable|string',
            'product_years' => 'nullable|string',
            'tag' => 'required|string'
        ]);

        // 🔍 ดึงคำต้องห้ามทั้งหมดจากตาราง checkproducts
        $forbiddenWords = CheckProduct::pluck('word')->toArray();

        // 📝 รวมข้อความที่ต้องเช็ก
        $textToCheck = strtolower($validated['product_name'] . ' ' . ($validated['product_description'] ?? ''));

        // ✅ เช็กว่ามีคำต้องห้ามหรือไม่
        $status = 'ok'; // ค่าเริ่มต้น
        foreach ($forbiddenWords as $word) {
            if (str_contains($textToCheck, strtolower($word))) {
                $status = 'wait';
                break; // หยุดทันทีถ้าพบคำต้องห้าม
            }
        }

        // 🔄 กำหนดค่า status
        $validated['status'] = $status;

        // 📌 บันทึกข้อมูลลง database
        $product = Product::create($validated);

        return response()->json($product, 201);
    }


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
            'product_location' => 'nullable|string',
            'product_condition' => 'required|string',
            'product_defect' => 'nullable|string',
            'product_years' => 'nullable|string',
            'tag' => 'required|string',
            "status" => "required|string"
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
