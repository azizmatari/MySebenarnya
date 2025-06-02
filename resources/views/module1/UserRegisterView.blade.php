@extends('layouts.authWrapper')

@section('title', 'Register')

@section('content')
    <form method="POST" action="{{ route('register.submit') }}">
        @csrf

        <label>Name</label>
        <input type="text" name="userName" required>

        <label>Email</label>
        <input type="email" name="userEmail" required>

        <label>Username</label>
        <input type="text" name="userUsername" required>

        <label>Password</label>
        <input type="password" name="userPassword" required>

        <label>Confirm Password</label>
        <input type="password" name="userPassword_confirmation" required>

        <button type="submit">Register</button>

        <p class="login-link">Already have an account? <a href="{{ route('login') }}">Login</a></p>
    </form>

    @if ($errors->any())
        <ul style="color: red; margin-top: 10px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif
@endsection
