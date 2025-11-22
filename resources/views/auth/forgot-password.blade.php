@extends('layouts.app')

@section('title', 'Quên mật khẩu - CineBook')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3>Quên mật khẩu</h3>
            </div>
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <p class="text-muted mb-4">
                    Nhập email đã đăng ký tài khoản của bạn. Mật khẩu mới sẽ được gửi đến email này.
                </p>

                <form action="{{ route('password.forgot.post') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label">Email *</label>
                        <input type="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               id="email" 
                               name="email" 
                               value="{{ old('email') }}" 
                               required
                               placeholder="Nhập email đã đăng ký">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mb-3">Gửi mật khẩu mới</button>
                </form>

                <div class="text-center">
                    <a href="{{ route('login') }}" class="text-decoration-none">Quay lại đăng nhập</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

