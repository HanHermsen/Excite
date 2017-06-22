<!doctype html>
<html lang="nl">
	<head>
		@section('title', $title)
		@include('portal.includes.head')
			
		{!! HTML::style('css/leaflet.css') !!}
		{!! HTML::script('jquery/jquery-ui.min.js') !!}
		{!! HTML::script('/js/exciteShared.js') !!}
		<?php
			if( !(strpos($_SERVER['HTTP_USER_AGENT'],"Trident") === false) )
				echo "<script>L_PREFER_CANVAS = true;</script>\n"; // this is for leaflet perfomanceon IE
		?>
		
		{!! HTML::script('js/leaflet.js') !!} <!-- map viewer -->
		{!! HTML::script('js/express.js') !!}
		{!! HTML::script('js/portal.js') !!}		
	</head>
	<body>
		<header>
			@include('portal.includes.header')
		</header>
		<img id="background" src="{{ URL::to('images/bg_abbo.jpg') }}" id="bg" alt="">
		
		<div class="section" style="min-height:100%;padding-top:0;">
			<div id='addGroupContainer'>
				<div id='formContainer'>
					{!! Form::open(array('id' => 'expressForm','method' => 'post')) !!}
						<div>
							<fieldset id="fsGroupName">
								<p>
									<label for="groupName">Groepsnaam</label>
								</p>
								<p>
									{!! Form::text('GroupName', null, array('maxlength' => 72,'class' => 'answer', 'value' => '','id' => 'groupName','placeholder' => 'Groepsnaam')) !!}
								</p>
							</fieldset>
							<fieldset id="fsGroupLable">
								<p>
									<label for="groupLable">Labelnaam</label>
								</p>
								<p>
									{!! Form::text('LabelName', null, array('maxlength' => 72,'class' => 'answer', 'value' => '','id' => 'labelName','placeholder' => 'Label- of Bedrijfsnaam')) !!}
								</p>
							</fieldset>
							<fieldset>
								<p>
									<input type="radio" name="region" id="region1" value="nl" />
									<label for="region1">Nederland</label>
									<input type="radio" name="region" id="region2" value="custom" />
									<label for="region2">Bereik op postcode / Kaartklik</label>
								</p>
								<p>
									{!! Form::text('zipCode', null, array('maxlength' => 10,'class' => 'answer', 'value' => '','id' => 'zipCode','placeholder' => 'Postcode')) !!}
									<button id='zipOkB'>Toon op Kaart</button>
									{!! Form::select(
									'area',
									['5' => '5km' , '10' => '10km', '25' => '25km'],		
									5,
									array(
										'id' => 'areaSelector',
										'class' => 'oCol'
										)
									) !!}
								</p>
								<div id='mapBox' class='flexBox'>
									<div id='openMap'> </div>
								</div>
							</fieldset>
							<fieldset>
								<p>Looptijd</p>
								<p>
									{!! Form::select(
										'period',
										['1' => '1 maand' , '2' => '2 maanden','3' => '3 maanden','4' => '4 maanden','5' => '5 maanden','6' => '6 maanden','7' => '7 maanden','8' => '8 maanden','9' => '9 maanden', '12' =>'1 jaar' ],		
										3,
										array(
											'id' => 'periodSelector',
											'class' => 'oCol'
											)
										) !!}
								</p>
							</fieldset>
							<p> 
								<span>Factuurwaarde &euro; <span id="price">90</span>,-</span>
							</p>
							<p>
								
							</p>
							<p>
								
							</p>
							<p style="text-align:left;">								
								<input type="button" value="Opslaan" id="saveNewGroupBtn">
								<input type="button" value="Annuleren" id="cancelNewGroupBtn">
							</p>
						</div>
						
						{!! Form::hidden('hiddenZipCode', null,array('id'=> 'hiddenZipCode'))!!}
						{!! Form::hidden('lat', 'undefined', array('id'=> 'lat'))!!}
						{!! Form::hidden('lng', '0', array('id'=> 'lng'))!!}
						
						{!! Form::hidden('population', null, ['id' => 'population', 'readonly' => 'true']) !!}
						{!! Form::hidden('hiddenPopulationVal', null,array('id'=> 'hiddenPopulationVal'))!!}						
						
						@if (count($errors) > 0)
						<div id="dialog-message">
							<ul>
							@foreach ($errors->all() as $error)
								<li>{{ $error }}</li>
							@endforeach
							</ul>
						</div>
						@endif
						<div id='js-dialog-message'>
							<ul>
							</ul>
						</div>
						
						<!--
						{!! Form::label('Bedrijfsnaam') !!} <br />
						{!! Form::text('companyName', null, array('maxlength' => 72,'class' => 'answer', 'value' => '','id' => 'companyName')) !!} <br />
						
						{!! Form::label('KvK nummer') !!} <br />
						{!! Form::text('kvkNo', null, array('maxlength' => 72,'class' => 'answer', 'value' => '','id' => 'kvkNo')) !!} <br />
						
						<button id='exciteSubmitB'>Bestel</button>-->
					
					{!! Form::close() !!}
				</div>
			</div>
			<div id="registerContainer">
				{!! Form::open(array( 'id' => 'exciteForm','method' => 'post')) !!}
				{!! Form::hidden('subscriptionType', $type)!!}
				<h1>Bestel Yixow {{ $name }}</h1>
