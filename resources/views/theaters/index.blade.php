@extends('layouts.app')

@section('title', 'Theaters - CineBook')

@section('content')
<div class="row mb-4">
    <div class="col-md-12 d-flex justify-content-between align-items-center">
        <h2>Theaters Management</h2>
        <a href="{{ route('theaters.create') }}" class="btn btn-primary">Add New Theater</a>
    </div>
</div>

@if($theaters->count() > 0)
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>City</th>
                    <th>Address</th>
                    <th>Seating Capacity</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($theaters as $theater)
                <tr>
                    <td>{{ $theater->id }}</td>
                    <td><strong>{{ $theater->name }}</strong></td>
                    <td>{{ $theater->city }}</td>
                    <td>{{ $theater->address }}</td>
                    <td>{{ number_format($theater->seating_capacity) }} seats</td>
                    <td>
                        <a href="{{ route('theaters.show', $theater) }}" class="btn btn-sm btn-info">View</a>
                        <a href="{{ route('theaters.edit', $theater) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('theaters.destroy', $theater) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this theater?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="alert alert-info">
        No theaters found. <a href="{{ route('theaters.create') }}">Create your first theater!</a>
    </div>
@endif
@endsection