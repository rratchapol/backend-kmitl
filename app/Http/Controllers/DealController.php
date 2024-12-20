<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Deal;
use OpenApi\Annotations as OA;

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

    /**
     * @OA\Get(
     *     path="/api/deals/{id}",
     *     summary="Get a specific deal",
     *     tags={"Deals"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=404, description="Deal not found")
     * )
     */
    public function show($id)
    {
        $deal = Deal::with(['buyer', 'product'])->findOrFail($id);
        return response()->json($deal);
    }

    /**
     * @OA\Post(
     *     path="/api/deals",
     *     summary="Create a new deal",
     *     tags={"Deals"},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="buyer_id", type="integer"),
     *             @OA\Property(property="product_id", type="integer"),
     *             @OA\Property(property="qty", type="integer"),
     *             @OA\Property(property="deal_date", type="string", format="date"),
     *             @OA\Property(property="status", type="string")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Deal created successfully"),
     *     @OA\Response(response=400, description="Invalid input")
     * )
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'buyer_id' => 'required|exists:users,id',
            'product_id' => 'required|exists:products,id',
            'qty' => 'required|integer',
            'deal_date' => 'required|date',
            'status' => 'required|string'
        ]);

        $deal = Deal::create($validatedData);
        return response()->json($deal, 201);
    }

    /**
     * @OA\Put(
     *     path="/api/deals/{id}",
     *     summary="Update a specific deal",
     *     tags={"Deals"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="buyer_id", type="integer"),
     *             @OA\Property(property="product_id", type="integer"),
     *             @OA\Property(property="qty", type="integer"),
     *             @OA\Property(property="deal_date", type="string", format="date"),
     *             @OA\Property(property="status", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Deal updated successfully"),
     *     @OA\Response(response=404, description="Deal not found"),
     *     @OA\Response(response=400, description="Invalid input")
     * )
     */
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
        $deal->update($validatedData);

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
