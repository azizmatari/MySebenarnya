@extends('layouts.authWrapper')

@section('title', 'Login')

@section('content')
    <form method="POST" action="{{ route('login.submit') }}">
        @csrf
        <label>Email</label>
        <input type="email" name="email" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button type="submit">Login</button>
        <div class="form-footer">
            <p>Don’t have an account? <a href="{{ route('register.view') }}">Register</a></p>
            <a  href="{{ route('forgot.password') }}">Forgot your password?</a>
        </div>


    </form>

    @if(session('error'))
        <p style="color:red;">{{ session('error') }}</p>
    @endif
@endsection
