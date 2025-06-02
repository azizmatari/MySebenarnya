@extends('layouts.layoutWrapper')

@section('title', 'Dashboard')

@section('content')
    <h1>Welcome to the User Dashboard</h1>
    <p>This is where the latest news and updates will appear.</p>

    <div class="event-grid">
        @for ($i = 1; $i <= 6; $i++)
            <div class="event-card">
                <img src="{{ asset('images/event-placeholder.png') }}" alt="Event Image">
                <h3>Event Title {{ $i }}</h3>
            </div>
        @endfor
    </div>

    <a href="#" class="btn-view-more">View More Events</a>
@endsection
