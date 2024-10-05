<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Preorder;

class PreorderController extends Controller
{
    public function index()
    {
        $preorders = Preorder::with(['buyer', 'product'])->get();
        return response()->json($preorders);
    }

    public function show($id)
    {
        $preorder = Preorder::with(['buyer', 'product'])->findOrFail($id);
        return response()->json($preorder);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'buyer_id' => 'required|exists:users,id',
            'product_id' => 'required|exists:products,id',
            'qty' => 'required|integer',
            'deal_date' => 'required|date',
            'status' => 'required|string',
            'bill' => 'nullable|string'
        ]);

        $preorder = Preorder::create($validatedData);
        return response()->json($preorder, 201);
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'buyer_id' => 'required|exists:users,id',
            'product_id' => 'required|exists:products,id',
            'qty' => 'required|integer',
            'deal_date' => 'required|date',
            'status' => 'required|string',
            'bill' => 'nullable|string'
        ]);

        $preorder = Preorder::findOrFail($id);
        $preorder->update($validatedData);

        return response()->json($preorder);
    }

    public function destroy($id)
    {
        $preorder = Preorder::findOrFail($id);
        $preorder->delete();

        return response()->json(null, 204);
    }
}
