<!doctype html>
<html lang="pt">
  <head>
    <meta charset="UTF-8">
    <title>@yield('title')</title>
    <link rel="stylesheet" href="{{ URL::asset('css/bootstrap-3.3.7.min.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('css/font-awesome-4.6.1.min.css') }}">
    <!-- link rel="stylesheet" href="{{ URL::to('assets/css/app.css') }}" -->
    @yield('styles')
  </head>
  <body>
    @include('html_parts.header')
    <div class="container">
        @yield('content')
    </div>
    <script src="{{ URL::to('js/jquery-3.1.1.min.js') }}"></script>
    <script src="{{ URL::to('js/bootstrap-3.3.7.min.js') }}"></script>
      @yield('scripts')
  </body>
</html>
