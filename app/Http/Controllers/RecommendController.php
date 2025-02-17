<?php

namespace App\Http\Controllers;
use App\Models\Recommend;
use Illuminate\Http\Request;

class RecommendController extends Controller
{
    //

        // ฟังก์ชันดึงสินค้าที่ผู้ใช้เคยซื้อ
        public function getUserRecommendation($user_id)
        {
            $recommendations = Recommend::where('user_id', $user_id)
                ->with('product')
                ->get();
    
            return response()->json($recommendations);
        }


        public function getUserRecommendations($user_id)
        {
            $productIds = Recommend::where('user_id', $user_id)
                ->pluck('product_id'); // ดึงแค่ product_id

            // return response()->json($productIds);

            $userLikeIds = Recommend::where('userlike_id', $user_id)
            ->pluck('productlike_id'); // ดึงเฉพาะ userlike_id

            return response()->json([
                'product_id' => $productIds
                , 'userlike_id' => $userLikeIds
            ]);
        }
}
