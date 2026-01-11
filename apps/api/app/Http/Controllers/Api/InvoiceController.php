<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('invoices')
            ->join('subscriptions', 'invoices.subscription_id', '=', 'subscriptions.id')
            ->join('customers', 'subscriptions.customer_id', '=', 'customers.id')
            ->join('products', 'subscriptions.product_id', '=', 'products.id')
            ->select(
                'invoices.*',
                'customers.name as customer_name',
                'products.name as product_name'
            );

        if ($request->has('status')) {
            $query->where('invoices.status', $request->status);
        }

        $invoices = $query->orderBy('invoices.created_at', 'desc')->paginate(15);

        return response()->json($invoices);
    }

    public function show($id)
    {
        $invoice = DB::table('invoices')
            ->join('subscriptions', 'invoices.subscription_id', '=', 'subscriptions.id')
            ->join('customers', 'subscriptions.customer_id', '=', 'customers.id')
            ->join('products', 'subscriptions.product_id', '=', 'products.id')
            ->select(
                'invoices.*',
                'customers.name as customer_name',
                'customers.email as customer_email',
                'customers.phone as customer_phone',
                'products.name as product_name'
            )
            ->where('invoices.id', $id)
            ->first();

        if (!$invoice) {
            return response()->json(['message' => 'Invoice not found'], 404);
        }

        // Get invoice items
        $items = DB::table('invoice_items')
            ->where('invoice_id', $id)
            ->get();

        $invoice->items = $items;

        return response()->json($invoice);
    }
}
