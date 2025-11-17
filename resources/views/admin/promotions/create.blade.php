@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Thêm khuyến mãi mới</h1>
        <a href="{{ route('admin.promotions.index') }}" class="btn btn-outline-secondary">Quay lại danh sách</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Đã có lỗi xảy ra!</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.promotions.store') }}" method="POST" enctype="multipart/form-data">
                @include('admin.promotions._form')
            </form>
        </div>
    </div>
</div>
@endsection

