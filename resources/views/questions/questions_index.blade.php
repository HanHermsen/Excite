@extends('master')
<?php $userType = Excite\Models\CustomerDbModel::getUserType(); ?>
@section("customStyleHead")
    <link rel="stylesheet" type="text/css" href="/css/questions.css">
	<link rel="stylesheet" href="/css/leaflet.css" />
@stop

@section("customScriptHead")
    <script src="/js/questions.js"></script>
	<script src="/js/stats.js"></script>
	
	<?php
		if( !(strpos($_SERVER['HTTP_USER_AGENT'],"Trident") === false) )
			echo "<script>L_PREFER_CANVAS = true;</script>\n"; // this is for IE, werkt anders niet goed: traaag		
	?>
	<!-- <script>L_PREFER_CANVAS = true;</script> put this before leaflet.js include; solved 'IE too slow to use' problem
		vooralsnog alleen voor IE toepassen, anders weer problemen met Gemeentegrenzen (is tijdelijk) bij andere Browsers -->
	<script src="/js/leaflet.js"></script> <!-- map viewer -->
	<!-- new 11-7-2016: try to prevent new and unsuspected keyMap and sensor Errors/Warnings
	     from the Google Maps api (????)
	<script src='http://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&key=my_key'></script> -->
	<script>
		if ( Excite.qu.newStats ) { // new approach
			$.getScript("https://www.gstatic.com/charts/loader.js");
		} else {
			$.getScript("https://www.google.com/jsapi");
		}
	</script>
	<!-- <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script> -->
	<!-- <script type="text/javascript" src="https://www.google.com/jsapi"></script> -->

@stop

@section('content')
		<?php $groupId = Session::get('groupId', 0); // 'messages.gChooseGroup'
				if ( ! isset($deletedGroups) ) Session::get('deletedGroups', []);
	  	?>

@if(empty($viewGroups) && empty($deletedGroups) && !empty(Auth::user()->contact_id) )
	<p><a href="/groups">{{ trans('messages.qNoGroup') }}</a></p>
@endif

@if(!empty(Auth::user()->contact_id))

{!! Form::open(array('action' => 'Questions\QController@store', 'files' => true, 'id' => 'exciteForm','method' => 'post')) !!}
<div class="container-fixed">
<div class="row">
	<div class="col-md-3">
 
		{!! Form::label(trans('messages.qGroup')) !!}
		{!! Form::select(
		    'groups',
			[ 'Actief' => (['0' => trans('messages.gChooseGroup')] + $viewGroups), 'Inactief'=>  $deletedGroups],		
		    $groupId,
		    array(
		        'id' => 'groupSelector',
		        'class' => 'oCol'
		        )
		    ) !!}
   		 
	</div>
	<div class="col-md-5">
		{!! Form::label(trans('messages.qQuestion')) !!}
		{!! Form::textarea('question',null,['cols' => '72', 'rows' => '4', 'maxlength' => '200','class' => 'question' , 'id' => 'exciteQuestion' , 'value' => '' ]) !!}	
	</div>
	<div class="col-md-4">
	<div id="dateFromDiv">
	    {!! Form::label(trans('messages.gTableDateIn')) !!}
		{!! Form::text('inputDateFrom', null, array('id' => 'datepickerFrom','readonly')) !!}
	</div>
	<div id="dateTillDiv">
	    {!! Form::label(trans('messages.gTableDateOut')) !!}
		{!! Form::text('inputDateTill', null, array('id' => 'datepickerTill','readonly')) !!}
		<button class="cancelBtn" id='eraseDate' onclick='Excite.qu.eraseDate(event);'>X</button>
	</div>
  </div>
</div>
<div class="row">
	<div class="col-md-3">
		{!! Form::label(trans('messages.qQuestionImage')) !!}   

		{{-- <div class="fileUpload btn"> --}}
		<div class="fileUpload btn">
			<!-- oude aanpak <input id="uploadFile" placeholder="Kies bestand..." disabled="disabled" /> -->
			<input id="uploadFile" placeholder="Kies bestand..."  style='display: none' />
			<button id='loadImageBtn' style='width: 175px'>Kies bestand</button>
		    {!! Form::file('user_image', ['id' => 'user_image','class' => 'upload']) !!}

		</div> <!-- Let op: deze Cancel Buttom mag niet binnen bovenstaande div; dan werkt ie niet -->
		<button class='cancelBtn' id='eraseImageB'>X</button>

		<script type="text/javascript">
		document.getElementById("user_image").onchange = function () {
		    document.getElementById("uploadFile").value = this.value.substring(12);
		};
		</script>
		<div id="imageUploadPreview">(geen afbeelding)</div>
		{!! Form::hidden('hiddenImageFile', '', array('id'=> 'hiddenImageFile'))!!}
		{!! Form::hidden('hiddenImageDataUrl', '', array('id'=> 'hiddenImageDataUrl'))!!}
	</div>
	<div class="col-md-5">
		{!! Form::label(trans('messages.qAnswers')) !!}
		@for ($i = 1; $i < 7; $i++)
		    <!-- {!! Form::label($i) !!} -->
		
			{!! Form::text('answers' . $i, null, array('maxlength' => 72,'class' => 'answer', 'value' => '','id' => 'exciteAnswer' . $i)) !!}
			<?php

			$classAttrUp = " class='navButton buttonUp'";
			$classAttrDown = " class='navButton buttonDown'";
			if ( $i == 1 )
				$classAttrUp=" class='navButton noshow buttonUp'";
			echo "<input name='up$i'" . $classAttrUp . " type='button' />";
			if ($i != 6 )
				echo "<input name='down$i'" . $classAttrDown . " type='button' />";
			?>

		@endfor
	</div>
	<div class="bottom col-md-4">
		<button id='reuseQuestion'>Hergebruik gegevens</button><button id='delQuestion'>Verwijder uit app</button>
		<button class="cancelBtn" id='clearForm'>Annuleer</button>
		<!-- <button class="cancelBtn" id='allQuestions'>Annuleer-Cancel</button> -->
		{!! Form::submit('Bevestig', array( 'name' => 'submitb', 'class' => 'submitBtn' )) !!}
		
	</div>
</div>
</div>

{!! Form::close() !!}

<hr />
@endif
<table width="100%" class="display nowrap responsive" id="questionsDataTable" cellspacing="0">
	<thead>
		<tr>
			<th width="10px"></th>
			<th width="200px">Vraag</th>
			<th></th>
			<th>{{ trans('messages.gTableDateIn') }}</th>
			<th>Groep</th>
			<th width="50px"><a href='#' class='hoverRow' title='aantal gasten (+ aantal lopende uitnodigingen)'>Bereik</a></th>
			<th>Response</th>
			<th>{{ trans('messages.gTableDateOut') }}</th>
			<th>A</th>
			<th></th>
		</tr>
	</thead>
	<tbody>

	</tbody>
</table>

<!-- where the statistics go -->
<div id='statsWindow'> </div>
<div id='miniStatsWindow'></div>
@stop