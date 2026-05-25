<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Staff;
use App\Models\Location;

class AuthController extends Controller
{
    public function showLogin(Request $request)
    {
        if (Auth::guard('staff')->check()) {
            return redirect()->to(
                $this->resolvePosRedirect(
                    $request->session()->get('pos_last_login_action')
                )
            );
        }

        return response()
            ->view('pos.login')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'location_code' => 'required',
            'phone' => 'required',
            'password' => 'required'
        ], [
            'location_code.required' => 'Vui lòng nhập mã quán.',
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
        ]);

        if ($validator->fails()) {
            $message = $validator->errors()->first();
            if ($request->expectsJson()) {
                return response()->json([
                    'ok' => false,
                    'message' => $message,
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $locationCode = trim((string) $request->input('location_code'));
        if (!Location::where('code', $locationCode)->exists()) {
            $message = "Cửa hàng {$locationCode} không tồn tại.";
            if ($request->expectsJson()) {
                return response()->json([
                    'ok' => false,
                    'message' => $message,
                ], 404);
            }
            return back()->withErrors(['location_code' => $message])->withInput();
        }

        $credentials = [
            'location_code' => $locationCode,
            'phone' => $request->phone,
            'password' => $request->password,
        ];

        if (Auth::guard('staff')->attempt($credentials)) {
            $request->session()->regenerate();
            $action = $request->input('action') === 'cashier' ? 'cashier' : 'manage';
            $request->session()->put('pos_last_login_action', $action);
            $redirect = $this->resolvePosRedirect($action);

            if ($request->expectsJson()) {
                return response()->json([
                    'ok' => true,
                    'redirect' => $redirect,
                ]);
            }

            return redirect()->to($redirect);
        }

        $message = 'Tên đăng nhập hoặc mật khẩu chưa đúng.';
        if ($request->expectsJson()) {
            return response()->json([
                'ok' => false,
                'message' => $message,
            ], 401);
        }

        return back()->withErrors(['login' => $message])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::guard('staff')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function resolvePosRedirect(?string $action): string
    {
        return $action === 'cashier'
            ? route('pos.cashier')
            : route('pos.kiot');
    }
}
