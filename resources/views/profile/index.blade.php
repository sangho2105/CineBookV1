@extends('layouts.app')

@section('title', 'My Profile')


@section('content')
<div class="container">
    <h1 class="mb-4">My Profile</h1>

    {{-- Statistics Cards --}}
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card text-center h-100">
                <div class="card-body">
                    <i class="bi bi-ticket-perforated text-primary" style="font-size: 2rem;"></i>
                    <h3 class="mt-2 mb-0">{{ $totalBookings }}</h3>
                    <p class="text-muted mb-0">Total Bookings</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card text-center h-100">
                <div class="card-body">
                    <i class="bi bi-ticket text-success" style="font-size: 2rem;"></i>
                    <h3 class="mt-2 mb-0">{{ $totalTickets }}</h3>
                    <p class="text-muted mb-0">Total Tickets</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card text-center h-100">
                <div class="card-body">
                    <i class="bi bi-currency-dollar text-warning" style="font-size: 2rem;"></i>
                    <h3 class="mt-2 mb-0">{{ format_currency($totalSpent) }}</h3>
                    <p class="text-muted mb-0">Total Spent</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card text-center h-100">
                <div class="card-body">
                    <i class="bi bi-heart-fill text-danger" style="font-size: 2rem;"></i>
                    <h3 class="mt-2 mb-0">{{ $favoriteMoviesCount }}</h3>
                    <p class="text-muted mb-0">Favorite Movies</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Personal Information --}}
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0">Personal Information</h5>
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('profile.edit') }}">Edit</a>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Name:</strong> {{ $user->name }}</p>
                            <p class="mb-2"><strong>Email:</strong> {{ $user->email }}</p>
                            @if($user->phone)
                                <p class="mb-2"><strong>Phone:</strong> {{ $user->phone }}</p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            @if($user->date_of_birth)
                                <p class="mb-2"><strong>Date of Birth:</strong> {{ \Carbon\Carbon::parse($user->date_of_birth)->format('d/m/Y') }}</p>
                            @endif
                            @if($user->preferred_language)
                                <p class="mb-2"><strong>Preferred Language:</strong> {{ $user->preferred_language }}</p>
                            @endif
                            @if($user->preferred_city)
                                <p class="mb-2"><strong>Preferred City:</strong> {{ $user->preferred_city }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3">Quick Actions</h5>
                    <div class="d-grid gap-2">
                        <a class="btn btn-outline-success" href="{{ route('profile.favorites') }}">
                            <i class="bi bi-heart-fill"></i> Favorite Movies
                        </a>
                        <a class="btn btn-outline-info" href="{{ route('profile.tickets') }}">
                            <i class="bi bi-ticket-perforated"></i> My Tickets
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

