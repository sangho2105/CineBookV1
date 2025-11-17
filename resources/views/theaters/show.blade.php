@extends('layouts.app')

@section('title', $theater->name . ' - CineBook')

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">{{ $theater->name }}</h4>
                <div>
                    <a href="{{ route('theaters.edit', $theater) }}" class="btn btn-warning btn-sm">Edit</a>
                    <a href="{{ route('theaters.index') }}" class="btn btn-secondary btn-sm">Back to List</a>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="200">Theater ID:</th>
                        <td>{{ $theater->id }}</td>
                    </tr>
                    <tr>
                        <th>Theater Name:</th>
                        <td><strong>{{ $theater->name }}</strong></td>
                    </tr>
                    <tr>
                        <th>City:</th>
                        <td>{{ $theater->city }}</td>
                    </tr>
                    <tr>
                        <th>Address:</th>
                        <td>{{ $theater->address }}</td>
                    </tr>
                    <tr>
                        <th>Seating Capacity:</th>
                        <td>
                            <span class="badge bg-primary">{{ number_format($theater->seating_capacity) }} seats</span>
                        </td>
                    </tr>
                    <tr>
                        <th>Created At:</th>
                        <td>{{ $theater->created_at->format('d F Y, H:i') }}</td>
                    </tr>
                    <tr>
                        <th>Updated At:</th>
                        <td>{{ $theater->updated_at->format('d F Y, H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection