@extends('layouts.app')

@section('title', 'Edit Profile - CineBook')

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Edit Profile</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                            id="name" name="name" value="{{ old('name', $user->name) }}" required>
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                            id="email" name="email" value="{{ old('email', $user->email) }}" required readonly disabled>
                        <input type="hidden" name="email" value="{{ $user->email }}">
                        @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror"
                            id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
                        @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="date_of_birth" class="form-label">Date of Birth</label>
                        <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror"
                            id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth', $user->date_of_birth) }}" 
                            max="{{ date('Y-m-d', strtotime('-1 day')) }}">
                        @error('date_of_birth')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="preferred_language" class="form-label">Preferred Language</label>
                        <select class="form-control @error('preferred_language') is-invalid @enderror"
                            id="preferred_language" name="preferred_language">
                            <option value="">-- Select Language --</option>
                            <option value="Vietnamese" {{ old('preferred_language', $user->preferred_language) == 'Vietnamese' ? 'selected' : '' }}>Vietnamese</option>
                            <option value="English" {{ old('preferred_language', $user->preferred_language) == 'English' ? 'selected' : '' }}>English</option>
                            <option value="Chinese" {{ old('preferred_language', $user->preferred_language) == 'Chinese' ? 'selected' : '' }}>Chinese</option>
                        </select>
                        @error('preferred_language')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="preferred_city" class="form-label">Preferred City</label>
                        <input type="text" class="form-control @error('preferred_city') is-invalid @enderror"
                            id="preferred_city" name="preferred_city" value="{{ old('preferred_city', $user->preferred_city) }}">
                        @error('preferred_city')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="changePasswordCheck" name="change_password">
                            <label class="form-check-label" for="changePasswordCheck">
                                I want to change my password
                            </label>
                        </div>
                    </div>

                    <div id="passwordFields" style="display: none;">
                        <div class="mb-3">
                            <label for="password" class="form-label">New Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                id="password" name="password">
                            @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control"
                                id="password_confirmation" name="password_confirmation">
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('profile.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const changePasswordCheck = document.getElementById('changePasswordCheck');
    const passwordFields = document.getElementById('passwordFields');
    const passwordInput = document.getElementById('password');
    const passwordConfirmationInput = document.getElementById('password_confirmation');

    changePasswordCheck.addEventListener('change', function() {
        if (this.checked) {
            passwordFields.style.display = 'block';
            passwordInput.required = true;
            passwordConfirmationInput.required = true;
        } else {
            passwordFields.style.display = 'none';
            passwordInput.required = false;
            passwordConfirmationInput.required = false;
            passwordInput.value = '';
            passwordConfirmationInput.value = '';
        }
    });
});
</script>
@endsection