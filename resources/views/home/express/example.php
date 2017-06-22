<!DOCTYPE html>

<html>
<head>
	<meta charset="UTF-8" />
	<title>Express Groep bestelling</title>       
	<!-- {!! HTML::style('css/style.css') !!}
	{!! HTML::style('css/questions.css') !!} -->
	{!! HTML::style('css/home.css') !!}
	{!! HTML::style('jquery/jquery-ui.min.css') !!}
	<link rel="stylesheet" href="/css/leaflet.css" />
	
	{!! HTML::script('jquery/external/jquery/jquery.js') !!}
	{!! HTML::script('jquery/jquery-ui.min.js') !!}
	{!! HTML::script('/js/exciteShared.js') !!}
	<?php
		if( !(strpos($_SERVER['HTTP_USER_AGENT'],"Trident") === false) )
			echo "<script>L_PREFER_CANVAS = true;</script>\n"; // this is for leaflet perfomanceon IE
	?>

	<script src="/js/leaflet.js"></script> <!-- map viewer -->
	<script src="/js/express.js"></script>

</head>

<body>
	<div id='header'>
		<div id='headerTop'>
			<div id='headerTopLogo'><img width='350px' src='/images/whiteYixowLogo.png' /></div>
			<div id='headerTopText'><h2>intelligente interactie met je publiek</h2></div>
			<div id='headerMenu'><div>&nbsp;</div>
			</div>
		</div>
		<div id='headerLogo'><img width='250px' src='/images/yixowlogobw.png' /></div>
		<div id='headerText' style='font-size: 18px;'>in organisaties, in de markt en in de samenleving</div>
	</div>
	<div id='content' class='flexContainer'>
		<div id='userInputBox' class='flexBox'>
		@include('home.express.form')
		</div>
		<div id='mapBox' class='flexBox'>
			<div id='openMap'> </div>
		</div>
	</div>
@if (count($errors) > 0)
	<div id="dialog-message">
		<ul>
		@foreach ($errors->all() as $error)
			<li>{{ $error }}</li>
		@endforeach
		</ul>
	</div>
@endif
	<div id='js-dialog-message'>
	<ul>
	</ul>
	</div>


</body>
</html>
