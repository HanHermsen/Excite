<?php
	// TODO Leslie. Alle code die volgt hoort in de Controller
	// die moet de juiste parameters gaan meegeven; het makkelijkst is ze in public instance vars te zetten
	// en de Controller als data object aan het view mee te geven ( ->with('ctrl', $this) )
	// var nodig? Dan b.v. $ctrl->aChecked

if(isset($selectGroup->type) ? $selectGroup->type : '') {
	$gType = $selectGroup->type;
	if($gType == 1) {
		$aChecked = true;
		$bChecked = true;
	}elseif($gType == 2) {
		$aChecked = true;
		$bChecked = false;
	}elseif($gType == 3) {
		$aChecked = false;
		$bChecked = true;
	}elseif($gType == 4) {
		$aChecked = false;
		$bChecked = false;
	}else{
		$aChecked = false;
		$bChecked = false;		
	}
};
isset($selectGroup->sort_type) ? $sType = $selectGroup->sort_type :	$sType = 0;
isset($selectGroup->group_display) ? $group_display = $selectGroup->group_display :	$group_display = 0;

	if ( ! isset($previewWidth) ) $previewWidth = 160;
	if ( ! isset($previewHeight) ) $previewHeight = 120;

	if ( ! isset($selectGroup) ) { // initial Page view from Controller index method
		$selectGroup = (Object) [ 'image' => null ];
	}
	// je moet als image niet null _ook_ testen of de file bestaat; niet bestaande file komt voor ... (oude db zooi?)
	// het root path voor een file test is Excite/app; zie config/filesystems.php
	if ( empty($selectGroup->image) || ! file_exists ( '../public/api/api/images/groups/' . $selectGroup->image) ) {
		// Arie wil dit als default!! Zie je pas na kiezen background color
		$selectGroup->image = "images/whiteYixowLogo.png";
		$imageType = 'default';
	} else {
		$selectGroup->image = 'api/api/images/groups/' . $selectGroup->image;
		$imageType = 'custom';
	}
	$imagedata = getimagesize($selectGroup->image);
	$imageURL = '/' . $selectGroup->image;

	$width = $imagedata[0];
	$height = $imagedata[1];

	$maxDisplayWidth = $previewWidth;
	$maxDisplayHeight = $previewHeight; 
	
	$hRatio = $maxDisplayWidth / $width;
	$vRatio = $maxDisplayHeight / $height;
	if ( $hRatio < $vRatio )
		$ratio  = $hRatio;
	else $ratio = $vRatio;
	$displayWidth = floor($width*$ratio);
	$displayHeight = floor($height*$ratio);
	
	if ( ! isset($lastInsertedGroupId) ) $lastInsertedGroupId = 0;

	$expressGui = false;
	$userType = Excite\Models\CustomerDbModel::getUserType();

	if( $userType == Excite\Models\CustomerDbModel::EXPRESS ) {
			$expressGui = true;
	}
	// pass area info to Javascript
	$area = json_encode (Excite\Models\CustomerDbModel::getArea($lastInsertedGroupId));
	echo "<script>Excite.gr.area = $area; </script> \n";
