<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\CheckProduct;
use App\Models\Customer;

class PostController extends Controller
{
    //

    public function post()
    {
        return response()->json(Post::all());
    }


    public function index(Request $request)
    {
        $columns = $request->input('columns', []);
        $length = $request->input('length', 10);
        $order = $request->input('order', []);
        $search = $request->input('search', []);
        $start = $request->input('start', 0);
        $page = ($start / $length) + 1;

        $col = ['id', 'image', 'detail', 'category', 'tag', 'price', 'userpost_id', 'status'];
        $orderby = ['id', 'image', 'detail', 'category' , 'tag', 'price', 'userpost_id', 'status'];

        // $posts = Post::select($col);
            // ดึงข้อมูลจากตาราง Post พร้อมข้อมูลของ User
        $posts = Post::with('user')->select($col);


            // กรองตาม column ที่ส่งมา

        if ($poststatus = $request->input('status', '')) {
            $posts->where('status', 'like', "%$poststatus%");
        }

        // if ($postcategory = $request->input('category', '')) {
        //     $posts->where('category', 'like', "%$postcategory%");
        // }

        if ($request->has('category') && !empty($request->input('category'))) {
            $postcategory = $request->input('category');
            $posts->where('category', 'like', "%$postcategory%");
        }

        if ($posttag = $request->input('tag', '')) {
            $posts->where('tag', 'like', "%$posttag%");
        }


        if (isset($order[0]['column']) && isset($orderby[$order[0]['column']])) {
            $posts->orderBy($orderby[$order[0]['column']], $order[0]['dir']);
        }

        if (!empty($search['value'])) {
            $posts->where(function ($query) use ($search, $col) {
                foreach ($col as $c) {
                    $query->orWhere($c, 'like', '%' . $search['value'] . '%');
                }
            });
        }

        $d = $posts->paginate($length, ['*'], 'page', $page);

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


        // สร้างข้อมูล Post
        public function store(Request $request)
        {
            $validated = $request->validate([
                'userpost_id' => 'required|exists:customers,id',
                'image' => 'required|string',
                'detail' => 'required|string',
                'category' => 'required|string',
                'tag' => 'required|string',
                'price' => 'required|numeric',
            ]);
    
        // 🔍 ดึงคำต้องห้ามทั้งหมดจากตาราง checkproducts
        $forbiddenWords = CheckProduct::pluck('word')->toArray();

        // 📝 รวมข้อความที่ต้องเช็ก
        $textToCheck = strtolower($validated['detail'] . ' ' . ($validated['tag'] ?? ''));

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
        $post = Post::create($validated);

        return response()->json($post, 201);
        }

        
    
        // ดูข้อมูล Post ทั้งหมด
        // public function index()
        // {
        //     $posts = Post::all();
        //     return response()->json($posts);
        // }
    
        // ดูข้อมูล Post ตาม ID
        public function show($id)
        {
            // $post = Post::find($id);
            // $post = Post::with('user_id')->findOrFail($id);
            $post = Post::with('user')->find($id);
            if (!$post) {
                return response()->json(['message' => 'post not found'], 404);
            }

            return response()->json($post);
        }
    

        public function look($user_id)
        {
            // ค้นหาโพสต์ที่ตรงกับ user_id
            $posts = Post::with('user')
                        ->where('userpost_id', $user_id)
                        ->get();

            // $posts = Post::with('user')->find($user_id);
            // ถ้าไม่พบโพสต์
            if ($posts->isEmpty()) {
                return response()->json(['message' => 'No posts found for this user'], 404);
            }
        
            // คืนค่ารายการโพสต์ที่พบ
            return response()->json($posts);
        }


        // อัปเดตข้อมูล Post
        public function update(Request $request, $id)
        {
            $validated = $request->validate([
                'image' => 'string',
                'detail' => 'string',
                'category' => 'string',
                'tag' => 'string',
                'price' => 'numeric',
                'status' => 'string',
            ]);
    
            $post = Post::findOrFail($id);
            $post->update($validated);
    
            return response()->json($post);
        }
    
        // ลบข้อมูล Post
        public function destroy($id)
        {
            $post = Post::findOrFail($id);
            $post->delete();
    
            return response()->json(['message' => 'Post deleted successfully']);
        }
}
