<!doctype html>
<html lang="nl">
	<head>
		@section('title', $title)
		@include('portal.includes.head')
	</head>
	<body>
		<header>
			@include('portal.includes.header')
		</header>
		<img  id="background" src="{{ URL::to('images/bg_home.jpg') }}" id="bg" alt="">
		<div id="content">
			<div class="section" id="section_first">
				<div id="forgot_password">
					<h1>Wachtwoord herstellen</h1>
					
					@if ($showForm)
					<p>Vul je e-mailadres in en druk op "reset" om een e-mail met een link te ontvangen waarmee je een nieuw wachtwoord kan aanmaken.</p>
						{!! Form::open(array('url' => 'wachtwoord-vergeten','method' => 'post','class' => 'centered')) !!}
							{!! Form::text('email', '' ,array('placeholder' => trans('messages.LoginEmail'),'size' => '25')) !!}
							{!! Form::submit('Reset') !!}
							
						{!! Form::close() !!}
					@endif
					
					@if ($message != '')
						<p>{{ $message }}</p>
					@endif
					
					@if($errors->has())
						<p>{!! $errors->first('email',':message') !!}</p>
					@endif
					
				</div>
			</div>
		</div>
	</body>
</html>
	