<?php

namespace App\Http\Controllers;
use App\Models\Location;

use Illuminate\Http\Request;

class LocationController extends Controller
{
    //

    public function index(Request $request)
    {
        $columns = $request->input('columns', []);
        $length = $request->input('length', 10);
        $order = $request->input('order', []);
        $search = $request->input('search', []);
        $start = $request->input('start', 0);
        $page = ($start / $length) + 1;
    
        $col = ['id', 'name'];
        $orderby = ['id', 'name'];
    
        $locations = Location::select($col);
    
        if (isset($order[0]['column']) && isset($orderby[$order[0]['column']])) {
            $locations->orderBy($orderby[$order[0]['column']], $order[0]['dir']);
        }
    
        if (!empty($search['value'])) {
            $locations->where(function ($query) use ($search, $col) {
                foreach ($col as $c) {
                    $query->orWhere($c, 'like', '%' . $search['value'] . '%');
                }
            });
        }
    
        $d = $locations->paginate($length, ['*'], 'page', $page);
    
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

    // public function index()
    // {
    //     $locations = Location::all();
    //     return response()->json($locations);
    // }

    // public function store(Request $request)
    // {
    //     $location = Location::create($request->only(['name', 'address']));
    //     return response()->json($location, 201);
    // }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:locations,name|max:255',
            'address' => 'nullable|string',
        ]);

        $location = Location::create([
            'name' => $validated['name'],
            'address' => $validated['address'],
        ]);

        return response()->json(['message' => 'location created successfully', 'location' => $location], 201);
    }


    public function show($id)
    {
        $location = Location::find($id);

        if (!$location) {
            return response()->json(['message' => 'location not found'], 404);
        }

        return response()->json($location);
    }


    public function update(Request $request, $id)
    {
        $location = Location::find($id);

        if (!$location) {
            return response()->json(['message' => 'location not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|unique:locations,name|max:255',
        ]);

        $location->update([
            'name' => $validated['name'],
        ]);

        return response()->json(['message' => 'location updated successfully', 'location' => $location]);
    }


    public function destroy($id)
    {
        $location = Location::find($id);

        if (!$location) {
            return response()->json(['message' => 'location not found'], 404);
        }

        $location->delete();

        return response()->json(['message' => 'location deleted successfully']);
    }
}
