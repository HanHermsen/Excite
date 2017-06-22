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
		<img  id="background" src="{{ URL::to('images/bg_voorwaarden.jpg') }}" id="bg" alt="">
		<div id="content">
			<div class="section" id="section_first">
				<div id="title_wrapper">
					<h1>Iedereen telt mee</h1>
					<h2>daar kun je mee rekenen</h2>
				</div>
				<div id="sliderContainer" class="hiddenForMedium">
					@include('portal.includes.slider')
				</div>
			</div>
			<div class="section">
				<h2 class="subheader">Voorwaarden voor het gebruik van Yixow.<br>Laatst bijgewerkt: maart 2016</h2>
				<div class="column first">
					<h3>Grote vrijheid.</h3>
					<p>Gebruikers hebben grote vrijheid. Er is geen beperking in onderwerpen en er gelden geen explicite voorschriften voor het gebruik van woord en beeld. Onze voorwaarden betreffen uitsluitend de bescherming van rechten in het algemeen en een oproep aan je verantwoordelijkheid in het gebruik. Wij verzoeken je daarom om onze minimale voorwaarden met maximale aandacht te lezen.</p>
				</div>
				<div class="column">
					<h3>Wettelijk en maatschappelijk kader.</h3>
					<p>Je bent verantwoordelijk om Yixow te gebruiken binnen de kaders van de wet en het algemeen maatschappelijk aanvaarde. Wij nodigen je uit om je daar naar te gedragen bij het plaatsen van vragen, antwoordopties en beeldmateriaal. Daarbij geven we als referentie mee dat de app is bedoeld en gecategoriseerd voor gebruikers vanaf 12 jaar.</p>
				</div>
				<div class="column">
					<h3>Auteursrecht en portretrecht.</h3>
					<p>Bij iedere vraag kan tekst en beeldmateriaal worden toegevoegd. De gebruiker is persoonlijk verantwoordelijk voor het respecteren van de rechten die aan het (weder)gebruik van het materiaal verbonden zijn. Yixbow wijst iedere aansprakelijkheid daarvoor nadrukkelijk van de hand.</p>
				</div>
			</div>
			<div class="section">
				<div class="column first">
					<h3>Ongepaste uitingen.</h3>
					<p>Je kunt vragen, inbegrepen de antwoordopties en het beeldmateriaal, die, naar je persoonlijke oordeel, ongepast zijn, markeren als ongepast. De vraag, inbegrepen de antwoordopties en het beeldmateriaal, verdwijnt daarmee uit je eigen overzicht. Bij meerdere markeringen wordt de vraag voor alle gebruikers onzichtbaar. Yixbow behoudt zich bovendien het recht voor om daar naar haar</p>
				</div>
				<div class="column">
					<p>eigen normen op te interveniëren en content die zij ongepast vindt,te verwijderen. Yixbow wijst iedere verantwoordelijkheid voor die uitingen en de gevolgen daarvan, nadrukkelijk van de hand. Yixbow behoudt zich het recht voor om dergelijke content, zelfs bij het vermoeden daarvan, te verwijderen en daarvan, voor zover dat mogelijk is, melding te maken bij relevante autoriteiten en/of gedupeerden.</p>
				</div>
				<div class="column">
					<p>Als je vermoedt dat je privacy desondanks geschonden is, kun je dat melden bij Yixow op welcome@yixow.com of bij het College Bescherming Persoonsgegevens.<br>Wij vinden jouw privacy belangrijk en willen graag weten als en waardoor het (b)lijkt dat wij daarin onvoldoende zorgzaam zijn.</p>
				</div>
			</div>
			<div class="section">
				<div class="column first">
					<h3>Zorgvuldig in eigen gebruik.</h3>
					<p>Wij beloven dat je voor derden anoniem blijft bij al je vragen en antwoorden op Yixow. We brengen je profielinformatie niet in relatie je identiteit. Wij willen je daarnaast attent maken om ook zelf aan je anonimiteit te denken binnen de groepen waaraan je deelneemt. Groepen ontstaan in de meeste gevallen op basis van relaties die buiten onze service reeds bestonden.</p>
				</div>
				<div class="column containsbutton">
					<h3>&nbsp;</h3>
					<p>In die relaties is waarschijnlijk meer van jou bekend en is het mogelijk dat je profielinformatie door gebruikers zelf in verband wordt gebracht met kennis die ze al van je hebben. In het algemeen geldt dat zo'n verband makkelijker is te leggen naarmate de groep kleiner is. Op het groepslabel laten wij daarom altijd zien uit hoeveel deelnemers de groep bestaat.</p>
					<a href="{{ URL::to('privacy') }}"><button>Lees de privacyverklaring</button></a>
				</div>
				
			</div>
			<footer>
				@include('portal.includes.footer')
			</footer>
		</div>
		@include('portal.includes.footer-stats')	
	</body>
</html>
	