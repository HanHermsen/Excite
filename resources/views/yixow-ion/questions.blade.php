
		  @foreach ($qu as $q)
			@if ( $q->image != null )
				<div class='questionImage'><img class='qImage' src="{{$q->image}}"></div>
				<br />
			@endif
			<div id='questionText' class='defaultText'>{{$q->question}}</div>
			<div id='questionAgo' class='defaultText'>{{$q->ago}}</div>
			<a href='#' id='answerBtn'>ANTWOORDEN</a>
			<br />
			<hr class='hrStyle'>
			<br />
		@endforeach
