<?php

namespace App\Http\Controllers;
use App\Models\Area;
use App\Models\Table;
use App\Models\Product;
use App\Models\Invoice;
use App\Models\CategoryProduct;
use App\Models\Booking;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CashierController extends Controller
{
    public function index(Request $request)
    {
        $areas = Area::all();
        $categories = CategoryProduct::all();
        $tables = Table::with('area')->get();
        $products = Product::leftJoin('product_available as pa', 'pa.product_id', '=', 'product.id')
            ->select('product.*', 'pa.available_qty')
            ->get();

        $staff = auth('staff')->user();
        $locationId = null;
        if ($staff && !empty($staff->location_code)) {
            $locationId = DB::table('location')
                ->where('code', $staff->location_code)
                ->value('id');
        }

        $bookingQuery = Booking::with('table')
            ->whereIn('status', ['waiting', 'assigned'])
            ->where('booking_time', '>=', now())
            ->orderBy('booking_time');

        if ($locationId) {
            $bookingQuery->where('location_id', $locationId);
        }

        $bookings = $bookingQuery->get();
        $bookingGroups = $bookings->groupBy(function ($booking) {
            return Carbon::parse($booking->booking_time)->toDateString();
        });

        return view('pos.cashier', compact(
            'areas', 'categories', 'tables', 'products', 'bookingGroups'
        ));
    }

    public function startServing(Request $request)
    {
        try {
            $tableId = (int) $request->table_id;
            if (!$tableId) {
                return response()->json(['ok' => false, 'error' => 'Thiếu Table ID'], 400);
            }

            $exists = Invoice::where('table_id', $tableId)
                ->where('status', 'serving')
                ->exists();

            if (!$exists) {
                $invoice = Invoice::create([
                    'table_id'   => $tableId,
                    'user_id'    => auth('staff')->id(),
                    'status'     => 'serving',
                    'time_start' => now(),
                    'total'      => 0,
                    'discount'   => 0,
                    'pay_amount' => 0,
                ]);
                return response()->json(['ok' => true, 'message' => 'Created', 'data' => $invoice]);
            }
            return response()->json(['ok' => true, 'message' => 'Already exists']);
        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function removeServing(Request $request)
    {
        Invoice::where('table_id', $request->table_id)
            ->where('status', 'serving')
            ->delete();
        return response()->json(['ok' => true]);
    }

    public function searchProduct(Request $request)
    {
        $keyword = trim($request->q);

        if (!$keyword) {
            return response()->json([]);
        }

        $products = Product::leftJoin('product_available as pa', 'pa.product_id', '=', 'product.id')
            ->where('product.name', 'like', "%{$keyword}%")
            ->limit(10)
            ->get([
                'product.id',
                'product.name',
                'product.price',
                'product.unit',
                'pa.available_qty'
            ]);

        return response()->json($products);
    }
}
