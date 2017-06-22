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

<style>

</style>

<div data-role="page" id='pageOne'>
	@include('yixow.header')
	<br/><br />
		 <div data-role="navbar">
			<ul>
			<!-- ui-btn-active  -->
			  <li><a href="/yixow/" id='qTab1' class="qTab {{$tabClass['#qTab1']}}" >PUBLIEK</a></li>
			  <li><a href="/yixow?qTab=2" id='qTab2' class="qTab {{$tabClass['#qTab2']}}">POPULAIR</a></li>
			  <li><a href="/yixow?qTab=3" id='qTab3' class="qTab {{$tabClass['#qTab3']}}">PASSEND</a></li>
			</ul>
		 </div>	
	</div> <!-- header closure -->

	<div role="main" class="ui-content">
		<br />
	@if( $tab == '#qTab1')
		@foreach ($qu as $q)
			@if ( $q->image != null )
				<div class='questionImage'><img class='qImage' src="/api/api/images/{{$q->image}}"></div>
				<br />
			@endif
			<div id='questionText' class='defaultText'>{{$q->question}}</div>
			<div id='questionAgo' class='defaultText'>{{$q->ago}}</div>
			<a href='#' id='answerBtn'>ANTWOORDEN</a>
			<br />
			<hr class='hrStyle'>
			<br />
		@endforeach
	@else
		In Development {{$tab}}
	@endif
	</div><!-- /content -->

	@include('yixow.menuPanel')

	<div data-role="footer" id='footer' data-position='fixed' style='background-color: transparent; border-color: transparent;'>
	<a href="#" data-role="button" data-icon="plus" data-iconpos="notext"  style='float: right' >Menu</a>
		<!-- <h4>Page Footer</h4> -->
	</div><!-- /footer -->
	<script>
		Excite.y.pageId = Excite.y.QUESTIONS;
		Excite.y.highlight(Excite.y.QUESTIONS);
		//Excite.y.qTabSet(Excite.y.qTabId);
	</script>
</div><!-- /page -->


<script>
//Excite.y.qTabChoice(Excite.y.qTabId);
	//Excite.y.pageId = Excite.y.QUESTIONS;
	//Excite.y.highlight(Excite.y.QUESTIONS);
</script>
</body>
</html>
