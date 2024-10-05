<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    // ฟังก์ชันสำหรับเพิ่มลูกค้า
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:customers',
            'mobile' => 'required|string|max:15',
            'address' => 'required|string|max:255',
            'faculty' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'classyear' => 'required|string|max:4',
            'role' => 'required|string|max:50',
        ]);
    
        // เพิ่ม user_id จากผู้ใช้ที่ล็อกอินอยู่
        $validatedData['user_id'] = auth()->id();
    
        $customer = Customer::create($validatedData);
    
        return response()->json(['message' => 'Customer created successfully', 'customer' => $customer], 201);
    }
    

    // ฟังก์ชันสำหรับแก้ไขลูกค้า
    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:customers,email,' . $customer->id,
            'mobile' => 'required|string|max:15',
            'address' => 'required|string|max:255',
            'faculty' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'classyear' => 'required|string|max:4',
            'role' => 'required|string|max:50',
        ]);

        $customer->update($validatedData);

        return response()->json(['message' => 'Customer updated successfully', 'customer' => $customer]);
    }

    // ฟังก์ชันสำหรับลบลูกค้า
    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();

        return response()->json(['message' => 'Customer deleted successfully']);
    }

    // ฟังก์ชันสำหรับแสดงรายชื่อลูกค้า
    public function index()
    {
        $customers = Customer::all();
        return response()->json($customers);
    }

    // ฟังก์ชันสำหรับแสดงลูกค้ารายละเอียด
    public function show($id)
    {
        $customer = Customer::findOrFail($id);
        return response()->json($customer);
    }

    
    
}

