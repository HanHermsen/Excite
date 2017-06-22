/*** DataTable stuff */

$(document).ready(function(){
				GROUP = 0;
				datumIn = 1;
				gasten = 2;
				vragen = 3;
				response = 5; // hide later on; moet zijn totaal aantal responseoorden op alle vragen van de groep
				PERCENTAGE = 4;
				datumUit = 6; // hide later on
				GROUPTYPE = 7;
				GROUP_DELETED = 8;
				MIN_RESP = 5; // the real value

	var table = $('#groupsDataTable').DataTable( {
		"ajax": "/groups/getTableData",
		responsive: false,
		serverSide:true,
		processing:true,
		sDom: 'ftip',
		"scrollY":        "370px",
        "scrollCollapse": true,
        "paging":         false,
		order: [[ 0, "asc" ]],
		"createdRow": function ( row, data, index ) {
				/* is onzin
				if ( data[vragen] < MIN_RESP )
					$('td', row).eq(vragen).html( "<10");
				*/
				if ( data[vragen] == 0 || data[gasten] == 0 ) {
					$('td', row).eq(PERCENTAGE).html(Excite.statResponseBar(0));
				}
				else
					$('td', row).eq(PERCENTAGE).html( Excite.statResponseBar(Math.round( (data[response]/(data[vragen]*data[gasten])) * 100)));
				$('td', row).eq(datumUit).html( "Updt: " + data[datumUit]);
				deco = '';
				if ( data[GROUP_DELETED] != 0 )
					deco = " style='text-decoration: line-through' ";
				if ( data[GROUPTYPE] != 0 ) {
					$('td', row).eq(GROUP).html("<span class='businessGroup'" + deco + ">" + data[GROUP] + "</span>");
				}
				else {
					$('td', row).eq(GROUP).html("<span class='publicGroup'" + deco + ">" + data[GROUP] + "</span>");
				}
			},
		"language": {
				"url": "/js/DataTables/Dutch.json"

			},

		"columnDefs": [ {
				// Group name 
				"targets": GROUP
			},
			{
				// Date start
				"targets": datumIn,
				"searchable": false
			},
			{
				// gasten count
				"targets": gasten,
				"searchable": false
			},
			
			{
				// Vragen count
				"targets": vragen,
				"searchable": false
			},

			{
				// Response count
				"targets": response,
				"visible": false,
				// this is needed for invisibility when responsive is true
				"className": "never", 
				"searchable": false
			},

			{
				// %
				"targets": PERCENTAGE,
				"searchable": false,
				sortable: false,
				"defaultContent": "PERCENTAGE",
				//"iDataSort": response, // sorteer op hidden abs aantal column
			},
			{
				// datumUit
				"targets": datumUit,
				//"visible": false,
				// this is needed for invisibility when responsive is true
				//"className": "never",                         
				//"searchable": false
			}
					
		 ],
	});


});

Excite.confirmPageChange = function () { // light weight check
	if ( Excite.gr.formInputs.uploadFile.val().trim() != '')
		return false;
	if ( Excite.gr.groupId == 0 && $('#exciteNewGroupName').val() != '')
		return false;
	if ( Excite.gr.groupId > 0 &&  $('#exciteNewGroupName').val() != Excite.gr.initialGroupName )
		return false;
	return true;
}
Excite.gr.initActivate = function(groupId)  {
console.log("Groep Id " + groupId);
		if ( groupId == 0 || groupId == null) { // de null is nodig voor: user heeft geen groepen
			$('#activateLabel').css('visibility', 'hidden');
			//$("input[name='activate']").hide();
			$("input[name='activate']").css('visibility', 'hidden');
			$("input[name='activate']").val(0); // val used only when checked; controlled below
			$('#deleteGroupB').css('visibility', 'hidden');
			return;
		}
		elem = $("#groupSelector option[value='" + groupId + "']");
		//console.log(elem);
		//console.log(elem.text() + '' + elem.prop('value'));
		//console.log(elem.parent().prop('label'));
		if ( elem.parent().prop('label') == 'Actief' ) {
			$('#activateLabel').html('Maak Groep Inactief');
			$('#deleteGroupB').css('visibility', 'hidden');
			actVal = 1;
		} else {
			$('#activateLabel').html('Activeer de Groep');
			$('#deleteGroupB').css('visibility', 'visible');
			actVal = 0;
		}
		if ( Excite.userType == Excite.userTypes.EXPRESS) { //Express
				$('#groupTypeInvite').prev().prop('disabled', true);
		}
		$('#activateLabel').css('visibility', 'visible');
		elem = $("input[name='activate']");
		elem.prop('checked', false);
		elem.val(actVal);
		//elem.show();
		elem.css('visibility', 'visible');
}

