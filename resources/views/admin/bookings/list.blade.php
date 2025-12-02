@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <h1 class="mb-0">Tickets</h1>
            <form method="GET" action="{{ route('admin.bookings.index') }}" class="d-flex align-items-center gap-2">
                <div class="input-group" style="width: 300px;">
                    <input type="text" 
                           class="form-control" 
                           id="search" 
                           name="search" 
                           placeholder="Search by movie name..."
                           value="{{ request('search') }}">
                    @if(request('search'))
                    <a href="{{ route('admin.bookings.index') }}" class="btn btn-outline-secondary" title="Clear filter">
                        <i class="bi bi-x-circle"></i>
                    </a>
                    @endif
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Ticket sales statistics by movie --}}
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-ticket-perforated"></i> Ticket Sales Statistics by Movie</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">No.</th>
                            <th>Movie Title</th>
                            <th class="text-end" style="width: 200px;">Tickets Sold</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ticketsByMovie as $index => $item)
                            <tr>
                                <td>{{ ($ticketsByMovie->currentPage() - 1) * $ticketsByMovie->perPage() + $index + 1 }}</td>
                                <td>
                                    <strong>{{ $item->title }}</strong>
                                </td>
                                <td class="text-end">
                                    <span class="badge bg-primary fs-6">{{ number_format($item->total_tickets) }} tickets</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-4">
                                    <p class="text-muted mb-0">No ticket sales data available.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($ticketsByMovie->count() > 0)
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="2" class="text-end">Total ({{ $ticketsByMovie->total() }} movies):</th>
                            <th class="text-end">
                                <span class="badge bg-primary fs-6">{{ number_format($totalTickets) }} tickets</span>
                            </th>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
            {{-- Phân trang --}}
            @if($ticketsByMovie->hasPages())
                <div class="card-footer bg-light">
                    {{ $ticketsByMovie->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

@endsection