?>
<style>
#uploadPreview { /* cascades on #uploadPreview in groups.css */
	width: {{$previewWidth}}px; /* wordt in exciteShared.js bij scaling gebruikt als image max display width */
	height: {{$previewHeight}}px; /* wordt in exciteShared.js bij scaling gebruikt als image max display height */
}
</style>
{!! Form::open(array('action' => 'Groups\GroupController@AddGroup','method' => 'post', 'id' => 'exciteForm','files' => true )) !!}
<div class="container-fixed">
	<div class="row">
		<div class="col-md-6">
			<div class="row">
				<div class="col-md-12">
					<div class="row">
						<div class="col-md-6">
							  <?php
									if ( ! isset($deletedGroups) ) $deletedGroups = [];
									$firstChoice = trans('messages.groupNewGroup');
									$activeGroups = ['0' => $firstChoice] + $viewGroups;
									if ( Excite\Models\CustomerDbModel::getUserType() == Excite\Models\CustomerDbModel::EXPRESS ) {
										$activeGroups = $viewGroups;
									}
							  ?>
							{!! Form::label(trans('messages.qGroup')) !!}
							{!! Form::select(
							    'groups', 
							    [ 'Actief' => $activeGroups, 'Inactief'=>  $deletedGroups  ],
								$lastInsertedGroupId,
							    array(
							        'id' => 'groupSelector',
							        'class' => 'oCol',
							        )
							    ) !!}
							
							{!! Form::hidden('hiddenGroupId', '0', array('id'=> 'hiddenGroupId'))!!}
							{!! Form::label(trans('messages.NewGroupName')) !!}    
							{!! Form::text('GroupName', (isset($selectGroup->name) ? $selectGroup->name : '') ,array('size' => '45','maxlength' => '24' , 'id' => 'exciteNewGroupName', 'value' => '' ) ) !!}
						</div>
						<div class="col-md-6">

									{!! Form::label(trans('messages.qGroupLabel'), null,[ 'id' => 'groupLabelCheckLabel']) !!}
							<div id='groupLabelCheck'>
									<div class="ExpressHeightFix">
									{!! Form::checkbox('GroupLabelActivate', 1, $group_display == 1, ['id' => 'groupLabelCheckB']) !!}Toon in 'Groepen aanbod'</div>
							</div>
							<div id='groupLabelContainer'>
							@if($expressGui)
								<div style="margin-top:73px"></div>
							@endif
								{!! Form::label(trans('messages.qGroupLabelName'), null,['id' => 'xxxx']) !!}
								{!! Form::text('LabelName', (isset($selectGroup->express_label) ? $selectGroup->express_label : '') ,array('size' => '45','maxlength' => '24' , 'id' => 'exciteNewGroupLableName', 'value' => '', 'placeholder' => trans('messages.groupexpressSuggestion') ) ) !!}
							</div>

						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">
					{!! Form::label(trans('messages.qQuestionImage')) !!}
					
					<div class="fileUpload btn">
					<!-- oude aanpak <input id="uploadFile" placeholder="Kies bestand..." disabled="disabled" /> -->
					<input id="uploadFile" placeholder="Kies bestand..."  style='display: none' />
					<button id='loadImageBtn' style='width: 175px'>Kies bestand</button>
						{!! Form::file('GroupImage', ['id' => 'GroupImage','class' => 'upload']) !!}
					</div> <!-- Let op: deze Cancel Buttom mag niet binnen bovenstaande div; dan werkt ie niet -->
					<button class='cancelBtn' id='eraseImageB'>X</button>

					<script type="text/javascript">
						document.getElementById("GroupImage").onchange = function () {
					    document.getElementById("uploadFile").value = this.value.substring(12);
					};
					</script>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">
					<div class="row">
						<div class="col-md-6">
							{!! Form::label(trans('messages.groupColor')) !!}

							<div id="colorpicker"></div>

						</div>
						<div class="col-md-6">
							{!! Form::label(trans('messages.groupPreview')) !!}

						<div style="background-color:#{{(isset($selectGroup->color) ? dechex($selectGroup->color) : '')}}" id="groupPreview">
							<!-- door Han toegevoegd: &nbsp; ipv '' als default in de <h1> -->
							<h1>{{ (isset($selectGroup->name) ? $selectGroup->name : '&nbsp;') }}</h1>
							<div id='uploadPreview'>	
								<img imagetype="{{$imageType}}" height="{{$displayHeight}}" width="{{$displayWidth}}" src="{{$imageURL}}"/>			
							</div>
						</div><input type="text" id="color" name="GroupColor" value="{{ (isset($selectGroup->color) ? '#'.dechex($selectGroup->color) : '#ffffff') }}" />
								{!! Form::hidden('hiddenImageDel', '', array('id'=> 'hiddenImageDel'))!!}
								{!! Form::hidden('hiddenImageDataUrl', '', array('id'=> 'hiddenImageDataUrl'))!!}
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-3">
		<div id='groupTypeContainer'>
			{!! Form::label(trans('messages.groupType')) !!}
			{!! Form::checkbox('GroupType[]', '2',(isset($aChecked) ? $aChecked : ''),['id' => 'guestInviteAllowedCheckB']) !!}
			<span id='groupTypeInvite'>{{ trans('messages.groupTypeInvite') }}</span>
			<br />
			{!! Form::checkbox('GroupType[]', '3',(isset($bChecked) ? $bChecked : '')) !!}
			{{ trans('messages.groupTypeQuestion') }}	
		</div>
			{!! Form::label(trans('messages.groupSort')) !!}
			{!! Form::radio('GroupSort', '0',$sType=='0') !!} {{ trans('messages.groupSortZ') }}<br />
			{!! Form::radio('GroupSort', '1',$sType=='1') !!} {{ trans('messages.groupSortA') }}

			{!! Form::label(trans('messages.groupActive')) !!}
			{!! Form::checkbox('activate','0', null, ['id' => 'activateCheckB']) !!}
			<span id='activateLabel'>{{ trans('messages.groupActiveOption') }}</span><br />			
			
			{!! Form::label(trans('messages.gTableDateOut')) !!}
			{!! Form::text('GroupExpire', (isset($selectGroup->date_expired) ? $selectGroup->date_expired : ''), array('id' => 'datepickerExpire','readonly')) !!}

			<button class="cancelBtn" id='eraseDate' onclick='Excite.gr.eraseDate(event);'>X</button>

			<button class="submitBtn" id='deleteGroupB' onclick='Excite.gr.deleteGroup(event)'>Verwijder Definitief</button><br />
			<button class="cancelBtn" id='resetB'>Annuleer</button>
			<button class="submitBtn" id='exciteSubmitB' onclick='Excite.gr.formSubmit(event)'>{{trans('messages.NewGroupSubmit')}}</button>

		</div>
		<div class="col-md-3">
			<div id='openMap'></div>
			<?php
			
				if(isset($selectGroup->radius)) {
					if($selectGroup->radius == '0') {
						$radius = 'Nederland';
					} else {
						$radius = $selectGroup->radius . ' Km';
					}
				} else {
					$radius = '';
				}
				function number_format_locale($number,$decimals=0) {
					// set locale to that of the client
					$locale = setlocale(LC_ALL,$_SERVER['HTTP_ACCEPT_LANGUAGE']);
				    return number_format($number,$decimals,
				               $locale['decimal_point'],
				               $locale['thousands_sep']);
				 }

			?>
			@if($expressGui)
				<div id='rangeInfo'>
					<div class="rangeInfoProp">Postcode:</div> <div class="rangeInfoVal">{{ isset($selectGroup->properties) ? $selectGroup->properties : '' }}</div>
					<div class="rangeInfoProp">Bereik:</div> <div class="rangeInfoVal">{{ $radius }}</div>
					<div class="rangeInfoProp">Populatie:</div>	<div class="rangeInfoVal">{{ isset($selectGroup->population) ? number_format_locale($selectGroup->population) : '' }}</div>
				</div>
			@endif
		</div>
	</div>
</div>

{!! Form::close() !!}