Excite.gr.deleteGroup = function (e) {
	e.preventDefault();
	Excite.yesNoDialog("Groep Definitief Verwijderen?", function(val, dialog) {
		dialog.dialog("close");
		if ( val ) {
			$('#hiddenGroupId').val( -1 * $("select[name='groups']").val() );
			$('#exciteForm').submit();
		}
	});
}

Excite.gr.ajaxDeleteGroup = function(groupId) {
}
Excite.gr.ajaxGetFormHTML = function(groupId) {
	var get = $.get( "/groups/getGroupFormHTML", {
		groupId: groupId,
	} );
	get.done(function( data, textStatus,jqXHR ) {
		if ( data == null ) { // TODO
			Excite.dialogAlert("<li>Bel de brandweer, er is een probleem.</li>");
			return;
		}
		$('#exciteFormContainer').html(data);
		sessionStorage.defaultGroupImageHTML = $('#uploadPreview').html();

		// get server side filled in date
		setDate = $("#datepickerExpire").attr('value');
		// after refersh: new datepicker nodig
		d = Excite.datepicker($("#datepickerExpire"));
		d.datepicker('option', 'minDate', '+1');

		if ( setDate == '' || setDate === undefined )
			d.show();
		else
			d.datepicker('setDate', setDate); // implicit show
		if (Excite.userType == 1) // eXpress
			d.datepicker( "option", "disabled", true );
		if( $('#uploadPreview > img').attr('imagetype') == 'custom' ) {
			Excite.gr.customImage = true;
			Excite.gr.imageEraseDialog = true;
		}
		if ( $('#color').val == '#0' ) // gladstrijken foutje leslie
			$('#color').val('#ffffff');
		// new event handlers needed after refresh
		Excite.gr.initStuff(groupId);
		//console.log("User " + Excite.userType);
		if (Excite.userType == Excite.userTypes.EXPRESS ) { //eXpress Map shower
			if ( Excite.gr.area.found ) {
			//console.log("Area :" + Excite.gr.area.lat + " area.lng " + Excite.gr.area.lng + ' ' + Excite.gr.area.radius);
				if ( Excite.gr.area.radius == 0 ) {
					Excite.gr.showMap(true);
				} else {
					Excite.gr.latLng = null;
					Excite.gr.showMap(false);
					Excite.gr.newLayer( [Excite.gr.area.lat,Excite.gr.area.lng], Excite.gr.area.radius );
				}
			} else
				Excite.gr.showMap(false);
		}

		// note that Excite.gr.groupNames need not to be refreshed
	});
}
Excite.gr.initStuff = function (groupId) {
		Excite.gr.groupId = groupId
		Excite.gr.initialGroupName = '';
		if(groupId > 0 ) {
			Excite.gr.initialGroupName = $('#exciteNewGroupName').val().trim().replace(/  +/g, ' ');
		}
		Excite.gr.initActivate(groupId);
		
		$('#resetB').on('click', function(e) {
			e.preventDefault();
			if ( Excite.gr.groupId > 0 )
				Excite.gr.ajaxGetFormHTML(Excite.gr.groupId);
			else
				Excite.gr.clearForm();
		});
		Excite.gr.bindEnterPrevent();
		Excite.gr.bindImageUploadEvent();
		Excite.gr.bindEraseImageEvent();
		Excite.gr.colorP = $.farbtastic($('#colorpicker'), Excite.gr.colorPCallback);
		Excite.gr.bindColorPickerEvents();
		Excite.gr.bindLabelCheckEvent();
		Excite.gr.bindGroupNameChangeEvent();
		Excite.gr.bindSelectEvents();
		//Excite.gr.radioButnEventHandler();
}
Excite.gr.resetForm = function () {
		// never called
		console.log("Reset Form");
		$('#exciteForm').trigger("reset");
}
Excite.gr.clearForm = function () {
			//console.log("Clear Form");
			$('#exciteNewGroupLableName').val('');
			imageEraseDialog = false;
			Excite.gr.initActivate(0);
			Excite.gr.initialGroupName = '';
			$('#exciteNewGroupName').val('');
			// clear checkboxes
			$('input[type="checkbox"]').prop('checked', false);
			$('input[type="radio"]').each( function(){
				if ( $(this).prop('name') == 'GroupSort' && $(this).val() == 0 )
					$(this).prop('checked', true);
			});
			//$('.showCheckChar').attr('class', 'hideCheckChar');
			// reset App impression image preview
			$('#groupPreview').html(sessionStorage.defaultAppImpressie);
			$('#groupPreview').attr('style', 'background-color:#');
			Excite.gr.bindImageUploadEvent();
			Excite.gr.bindEraseImageEvent();
			$('#color').val('#ffffff');
			Excite.gr.colorP = $.farbtastic($('#colorpicker'), Excite.gr.colorPCallback); //XXXXXX
			Excite.gr.bindColorPickerEvents();
			elem = $('#groupLabelCheckB');
			elem.prop('checked', false);
			$('#exciteNewGroupLableName').val('');
			Excite.gr.bindLabelCheckEvent();
			$('#guestInviteAllowedCheckB').prop('disabled', false);
			//$('#guestInviteAllowedCheckB').prop('readOnly', false);
			Excite.gr.bindGroupNameChangeEvent();
			Excite.gr.formInputs.uploadFile.val('');
			Excite.gr.formInputs.uploadFile.trigger('change');
			d = Excite.datepicker($("#datepickerExpire"));
			d.datepicker('option', 'minDate', '+1');
			d.datepicker('setDate', null); //leeg laten

}
Excite.gr.radioButnEventHandler = function () {
	$('.colors').each ( function () {
		$(this).on ( 'click', function(e) {
			if ( $(this).children().prop('checked') ) {
			// verwijdert de bg color....
			// TODO het werkt en levert een random kleur op bij bevestig aanmaak
			// is dat de bedoeling? Maakt niet zo uit.....
				//console.log("CHECKED!");
				$('#groupPreview').removeAttr('style');
				// is this better? = orig state; geen verschil gemerkt
				$('#groupPreview').attr('style', 'background-color:#');
				$(this).children().next().attr('class', 'hideCheckChar');
				$(this).children().prop('checked', false);
				return;
			}
			$('#groupPreview').attr('style', 'background-color: ' + $(this).css('background-color'));
			$('.showCheckChar').attr('class', 'hideCheckChar');
			$(this).children().next().attr('class', 'showCheckChar');
			$(this).children().prop('checked', true);
		});
	
	});
}
//$.ajaxSetup({ // for a non Laravel supported POST; is not needed here since the Form has already the right hidden _token input!
//		headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
//		data: { '_token': $('meta[name=_token]').attr('content') }
//	});

