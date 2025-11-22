<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class PasswordResetController extends Controller
{
    // Hiển thị form quên mật khẩu
    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    // Xử lý gửi mật khẩu mới
    public function sendResetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ], [
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không hợp lệ.',
            'email.exists' => 'Email này chưa được đăng ký trong hệ thống.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Email này chưa được đăng ký trong hệ thống.'])->withInput();
        }

        // Tạo mật khẩu mới ngẫu nhiên
        $newPassword = Str::random(12);

        // Cập nhật mật khẩu mới
        $user->password = Hash::make($newPassword);
        $user->save();

        // Gửi email chứa mật khẩu mới
        try {
            Mail::send('emails.password-reset', [
                'user' => $user,
                'newPassword' => $newPassword,
            ], function ($message) use ($user) {
                $message->to($user->email, $user->name)
                        ->subject('Đặt lại mật khẩu - CineBook');
            });

            return redirect()->route('login')->with('success', 'Mật khẩu mới đã được gửi đến email của bạn. Vui lòng kiểm tra hộp thư.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Có lỗi xảy ra khi gửi email. Vui lòng thử lại sau.'])->withInput();
        }
    }
}
