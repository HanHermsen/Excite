<!doctype html>
<html lang="nl">
	<head>
		@section('title', $title)
		@include('portal.includes.head')
		{!! HTML::script('/js/question-by-email.js') !!}
	</head>
	<body>
		<header>
			@include('portal.includes.header')
		</header>
		<img  id="background" src="{{ URL::to('images/bg_home.jpg') }}" id="bg" alt="">
		<div id="content">
			<div class="section" id="section_first">
				<div id="question_by_email_block">
										
				@if ($showForm)
					
					<p id="question_by_email_title">Nieuwe vraag van:<br><strong>{{$group}}</strong></p>
					
					<div id="question_by_email_inner">
					@if ( $q->image != null )
						<div class="questionImage" style="background-image:url('/api/api/images/{{$q->image}}')"></div>
					@else
						<div class="questionImage" style="background-image:url('{{ URL::to('images/placeholder.png') }}')"></div>
					@endif
					
					<h3>{{$q->question}}</h3>
					
					{!! Form::open(array('url' => '/','method' => 'post','class' => 'centered', 'name' => 'question_by_email_form')) !!}

						{!! Form::hidden('hiddenEmailLinkOption',$option) !!}
						
						@foreach ($opts as $opt)
							{!! Form::button($opt->text, [ 'data-id' => $opt->id, 'class' => 'radioB']) !!}<br>
							
						@endforeach
													
						{!! Form::button('PLAATSEN', [ 'id' => 'submitB']) !!}
						
					{!! Form::close() !!}
					</div>
				@endif
					
				@if ($message != '')
					<p>{{ $message }}</p>
				@endif
				
					<div id="appstore_buttons">
						@if ($os == 'ios' || $os == 'desktop')
						<a href="https://itunes.apple.com/nl/app/yixow-open-in-opinie.-zelf/id999394569?mt=8"><img alt="Download in App Store" src="https://www.yixow.com/images/appstore_ios.svg"></a>
						@endif
						@if ($os == 'android' || $os == 'desktop')
						<a href="https://play.google.com/store/apps/details?id=nl.montanamedia.yixow&amp;hl=nl"><img alt="Download in Google Play" src="https://www.yixow.com/images/google-play-badge.png"></a>
						@endif
						
						@if ($os == 'desktop')
							<p class="question_by_email_subtext">Met de Yixow app op je telefoon bereik je meer met je mening. Gratis en zonder reclame.</p>
						@else
							<p class="question_by_email_subtext">Download Yixow gratis, zonder reclame, <br>en bereik meer met je mening.</p>
						@endif					
					</div>
					
					
				</div>
			</div>
		</div>
	</body>
</html>
	