// not in use anymore; does not support image uploads
Excite.gr.ajaxPostGroupChangeData = function(groupId) {
	$('#hiddenGroupId').val(groupId);
	var post = $.post("/groups/changeGroup", $('#exciteForm').serialize() + '&groupId=' + groupId);
	post.done(function( data, textStatus,jqXHR ) {
		if ( data == null ) { // TODO
			Excite.dialogAlert("<li>Bel de brandweer, er is een probleem.</li>");
			return;
		}
		$('#exciteFormContainer').html(data);
		// new datepicker nodig na refresh
		d = Excite.datepicker($("#datepickerExpire"));
		d.datepicker('option', 'minDate', '+1');
		//setDate = $("#datepickerExpire").attr('value');
		setDate = d.attr('value');
		if ( setDate == '' || setDate === undefined )
			d.show();
		else
			d.datepicker('setDate', setDate);
		Excite.gr.bindGroupNameChangeEvent();
		// new event handler hier niet nodig?
		//Excite.gr.bindImageUploadEvent();
	});
	return;
}

// Global var that holds the seleted groupId 
Excite.gr.groupId = 0;
Excite.gr.prevVal = 0;
Excite.gr.bindSelectEvents = function () {
	selectList = $("select[name='groups']");
	selectList.unbind('click');
	selectList.unbind('change');
	selectList.on( 'click', function(event) {
		elem = $("select[name='groups'] option:selected");
		Excite.gr.prevVal = elem.val();
		//prevSelectedGroupName = elem.html();
	});	
	selectList.change( function(event) {
		//console.log( 'Group change pending: new ' + $(this).val() + ' old ' + prevVal );
		if (  ! Excite.confirmPageChange() ) {
			event.preventDefault();
			msg = "Naar een andere groep kan, maar dan gaan wijzigingen verloren.<br>Toch naar andere groep?";
			Excite.pageChangeAlert(msg , Excite.gr.prevVal, function(ok, dialog, prevVal) {
			// async call back function;
			// so prevVal must be saved as call arg and be returned in the callback 
				dialog.dialog("close");
				if (ok) { // do the group change
					Excite.gr.groupChange($("select[name='groups']"), prevVal);
				}
				else {
					// set option list back to previous
					$("select[name='groups']").val(prevVal);
					return;
				
				}
			});
		} else {
			Excite.gr.groupChange($(this), Excite.gr.prevVal);
		}
	});
}
Excite.gr.brotherWasChecked = false;
Excite.gr.bindLabelCheckEvent = function () {
	elem = $('#groupLabelCheckB');
	brother = $('#guestInviteAllowedCheckB');
	labelName = $('#exciteNewGroupLableName');
	

	if ( Excite.userType == Excite.userTypes.EXPRESS ) {
		//console.log("HIER" + elem.prop('checked'));
		//if (elem.prop('checked')) {
			//console.log("Ja");
			elem.prop('disabled', true);
			brother.prop('disabled', true);
			//elem.prop('disabled', true);
			$('#groupLabelCheck').hide();
			$("#groupLabelCheckLabel").hide();
			
		/*} else {
			console.log("neee"); Dit komt niet meer voor.
			brother.prop('disabled', true);
			$('#groupLabelCheck').hide();
			$('#groupLabelContainer').hide();
			$('#groupTypeContainer').css('visibility', 'hidden');
			$('#openMap').hide();
			$("#groupLabelCheckLabel").text("Publieke groep");
			
		}*/
		//elem.prop('disabled', true);
		return;
	}


	if ( brother.prop('checked') ) Excite.gr.brotherWasChecked = true;
	else Excite.gr.brotherWasChecked = false;
		
	if ( elem.prop('checked') ) {
		brother.prop('disabled', true);
		//brother.prop('readOnly', true);
		labelName.prop('disabled', false);
	} else labelName.prop('disabled', true);
	elem.unbind('click');
	elem.on('click', function(e) {	
		if ( $(this).prop('checked') ) {
			// new status is checked
			brother.prop('disabled', true);
			//brother.prop('readOnly', true);
			brother.prop('checked', true);
			labelName.prop('disabled', false);
			return;
		}
		brother.prop('disabled', false);
		//brother.prop('readOnly', false);
		brother.prop('checked', Excite.gr.brotherWasChecked);
		labelName.prop('disabled', true);
	});
}
Excite.gr.formInputs = {};
Excite.gr.bindImageUploadEvent = function () {
	// dit moet telkens opnieuw na binnenhalen form html! Anders werkt het niet.
	Excite.gr.formInputs.uploadFile = $('#GroupImage');
	viewElem = $('#uploadPreview');
	var defaultImageHTML = sessionStorage.defaultGroupImageHTML;
	Excite.gr.formInputs.uploadFile.unbind('change');
	Excite.gr.formInputs.uploadFile.change( function(e) {
		//console.log("image change " + Excite.gr.formInputs.uploadFile.val() );
		if ( Excite.gr.formInputs.uploadFile.val() != '') {
			Excite.imageUploadEventHandler(e,$(this), viewElem, 1, defaultImageHTML,$('#hiddenImageDataUrl'), {width:320,height: 240});
			Excite.gr.imageEraseDialog = false;
			if ( Excite.gr.customImage )
				$('#hiddenImageDel').val('delete');
		}
	});
}

