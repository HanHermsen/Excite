<?php
$allow = array("213.125.163.170", "83.86.80.68","213.125.23.251");
if (!in_array ($_SERVER['REMOTE_ADDR'], $allow) && gethostname() != "han64") {
     exit('Under Construction');
} ?>

<!DOCTYPE html>
<html>
    <head>
    	<meta charset="UTF-8" />
        <title>Yixow</title>

		{!! HTML::style('css/style.css') !!}
		{!! HTML::style('jquery/jquery-ui.min.css') !!}
		@section('customStyleHead')
		@show
		
		{!! HTML::script('jquery/external/jquery/jquery.js') !!}
		{!! HTML::script('jquery/jquery-ui.min.js') !!}
		{!! HTML::script('js/dropdownmenu.js') !!}
		
		@section('customScriptHead')
		@show

    </head>
    <body>
    
	    <div id="Menu">
			<a href="/"><div class="SubMenuLogo"></div></a>
			<a class="SubMenu" href="/new">{{ trans('messages.MenuNew') }}</a>
			<a class="SubMenu" href="/guests">{{ trans('messages.MenuGuests') }}</a>
			<a class="SubMenu" href="/">{{ trans('messages.MenuQuestions') }}</a>
			<a class="SubMenu" href="/">{{ trans('messages.MenuGroups') }}</a>
			<a class="SubMenu" href="/">{{ trans('messages.MenuAdmin') }}</a>
		</div>
	    
	    <div id="Content">
	    	@yield('content')    	
		</div>
	
    </body>
</html>
