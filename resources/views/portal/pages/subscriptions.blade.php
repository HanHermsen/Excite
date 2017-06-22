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
		<img  id="background" src="{{ URL::to('images/bg_abbo.jpg') }}" id="bg" alt="">
		<div id="content">
			<div class="section" id="section_first">
				<div id="title_wrapper">
					<h1>Yixow: Billboard</h1>
					<h2>In de binnenzak van je publiek</h2>
				</div>
				<div id="sliderContainer" class="hiddenForMedium">
					@include('portal.includes.slider')
				</div>
			</div>
			<div class="section">
				<h2>Twee manieren om publiek te betrekken</h2>
				<p class="subheader">Met je zakelijke account breng je een merk tot in de binnenzak van je publiek.<br>Met snelle response op jouw vragen krijg je zicht op de kansen in jouw markt.</p>
				<div class="column first equalHeights containsbutton">
					<h3>Yixow eXpress</h3>
					<p>In eXpress presenteer je jouw merk aan een groeiend publiek.</p>
					<p>Mensen die jouw merk volgen krijgen jouw vragen met voorrang gepresenteerd.</p>
					<p>Je bereikt nieuw publiek dat kan groeien door je eigen volgers.</p>
					<p>Je wordt nooit beperkt door Add-blockers of spam-boxen.</p>
					<p>Je bereikt mensen met interesse in jouw merk op hun telefoon.</p>
					<a href="{{ URL::to('bestellen/express') }}">
						<button class="hiddenForSmall">Bestel Yixow <i>eXpress</i></button>
					</a>
				</div>
				<div class="columnDouble equalHeights">
					<img id="flowchart" src="{{ URL::to('images/express_flowchart.jpg') }}" id="bg" alt="">
				</div>
			</div>
			<div class="section">
				<div class="column first equalHeights  containsbutton">
					<h3>Yixow eXcite</h3>
					<p>Met eXcite kun je exclusieve groepen maken, waarvoor je mensen persoonlijk uitnodigt.</p>
					<p>Er is geen beperking in aantal groepen, gasten en vragen.</p>
					<p>eXcite is geschikt voor interne surveys en specifieke doelgroepen.</p>
					<p>Ontdek het spelenderwijs in de proefmaand en krijg tevens gratis en vrijblijvend advies over het nieuwe Question Marketing.</p>
					<a href="{{ URL::to('bestellen/excite') }}">
						<button class="hiddenForSmall">Proefmaand <i>eXcite</i></button>
					</a>
				</div>
				<div class="columnDouble equalHeights">
					<img id="flowchart" src="{{ URL::to('images/excite_flowchart.jpg') }}" id="bg" alt="">
				</div>
			</div>
			<div class="section">
				<table id="subscriptionTable">
					<tr id="tblNameRow">
						<th></th>
						<td><img src="{{ URL::to('images/logo_x.png') }}"><br>eXpress</td>
						<td><img src="{{ URL::to('images/logo_x.png') }}"><br>eXcite</td>
					</tr>
					<tr>
						<th class="whiteCell">Eigen label</th>
						<td><img src="{{ URL::to('images/checkmark.svg') }}"></td>
						<td><img src="{{ URL::to('images/checkmark.svg') }}"></td>
					</tr>
					<tr>
						<th>Aansluiting op Yixow app</th>
						<td><img src="{{ URL::to('images/checkmark.svg') }}"></td>
						<td><img src="{{ URL::to('images/checkmark.svg') }}"></td>
					</tr>
					<tr>
						<th class="whiteCell">Regio gebonden</th>
						<td><img src="{{ URL::to('images/checkmark.svg') }}"></td>
						<td><img src="{{ URL::to('images/cross.svg') }}"></td>
					</tr>
					<tr>
						<th>Bereik Nederland</th>
						<td><img src="{{ URL::to('images/checkmark.svg') }}"></td>
						<td><img src="{{ URL::to('images/checkmark.svg') }}"></td>
					</tr>
					<tr>
						<th class="whiteCell">Statistieken per antwoord</th>
						<td><img src="{{ URL::to('images/checkmark.svg') }}"></td>
						<td><img src="{{ URL::to('images/checkmark.svg') }}"></td>
					</tr>
					<tr>
						<th>Informeer je publiek zelf</th>
						<td><img src="{{ URL::to('images/checkmark.svg') }}"></td>
						<td><img src="{{ URL::to('images/checkmark.svg') }}"></td>
					</tr>
					<tr>
						<th class="whiteCell">Nodig je publiek uit via Yixow</th>
						<td><img src="{{ URL::to('images/cross.svg') }}"></td>
						<td><img src="{{ URL::to('images/checkmark.svg') }}"></td>
					</tr>
					<tr>
						<th>Ongelimiteerde groepen</th>
						<td><img src="{{ URL::to('images/cross.svg') }}"></td>
						<td><img src="{{ URL::to('images/checkmark.svg') }}"></td>
					</tr>
					<tr>
						<th class="whiteCell">Exclusieve groepen</th>
						<td><img src="{{ URL::to('images/cross.svg') }}"></td>
						<td><img src="{{ URL::to('images/checkmark.svg') }}"></td>
					</tr>
					<tr>
						<th>Interne surveys</th>
						<td><img src="{{ URL::to('images/cross.svg') }}"></td>
						<td><img src="{{ URL::to('images/checkmark.svg') }}"></td>
					</tr>
					<tr>
						<th class="whiteCell">Permissies voor gasten instellen</th>
						<td><img src="{{ URL::to('images/cross.svg') }}"></td>
						<td><img src="{{ URL::to('images/checkmark.svg') }}"></td>
					</tr>
					<tr>
						<th>Proefperiode</th>
						<td><img src="{{ URL::to('images/cross.svg') }}"></td>
						<td><img src="{{ URL::to('images/checkmark.svg') }}"></td>
					</tr>
					<tr>
						<th class="whiteCell">Kosten</td>
						<td>Vanaf &euro; 30,- p.m.<br>(op basis van uw wensen)</td>
						<td>Op aanvraag</td>
					</tr>
				</table>
				<br>
			</div>	
			<footer>
				@include('portal.includes.footer')
			</footer>
		</div>
		@include('portal.includes.footer-stats')	
	</body>
</html>
	