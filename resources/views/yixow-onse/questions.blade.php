<ons-page id='theTabs'>
       <ons-tabbar id="myTabbar" position="top">
          <ons-tab label="PUBLIEK" active>
          </ons-tab>
          <ons-tab label="POPULAIR">
          </ons-tab>
		  <ons-tab label="PASSEND">
          </ons-tab>
        </ons-tabbar>
<ons-page>
<ons-page id='questions'>
 	{{-- @include('yixow-onse.toolbar') --}}
		  @foreach ($qu as $q)
			@if ( $q->image != null )
				<div class='questionImage'><img class='qImage' src="http://excite.app/api/api/images/{{$q->image}}"></div>
				<br />
			@endif
			<div id='questionText' class='defaultText'>{{$q->question}}</div>
			<div id='questionAgo' class='defaultText'>{{$q->ago}}</div>
			<a href='#' id='answerBtn'>ANTWOORDEN</a>
			<br />
			<hr class='hrStyle'>
			<br />
		@endforeach
</ons-page>