<p>nieuwste vragen uit <b>'publiek'</b></p>
<div class="slider">
	@foreach ($questions as $q)
		<div>
			@if ( $q->image != null )
				<div class="questionImage" style="background-image:url('/api/api/images/{{$q->image}}')"></div>
			@else
				<div class="questionImage" style="background-image:url('{{ URL::to('images/placeholder.png') }}')"></div>
			@endif
			<h3>{{$q->question}}</h3>
			<p>{{$q->ago}}</p>
			<button class='browserStatsButton' onclick='doStats({{$q->id}}, "{{$q->question}}");pauseSlider()'>Bekijk statistieken</button>
		</div>
	@endforeach
</div>