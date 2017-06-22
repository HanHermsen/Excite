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
	@include('yixow.header')
	</div>
	<div role="main" class="ui-content">
	<br />
		<p>Hier komen de Groepen</p>
	</div><!-- /content -->

	@include('yixow.menuPanel')

	<div id='footer' data-role="footer" data-position='fixed' style='background-color: transparent; border-color: transparent;'>
	<a href="#" data-role="button" data-icon="plus" data-iconpos="notext"  style='float: right' >Menu</a>
		<!-- <h4>Page Footer</h4> -->
	</div><!-- /footer -->
	<script>
	Excite.y.pageId = Excite.y.GROUPS;
	try {
		Excite.y.highlight(Excite.y.GROUPS); } catch(e) {};
	</script>
</div><!-- /page -->


<script>
	//Excite.y.pageId = Excite.y.GROUPS;
	//Excite.y.highlight(Excite.y.GROUPS);
</script>
</body>
</html>
