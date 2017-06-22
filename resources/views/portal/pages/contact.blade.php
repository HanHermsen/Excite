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
				<div id="title_wrapper">
					<h1>Contact</h1>
				</div>
				<div id="sliderContainer" class="hiddenForMedium">
					@include('portal.includes.slider')
				</div>
			</div>
			<div class="section">
				<h2 class="subheader">Vragen stellen en reageren</h2>
				<div class="column first">
					<h3>Bel ons</h3>
					<p>We zijn bereikbaar op werkdagen van 08.30 uur tot 17.30 uur. Je bereikt ons op:</p>
					<p><b>+31 10 206 55 66</b></p>
				</div>
				<div class="column">
					<h3>Mail ons</h3>
					<p>Je bereikt ons op:</p>
					<p><b><a href="mailto:welcome@yixow.com">welcome@yixow.com</a></b></p>
				</div>
				<div class="column">
					<h3>Twitter</h3>
					<p>Tussen 08.00 en 20.00 uur zijn wij online.</p>
					<p><b><a href="https://twitter.com/yixow">twitter.com/yixow</a></b></p>
				</div>
			</div>
			<footer>
				@include('portal.includes.footer')
			</footer>
		</div>		
		@include('portal.includes.footer-stats')		
	</body>
</html>