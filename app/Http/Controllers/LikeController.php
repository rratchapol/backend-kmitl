<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Like;
use App\Models\Recommend;
use OpenApi\Annotations as OA;

class LikeController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/likes",
     *     summary="Get a list of likes",
     *     tags={"Likes"},
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

        $likes = Like::with(['user', 'product']);

        if (!empty($search['value'])) {
            $likes->whereHas('user', function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search['value'] . '%');
            })
            ->orWhereHas('product', function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search['value'] . '%');
            });
        }

        $paginatedLikes = $likes->paginate($length, ['*'], 'page', $page);

        return response()->json([
            'status' => 'success',
            'message' => 'เรียกดูข้อมูลสำเร็จ',
            'data' => $paginatedLikes
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/likes/{id}",
     *     summary="Get a specific like",
     *     tags={"Likes"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=404, description="Like not found")
     * )
     */
    public function show($id)
    {
        $like = Like::with(['user', 'product'])->findOrFail($id);
        if (!$like) {
            return response()->json(['message' => 'like not found'], 404);
        }

        return response()->json($like);
    }

    /**
     * @OA\Post(
     *     path="/api/likes",
     *     summary="Create a new like",
     *     tags={"Likes"},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="userlike_id", type="integer"),
     *             @OA\Property(property="product_id", type="integer"),
     *         )
     *     ),
     *     @OA\Response(response=201, description="Like created successfully"),
     *     @OA\Response(response=400, description="Invalid input")
     * )
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'userlike_id' => 'required|exists:users,id',
            'product_id' => 'required|exists:products,id',
        ]);

        $like = Like::create($validatedData);


    $recommend = Recommend::updateOrCreate(
        [
            'userlike_id' => $validatedData['userlike_id'],
            'productlike_id' => $validatedData['product_id'],
        ],
        [
            'userlike_id' => $validatedData['userlike_id'], // ทำการอัปเดตค่าตรงนี้
            'productlike_id' => $validatedData['product_id']
        ]
    );


        
        return response()->json($like, 201);
        // return response()->json([
        //     'like' => $like,
        //     'recommend' => $recommend
        // ], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/likes/{id}",
     *     summary="Update a specific like",
     *     tags={"Likes"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="userlike_id", type="integer"),
     *             @OA\Property(property="product_id", type="integer"),
     *         )
     *     ),
     *     @OA\Response(response=200, description="Like updated successfully"),
     *     @OA\Response(response=404, description="Like not found"),
     *     @OA\Response(response=400, description="Invalid input")
     * )
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'userlike_id' => 'required|exists:users,id',
            'product_id' => 'required|exists:products,id',
        ]);

        $like = Like::findOrFail($id);
        $like->update($validatedData);

        return response()->json($like);
    }

  
    // public function destroy($id)
    // {
    //     $like = Like::findOrFail($id);
    //     $like->delete();

    //     return response()->json(['message' => 'Like deleted successfully']);
    // }

    public function destroy($userlike_id, $product_id)
{
    $like = Like::where('userlike_id', $userlike_id)
                ->where('product_id', $product_id)
                ->firstOrFail();

    $like->delete();

    return response()->json(['message' => 'Like deleted successfully']);
}


  
    public function getLikesByUser($userId)
    {
        $likes = Like::where('userlike_id', $userId)->with(['user', 'product'])->get();
        return response()->json($likes);
    }
}
