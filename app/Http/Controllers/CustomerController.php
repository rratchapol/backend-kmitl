<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class CustomerController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/customers",
     *     summary="Get a list of customers",
     *     tags={"Customers"},
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=400, description="Invalid request")
     * )
     */
    public function index(Request $request)
    {
        $columns = $request->input('columns', []);
        $length = $request->input('length', 10);
        $order = $request->input('order', []);
        $search = $request->input('search', []);
        $start = $request->input('start', 0);
        $page = ($start / $length) + 1;

        $col = ['id', 'name', 'email', 'mobile', 'address', 'faculty', 'department', 'classyear', 'role', 'pic', 'status','user_id'];
        $orderby = ['id', 'name', 'email', 'mobile', 'address', 'faculty', 'department', 'classyear','role' , 'pic', 'status','user_id'];

        $customers = Customer::select($col);

        if (isset($order[0]['column']) && isset($orderby[$order[0]['column']])) {
            $customers->orderBy($orderby[$order[0]['column']], $order[0]['dir']);
        }

        if (!empty($search['value'])) {
            $customers->where(function ($query) use ($search, $col) {
                foreach ($col as $c) {
                    $query->orWhere($c, 'like', '%' . $search['value'] . '%');
                }
            });
        }

        $d = $customers->paginate($length, ['*'], 'page', $page);

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

    /**
     * @OA\Post(
     *     path="/api/customers",
     *     summary="Create a new customer",
     *     tags={"Customers"},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="email", type="string"),
     *             @OA\Property(property="mobile", type="string"),
     *             @OA\Property(property="address", type="string"),
     *             @OA\Property(property="faculty", type="string"),
     *             @OA\Property(property="department", type="string"),
     *             @OA\Property(property="classyear", type="string"),
     *             @OA\Property(property="role", type="string")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Customer created successfully"),
     *     @OA\Response(response=400, description="Invalid input")
     * )
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'pic' => 'nullable|string',
            'email' => 'required|string|email|max:255|unique:customers',
            'mobile' => 'required|string|max:15',
            'address' => 'required|string|max:255',
            'faculty' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'classyear' => 'required|string|max:4',
            'role' => 'required|string|max:50',
            'status' => 'nullable|string',
        ]);

        $validatedData['user_id'] = auth()->id();

        $customer = Customer::create($validatedData);

        return response()->json($customer, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/customers/{id}",
     *     summary="Get a specific customer",
     *     tags={"Customers"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=404, description="Customer not found")
     * )
     */
    //หาจาก id login
    public function show($user_id)
    {
        // $customer = Customer::find($id);
        $customer = Customer::where('user_id', $user_id)->first();

        

        if (!$customer) {
            return response()->json(['id' => 'no'], 404);
        }

        return response()->json($customer);
    }


// หาจาก id ที่สร้างใหม่
    public function look($id)
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json(['message' => 'Customer not found'], 404);
        }

        return response()->json($customer);
    }


    /**
     * @OA\Put(
     *     path="/api/customers/{id}",
     *     summary="Update a specific customer",
     *     tags={"Customers"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="email", type="string"),
     *             @OA\Property(property="mobile", type="string"),
     *             @OA\Property(property="address", type="string"),
     *             @OA\Property(property="faculty", type="string"),
     *             @OA\Property(property="department", type="string"),
     *             @OA\Property(property="classyear", type="string"),
     *             @OA\Property(property="role", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Customer updated successfully"),
     *     @OA\Response(response=404, description="Customer not found"),
     *     @OA\Response(response=400, description="Invalid input")
     * )
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'pic' => 'nullable|string',
            'email' => 'sometimes|required|string|email|max:255|unique:customers,email,' . $id,
            'mobile' => 'sometimes|required|string|max:15',
            'address' => 'sometimes|required|string|max:255',
            'faculty' => 'sometimes|required|string|max:255',
            'department' => 'sometimes|required|string|max:255',
            'classyear' => 'sometimes|required|string|max:4',
            'role' => 'sometimes|required|string|max:50',
            'status' => 'nullable|string',
        ]);
    
        $customer = Customer::find($id);
    
        if (!$customer) {
            return response()->json(['message' => 'Customer not found'], 404);
        }
    
        // รวมข้อมูลเดิมกับข้อมูลใหม่
        $customer->update(array_merge($customer->toArray(), $validatedData));
    
        return response()->json($customer);
    }
    

    /**
     * @OA\Delete(
     *     path="/api/customers/{id}",
     *     summary="Delete a specific customer",
     *     tags={"Customers"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="Customer deleted successfully"),
     *     @OA\Response(response=404, description="Customer not found")
     * )
     */
    public function destroy($id)
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json(['message' => 'Customer not found'], 404);
        }

        $customer->delete();
        return response()->json(['message' => 'Customer deleted successfully']);
    }
}
