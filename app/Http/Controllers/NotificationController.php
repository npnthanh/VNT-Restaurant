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

        $bookingQuery = DB::table('booking')
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
            ->orderByDesc('booking.id');

        if ($locationId) {
            $bookingQuery->where('booking.location_id', $locationId);
        }

        $bookings = $bookingQuery->limit($limit)->get();

        $bookingItems = $bookings->map(function ($booking) {
            $timeValue = $booking->created_at ?: $booking->booking_time;
            $timeText = '';
            $timeSort = 0;
            if (!empty($timeValue)) {
                $timeParsed = Carbon::parse($timeValue);
                $timeText = $timeParsed->format('H:i d/m/Y');
                $timeSort = $timeParsed->getTimestamp();
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
                'url' => url('/pos/booking'),
                'sort_time' => $timeSort,
            ];
        });

        $contacts = DB::table('contact')
            ->select(
                'contact.id',
                'contact.name',
                'contact.subject',
                'contact.status',
                'contact.created_at'
            )
            ->orderByDesc('contact.id')
            ->limit($limit)
            ->get();

        $contactItems = $contacts->map(function ($contact) {
            $timeText = '';
            $timeSort = 0;
            if (!empty($contact->created_at)) {
                $timeParsed = Carbon::parse($contact->created_at);
                $timeText = $timeParsed->format('H:i d/m/Y');
                $timeSort = $timeParsed->getTimestamp();
            }

            $contactName = $contact->name ?: 'Khách hàng';
            $contactSubject = $contact->subject ?: 'Liên hệ';

            return [
                'id' => (int) $contact->id,
                'type' => 'contact',
                'title' => 'Bạn có liên hệ mới',
                'message' => trim($contactName . ' • ' . $contactSubject),
                'time' => $timeText,
                'status' => $contact->status,
                'url' => url('/pos/contact'),
                'sort_time' => $timeSort,
            ];
        });

        $items = $bookingItems
            ->concat($contactItems)
            ->sortByDesc('sort_time')
            ->take($limit)
            ->values()
            ->map(function ($item) {
                unset($item['sort_time']);
                return $item;
            });

        $latestIds = [
            'booking' => (int) ($bookings->max('id') ?? 0),
            'contact' => (int) ($contacts->max('id') ?? 0),
        ];

        $latestId = max($latestIds['booking'], $latestIds['contact']);

        return response()->json([
            'items' => $items,
            'latest_id' => (int) $latestId,
            'latest_ids' => $latestIds
        ]);
    }
}
