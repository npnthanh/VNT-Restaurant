<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class WebsiteAdminAuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('staff')->check() && Gate::forUser(Auth::guard('staff')->user())->allows('manage_website_settings')) {
            return redirect()->route('admin.website-settings.edit');
        }

        return response()->view('admin.login')->header('Cache-Control', 'no-store');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'location_code' => 'required|string|max:50',
            'phone' => 'required|string|max:30',
            'password' => 'required|string|max:200',
        ], ['required' => 'Vui lòng nhập :attribute.'], [
            'location_code' => 'mã quán', 'phone' => 'số điện thoại', 'password' => 'mật khẩu',
        ]);

        $guard = Auth::guard('staff');
        if (!$guard->attempt($credentials)) {
            throw ValidationException::withMessages(['phone' => 'Mã quán, số điện thoại hoặc mật khẩu chưa đúng.']);
        }
        if (!Gate::forUser($guard->user())->allows('manage_website_settings')) {
            $guard->logout();
            throw ValidationException::withMessages(['phone' => 'Tài khoản chưa có quyền cấu hình trang khách.']);
        }

        $request->session()->regenerate();

        return redirect()->route('admin.website-settings.edit');
    }

    public function logout(Request $request)
    {
        Auth::guard('staff')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
