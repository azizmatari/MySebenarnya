<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title') - MySebenarnya</title>
<link rel="stylesheet" href="{{ asset('css/sharedStyles/commonStyles.css') }}">
<link rel="stylesheet" href="{{ asset('css/module1/authForm.css') }}">
</head>
<body style="font-family: sans-serif; background: #f2f2f2;">

    {{-- Shared Header --}}
    @include('layouts.header') {{-- already exists --}}

    {{-- Page content --}}
    <div class="auth-container" style="max-width: 500px; margin: 40px auto; background: white; padding: 30px; border-radius: 8px;">
        @yield('content')
    </div>

</body>
</html>
