<!DOCTYPE html>

<html>
<head>
	<meta charset="UTF-8" />
	<title>Excite qBrowser</title>       
	{!! HTML::style('jquery/jquery-ui.min.css') !!}
	{!! HTML::style('css/style.css') !!}
	{!! HTML::style('css/questions.css') !!}
	<style>
		body {
			width: 98%;
			margin: auto;
			background-color: rgba(0,0,0,0.1);
		}
		hr.hrStyle {
			height: 12px;
			border: 0;
			box-shadow: inset 0 12px 12px -12px rgba(0, 0, 0, 0.5);
		}

		img.qImage {
			height: 150px;
		}
		.questionImage {
			width: 75%;
			margin: auto;
		}
	</style>

	{!! HTML::script('jquery/external/jquery/jquery.js') !!}
	{!! HTML::script('jquery/jquery-ui.min.js') !!}

	{!! HTML::script('js/exciteShared.js') !!}
	{!! HTML::script('js/stats.js') !!}

	@include('home/qbrowser.dialog')

	<!-- where the statistics go -->
	<div id='miniStatsWindow'> </div>
</body>
</html>