Excite.gr.bindGroupNameChangeEvent = function () {
	elem = $('#exciteNewGroupName');
	elem.unbind('change');
	elem.change( function(e) {
		val = $(this).val().trim();
		if(val == '' ) val = '&nbsp;';
		$('#groupPreview').children('h1').html(val);
	});
}

Excite.gr.eraseDate = function (e) {
	e.preventDefault();
	if ( Excite.userType == Excite.userTypes.EXPRESS) return;
	d = Excite.datepicker($("#datepickerExpire"));
	d.datepicker('setDate', '');
}
Excite.gr.colorPCallback = function (color) {

	// console.log("calback " + color + ' ' + Excite.gr.colorP.hsl[2]);
	textColor = '#fff';
	if (Excite.gr.colorP.hsl[2] > 0.5 )
		textColor = '#000';
	$('#groupPreview').attr('style', 'background-color: ' + color);
	$('#color').css( 'color' , textColor);
	$('#color').css('background-color' , color);
	$('#color').val(color);
	
}
Excite.gr.bindColorPickerEvents = function () {
/*
	$('#color').on( 'click', function(e) {
		//$('#groupPreview').attr('style', 'background-color: ' + $(this).val());
		//console.log("ColorClick " + $(this).val());
	});
*/
	Excite.gr.colorP = $.farbtastic($('#colorpicker'), Excite.gr.colorPCallback);
	color = $('#color').val();

	if ( color == '#0' ) { // strijk foutje van Leslie glad
		color = '#ffffff';
	}
	if ( color == '#ffffff' ) { // correction introduced later
		color = '#a8a8a8';
		//color = '#dd2080';
	}
	missingZeros = 7 - color.length;
	if ( missingZeros > 0 ) { // strijk ander 'foutje' van Leslie glad; missing trailing zeros
		tmp = color.substring(1,7); // picks all chars after #
		while (missingZeros-- > 0) {
			tmp = '0' + tmp;
		}
		color = '#' + tmp;
    }
	$('#color').val(color);
	$('#groupPreview').attr('style', 'background-color: ' + color);
	Excite.gr.colorP.setColor(color);
	$('#color').unbind('input');
	$('#color').on('input',function(e) {
		color = $(this).val();
		//console.log("Newcolor " + color);
		var isHex = /^#[0-9A-F]*$/i.test(color);
		var isOk  = /^#[0-9A-F]{6}$/i.test(color);
		if ( isOk )
			Excite.gr.colorP.setColor(color);
		else {
			//  console.log ( "is hex: " + isHex + ' ' + color.length);
			if ( isHex && color.length < 7) {
				$(this).val(color);
				return;
			}
			else
				$(this).val( Excite.gr.colorP.color);
		}
	});
/*
	$('#colorpicker').change(function(e) {
		//console.log("Pickr " + $(this).val());
	});
*/
}
Excite.gr.bindEraseImageEvent = function () {
	elem = $('#eraseImageB');
	elem.unbind('click');
	elem.on( 'click', function(event) {
		event.preventDefault();
		if ( Excite.gr.imageEraseDialog ) {
			Excite.yesNoDialog('Image verwijderen?', function (val, dialog ) {
				if (val ) {
					Excite.gr.eraseImage();
					Excite.gr.imageEraseDialog = false;
					$('#hiddenImageDel').val( 'delete');
				}
				//Excite.gr.imageEraseDialog = false;
				dialog.dialog('close');
			});			
		} else {
			Excite.gr.eraseImage();
			if ( Excite.gr.customImage )
				$('#hiddenImageDel').val( 'delete' );
			else
				$('#hiddenImageDel').val( '' );
		}
	});
}

