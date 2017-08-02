<!doctype html>
<html lang="pt">
  <head>
    <meta charset="UTF-8">
    <title>@yield('title')</title>
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <!--link rel="stylesheet" href="{{ URL::asset('css/bootstrap-3.3.7.min.css') }}"-->

    <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">


    <link rel="stylesheet" href="{{ URL::asset('css//font-awesome-4.7.0/css/font-awesome.min.css') }}">
    <!-- link rel="stylesheet" href="{{ URL::to('assets/css/app.css') }}" -->
    @yield('styles')
  </head>
  <body>
    @include('html_parts.header')
    <div class="container">
        @yield('content')
    </div>
    <script src="http://code.jquery.com/jquery-3.2.1.min.js"></script>
    <!--script src="{{ URL::to('js/jquery-3.1.1.min.js') }}"></script-->

    <!-- Latest compiled and minified JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
    <!--script src="{{ URL::to('js/bootstrap-3.3.7.min.js') }}"></script-->
      @yield('scripts')
  </body>
</html>
