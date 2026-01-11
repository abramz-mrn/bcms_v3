<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = DB::table('customers')
            ->join('brands', 'customers.brand_id', '=', 'brands.id')
            ->select('customers.*', 'brands.name as brand_name')
            ->orderBy('customers.created_at', 'desc')
            ->paginate(15);

        return response()->json($customers);
    }

    public function show($id)
    {
        $customer = DB::table('customers')
            ->join('brands', 'customers.brand_id', '=', 'brands.id')
            ->select('customers.*', 'brands.name as brand_name')
            ->where('customers.id', $id)
            ->first();

        if (!$customer) {
            return response()->json(['message' => 'Customer not found'], 404);
        }

        return response()->json($customer);
    }

    public function store(Request $request)
    {
        $request->validate([
            'brand_id' => 'required|exists:brands,id',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'required|string',
            'address' => 'required|string',
            'id_card_number' => 'nullable|string',
        ]);

        $id = DB::table('customers')->insertGetId([
            'brand_id' => $request->brand_id,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'id_card_number' => $request->id_card_number,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['id' => $id, 'message' => 'Customer created'], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'sometimes|string',
            'address' => 'sometimes|string',
            'status' => 'sometimes|in:active,inactive,suspended',
        ]);

        $updated = DB::table('customers')
            ->where('id', $id)
            ->update([
                ...$request->only(['name', 'email', 'phone', 'address', 'status']),
                'updated_at' => now(),
            ]);

        if (!$updated) {
            return response()->json(['message' => 'Customer not found'], 404);
        }

        return response()->json(['message' => 'Customer updated']);
    }

    public function destroy($id)
    {
        $deleted = DB::table('customers')->where('id', $id)->delete();

        if (!$deleted) {
            return response()->json(['message' => 'Customer not found'], 404);
        }

        return response()->json(['message' => 'Customer deleted']);
    }
}
