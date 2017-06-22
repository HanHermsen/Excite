<div id='formContainer'>
<!-- {!! Form::open(array('action' => 'Ego\EgoController@portalOrder', 'id' => 'exciteForm','method' => 'post')) !!} -->
{!! Form::open(array('id' => 'exciteForm')) !!}
<div id='expressPart'>
Kies centrum gebied door:<br />
- op de kaart te klikken<br />
- een Postcode op te geven<br />
of kies hieronder voor heel Nederland.
<br /><br />
Wijzig afstand tot centrum gebied:
<br />

{!! Form::select(
	'area',
	['5' => '5km' , '10' => '10km', '25' => '25km',  '0' => 'Nederland' ],		
	5,
	array(
		'id' => 'areaSelector',
		'class' => 'oCol'
		)
	) !!} <br />
Plaats centrum nabij Postcode:<br />
{!! Form::text('zipCode', null, array('maxlength' => 30,'class' => 'answer', 'value' => '','id' => 'zipCode')) !!}
<button id='zipOkB'>Ok</button><br />
<br />
{!! Form::label('Looptijd') !!} <br />
{!! Form::select(
	'period',
	['1' => '1 maand' , '2' => '2 maanden','3' => '3 maanden','4' => '4 maanden','5' => '5 maanden','6' => '6 maanden','7' => '7 maanden','8' => '8 maanden','9' => '9 maanden', '12' =>'jaar' ],		
	1,
	array(
		'id' => 'periodSelector',
		'class' => 'oCol'
		)
	) !!}<br />
			{!! Form::label('Inwoneraantal') !!}<br />	
			{!! Form::text('population', null, ['id' => 'population', 'readonly' => 'true']) !!}<br />
{!! Form::label('Factuurwaarde') !!} <br />
{!! Form::text('price', null, array('maxlength' => 30,'class' => 'answer', 'value' => '','id' => 'price', 'readonly' => 'readonly')) !!} <br /><br />
Tmp visible hidden fields.<br />
{!! Form::hidden('hiddenZipCode', null,array('id'=> 'hiddenZipCode'))!!}
{!! Form::hidden('lat', 'undefined', array('id'=> 'lat'))!!}
{!! Form::hidden('lng', '0', array('id'=> 'lng'))!!}
{!! Form::hidden('hiddenPopulationVal', null,array('id'=> 'hiddenPopulationVal'))!!}
{!!Form::label('Express Labelnaam') !!}<br />
{!!Form::text('LabelName','Label',array('placeholder'=> trans('messages.groupexpressSuggestion'))) !!}<br />

{!!Form::label(trans('messages.NewGroupName')) !!}<br />
 {!!Form::text('GroupName', 'GroepsNaam') !!}<br />
<br />
</div> <!-- end expressPart -->
<br /><br />
{!! Form::label('Bedrijfsnaam') !!} <br />
{!! Form::text('company', 'Bedrijfsnaam', array('maxlength' => 72,'class' => 'answer', 'value' => '','id' => 'company')) !!} <br />

{!! Form::label('KvK nummer') !!} <br />
{!! Form::text('kvk', null, array('maxlength' => 72,'class' => 'answer', 'value' => '','id' => 'kvk')) !!} <br />

{!! Form::label('Voornaam') !!} <br />
{!! Form::text('firstname', 'Voornaam', array('maxlength' => 72,'class' => 'answer', 'value' => '','id' => 'firstname')) !!} <br />

{!! Form::label('Achternaam') !!} <br />
{!! Form::text('lastname', 'Achternaam', array('maxlength' => 72,'class' => 'answer', 'value' => '','id' => 'lastname')) !!} <br />

{!! Form::label('Telefoonnr') !!} <br />
{!! Form::text('phone', '0626846374', array('maxlength' => 72,'class' => 'answer', 'value' => '','id' => 'phone')) !!} <br />

{!! Form::label('Display name') !!} <br />
{!! Form::text('displayname', 'Display name', array('maxlength' => 72,'class' => 'answer', 'value' => '','id' => 'displayname')) !!} <br />

{!! Form::label('Gebruikersnaam/email zakelijk') !!} <br />
{!! Form::text('email', 'a@b', array('maxlength' => 72,'class' => 'answer', 'value' => '','id' => 'email')) !!} <br />

{!! Form::label('password') !!} <br />
{!! Form::text('password', 'fffff', array('maxlength' => 72,'class' => 'answer', 'value' => '','id' => 'password')) !!} <br />

{!! Form::label('herhaal password') !!} <br />
{!! Form::text('password_confirmation', null, array('maxlength' => 72,'class' => 'answer', 'value' => '','id' => 'password_confirmation')) !!} <br />
<br />
<br />
<button id='exciteSubmitB'>Bestel</button>





{!! Form::close() !!}
</div>