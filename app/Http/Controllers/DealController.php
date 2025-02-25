<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Deal;
use OpenApi\Annotations as OA;
use App\Models\Product;
use App\Models\Recommend;
use Carbon\Carbon;

class DealController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/deals",
     *     summary="Get a list of deals",
     *     tags={"Deals"},
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=400, description="Invalid request")
     * )
     */

     public function Deal()
     {
         return response()->json(Deal::all());
     }

     public function updateDealStatus()
    {
        // คำนวณวันที่ 7 วันก่อนหน้า
        $sevenDaysAgo = Carbon::now()->subDays(7);
        // $sevenDaysAgo = Carbon::now()->subMinutes(3);

        // อัปเดตสถานะของดีลที่เป็น "waiting" และเก่ากว่า 7 วัน
        $updatedCount = Deal::where('status', 'waiting')
            ->where('created_at', '<', $sevenDaysAgo)
            ->update(['status' => 'fail']);

        return response()->json([
            'message' => "อัปเดตสถานะสำเร็จ",
            'updated_count' => $updatedCount
        ]);
}

    public function index(Request $request)
    {
        $length = $request->input('length', 10);
        $start = $request->input('start', 0);
        $page = ($start / $length) + 1;
        $search = $request->input('search', []);
        $order = $request->input('order', []);

        $deals = Deal::with(['buyer', 'product']);

        if (!empty($search['value'])) {
            $deals->where(function ($query) use ($search) {
                $query->whereHas('buyer', function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search['value'] . '%');
                })
                ->orWhereHas('product', function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search['value'] . '%');
                });
            });
        }

        if (isset($order[0]['column'])) {
            // Assuming we want to order by the first column for simplicity
            $deals->orderBy('id', $order[0]['dir']);
        }

        $paginatedDeals = $deals->paginate($length, ['*'], 'page', $page);

        return response()->json([
            'status' => 'success',
            'message' => 'เรียกดูข้อมูลสำเร็จ',
            'data' => $paginatedDeals
        ]);
    }


    // public function show($id)
    // {
    //     $deal = Deal::with(['buyer', 'product'])->findOrFail($id);
    //     return response()->json($deal);
    // }
    public function show($id)
    {
        $deal = Deal::with(['buyer', 'product.seller']) // ดึง product พร้อม seller
                    ->findOrFail($id);
    
        return response()->json($deal);
    }

    public function look($id)
    {
        // ค้นหาโพสต์ที่ตรงกับ buyer_id พร้อมข้อมูล product และ seller
        $posts = Deal::with(['buyer','product.seller']) // ดึงข้อมูล seller ด้วย
                     ->where('buyer_id', $id)
                     ->get();
    
        // ถ้าไม่พบโพสต์
        if ($posts->isEmpty()) {
            return response()->json(['message' => 'No deals found for this user'], 404);
        }
    
        return response()->json($posts);
    }
    
    public function lookBySeller($sellerId)
    {
        // ค้นหาโพสต์ที่มีสินค้าที่ตรงกับ seller_id
        $posts = Deal::with(['buyer', 'product.seller'])
                    ->whereHas('product', function ($query) use ($sellerId) {
                        $query->where('seller_id', $sellerId);
                    })
                    ->get();

        if ($posts->isEmpty()) {
            return response()->json(['message' => 'No deals found for this seller'], 404);
        }

        return response()->json($posts);
}



    // public function store(Request $request)
    // {
    //     $validatedData = $request->validate([
    //         'buyer_id' => 'required|exists:users,id',
    //         'product_id' => 'required|exists:products,id',
    //         'qty' => 'nullable|integer',
    //         'deal_date' => 'required|date',
    //         'status' => 'required|string'
    //     ]);

    //     $deal = Deal::create($validatedData);
    //     return response()->json($deal, 201);
    // }

    public function store(Request $request)
{
    $validatedData = $request->validate([
        'buyer_id' => 'required|exists:users,id',
        'product_id' => 'required|exists:products,id',
        'qty' => 'nullable|integer',
        'deal_date' => 'required|date',
        'status' => 'required|string'
    ]);

    // ตรวจสอบว่ามีดีลที่รออยู่หรือไม่
    $existingDeal = Deal::where('buyer_id', $validatedData['buyer_id'])
                        ->where('product_id', $validatedData['product_id'])
                        ->where('status', 'waiting')
                        ->exists();
                        



    if ($existingDeal) {
        return response()->json([
            'message' => 'คุณมีดีลที่กำลังรอดำเนินการอยู่ ไม่สามารถซื้อซ้ำได้'
        ], 200);
    }

    // สร้างดีลใหม่
    $deal = Deal::create($validatedData);
    return response()->json($deal, 201);
}



    public function update(Request $request, $id)
{
    $validatedData = $request->validate([
        'buyer_id' => 'required|exists:users,id',
        'product_id' => 'required|exists:products,id',
        'qty' => 'required|integer',
        'deal_date' => 'required|date',
        'status' => 'required|string'
    ]);

    $deal = Deal::findOrFail($id);

    // ดึงข้อมูลสินค้า
    $product = Product::findOrFail($validatedData['product_id']);

    // คำนวณ qty ที่เปลี่ยนแปลง
    $oldQty = $deal->qty; // qty ก่อนหน้าใน deal
    $newQty = $validatedData['qty']; // qty ใหม่ที่ได้รับ
    $qtyDifference = $newQty - $oldQty;

    // ตรวจสอบว่า product_qty เพียงพอสำหรับการปรับปรุง
    if ($product->product_qty - $qtyDifference < 0) {
        return response()->json(['error' => 'Not enough product quantity available.'], 400);
    }

    // อัปเดต product_qty
    $product->product_qty -= $qtyDifference;
    $product->save();

    // อัปเดตข้อมูล deal
    $deal->update($validatedData);

        // ✅ **เพิ่มสินค้าเข้า recommend ถ้า status เป็น "ok"**
        if ($validatedData['status'] === 'ok') {
            Recommend::updateOrCreate(
                [
                    'user_id' => $validatedData['buyer_id'],
                    'product_id' => $validatedData['product_id']
                ],

            );
        }

    return response()->json($deal);
}


    /**
     * @OA\Delete(
     *     path="/api/deals/{id}",
     *     summary="Delete a specific deal",
     *     tags={"Deals"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=204, description="Deal deleted successfully"),
     *     @OA\Response(response=404, description="Deal not found")
     * )
     */
    public function destroy($id)
    {
        $deal = Deal::findOrFail($id);
        $deal->delete();

        return response()->json(['message' => 'Deal deleted successfully']);
    }
}
