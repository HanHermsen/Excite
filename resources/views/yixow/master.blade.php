<!DOCTYPE html>
<html>
<head>
	<title>Yixow</title>

	<meta name="viewport" content="width=device-width, initial-scale=1">

	<link rel="stylesheet" href="https://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.css" />
	{!! HTML::style('css/yixow.css') !!}
	<!-- <script src="https://code.jquery.com/jquery-1.11.3.min.js"></script> -->
	<script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
	<script src="https://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.js"></script>
	{!! HTML::script('js/exciteShared.js') !!}
	{!! HTML::script('js/yixow.js') !!}
</head>
<body>
<div data-role="page" id='pageOne'>
@yield('pageOne')
</div><!-- /page -->



</body>
</html>
