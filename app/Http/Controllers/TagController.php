<?php

namespace App\Http\Controllers;
use App\Models\Tag;
use App\Models\Category;
use Illuminate\Http\Request;

class TagController extends Controller
{
    //

        // 1. ดูแท็กทั้งหมด

        public function index(Request $request)
        {
            $columns = $request->input('columns', []);
            $length = $request->input('length', 10);
            $order = $request->input('order', []);
            $search = $request->input('search', []);
            $start = $request->input('start', 0);
            $page = ($start / $length) + 1;
        
            $col = ['id', 'name', 'category_id'];
            $orderby = ['id', 'name', 'category_id'];
        
            // $categories = Tag::select($col);
            $categories = Tag::with('category:id,category_name')->select($col);
        
            if (isset($order[0]['column']) && isset($orderby[$order[0]['column']])) {
                $categories->orderBy($orderby[$order[0]['column']], $order[0]['dir']);
            } else {
                // ถ้าไม่มีค่าการเรียงลำดับ ให้เรียงตาม id (ค่าใหม่สุดอยู่ล่างสุด)
                $categories->orderBy('id', 'asc');
            }
        
            if (!empty($search['value'])) {
                $categories->where(function ($query) use ($search, $col) {
                    foreach ($col as $c) {
                        $query->orWhere($c, 'like', '%' . $search['value'] . '%');
                    }
                });
            }
        
            $d = $categories->paginate($length, ['*'], 'page', $page);
        
            if ($d->isNotEmpty()) {
                $d->transform(function ($item, $key) use ($page, $length) {
                    $item->No = ($page - 1) * $length + $key + 1;
                    $item->category_name = $item->category ? $item->category->category_name : null;
                    // unset($item->category);
                    // unset($item->category_id);
                    return $item;
                });
            }
        
            return response()->json([
                'status' => 'success',
                'message' => 'เรียกดูข้อมูลสำเร็จ',
                'data' => $d
            ]);
        }

        // public function index()
        // {
        //     $tags = Tag::all(); // ดึงข้อมูลแท็กทั้งหมด
        //     return response()->json($tags);
        // }
    
        // 2. สร้างแท็กใหม่
        public function store(Request $request)
        {
            $validated = $request->validate([
                'name' => 'required|string|unique:tags,name|max:255',
                'category_id' => 'required|exists:categories,id', // ต้องส่ง category_id และต้องมีอยู่จริง
            ]);
    
            $tag = Tag::create([
                'name' => $validated['name'],
                'category_id' => $validated['category_id']
            ]);
    
            return response()->json(['message' => 'Tag created successfully', 'tag' => $tag], 201);
        }
    
        // 3. แสดงแท็กเดียว
        public function show($id)
        {
            $tag = Tag::find($id);
    
            if (!$tag) {
                return response()->json(['message' => 'Tag not found'], 404);
            }
    
            return response()->json($tag);
        }


        // public function getTags($id)
        // {
        //     $category = Category::with('tags')->findOrFail($id);
        //     return response()->json($category->tags);
        // }
    
        // 4. แก้ไขแท็ก
        public function update(Request $request, $id)
        {
            $tag = Tag::find($id);
    
            if (!$tag) {
                return response()->json(['message' => 'Tag not found'], 404);
            }
    
            $validated = $request->validate([
                'name' => 'required|string|unique:tags,name|max:255',
            ]);
    
            $tag->update([
                'name' => $validated['name'],
            ]);
    
            return response()->json(['message' => 'Tag updated successfully', 'tag' => $tag]);
        }
    
        // 5. ลบแท็ก
        public function destroy($id)
        {
            $tag = Tag::find($id);
    
            if (!$tag) {
                return response()->json(['message' => 'Tag not found'], 404);
            }
    
            $tag->delete();
    
            return response()->json(['message' => 'Tag deleted successfully']);
        }
}
