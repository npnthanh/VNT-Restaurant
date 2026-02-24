<?php

namespace App\Http\Controllers;

use App\Models\InventoryCheck;
use App\Models\InventoryCheckDetail;
use App\Models\Ingredient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index()
    {
        $checks = InventoryCheck::with(['staff', 'details.ingredient'])
            ->orderByDesc('check_time')
            ->get();

        return view('pos.inventory', compact('checks'));
    }

    public function create()
    {
        return view('pos.inventorydetail');
    }

    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.ingredient_id' => 'required|integer',
            'items.*.actual_qty' => 'required|numeric|min:0',
            'check_time' => 'required|date',
            'status' => 'required|in:draft,completed',
            'note' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();

        try {
            $check = InventoryCheck::create([
                'code' => '',
                'staff_id' => auth('staff')->id(),
                'check_time' => $request->check_time,
                'status' => $request->status,
                'note' => $request->note,
            ]);

            $check->code = 'KK' . str_pad((string) $check->id, 6, '0', STR_PAD_LEFT);
            $check->save();

            foreach ($request->items as $item) {
                $ingredient = Ingredient::lockForUpdate()->findOrFail($item['ingredient_id']);
                $actualQty = (float) $item['actual_qty'];
                $stockQty = (float) $ingredient->quantity;
                $diffQty = $actualQty - $stockQty;

                InventoryCheckDetail::create([
                    'inventory_check_id' => $check->id,
                    'ingredient_id' => $ingredient->id,
                    'stock_qty' => $stockQty,
                    'actual_qty' => $actualQty,
                    'diff_qty' => $diffQty,
                    'price' => $ingredient->price ?? 0,
                ]);

                if ($request->status === 'completed') {
                    if (abs($diffQty) > 0) {
                        $type = $diffQty > 0 ? 'import' : 'export';
                        $qty = abs($diffQty);
                        $price = $ingredient->price ?? 0;
                        $totalPrice = $qty * $price;

                        DB::table('inventory_log')->insert([
                            'ingredient_id' => $ingredient->id,
                            'type' => $type,
                            'quantity' => $qty,
                            'price' => $price,
                            'total_price' => $totalPrice,
                            'ref_type' => 'inventory',
                            'ref_id' => $check->id,
                            'staff_id' => auth('staff')->id(),
                            'created_at' => now(),
                        ]);
                    }

                    $ingredient->quantity = $actualQty;
                    $ingredient->save();
                }
            }

            if ($request->status === 'completed') {
                $check->balance_time = now();
                $check->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $request->status === 'completed'
                    ? 'Đã cân bằng kho'
                    : 'Đã lưu phiếu tạm',
                'check_id' => $check->id,
                'redirect' => route('pos.inventory')
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