@if ($type == 'express')
				<div id="orderLines">
					Maak eerst een groep aan.
					<!--<div class="groupBlock">
						<div class="groupBlockBar">
							<p>Groepsnaam, Label</p> 
							<p style="float:right;"><a><img src="images/icon-close-round.svg" height="18"></a></p>
							<p style="float:right;margin-right:0px !important;">&euro; 30,-</p>
							
							<div style="clear:both"></div>
						</div>
						<p style="float:left">Looptijd:<br>1 maand</p>
						<p style="float:right;text-align:right">postcode: 1234<br>5 km</p>
					</div>
					<div class="groupBlock">
						<div class="groupBlockBar">
							<p>Groepsnaam, Label</p> 
							<p style="float:right;"><a><img src="images/icon-close-round.svg" height="18"></a></p>
							<p style="float:right;margin-right:0px !important;">&euro; 50,-</p>
							
							<div style="clear:both"></div>
						</div>
						<p style="float:left">Looptijd:<br>2 maand</p> 
						<p style="float:right;text-align:right">postcode: 1235<br>5 km</p> 
					</div>-->
				</div>
				<fieldset>
				<p>
					&nbsp;
				</p>
				<p style="text-align:right;font-size:12pt;">
					Totaal: <b>&euro; <span id="priceTotal">0</span>,-</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				</p>
				</fieldset>
				<fieldset>
				<p>
					&nbsp;
				</p>
				<p>
					<button id='addGroup'>Groep Toevoegen</button>
				</p>
				</fieldset>
@endif
				<fieldset>
				<p>
					{!! Form::label('Bedrijfsnaam*') !!}
				</p>
				<p>
					{!! Form::text('company', null, array('maxlength' => 72,'class' => 'answer', 'value' => '','id' => 'companyName','placeholder' => 'Bedrijfsnaam')) !!}
				</p>
				</fieldset>
				<fieldset>
				<p>
					{!! Form::label('KvK-nummer*') !!}
				</p>
				<p>
					{!! Form::text('kvk', null, array('maxlength' => 72,'class' => 'answer', 'value' => '','id' => 'kvkNo','placeholder' => 'KvK-nummer')) !!}
				</p>
				</fieldset>
				<fieldset>
				<p>
					{!! Form::label('Voornaam*') !!}
				</p>
				<p>
					{!! Form::text('firstname', null, array('maxlength' => 72,'class' => 'answer', 'value' => '','id' => 'firstName','placeholder' => 'Voornaam')) !!}
				</p>
				</fieldset>
				<fieldset>
				<p>
					{!! Form::label('Achternaam*') !!}
				</p>
				<p>
					{!! Form::text('lastname', null, array('maxlength' => 72,'class' => 'answer', 'value' => '','id' => 'lastName','placeholder' => 'Achternaam')) !!}
				</p>
				</fieldset>
				<fieldset>
				<p>
					{!! Form::label('Telefoonnummer (zakelijk)*') !!}
				</p>
				<p>
					{!! Form::text('phone', null, array('maxlength' => 72,'class' => 'answer', 'value' => '','id' => 'phoneNo','placeholder' => 'Telefoonnummer')) !!}
				</p>
				</fieldset>
				<fieldset>
				<p>
					{!! Form::label('E-mailadres (zakelijk)*') !!}
				</p>
				<p>
					{!! Form::text('email', null, array('maxlength' => 72,'class' => 'answer', 'value' => '','id' => 'emailAdress','placeholder' => 'E-mailadres')) !!}
				</p>
				</fieldset>
				<fieldset>
				<p>
					{!! Form::label('Weergavenaam*') !!}
				</p>
				<p>
					{!! Form::text('displayname', null, array('maxlength' => 72,'class' => 'answer', 'value' => '','id' => 'userName','placeholder' => 'Weergavenaam')) !!}
				</p>
				</fieldset>
				<fieldset>
				<p>
					{!! Form::label('Wachtwoord*') !!}
				</p>
				<p>
					{!! Form::password('password', array('maxlength' => 72,'class' => 'answer', 'value' => '','id' => 'password1')) !!} 
				</p>
				</fieldset>
				<fieldset>
				<p>
					{!! Form::label('Herhaal wachtwoord*') !!}
				</p>
				<p>
					{!! Form::password('password_confirmation', array('maxlength' => 72,'class' => 'answer', 'value' => '','id' => 'password2')) !!} 
				</p>
				</fieldset>
				<fieldset>
				<p>
					&nbsp;
				</p>
				<p>
					<button id='exciteSubmitB'>Bestel</button>
				</p>
				</fieldset>
				
				
				
				{!! Form::close() !!}
			</div>
@if ($type == 'express')
			<div id="columnExpressInfo" class="column" style="">
				<h3>Effect in de Yixow App</h3>
				<p>Publiek kan je vinden en volgen via ‘Groepen aanbod’ in de App.
Bekijk voorbeelden in de App. Als je publiek een groep kiest in ‘Groepen aanbod’ verschijnt jouw label in hun persoonlijke ‘Groepen’ op de telefoon.</p>
				<p>Probeer het zelf op je telefoon om te zien hoe het werkt. </p>
				<p>Een logo of afbeelding voor jouw groep kun je toevoegen zodra je bent ingelogd op Yixow eXpress</p>
			</div>	
			<div id="columnExpressInfoOverlay" class="column" style="display:none;">
				<h3>Bereik van je groep</h3>
				<p>Al het publiek op Yixow dat een postcode heeft opgegeven in het gebied dat jij kiest, kan jouw groep vinden en volgen.</p>
				<p>Als je voor een bereik in heel Nederland kiest kan iedereen je groep op Yixow vinden en volgen.</p>
				<p>Voer een postcode in of wijs een punt aan op de kaart.
Kies daarna een bereik en een looptijd.</p>
			</div>
@endif
		</div>
		<footer style="background:none;margin-top:-105px;">
				@include('portal.includes.footer')
		</footer>
	</body>
</html>
	