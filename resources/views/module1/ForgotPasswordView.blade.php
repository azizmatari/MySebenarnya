@extends('layouts.authWrapper')

@section('title', 'Forgot Password')

@section('content')
    <div class="form-container">
        <form method="POST" action="#">
            @csrf
            <label for="email">Enter your email</label>
            <input type="email" name="email" required>
            <button type="submit">Send Reset Link</button>
        </form>
    </div>
@endsection
