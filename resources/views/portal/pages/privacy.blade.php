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
		<img  id="background" src="{{ URL::to('images/bg_privacy.jpg') }}" id="bg" alt="">
		<div id="content">
			<div class="section" id="section_first">
				<div id="title_wrapper">
					<h1>TRANSPARANT & ANONIEM</h1>
					<h2>EEN BETROUWBARE COMBINATIE</h2>
				</div>
				<div id="sliderContainer" class="hiddenForMedium">
					@include('portal.includes.slider')
				</div>
			</div>
			<div class="section">
				<h2 class="subheader">Privacy verklaring van Yixow.<br>Laatst bijgewerkt: Maart 2016</h2>
				<p>&nbsp;</p>
				<div class="column first">
					<p>Wij willen uitblinken in het beschermen van persoonlijke gegevens. Daarom maken wij de toegang tot onze service mogelijk met minimale persoonlijke gegevens.<br>Bij aanmelding slaan wij alleen het door jou verstrekte e-mail adres op. Dat bewaren we zolang je gebruikt wilt kunnen maken van onze service. Het mailadres dat je verstrekt bij de aanmelding wordt door ons als vertrouwelijke informatie beschouwd en behandeld. Je mailadres wordt door ons niet aan derden verstrekt.</p>
				</div>
				<div class="column">
					<p>We gebruiken je mailadres uitsluitend voor ons contact met jou, om een account -en om een token aan te maken. <br>In de token wordt je mailadres versleuteld in een code. De token wordt gebruikt om te communiceren tussen jou en onze service.<br><br>Als onze service je is aangeboden door een derde partij dan beschikte die partij op dat moment reeds over een e-mailadres dat je gebruikt en mogelijk over meer persoonlijke gegevens.</p>
				</div>
				<div class="column">
					<p>De profielinformatie die jij via de app zelf beheert beschouwen wij niet als vertrouwelijke informatie. Je bent vrij om te kiezen welke velden in het profiel je invult. Wij brengen die informatie nooit in relatie met je e-mailadres, ook niet als je gebruik maakt van onze service op uitnodiging van een derde partij.<br><br>Je gebruiksnaam wordt niet getoond in relatie tot gestelde vragen en gegeven antwoorden.</p>
				</div>
			</div>
			<div class="section">
				<div class="column first">
					<p>Als je vermoedt dat je privacy desondanks geschonden is, kun je dat melden bij Yixow op welcome@yixow.com of bij het College Bescherming Persoonsgegevens.<br>Wij vinden jouw privacy belangrijk en willen graag weten als en waardoor het (b)lijkt dat wij daarin onvoldoende zorgzaam zijn.
</p>
				</div>
				<div class="column">
					<h3>Zorgvuldig in eigen gebruik.</h3>
					<p>Wij beloven dat je voor derden anoniem blijft bij al je vragen en antwoorden op Yixow. We brengen je profielinformatie niet in relatie je identiteit. Wij willen je daarnaast attent maken om ook zelf aan je anonimiteit te denken binnen de groepen waaraan je deelneemt. Groepen ontstaan in de meeste gevallen op basis van relaties die buiten onze service reeds bestonden.</p>
				</div>
				<div class="column containsbutton">
					<h3>&nbsp;</h3>
					<p>In die relaties is waarschijnlijk meer van jou bekend en is het mogelijk dat je profielinformatie door gebruikers zelf in verband wordt gebracht met kennis die ze al van je hebben. In het algemeen geldt dat zo'n verband makkelijker is te leggen naarmate de groep kleiner is. Op het groepslabel laten wij daarom altijd zien uit hoeveel deelnemers de groep bestaat.</p>
					<a href="{{ URL::to('voorwaarden') }}"><button>Lees de voorwaarden</button></a>
				</div>
			</div>

			<footer>
				@include('portal.includes.footer')
			</footer>
		</div>
		@include('portal.includes.footer-stats')	
	</body>
</html>