@extends('layouts.admin')

@section('title', 'Chi tiết Combo')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Combo Details</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.combos.edit', $combo) }}" class="btn btn-warning">
                <i class="bi bi-pencil"></i> Edit
            </a>
            <a href="{{ route('admin.combos.index') }}" class="btn btn-outline-secondary">Back to List</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-3">ID:</dt>
                <dd class="col-sm-9">{{ $combo->id }}</dd>

                <dt class="col-sm-3">Combo Name:</dt>
                <dd class="col-sm-9"><strong>{{ $combo->name }}</strong></dd>

                <dt class="col-sm-3">Description / Details:</dt>
                <dd class="col-sm-9">{{ $combo->description ?? 'No description' }}</dd>

                <dt class="col-sm-3">Image:</dt>
                <dd class="col-sm-9">
                    @if($combo->image_path)
                        <img src="{{ $combo->image_url }}" alt="{{ $combo->name }}" 
                             class="img-fluid rounded" style="max-height: 300px;">
                    @else
                        <span class="text-muted">No image</span>
                    @endif
                </dd>

                <dt class="col-sm-3">Price:</dt>
                <dd class="col-sm-9">
                    <strong class="text-primary fs-5">{{ format_currency($combo->price) }}</strong>
                </dd>

                <dt class="col-sm-3">Status:</dt>
                <dd class="col-sm-9">
                    @if($combo->is_active)
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-secondary">Inactive</span>
                    @endif
                </dd>

                <dt class="col-sm-3">Created At:</dt>
                <dd class="col-sm-9">{{ $combo->created_at->format('d/m/Y H:i:s') }}</dd>

                <dt class="col-sm-3">Updated At:</dt>
                <dd class="col-sm-9">{{ $combo->updated_at->format('d/m/Y H:i:s') }}</dd>
            </dl>

            <div class="mt-4">
                <form action="{{ route('admin.combos.toggleHidden', $combo->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn {{ $combo->is_hidden ? 'btn-success' : 'btn-warning' }}" 
                            onclick="return confirm('Are you sure you want to {{ $combo->is_hidden ? 'show' : 'hide' }} this combo?')" 
                            title="{{ $combo->is_hidden ? 'Show Combo' : 'Hide Combo' }}">
                        <i class="bi {{ $combo->is_hidden ? 'bi-eye' : 'bi-eye-slash' }}"></i> 
                        {{ $combo->is_hidden ? 'Show Combo' : 'Hide Combo' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

