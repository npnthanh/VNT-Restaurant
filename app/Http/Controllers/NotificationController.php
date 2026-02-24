<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $limit = (int) $request->query('limit', 20);
        if ($limit < 1) {
            $limit = 20;
        }
        $limit = min($limit, 50);

        $locationId = null;
        $staff = Auth::guard('staff')->user();
        if ($staff && !empty($staff->location_code)) {
            $locationId = DB::table('location')
                ->where('code', $staff->location_code)
                ->value('id');
        }

        $query = DB::table('booking')
            ->select(
                'booking.id',
                'booking.customer_name',
                'booking.phone',
                'booking.guest_count',
                'booking.booking_time',
                'booking.status',
                'booking.created_at',
                'booking.table_id'
            )
            ->orderByDesc('booking.id')
            ->limit($limit);

        if ($locationId) {
            $query->where('booking.location_id', $locationId);
        }

        $bookings = $query->get();

        $items = $bookings->map(function ($booking) {
            $timeValue = $booking->created_at ?: $booking->booking_time;
            $timeText = '';
            if (!empty($timeValue)) {
                $timeText = Carbon::parse($timeValue)->format('H:i d/m/Y');
            }

            $guestText = $booking->guest_count ? $booking->guest_count . ' khách' : 'Chưa rõ số khách';
            $customerName = $booking->customer_name ?: 'Khách mới';

            return [
                'id' => (int) $booking->id,
                'type' => 'booking',
                'title' => 'Có đặt bàn mới',
                'message' => trim($customerName . ' • ' . $guestText),
                'time' => $timeText,
                'status' => $booking->status,
                'url' => url('/pos/booking')
            ];
        });

        $latestId = $bookings->max('id') ?? 0;

        return response()->json([
            'items' => $items,
            'latest_id' => (int) $latestId
        ]);
    }
}
