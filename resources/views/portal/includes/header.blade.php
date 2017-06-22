<button id="menu_button">
	<a><img id="menu_icon" src="{{ URL::to('images/menu.svg') }}"></a>
</button>
<ul id="menu">
	<li><a href="{{ URL::to('waarom-en-hoe') }}">Waarom & Hoe</a></li>
	<li><a href="{{ URL::to('abonnementen') }}">Abonnementen</a></li>
	<li>&nbsp;</li>
	<li><a href="{{ URL::to('privacy') }}">Privacy</a></li>
	<li><a href="{{ URL::to('voorwaarden') }}">Voorwaarden</a></li>
	<li><a href="{{ URL::to('contact') }}">Contact</a></li>
</ul>
<a href="/"><img id="headerlogo" alt="Yixow, wat jij vindt in de samenleving, in organisaties en in de markt" src="{{ URL::to('images/logo.png') }}"></a>
<div id="slogan" class="hiddenForMobile">wat jij vindt</div>
<div id="subslogan" class="hiddenForSmall">in de samenleving, in organisaties en in de markt</div>
<div id="login" class="hiddenForMedium">
	
	{!! Form::open(array('url' => '/auth/login','method' => 'post','class' => 'centered')) !!}
	    <div id="logininputs">
			{!! Form::text('email', '' ,array('placeholder' => trans('messages.LoginEmail'),'size' => '25')) !!}
			{!! Form::password('password', array('placeholder' => trans('messages.LoginPassword'),'size' => '25')) !!}
		</div>
		<div id="loginchecks">
			<p>{!! Form::checkbox('remember') !!}Onthoud</p>
			<p><a href="{{ URL::to('wachtwoord-vergeten') }}">Vergeten</a></p>
		</div>
		<input type="submit" value="&gt;" style="visibility:hidden">
		
	{!! Form::close() !!}
		
</div>