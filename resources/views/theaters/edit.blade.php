@extends('layouts.app')

@section('title', 'Edit Theater - CineBook')

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Edit Theater: {{ $theater->name }}</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('theaters.update', $theater) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="name" class="form-label">Theater Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name', $theater->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="city" class="form-label">City <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('city') is-invalid @enderror" 
                               id="city" name="city" value="{{ old('city', $theater->city) }}" required>
                        @error('city')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('address') is-invalid @enderror" 
                                  id="address" name="address" rows="3" required>{{ old('address', $theater->address) }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="seating_capacity" class="form-label">Seating Capacity <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('seating_capacity') is-invalid @enderror" 
                               id="seating_capacity" name="seating_capacity" value="{{ old('seating_capacity', $theater->seating_capacity) }}" min="1" required>
                        @error('seating_capacity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Enter the total number of seats in this theater.</small>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('theaters.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Theater</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection