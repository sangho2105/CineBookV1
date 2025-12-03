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
            'email.required' => 'Please enter your email.',
            'email.email' => 'Invalid email format.',
            'email.exists' => 'This email is not registered in the system.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'This email is not registered in the system.'])->withInput();
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
                        ->subject('Password Reset - CineBook');
            });

            return redirect()->route('login')->with('success', 'A new password has been sent to your email. Please check your inbox.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'An error occurred while sending email. Please try again later.'])->withInput();
        }
    }
}
