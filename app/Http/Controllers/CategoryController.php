<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use OpenApi\Annotations as OA;

class CategoryController extends Controller
{

    //  public function index()
    //  {
    //      return response()->json(Category::all());
    //  }
    public function index(Request $request)
    {
        $columns = $request->input('columns', []);
        $length = $request->input('length', 10);
        $order = $request->input('order', []);
        $search = $request->input('search', []);
        $start = $request->input('start', 0);
        $page = ($start / $length) + 1;
    
        $col = ['id', 'category_name'];
        $orderby = ['id', 'category_name'];
    
        $categories = Category::select($col);
    
        if (isset($order[0]['column']) && isset($orderby[$order[0]['column']])) {
            $categories->orderBy($orderby[$order[0]['column']], $order[0]['dir']);
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
                return $item;
            });
        }
    
        return response()->json([
            'status' => 'success',
            'message' => 'เรียกดูข้อมูลสำเร็จ',
            'data' => $d
        ]);
    }


    public function store(Request $request)
    {
        $request->validate([
            'category_name' => 'required|string|max:255',
        ]);

        $category = Category::create([
            'category_name' => $request->category_name,
        ]);

        return response()->json($category, 201);
    }


    public function getTags($id)
    {
        $category = Category::with('tags')->findOrFail($id);
        return response()->json($category->tags);
    }


    public function show($id)
    {
        $category = Category::findOrFail($id);
        return response()->json($category);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'category_name' => 'required|string|max:255',
        ]);

        $category = Category::findOrFail($id);
        $category->update([
            'category_name' => $request->category_name,
        ]);

        return response()->json($category);
    }


    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return response()->json(['message' => 'Category deleted successfully']);
    }
}
