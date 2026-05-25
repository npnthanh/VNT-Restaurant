<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\Role;
use App\Models\Location;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class StaffController extends Controller
{
    public function index()
    {
        $roles = Role::orderBy('name')->get();
        $locations = Location::orderBy('name')->get();
        $staff = Staff::with('role')->orderBy('id', 'desc')->get();

        return view('pos.staff', compact('staff', 'roles', 'locations'));
    }

    public function show($id)
    {
        $staff = Staff::with('role')->findOrFail($id);
        $salary = DB::table('salary_config')
            ->where('staff_id', $id)
            ->first();

        return response()->json([
            'success' => true,
            'staff' => $staff,
            'salary' => $salary,
        ]);
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'location_code' => 'required|exists:location,code',
        ], [
            'location_code.required' => 'Vui lòng chọn cơ sở.',
            'location_code.exists' => 'Cơ sở đã chọn không hợp lệ.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $request->only([
            'name','phone','cccd','email','password','dob','gender','role_id','start_date','location_code'
        ]);
        $data['location_code'] = trim((string) ($data['location_code'] ?? ''));

        $data['status'] = 'Active';
        if ($request->hasFile('img')) {
            $file = $request->file('img');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('images/staff'), $filename);
            $data['img'] = $filename;
        }
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $staff = Staff::create($data);

        return response()->json(['success' => true, 'staff' => $staff]);
    }

    public function update(Request $request, $id)
    {
        $staff = Staff::findOrFail($id);
        $profileFields = [
            'name', 'phone', 'cccd', 'email', 'password', 'dob',
            'gender', 'role_id', 'start_date', 'location_code'
        ];
        $shouldUpdateProfile = $request->hasAny($profileFields) || $request->hasFile('img');

        if ($shouldUpdateProfile) {
            $validator = Validator::make($request->all(), [
                'location_code' => 'required|exists:location,code',
            ], [
                'location_code.required' => 'Vui lòng chọn cơ sở.',
                'location_code.exists' => 'Cơ sở đã chọn không hợp lệ.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }
        }

        $data = $request->only($profileFields);
        if (array_key_exists('location_code', $data)) {
            $data['location_code'] = trim((string) $data['location_code']);
        }

        if ($shouldUpdateProfile) {
            if ($request->hasFile('img')) {
                $file = $request->file('img');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('images/staff'), $filename);
                $data['img'] = $filename;
            }

            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            $staff->update($data);
        }

        if ($request->has('salary_type') || $request->has('salary_rate')) {
            $salaryType = $request->input('salary_type');
            $salaryRate = $request->input('salary_rate');

            $validTypes = ['hour', 'shift', 'day', 'month'];
            if (!$salaryType || !in_array($salaryType, $validTypes, true)) {
                return response()->json(['success' => false, 'message' => 'Loại lương không hợp lệ.'], 422);
            }

            if ($salaryRate === null || $salaryRate === '' || !is_numeric($salaryRate) || $salaryRate < 0) {
                return response()->json(['success' => false, 'message' => 'Mức lương không hợp lệ.'], 422);
            }

            $existing = DB::table('salary_config')->where('staff_id', $id)->first();
            if ($existing) {
                DB::table('salary_config')
                    ->where('staff_id', $id)
                    ->update([
                        'salary_type' => $salaryType,
                        'salary_rate' => $salaryRate,
                        'updated_at' => now(),
                    ]);
            } else {
                DB::table('salary_config')->insert([
                    'staff_id' => $id,
                    'salary_type' => $salaryType,
                    'salary_rate' => $salaryRate,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return response()->json(['success' => true, 'staff' => $staff]);
    }

    public function updateAccount(Request $request)
    {
        /** @var Staff|null $user */
        $user = Auth::guard('staff')->user();
        if (!$user) {
            return response()->json(['errors' => ['auth' => ['Chưa đăng nhập.']]], 401);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'phone' => 'required',
            'email' => 'nullable|email',
            'current_password' => 'required',
            'new_password' => 'nullable|min:8|confirmed',
        ], [
            'name.required' => 'Tên không được bỏ trống.',
            'phone.required' => 'SĐT không được bỏ trống.',
            'email.email' => 'Email không đúng định dạng.',
            'current_password.required' => 'Nhập mật khẩu hiện tại.',
            'new_password.min' => 'Mật khẩu mới phải tối thiểu 8 ký tự.',
            'new_password.confirmed' => 'Xác nhận mật khẩu mới không khớp.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Kiểm tra mật khẩu hiện tại
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['errors' => ['current_password' => ['Mật khẩu hiện tại không đúng!']]], 422);
        }

        // Cập nhật thông tin
        $user->name = $request->name;
        $user->phone = $request->phone;
        $user->email = $request->email;

        if ($request->filled('new_password')) {
            $user->password = Hash::make($request->new_password);
        }

        $user->save();

        return response()->json(['success' => 'Cập nhật tài khoản thành công!']);
    }

    public function updateStatus(Request $request, $id)
    {
        $staff = Staff::findOrFail($id);
        $newStatus = $request->status ?? ($staff->status === 'Active' ? 'Inactive' : 'Active');
        $staff->status = $newStatus;
        $staff->save();

        return response()->json(['success' => true, 'status' => $staff->status]);
    }

    public function destroy($id)
    {
        $staff = Staff::findOrFail($id);
        $staff->delete();

        return response()->json(['success' => true]);
    }
    
}
