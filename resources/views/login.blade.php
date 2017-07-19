@extends('app')

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<div class="panel panel-default">
				<div class="panel-heading">Login</div>

				<div class="panel-body">
					<h1>Entrar</h1>
					<p>
						{{ $errors->first('email') }}
						{{ $errors->first('password') }}
					</p>

					<p>
						{{ Form::label('email', 'Email Address') }}
						{{ Form::text('email', Input::old('email'), array('placeholder' => 'awesome@c.com')) }}
					</p>

					<p>
						{{ Form::label('password', 'Password') }}
						{{ Form::password('password') }}
					</p>

					<p>
						{{ Form::submit('Submit!') }}
					</p>

					{{ Form::close() }}

					You are...
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
