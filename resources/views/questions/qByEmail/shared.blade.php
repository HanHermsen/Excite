<!DOCTYPE html>
<html lang="en">

<head>

	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	
	<title>Yixow</title>
	
	<!-- Custom CSS -->
	{!! HTML::style('jquery/jquery-ui.min.css') !!}
	{!! HTML::style('css/style.css') !!}
	
	{!! HTML::script('jquery/external/jquery/jquery.js') !!}
	{!! HTML::script('jquery/jquery-ui.min.js') !!}
	{!! HTML::script('js/exciteShared.js') !!}
	
</head>
<body>
<h1>Yixow vraag per Email</h1>
@yield('content')
<!-- for javascript dialog message -->
<div id='js-dialog-message'>
<ul>
</ul>
</div>

</body>

</html>