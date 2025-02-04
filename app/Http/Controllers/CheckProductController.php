<?php

namespace App\Http\Controllers;
use App\Models\CheckProduct;

use Illuminate\Http\Request;

class CheckProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(CheckProduct::all());
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
