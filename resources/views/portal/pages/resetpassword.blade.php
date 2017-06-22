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
					<h1>Nieuw wachtwoord instellen</h1>
					
				@if ($showForm)
					<p>Vul je emailadres en nieuwe wachtwoord in en druk op "Opslaan" om een nieuw wachtwoord in te stellen.<br>Je wordt daarna direct ingelogd.</p>
					{!! Form::open(array('url' => 'wachtwoord-vergeten/reset','method' => 'post','class' => 'centered')) !!}

						{!! Form::hidden('token',$token) !!}
						{!! Form::text('email', '' ,array('placeholder' => 'E-mailadres','size' => '25')) !!}<br>
						{!! Form::password('password', array('placeholder' => trans('messages.RegisterPassword'),'size' => '25')) !!}<br>
						{!! Form::password('password_confirmation', array('placeholder' => trans('messages.RegisterPasswordConfirm'),'size' => '25')) !!}<br>
													
						{!! Form::submit('Opslaan') !!}
						
					{!! Form::close() !!}
				@endif
					
				@if ($message != '')
					<p>{{ $message }}</p>
				@endif
										
				@if($errors->has())
				  	{!! $errors->first('email','<p>:message</p>') !!}
					{!! $errors->first('password','<p>:message</p>') !!}					
				@endif	
				
				</div>
			</div>
		</div>
	</body>
</html>
	