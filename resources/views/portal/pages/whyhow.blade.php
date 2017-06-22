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
		<img  id="background" src="{{ URL::to('images/bg_waarom.jpg') }}" id="bg" alt="">
		<div id="content">
			<div class="section" id="section_first">
				<div id="title_wrapper">
					<h1>Anders denken</h1>
					<h2>en net zo overtuigd</h2>
				</div>
				<div id="sliderContainer" class="hiddenForMedium">
					@include('portal.includes.slider')
				</div>
			</div>
			<div class="section">
				<h2>Waarom Yixow?</h2>
				<p class="subheader">Omdat mensen anders denken dan jij, andere keuzes maken en net zo overtuigd zijn als jij.<br>Daarom is Yixow er. Met belangstelling voor andere mensen en meningen.<br>Om te ontdekken hoe bruikbaar onze verschillen en onze overeenkomsten zijn.</p>
				<div class="column first">
					<h3>Gevoel voorbij feiten.</h3>
					<p>Soms zijn feiten niet meer dan een tijdelijke beperking.<br> 
					Neem het feit dat de aarde plat was of dat opgewarmde spinazie ongezond was.<br>
					Ons gevoel voor ontwikkeling brengt ons voorbij feiten.<br>
					Yixow geeft je doorkijkjes in het gevoel van mensen en brengt je voorbij feiten.</p>
				</div>
				<div class="column">
					<h3>Interesse is de relatie</h3>
					<p>Bij iedere vraag die je voorlegt aan jouw publiek, privé of zakelijk, toon je interesse in mensen en bouw je de relatie
verder uit.</p>
				</div>
				<div class="column">
					<h3>Kracht van meerkeuze</h3>
					<p>Met  een meerkeuzevraag verplaats jij je bijna vanzelf  in verschillende mogelijkheden.<br>
					Wie zich verplaatst in anderen maakt sneller verbinding met mensen.</p>
				</div>
			</div>
			<div class="section">
				<div class="column first">
					<h3>De waarde van vragen</h3>
					<p>Iedere ontwikkeling en iedere doorbraak is begonnen met een vraag. Wat is jouw vraag?</p>
				</div>
				<div class="column">
					<h3>Beter één vraag in de hand dan tien in een lijst</h3>
					<p>Yixow laat je zonder moeite één vraag stellen en vraag voor vraag meningen verzamelen.  Je ondervraagt mensen niet met een lijst op één moment  maar je toont blijvende belangstelling.</p>
				</div>
				<div class="column containsbutton">
					<h3>The fix is in de mix</h3>
					<p>Vragen uit een divers publiek, maatschappelijk, zakelijk of pure lol en onzin,<br>
krijgen op Yixow allemaal hun eigen ruimte in een afwisselende miix, verrassend en passend.</p>
					<a href="{{ URL::to('meer-info') }}">
						<button>Voor fanatieke lezers</button>
					</a>
				</div>
			</div>
			<footer>
				@include('portal.includes.footer')
			</footer>
		</div>
		@include('portal.includes.footer-stats')	
	</body>
</html>
	