@extends('layouts.admin')

@section('title', 'Sửa Combo')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Edit Combo</h1>
        <a href="{{ route('admin.combos.index') }}" class="btn btn-outline-secondary">Back to List</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>An error occurred!</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.combos.update', $combo) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('admin.combos._form', ['combo' => $combo])
            </form>
        </div>
    </div>
</div>
@endsection

