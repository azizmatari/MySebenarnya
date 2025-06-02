<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title') - MySebenarnya</title>
    <link rel="stylesheet" href="{{ asset('css/sharedStyles/commonStyles.css') }}">

</head>
<body>
    {{-- Header --}}
    @include('layouts.header')

    <div class="main-container">
        {{-- Sidebar --}}
        @if(session()->has('user_id'))
            @if(session('role') == 'mcmc')
                @include('layouts.sidebarMcmc')
            @elseif(session('role') == 'public')
                @include('layouts.sidebarPublic')
            @elseif(session('role') == 'agency')
                @include('layouts.sidebarAgency')
            @endif
        @endif

        {{-- Page content --}}
        <main class="content-area">
            @yield('content')
        </main>
    </div>
</body>
</html>
