<!doctype html>
<html lang="nl">
	<head>
		@section('title', $title)
		@include('portal.includes.head')
	</head>
	<body>
		<header>
			@include('portal.includes.header')
		</header>
		<img  id="background" src="{{ URL::to('images/bg_home.jpg') }}" id="bg" alt="">
		<div id="content">
			<div class="section" id="section_first">
				<div id="question_by_email_block" style="padding:0;margin-top:40px">			
					<div id="appstore_buttons">
						<a href="https://itunes.apple.com/nl/app/yixow-open-in-opinie.-zelf/id999394569?mt=8"><img alt="Download in App Store" src="https://www.yixow.com/images/appstore_ios.svg"></a>
						<a href="https://play.google.com/store/apps/details?id=nl.montanamedia.yixow&amp;hl=nl"><img alt="Download in Google Play" src="https://www.yixow.com/images/google-play-badge.png"></a>
						
						<p class="question_by_email_subtext">Met de Yixow app op je telefoon bereik je meer met je mening. Gratis en zonder reclame.</p>			
					</div>					
				</div>
			</div>
		</div>
	</body>
</html>
	