<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Deal;

class DealController extends Controller
{
    public function index()
    {
        $deals = Deal::with(['buyer', 'product'])->get();
        return response()->json($deals);
    }

    public function show($id)
    {
        $deal = Deal::with(['buyer', 'product'])->findOrFail($id);
        return response()->json($deal);
    }

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

    public function destroy($id)
    {
        $deal = Deal::findOrFail($id);
        $deal->delete();

        return response()->json(null, 204);
    }
}
