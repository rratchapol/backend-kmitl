<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Post;
use App\Models\Deal;
use App\Models\Like;

use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class CustomerController extends Controller
{

    public function index(Request $request)
    {
        $columns = $request->input('columns', []);
        $length = $request->input('length', 10);
        $order = $request->input('order', []);
        $search = $request->input('search', []);
        $start = $request->input('start', 0);
        $page = ($start / $length) + 1;

        $col = ['id', 'name', 'email', 'mobile', 'address', 'faculty', 'department', 'classyear', 'role', 'pic','user_id', 'guidetag'];
        $orderby = ['id', 'name', 'email', 'mobile', 'address', 'faculty', 'department', 'classyear','role' , 'pic','user_id', 'guidetag']; //, 'userhistory', 'userpost', 'userproduct'

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
            'guidetag' => 'nullable|string',
            // 'userhistory' => 'nullable|string',
            // 'userpost' => 'nullable|string',
            // 'userproduct' => 'nullable|string',
            // 'status' => 'nullable|string',
        ]);

        $validatedData['user_id'] = auth()->id();


        $customer = Customer::create($validatedData);

        return response()->json($customer, 201);
    }


    //หาจาก id login
    public function show($user_id)
    {
        $customer = Customer::where('user_id', $user_id)->first();

        if (!$customer) {
            return response()->json(['id' => 'no'], 404);
        }

        // 🔍 ดึง post_id ที่มี status = 'ok'
        $postIds = Post::where('userpost_id', $user_id)
            ->where('status', 'ok')
            ->pluck('id');

        // 🔍 ดึง product_id ที่ลูกค้าขาย ที่มี status = 'ok'
        $productIds = Product::where('seller_id', $user_id)
            ->where('status', 'ok')
            ->pluck('id');

        // 🔍 ดึง productdeal_id  ที่มี status = 'success'
        $dealIds = Deal::where('buyer_id', $user_id)
            ->where('status', 'success')
            ->pluck('product_id');

        // 🔍 ดึง productlike_id  
        $likeIds = Like::where('userlike_id', $user_id)
            ->pluck('product_id');

        // 🔄 รวมทั้งสองอาร์เรย์ (deal และ like) และกรองไอดีซ้ำ
        $combinedIds = $dealIds->merge($likeIds)->unique();

        return response()->json([
            'customer' => $customer,
            'userpost' => $postIds,
            'userproduct' => $productIds,
            'userhistory' => $combinedIds,
            // 'userlike' => $likeIds,
            // 'userhistory' => [
            //     'productdeal' => $dealIds,
            //     'productlike' => $likeIds
            // ]
        ]);



        // return response()->json($customer);
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


    public function calculateClassYear(Request $request)
    {
        // ตรวจสอบว่าอีเมลถูกส่งมาหรือไม่
        $validatedData = $request->validate([
            'email' => 'required|string|email|max:255',
        ]);
    
        // **ดึงรหัสนักศึกษาจากอีเมล**
        preg_match('/^(\d{8})@kmitl\.ac\.th$/', $validatedData['email'], $matches);
        if (!isset($matches[1])) {
            return response()->json(['error' => 'Invalid KMITL email format'], 400);
        }
    
        $studentCode = $matches[1]; // เช่น 64010724
    
        // **คำนวณปีที่เข้าเรียน**
        $admissionYear = 2500 + intval(substr($studentCode, 0, 2)); // เช่น 64 → 2564
    
        // **คำนวณปีปัจจุบัน**
        $currentYear = date('Y') + 543; // แปลงเป็น พ.ศ.
        
        // **ตรวจสอบวันที่**
        $isAfterJune = date('m-d') >= '06-01'; // เกิน 1 มิ.ย. หรือยัง
    
        // **คำนวณชั้นปี**
        $classYear = ($currentYear - $admissionYear) + ($isAfterJune ? 1 : 0);
    
        // **คืนค่าชั้นปีที่ไม่เกิน 4**
        $classYear = min($classYear, 4);
    
        // **หาผู้ใช้จากอีเมลที่ส่งมา**
        $user = Customer::where('email', $validatedData['email'])->first();
    
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
    
        // **อัพเดตชั้นปีในฐานข้อมูล**
        $user->classyear = $classYear;
        $user->save(); // บันทึกการอัพเดต
    
        // ส่งค่าผลลัพธ์เป็น JSON
        return response()->json(['classyear' => $classYear, 'message' => 'Class year updated successfully']);
    }
    

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
            // 'status' => 'nullable|string',
        ]);
    
        $customer = Customer::find($id);
    
        if (!$customer) {
            return response()->json(['message' => 'Customer not found'], 404);
        }
    
        // รวมข้อมูลเดิมกับข้อมูลใหม่
        $customer->update(array_merge($customer->toArray(), $validatedData));
    
        return response()->json($customer);
    }
    


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
