@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">{{ $theater->name }}</h4>
                    <div>
                        <a href="{{ route('admin.theaters.edit', $theater) }}" class="btn btn-warning btn-sm">Sửa</a>
                        <a href="{{ route('admin.theaters.index') }}" class="btn btn-secondary btn-sm">Quay lại</a>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="200">ID Rạp:</th>
                            <td>{{ $theater->id }}</td>
                        </tr>
                        <tr>
                            <th>Tên Rạp:</th>
                            <td><strong>{{ $theater->name }}</strong></td>
                        </tr>
                        <tr>
                            <th>Thành phố:</th>
                            <td>{{ $theater->city }}</td>
                        </tr>
                        <tr>
                            <th>Địa chỉ:</th>
                            <td>{{ $theater->address }}</td>
                        </tr>
                        <tr>
                            <th>Sức chứa:</th>
                            <td>
                                <span class="badge bg-primary">{{ number_format($theater->seating_capacity) }} ghế</span>
                            </td>
                        </tr>
                        <tr>
                            <th>Ngày tạo:</th>
                            <td>{{ $theater->created_at->format('d/m/Y, H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Cập nhật lần cuối:</th>
                            <td>{{ $theater->updated_at->format('d/m/Y, H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
