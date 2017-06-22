@extends('master')

@section("customStyleHead")
	<link rel="stylesheet" href="css/multiple-emails.css">
	<link rel="stylesheet" href="css/guests.css">
	<link rel="stylesheet" href="css/scroller.dataTables.min.css">
@stop

@section("customScriptHead")
	<script src="js/dataTables.scroller.min.js"></script>
	<script src="js/multiple-emails.js"></script>
	<script src="/js/guests.js"></script>
@stop

@section('content')

{!! Form::open(array('action' => 'Guests\GuestController@index', 'id' => 'exciteForm')) !!}
<div class="container-fixed">
    <div class="row">
        <div id="demo-12-col" class="col-xs-12">
            <div class="col-md-2 col-xs-15">
			<?php if ( ! isset($lastInsertedGroupId) ) $lastInsertedGroupId = 0;
				if ( ! isset($deletedGroups) ) $deletedGroups = [];
			?>
		    {!! Form::label(trans('messages.gGroup')) !!}
		    {!! Form::select(
		        'groups',
				[ 'Actief' => (['0' => 'Selecteer een groep'] + $viewGroups), 'Inactief'=>  $deletedGroups  ],
		        '0',
		        array(
		            'onchange' => 'this.form.submit()',
		            'class' => 'oCol'
		            )
		        ) !!}
			</div>
            <div class="col-md-2 col-xs-15"><div id='ajaxSpinner' style='display: none;'>Laden kan even duren...&nbsp;&nbsp;&nbsp;<img src="images/a_spinner-orange.gif" width='40px' /></div></div>
            <div class="col-md-2 col-xs-15"></div>
            <div class="col-md-2 col-xs-15"></div>
            <div class="col-md-2 col-xs-15"></div>
        </div>
    </div>
    <div class="row">
        <div id="demo-12-col" class="col-xs-12">
            <div class="col-md-2 col-xs-15">
				<div id="MailSelector">
					{!! Form::label('Invoer Mailadressen') !!}
					{!! Form::textarea('emailInput',null,['class' => 'emailInput' , 'id' => 'emailInput' ]) !!}
					<!-- {{ trans('messages.gInputNewGuestsDesc') }} -->
					<!-- {!! Form::label(trans('messages.gInputNewGuests')) !!} -->
					<div></div>
					<a href='#' class="hoverRow" title="Tik adres in en geef Enter of spatie _en/of_ Plak-Paste met Ctrl-V; tussen adressen: Enter, komma, puntkomma of spatie">Hulp bij invullen.</a>
				</div> 
			</div>
            <div class="col-md-2 col-xs-15">
			    <!-- {!! Form::label(trans('messages.gListNewGuest')) !!} -->
				<!-- {!! Form::label('Uitnodigen') !!} -->
				<label for="Uitnodigen">Uitnodigen&nbsp;&nbsp;<span id='newInvitationCnt'></span></label>
    			{!! Form::hidden('hiddenNewInvitationInput', null , array('id' => 'hiddenNewInvitationInput', 'class' => 'form-control' )) !!}
			</div>
            <div class="col-md-2 col-xs-15">
				<!-- {!! Form::label('Lopende Uitnodigingen') !!} --> <label for="Lopende Uitnodigingen">Eerder uitgenodigd&nbsp;&nbsp;<span id='invitationCnt'></span></label>
				{!! Form::hidden('hiddenInvitationInput', null , array('id' => 'hiddenInvitationInput', 'class' => 'form-control' )) !!}
			</div>
            <div class="col-md-2 col-xs-15">
				<!--{!! Form::label(trans('messages.gGuestsList')) !!}-->
				<label for="Gasten">Gasten&nbsp;&nbsp;<span id='memberCnt'></span></label>
				{!! Form::hidden('hiddenMemberInput', null , array('id' => 'hiddenMemberInput', 'class' => 'form-control' )) !!}
			</div>
            <div class="col-md-2 col-xs-15">
				<!-- {!! Form::label('Verwijderen') !!} -->
				<label for="Verwijderen">Verwijderen&nbsp;&nbsp;<span id='deleteCnt'></span></label>
				{!! Form::hidden('hiddenDoDeleteInput', null , array('id' => 'hiddenDoDeleteInput', 'class' => 'form-control' )) !!}
			</div>
        </div>
    </div>
    <div class="row">
        <div id="demo-12-col" class="col-xs-12">
            <div class="col-md-2 col-xs-15">			
				<!-- <button id='toListB'>{{ 'Controleer & Sorteer' }}</button> -->
			</div>
            <div class="col-md-2 col-xs-15"></div>
            <div class="col-md-2 col-xs-15"></div>
            <div class="col-md-2 col-xs-15"></div>
            <div class="col-md-2 col-xs-15">
			    <div id="GroupDateSelector">
					<!-- <button id='exciteSubmitB' class='submitb'>{{ trans('messages.gSubmitToGroup') }}</button><br />-->
					<button class="cancelBtn" id='cancelB'>Annuleer</button>
					<button class="submitBtn" id='exciteSubmitB' class='submitb'>Bevestig</button>
    			</div>
			</div>
        </div>
    </div>
</div>

{!! Form::close() !!}
<hr />

<table width="100%" class="display responsive" id="guestsDataTable" cellspacing="0">
	<thead>
		<tr>
			<th width="50px">Selecteer</th>
			<th>{{ trans('messages.gTableGuests') }}</th>
			<th>{{ trans('messages.gTableDateIn') }}</th>
			<th>{{ trans('messages.gTableGroup') }}</th>
			<th>{{ trans('messages.gTableQuestions') }}</th>
			<th>{{ trans('messages.gTableResponse') }}</th>
			<th>A</th>
			<th>{{ trans('messages.gTableDateOut') }}</th>
		</tr>
	</thead>

	<tbody>

	</tbody>
</table>

@stop