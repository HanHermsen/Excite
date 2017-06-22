@extends('questions/qByEmail/shared')
@section('content')
	{!! HTML::script('js/stats.js') !!}
	<style>
		body {
			width: 98%;
			margin: auto;
			background-color: rgba(0,0,0,0.1);
		}
		img.qImage {
			height: 150px;
		}
		.questionImage {
			width: 75%;
			margin: auto;
		}
	</style>
<br />
@if (! $answerDone)
	<h3>Bedankt voor je antwoord!</h3>
@else
	<h3 style='color: red;'>Je hebt al een antwoord gegeven op deze vraag.</h3>
@endif
<br /><br />
	@if ( $q->image != null )
		<div class='questionImage'><img class='qImage' src="/api/api/images/{{$q->image}}"></div>
		<br />
	@endif
	<h4>Response op: {{$q->question}}</h4>
	<script>
	$(document).ready(function(){
		// optional third param true is for embedding in <div id='miniStats'> 
		Excite.qu.ajaxGetMiniStats({{$q->id}},'{{$q->question}}',true);
	});
	</script>
<div id='miniStats'></div>

@stop