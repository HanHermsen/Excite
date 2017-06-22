
	<script>
		function doStats(questionId, questionText) {
				Excite.qu.statsWindow.disabledButton = ''; // init to be disbabled button in Stats Home window
					$( "#miniStatsWindow" ).dialog({
						modal: true,
						height: 575,
						width: 410,
						autoOpen: false,
					});
				Excite.qu.ajaxGetMiniStats(questionId,questionText);
		}
	</script>

	<h1>Nieuwste vragen van Yixow</h1>
	@foreach ($qu as $q)
		@if ( $q->image != null )
			<div class='questionImage'><img class='qImage' src="/api/api/images/{{$q->image}}"></div>
			<br />
		@endif
		<h3>{{$q->question}}</h3>
		{{$q->ago}}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<button class='browserStatsButton' onclick='doStats({{$q->id}}, "{{$q->question}}")'>Bekijk statistieken</button>

		<br />
		<hr class='hrStyle'>
		<br />
	@endforeach

