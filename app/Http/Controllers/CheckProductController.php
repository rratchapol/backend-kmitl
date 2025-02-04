<?php

namespace App\Http\Controllers;
use App\Models\CheckProduct;

use Illuminate\Http\Request;

class CheckProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // public function index()
    // {
    //     return response()->json(CheckProduct::all());
    // }

    public function index(Request $request)
    {
        $columns = $request->input('columns', []);
        $length = $request->input('length', 10);
        $order = $request->input('order', []);
        $search = $request->input('search', []);
        $start = $request->input('start', 0);
        $page = ($start / $length) + 1;
    
        $col = ['id', 'word'];
        $orderby = ['id', 'word'];
    
        $CheckProduct = CheckProduct::select($col);
    
        if (isset($order[0]['column']) && isset($orderby[$order[0]['column']])) {
            $CheckProduct->orderBy($orderby[$order[0]['column']], $order[0]['dir']);
        } else {
            // ถ้าไม่มีค่าการเรียงลำดับ ให้เรียงตาม id (ค่าใหม่สุดอยู่ล่างสุด)
            $CheckProduct->orderBy('id', 'asc');
        }
    
        if (!empty($search['value'])) {
            $CheckProduct->where(function ($query) use ($search, $col) {
                foreach ($col as $c) {
                    $query->orWhere($c, 'like', '%' . $search['value'] . '%');
                }
            });
        }
    
        $d = $CheckProduct->paginate($length, ['*'], 'page', $page);
    
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





    public function show($id)
    {
        $CheckProduct = CheckProduct::find($id);

        if (!$CheckProduct) {
            return response()->json(['message' => 'Tag not found'], 404);
        }

        return response()->json($CheckProduct);
    }

    public function store(Request $request)
    {
        $request->validate([
            'word' => 'required|unique:checkproducts,word',
        ]);

        $checkProduct = CheckProduct::create(['word' => $request->word]);

        return response()->json($checkProduct, 201);
    }

    public function update(Request $request, $id)
    {
        $checkProduct = CheckProduct::findOrFail($id);
        $request->validate([
            'word' => 'required|unique:checkproducts,word,' . $id,
        ]);

        $checkProduct->update(['word' => $request->word]);

        return response()->json($checkProduct);
    }

    public function destroy($id)
    {
        $checkProduct = CheckProduct::findOrFail($id);
        $checkProduct->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }

}