Excite.gr.eraseImage = function () {
	defaultImageHTML = sessionStorage.defaultImageHTML;
	viewElem = $('#uploadPreview');
	if ( defaultImageHTML == undefined || defaultImageHTML == null || defaultImageHTML == '')
		viewElem.html('<img>');
	else
		viewElem.html(defaultImageHTML);
	$('#hiddenImageDataUrl').val('');
	elem = $('#GroupImage');
	elem.val('');
	elem.trigger('change');
}

$(document).ready(function(){
	viewElem = $('#uploadPreview');

	Excite.gr.imageEraseDialog = false;
	form = $('#exciteForm');
	// init
	d = Excite.datepicker($("#datepickerExpire"));
	d.datepicker('option', 'minDate', '+1');
	//d.datepicker('setDate', '+30'); geen default leeg laten
	d.show();
	// Group option select list
	// remove onchange attr from option list
	selectList = $("select[name='groups']");
	selectList.removeAttr('onchange');
	// remove action from form; niet doen; we make use of plain submit with this action
	//form.removeAttr('action');
	
	Excite.gr.groupId = selectedVal = selectList.val();
	$('#hiddenGroupId').val(selectedVal);

	Excite.gr.initActivate(selectedVal)

	// create a group name list; for check on new name
	tmp = [];
	$('option').each(function() {
		if ( $(this).val() != 0 ) { // skip 'Nieuwe groep'
			if ( tmp.length == 0 ) tmp.push($(this).html());
			else {
				// check for double name values; can occur!
				if ($(this).html() != tmp[tmp.length -1] )
					tmp.push($(this).html());
			}
		}
	});
	// make it global accessable in the namespace
	Excite.gr.groupNames = tmp;
	newName = $('#exciteNewGroupName').val().trim().replace(/  +/g, ' ');
	Excite.gr.initialGroupName = newName;
	//Excite.gr.radioButnEventHandler();
	Excite.gr.bindImageUploadEvent();
	Excite.gr.bindGroupNameChangeEvent();
	Excite.gr.bindEraseImageEvent();
	Excite.gr.bindColorPickerEvents();
	Excite.gr.bindLabelCheckEvent();
	Excite.gr.bindSelectEvents();
	// Excite.gr.bindEnterPrevent(); TODO nog testen
	
	
	if ( Excite.gr.groupId == 0 ) {
		sessionStorage.defaultAppImpressie = $('#groupPreview').html();
		sessionStorage.defaultGroupImageHTML = sessionStorage.defaultImageHTML = $('#uploadPreview').html();
	} else // eXpress starts with group no new group; tricky business when defaults change !!!!
		sessionStorage.defaultAppImpressie = '<h1>&nbsp;</h1><div id="uploadPreview"><img imagetype="default" height="53" width="160" src="/images/whiteYixowLogo.png"/></div>';
		sessionStorage.defaultImageHTML = '<img imagetype="default" height="53" width="160" src="/images/whiteYixowLogo.png"/>';
	if ( Excite.gr.groupId != 0 )
		Excite.gr.ajaxGetFormHTML(Excite.gr.groupId);
	else {
		if (Excite.userType == Excite.userTypes.EXPRESS )
			Excite.gr.showmap(false);
	}

//EVENT handling


	Excite.gr.groupChange = function (selectList, prevVal) {
		Excite.gr.groupId = selectedVal = selectList.val();
		$('#hiddenGroupId').val(selectedVal); // this one too for Form Submit
		//console.log( 'Group change: new ' + selectedVal + ' old ' + prevVal );
		if ( selectedVal == 0 ) { // back to default; clear the Form
			Excite.gr.clearForm();
			return;
		}
		Excite.gr.ajaxGetFormHTML(selectedVal);
	}
	Excite.gr.formSubmit = function(event) {
		//console.log("SUBMIT in " + Excite.gr.groupId + 'userType ' + Excite.userType);
		groupId = Excite.gr.groupId;
		if ( groupId == null) { // no groups yet
			event.preventDefault();
			type = Excite.userType;
			msg = "Bestel eerst een Groep bij Abonnementen (menu rechtsboven)";
			if (type == Excite.userTypes.EXCITE )
				msg = "Maak eerst een groep bij Groepen";
			Excite.dialogAlert(msg);
			return;
		}

		// validate
		mess = '';
		// remove multiple spaces too
		newName = $('#exciteNewGroupName').val().trim().replace(/  +/g, ' ');
		$('#exciteNewGroupName').val(newName);
		if ( newName == '' )
			mess += '<li>Vul groepsnaam in.</li>';
		else {
			// verkeerde groepsnaam testen
			if ( $.inArray(newName, Excite.gr.groupNames) > -1 ) {
				// groepsnaam bestaat al
				if ( groupId == 0 )
					mess += '<li>Groepsnaam bestaat al.</li>';
				else {
					if ( Excite.gr.initialGroupName != newName )
						mess += '<li>Nieuw gekozen groepsnaam bestaat al.</li>';
				}
			}
		}
		if ( $('#groupLabelCheckB').prop('checked') && $('#exciteNewGroupLableName').val().trim() == '' )
			mess +=  '<li>Vul eXpress labelnaam in.</li>';
		//mess = ''; // uncomment this line to test php validation
		if ( mess != '' ) {
				event.preventDefault();
				Excite.dialogAlert(mess);
				return;

		}

		$('#hiddenGroupId').val(groupId);
			d = Excite.datepicker($("#datepickerExpire"));
			d.datepicker( "option", "disabled", false );
			elem = $('#groupLabelCheckB');
			brother = $('#guestInviteAllowedCheckB');
			elem.prop('disabled', false);
			brother.prop('disabled', false);
		//console.log("SUBMIT Out " + Excite.gr.groupId);
		event.preventDefault();
		$('#exciteForm').submit();
	}

	$('#resetB').on('click', function(e) {
		e.preventDefault();
		console.log("RESET");
		if ( Excite.gr.groupId > 0 )
			Excite.gr.ajaxGetFormHTML(Excite.gr.groupId);
		else
			Excite.gr.clearForm();
	});
	
	Excite.gr.bindEnterPrevent = function () {
		// needed to prevent Enter in input field to invoke a submit
		$('#exciteForm :input').each(function(){
			$(this).on("keypress keydown keyup", function (e) {
				if (e.keyCode == 13) {
					e.preventDefault();
					// side effect: geen Enter in textarea mogelijk; gewoon doortikken met spaties;
					// textarea kan hiervan worden uitgezonderd zodat er toch Enters in kunnen komen te staan,
					// is zinvol als het ook met die newlines de db in moet..
					// dan wel goed letten op of de line terminators correct behouden worden
					// voor latere display op een web page (geen details hier, nog niet getest)
				}
			});
		});
	}


	$('#exciteForm :input').each(function(){
		$(this).on("keypress keydown keyup", function (e) {
			if (e.keyCode == 13) {
				e.preventDefault();
				// side effect: geen Enter in textarea mogelijk; gewoon doortikken met spaties;
				// textarea kan hiervan worden uitgezonderd zodat er toch Enters in kunnen komen te staan,
				// is zinvol als het ook met die newlines de db in moet..
				// dan wel goed letten op of de line terminators correct behouden worden
				// voor latere display op een web page (geen details hier, nog niet getest)
			}
		});

	});
	
});


