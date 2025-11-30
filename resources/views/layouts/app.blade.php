<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    <link rel="stylesheet" href="/css/app.css">
    @stack('styles')
</head>
<body>
    @include('partials.navbar')

    <main class="container">
        @if(isset($success))
            <div class="alert alert-success">
                {{ $success }}
            </div>
        @endif

        @if(isset($error))
            <div class="alert alert-danger">
                @if(is_array($error))
                    @foreach($error as $message)
                    {{ $message }} <br>
                    @endforeach
                @else
                {{ $error }}
                @endif
            </div>
        @endif

        @if(isset($warning))
            <div class="alert alert-warning">
                {{ $warning }}
            </div>
        @endif

        @if(isset($info))
            <div class="alert alert-info">
                {{ $info }}
            </div>
        @endif

        @yield('content')
    </main> 

    @include('partials.footer')

    <script src="/js/app.js"></script>
    @stack('scripts')
</body>
</html>
