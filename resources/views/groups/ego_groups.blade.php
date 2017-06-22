@extends('master')

@section("customStyleHead")
<style>
/* for the map div  */
#openMap {
	position: relative;
	height: 400px;
	width: 370px;
	left: 0px;
	top: 0px;
}
</style>
	<link rel="stylesheet" type="text/css" href="/css/leaflet.css" />
	<!-- <link rel="stylesheet" type="text/css" href="/css/groups.css" /> -->
	<link rel="stylesheet" type="text/css" href="/css/ego.css" />
@stop

@section("customScriptHead")
	<script src="/js/leaflet.js"></script>
	<script src="/js/express.js"></script>
@stop
@section('content')
{!! Form::open(array('action' => 'Ego\EgoController@storeContract','method' => 'post', 'id' => 'exciteForm','files' => true )) !!}
<!-- {!! Form::open(array('method' => 'post', 'id' => 'exciteForm','files' => true )) !!} -->
<div class="container-fixed">
    <div class="row rowTop">
        <div class="col-xs-12">
        	{!!Form::label('Nieuw Abonnement') !!}		
		</div>
    </div>
    <div class="row">
        <div class="col-md-2">
			<!-- required Form fields for express.js; do _not_ change the id's-->
			{!!Form::label('Plaats centrum nabij Postcode') !!}
			{!! Form::text('zipCode', null, array('maxlength' => 30,'class' => 'answer', 'value' => '','id' => 'zipCode')) !!}
			<button id='zipOkB' class='cancelBtn'>Ok</button>
			{!!Form::label('Bereik vanuit centrum of Nederland') !!}
			{!! Form::select(
				'area',
				['5' => '5km' , '10' => '10km', '25' => '25km',  '0' => 'Nederland' ],		
				5,
				array(
					'id' => 'areaSelector',
					'class' => 'oCol'
					)
				) !!}
            {!! Form::label('Looptijd') !!}
			{!! Form::select(
				'period',
				['1' => '1 maand' , '2' => '2 maanden','3' => '3 maanden','4' => '4 maanden','5' => '5 maanden','6' => '6 maanden','7' => '7 maanden','8' => '8 maanden','9' => '9 maanden', '12' =>'jaar' ],		
				1,
				array(
					'id' => 'periodSelector',
					'class' => 'oCol'
					)
				) !!}<br />
        	{!!Form::label('Express Labelnaam') !!}
        	{!!Form::text('LabelName',null,array('placeholder'=> trans('messages.groupexpressSuggestion'))) !!}
			{!!Form::label(trans('messages.NewGroupName')) !!}
        	{!!Form::text('GroupName') !!}

			{!! Form::hidden('hiddenZipCode', null,array('id'=> 'hiddenZipCode'))!!}
			<!-- hidden GPS coordinaten van het centrum van een gebied; do _not_ change the value of lat -->
			{!! Form::hidden('lat', 'undefined', array('id'=> 'lat'))!!}
			{!! Form::hidden('lng', '0', array('id'=> 'lng'))!!}
			
			

		</div>
        <div style="margin-top:11px;" class="col-md-2">
			Kies centrum gebied door:<br />
			<ul>
				<li>op de kaart te klikken</li>
				<li>een Postcode op te geven</li>
			</ul>
			<p>Kies vervolgens bereik en looptijd.<br />
			Bij 10 maanden + 2 maanden gratis.</p>
			<p>Kies de Labelnaam die straks in de App getoond wordt.</p>
			<p>Kies de Groepsnaam die straks in de App getoond wordt.</p>
			{!! Form::label('Factuurwaarde') !!}
			{!! Form::text('price', null, array('maxlength' => 30,'class' => 'answer', 'value' => '','id' => 'price', 'readonly' => 'readonly')) !!}
			{!! Form::label('Inwoneraantal') !!}			
			{!! Form::text('population', null, ['id' => 'population', 'readonly' => 'true']) !!}
			{!! Form::hidden('hiddenPopulationVal', null,array('id'=> 'hiddenPopulationVal'))!!}

 <br />
		</div>
        <div class="col-md-6">
			<!-- required; where the map goes; see above #openMap style for tuning the size -->
			<div id='openMap'></div>
		</div>
        <div class="col-md-2">
	        <div id="ConfirmSubmitTxt">
			Je nieuwe abonnement is nu actief.<br />
			Abonnementen op Yixow zijn nu altijd direct gebruiksklaar. Je kunt meteen aan de slag.<br /><br />
			We vragen je vriendelijk om de factuur, die inmiddels per e-mail naar je is verzonden, uiterlijk binnen een week te voldoen. Met dat vertrouwen willen wij graag werken.
			</div>
			<!-- {!!Form::reset('Annuleer',array('class' => 'cancelBtn'))!!} -->
			<button class='cancelBtn' id='resetB'>Annuleer</button>
			<button class="submitBtn" id='exciteSubmitB'>Bevestig</button>
		</div>
    </div>
    <div class="row rowBorder">
        <div class="col-md-10">
		<!--
        Naast abonnementen per groep kun je ook kiezen voor de uitgebreide mogelijkheden van eXcite.<br />
Met ondermeer een ongelimiteerd aantallen groepen, keuze uit openbare -en besloten groepen en het beheer van gasten.<br />
Ook als je reeds gebruikt maakt van eXpress kun je het uitgebreide eXcite aan maand gratis uitproberen.<br /><br />

			{!! Form::checkbox('confirmTrial', '1',null, [ 'class' => 'confirmTrial' ] ) !!}
			<b>1 maand gratis Yixow eXcite op proef.</b>
		-->
		</div>
        <div class="col-md-2">
		
		</div>
    </div>
</div>





{!! Form::close() !!}

<table width="100%" class="display responsive" id="contractDataTable" cellspacing="0">
	<thead>
		<tr>
			<th>Groepsnaam</th>
			<th>Labelnaam</th>
			<th>Postcode</th>
			<th>Bereik</th>
			<th>Looptijd</th>
			<th>{{ trans('messages.gTableDateIn') }}</th>
			<th>{{ trans('messages.gTableDateOut') }}</th>
		</tr>
	</thead>
<tbody>

</tbody>
</table>

@stop