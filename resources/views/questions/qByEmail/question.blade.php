@extends('questions/qByEmail/shared')
@section('content')
	<script>
		$(document).ready(function(){
			// any answer selected? 
			$("#submitB").on( 'click', function(event) {
				checked = false;
				$(".radioB").each( function() {
					elem=$(this);
					if ( elem.prop('checked')) {
						checked = true;
						return;
					}
				});
				if ( ! checked ) {
					event.preventDefault();
					Excite.dialogAlert("Kies een antwoord");
				}
				// Form submit here, when not prevented
			});
		});
	</script>
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
	<br /><br />
	@if ( $q->image != null )
		<div class='questionImage'><img class='qImage' src="/api/api/images/{{$q->image}}"></div>
		<br />
	@endif
	<h4>{{$q->question}}</h4>
	{!! Form::open(array('action' => 'Questions\QController@storeAnswer','id' => 'exciteForm')) !!}
	{!! Form::hidden('hiddenEmailLinkOption', $option)!!}
	@foreach ($opts as $opt)
		{!! Form::radio('answerOption', $opt->id, null, [ 'class' => 'radioB'] ) !!} {{ $opt->text }}<br />
	@endforeach
		<br /><br />
	<button id='submitB'>Geef antwoord</button>
{!! Form::close() !!}
@stop