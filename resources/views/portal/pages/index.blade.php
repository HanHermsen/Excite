<!doctype html>
<html lang="nl">
	<head>
		@section('title', $title)
		@include('portal.includes.head')		
		@include('portal.includes.head-stats')
	</head>
	<body>
		<header>
			@include('portal.includes.header')
		</header>
		<img  id="background" src="{{ URL::to('images/bg_home.jpg') }}" id="bg" alt="">
		<div id="content">
			<div class="section" id="section_first">
				<div id='extra_wrapper'>
					<div style='color:white;height:80px; margin-top: 0px;line-height: 140%'>Yixow steunt:
						<a style='color:white' href='http://www.laatgroningennietzakken.nl/' target='_blank'>Laat Groningen niet zakken</a><br/>
						<a style='color:white' href='https://laatgroningennietzakken.petities.nl/' target='_blank'>Onderteken de petitie</a><br />
						<a style='color:white' href='https://yixow.com/metahlApp/' target='_blank'>Er is een kaart met de landelijke response</a><br />	
						Zie ook: <a style='color:white' href='http://gasberaad.nl/' target='_blank'>Groninger Gasberaad</a><br />						
					</div>
				</div>
				<div id="title_wrapper">
					<h1>Opinieonderzoek</h1>
					<h2>Dat mensen aan je verbindt</h2>
					<!--
					<div style='color:white;height:70px; margin-top: 10px'>Yixow steunt:
					<a style='color:white' href='http://www.laatgroningennietzakken.nl/' target='_blank'>Laat Groningen niet zakken</a><br/>
					<a style='color:white' href='https://laatgroningennietzakken.petities.nl/' target='_blank'>Onderteken de petitie</a><br />
					<a style='color:white' href='https://yixow.com/metahlApp/' target='_blank'>Er is een kaart van de landelijke response</a>					
					</div>
					-->
				</div>				
			</div>
				<div id="sliderContainer" class="hiddenForMedium">					
					@include('portal.includes.slider')
				</div>
			<div class="section">
				<h2>Vragen stellen is het nieuwe adverteren</h2>
				<p class="subheader">Bouw vraag voor vraag een band op met jouw publiek, dat je bereikt <br>tot in hun binnenzak. Betrokken mensen leveren je eerlijk informatie.</p>
				<div class="column first containsbutton">
					<h3>Groeien met klanten</h3>
					<p>Met aandacht win je aandacht. <br>Uit response haal je kansen. Openheid wekt vertrouwen en schept een band.</p>
					<p>Als volgers promotors worden groei je harder met elkaar.</p>
					<p>Betrek publiek met Yixow.</p>
					<a href="{{ URL::to('abonnementen') }}"><button>Abonnementen</button></a>
				</div>
				<div class="column">
					<h3>Met medewerkers</h3>
					<p>Real-time surveys leveren permanent informatie over passie en mogelijkheden in de onderneming.</p>
					<p>Vertrouwen en betrokkenheid worden zichtbaar met directe feedback uit Yixow.</p>
				</div>
				<div class="column containsbutton">
					<h3>In de samenleving</h3>
					<p>We willen mensen meer stem en een groter bereik geven.</p>
					<p>Aandacht voor publiek in Zorg, Onderwijs en Maatschappelijk initiatief ondersteunen wij gratis.</p>
					<p>Geef mensen ruimte met Yixow.</p>
					<div id="appstore_buttons">
						<a href="https://itunes.apple.com/nl/app/yixow-open-in-opinie.-zelf/id999394569?mt=8"><img src="{{ URL::to('images/appstore_ios.svg') }}" alt="Download in App Store"></a>
						<a href="https://play.google.com/store/apps/details?id=nl.montanamedia.yixow&hl=nl"><img src="{{ URL::to('images/google-play-badge.png') }}" alt="Download in Google Play"></a>
					</div>
				</div>
			</div>
			<footer>
				@include('portal.includes.footer')
			</footer>
		</div>		
		@include('portal.includes.footer-stats')		
	</body>
</html>
	