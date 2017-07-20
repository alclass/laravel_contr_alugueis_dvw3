@extends('app')

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<div class="panel panel-default">
				<div class="panel-heading">Login</div>

				<div class="panel-body">
					<h1>Registrar-se</h1>
            @if(count($errors)>0)
            <div class="alert alert-danger">
              @foreach($errors as $error)
                <p>
                  {{ $error }}
                  {{-- $errors->first('email') --}}
      						{{-- $errors->first('password') --}}
                </p>
              @endforeach
            </div>
            @endif

            <form action="{{ route('authusers.signup') }}" method="POST">
              {!! csrf_field() !!}
              <div class="form-group">
                <label for="email">Email Address</label>
                <input type="text" id="email" name="email">
                {{-- Form::label('email', 'Email Address') --}}
                {{-- Form::text('email', Input::old('email'), array('placeholder' => 'awesome@c.com')) --}}
              </div>
              <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password">
                {{-- Form::label('password', 'Password') --}}
    						{{-- Form::password('password') --}}
              </div>
              <button type="submit" class="btn btn-primary">Registrar-se</button>
              {{-- Form::submit('Registrar-se!') --}}
            </form>

					<p>
					</p>

					{{-- Form::close() --}}

					You are...
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