Excite.gr.showMap = function(color) {
	Excite.gr.provCenter = {
		'NL-GR': [53.27260, 6.77911],							
		'NL-FR': [53.17025, 5.63836],
		'NL-DR': [52.88703, 6.60399],
		'NL-OV': [52.51391, 6.33840],
		'NL-GE': [52.10461, 5.90541],
		'NL-UT': [52.09061, 5.12143],
		'NL-NH': [52.68396, 4.77806],
		'NL-ZH': [52.08332, 4.59388],
		'NL-ZE': [51.55211, 3.86345],
		'NL-NB': [51.73603, 5.14031],
		'NL-LI': [51.21399, 5.85258],
		'NL-FL': [52.42745, 5.55969],
	}
	Excite.gr.provColor = {
		'Groningen': '#CA3E6B',							
		'Friesland': '#4CDEEB',
		'Deenthe': '#649A34',
		'Overijssel': '#C3D605',
		'Gelderland': '#0492D4',
		'Utrecht': '#046A7A',
		'Noord-Holland': '#FC820C',
		'Zuid-Holland': '#A5A216',
		'Zeeland': '#0566CC',
		'Noord-Brabant': '#FCCD05',
		'Limburg': '#B54B2C',
		'Flevoland': '#FC8DA5',
	}

	map = Excite.gr.openMap = L.map('openMap').setView(Excite.gr.provCenter['NL-UT'], 6); // Utrecht in het centrum
	// waar komen de map tiles (plaatjes) vandaan en give credits (it's free and not Google!)
	// gebruik https ivm prod; anders warnings in console van ten minste IE over mixed http en https content
	L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
		attribution: '&copy; <a href="http://openstreetmap.org" target="_blank">OpenStreetMap</a> ',
		//maxZoom: 10, // was 18
		zoomControl: false,
	}).addTo(Excite.gr.openMap);
	$('.leaflet-control-zoom').hide();
	// get geoJson text file with Provinces borders
	var get = $.get( "/js/json/NL-provBorders.json");
	get.done(function( data, textStatus,jqXHR ) {
		if ( data == null ) { // TODO
			Excite.dialogAlert("<li>provBorders: Bel de brandweer, er is een probleem.</li>");
			return;
		}
		
		Excite.ex.colorProvB = L.geoJson(data, {
			style: function (feature) {
					return {
						//color:'#323940',
						color:'black',
						fill: true,
						fillColor: 'red',
						//fillOpacity: 0.1,
						weight: 1.3,
						//dashArray: '1,1',
					};
			},
		});
		if (color) Excite.ex.colorProvB.addTo(map);
		
		Excite.gr.provBorders = L.geoJson(data, {
			style: function (feature) {
					fillColor = Excite.gr.provColor[ feature.properties.Provincienaam];

					return {
						//color:'#323940',
						color:'black',
						fill: true,
						fillColor: fillColor,
						fillOpacity: 0.5, //0.1,
						weight: 1, // dikte grens
						//dashArray: '1,1',
					};
			},
			onEachFeature: function (feature, layer) { // NO not this
					//layer.bindPopup('<strong>' + feature.properties.Provincienaam + '</strong>');
			},
		});
		if (!color) { Excite.gr.provBorders.addTo(map);
		Excite.gr.provBorders.bringToBack(); }
	});
}

Excite.gr.newLayer = function (latLng, radius) {

	layer = Excite.gr.layer;
	//console.log('Newlayer ' + latLng + ' rad ' + radius + ' layer ' + layer);
	// werkt niet goed if ( radius == 0 ) Excite.gr.colorProvB.addTo(Excite.gr.openMap);
	radKm = radius;
	radius = radius * 1000;
	if ( Excite.gr.latLng != null && latLng.equals(Excite.gr.latLng) ) {
		Excite.gr.circle.setRadius(radius);
		return;
	}
	
	if ( layer != null ) { // kan weg
	//console.log("REMOVE ");
		Excite.gr.openMap.removeLayer(layer);
	}
	var circles = new L.layerGroup();

	Excite.gr.latLng = latLng;
	Excite.gr.layer = circles;
	var c = Excite.gr.circle = L.circle(latLng, radius, {
		color: 'Red',
		//fillColor: pink,
		fill: false,
		fillOpacity: 0,
		});
	circles.addLayer(c);
	c = L.circle(latLng, 20, {
		color: 'Blue',
		fillColor: 'Blue',
		fill: true,
		fillOpacity: 1,
	});
	circles.addLayer(c);
	circles.addTo(Excite.gr.openMap);

	return circles;		
}
