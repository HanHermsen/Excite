
/*** DataTable stuff */

$(document).ready(function(){
		// only needed when posting a non Laravel Form; this is a GET!
		// $.ajaxSetup({
		//	headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
		//	data: { '_token': $('meta[name=_token]').attr('content') }
		//});
	QUESTION_SRC = 0; // gast in groep
	QUESTION = 1;
	STATB = 2;
	DATE_IN = 3;
	GROUP_NAME = 4;
	REACH = 5;
	MEMBER_RESP_PERC = 6;
	DATE_OUT = 7;
	RESPONSE_TOTAL = 8; // totaal aantal responses op deze vraag
						// gasten, eigenaar groep en by email
						// all are vars hidden from this one
	QUESTION_ID = 9;
	GROUP_OWNER_ID = 10;
	QUESTION_USER_ID =11;
	QUESTION_GROUP_ID = 12;
	QUESTION_CREATE_DATE = 13;
	QUESTION_DELETED = 14;
	GROUP_DELETED = 15;
	QUESTION_INAPPROP = 16;
	MIN_RESPONSE_FOR_STATS_BUTTON = 5;
	languageUrl = '';
	if (Excite.lang == 'nl' )
		languageUrl = "/js/DataTables/Dutch.json";
	if ( Excite.userType == 0 ) // Excite Light
		MIN_RESPONSE_FOR_STATS_BUTTON = 5;
	if ( location.hostname == 'demo.yixow.com' || location.hostname == 'excite.app' ) MIN_RESPONSE_FOR_STATS_BUTTON = 0;

	var table = $('#questionsDataTable').DataTable( {
		"ajax":  {
				url: "/questions/getTableData",
				"data":   function(d,s) { //show only this group
						if ( $('#publicQuestionsB').prop('checked') ) { 
							d.groupId = -1;
						}
						else
							d.groupId = $("select[name='groups']").val();
					},
			},
		responsive: false,
		serverSide:true,
		processing:true,
		//sDom: 'ftip',
		//dom: '<"#tableToolbar"f>tip',
		dom: 'f<"#tableToolbar">tip',
        "scrollY":        "370px",
        "scrollCollapse": true,
        "paging":         false,
		
		order: [[ GROUP_NAME, "asc" ]],
		"initComplete": function(settings, json) {
			if (Excite.userId == 55 || Excite.userId == 56) {
				txt = Excite.i18n('Toon beknopte statistiek');
				$("#tableToolbar").html('<span id="shortStats"><input id="shortStatsCheckB" type="checkbox" name="shortStatsCheckB"  class="tableCheckB" value="0">&nbsp;&nbsp;'+ txt + '</span>' + '<span id="newStats"><input id="newStatsCheckB" type="checkbox" name="newStatsCheckB" class="tableCheckB" value="0">&nbsp;&nbsp;OldStats</span>');
				//$("#tableToolbar").html('<span id="shortStats"><input id="shortStatsCheckB" type="checkbox" name="shortStatsCheckB" value="0">&nbsp;&nbsp;Toon beknopte statistiek</span>');
			}
			
			if ( Excite.userType != Excite.userTypes.LIGHT ) {
				htm = $("#tableToolbar").html();
				txt = Excite.i18n('Toon mijn vragen buiten mijn zakelijke groepen');
				$("#tableToolbar").html('<span id="publicQuestions"><input id="publicQuestionsB" name="publicQuestionsB" type="checkbox" class="tableCheckB" value="0">&nbsp;&nbsp;' + txt + '</span>' + htm);
				//$("#tableToolbar").html('<input id="publicQuestionsB" name="publicQuestionsB" type="checkbox" value="0">&nbsp;&nbsp;Toon mijn vragen buiten mijn zakelijke groepen' + htm);
				Excite.qu.bindPublicQuestionsB();
			}
			 

		},
		"createdRow": function ( row, data, index ) {
				// get prepared additional data
				addData = table.ajax.json().addData;
				questionId = parseInt(data[QUESTION_ID]);
				if ( addData[questionId] !== undefined) {
					addData = addData[questionId];
					//console.log(addData);
					invitationCnt = addData.invitationCnt;
					emailAnswerCnt = addData.emailAnswerCnt;
					ownerAnswerCnt = addData.ownerAnswerCnt;
				} else {
					invitationCnt = 0;
					emailAnswerCnt = 0;
					ownerAnswerCount = 0;
				}
				totalAnswerCnt = parseInt(data[RESPONSE_TOTAL]);
				memberAnswerCnt = totalAnswerCnt - emailAnswerCnt - ownerAnswerCnt;
				/*** column processing **/
				/** G label */
				if ( data[GROUP_OWNER_ID] != null && data[QUESTION_USER_ID] != data[GROUP_OWNER_ID] ) {
					if ( data[GROUP_OWNER_ID] != Excite.userId ) // vraag als Gast in andermans groep
						tooltip = 'Vraag als gast in Groep van iemand anders';
					else // vraag van Gast in door user beheerde groep
						tooltip = 'Vraag van gast';
					gButton = "<a href='#' class='hoverRow' title='" + tooltip + "'>" + "<button id='gbutton' disabled='disabled' style='background-color: #FF7020'>G</button> </a>";
					$('td', row).eq(QUESTION_SRC).html(gButton);					
				}
				/** question text */
				setColor = '';
				if ( data[QUESTION_DELETED] != 0 ) { // deleted question
					// //#FF7020 #c1c1c7
					setColor = "class='questionDeleted' style='text-decoration: line-through'";
				} else {
					if ( data[DATE_OUT] != null && Excite.isBeforeToday(new Date(data[DATE_OUT]) ) )
						setColor = "class='questionExpired'";
					else // #FF7020
						setColor = "class='defaultQuestion'";
				}
				qText = data[QUESTION];
				//if ( parseInt(data[QUESTION_INAPPROP]) >= 8 ) // wordt gedaan in table controller
					//qText = '[Ongepast!]  ' + qText;
				if (data[QUESTION].length > 50) {
					qText = qText.substring(0,49) + "...";
				}

				tooltip = "<a href='#' class='.hoverRow'" + " title='" + data[QUESTION] + "'>" + "<span " + setColor + ">" + qText + "</span></a>";
				elem = $('td', row).eq(QUESTION);
				elem.html(tooltip );
				/** Stats button */
				if ( totalAnswerCnt - MIN_RESPONSE_FOR_STATS_BUTTON < 0 ) { // no statsButton
						$('td', row).eq(STATB).html("");
				}
				/** Date in */				
				if( data[DATE_IN] == null ){ //old question in db
					//console.log("NULL");
					data[DATE_IN] = data[QUESTION_CREATE_DATE];
					$('td', row).eq(DATE_IN).html(data[QUESTION_CREATE_DATE]);
				}
				
				/** group name */
				if ( data[GROUP_NAME] !== null && data[GROUP_DELETED] != 0 ) { // inactive group
					$('td', row).eq(GROUP_NAME).html("<span style='text-decoration: line-through'>" + data[GROUP_NAME] + "</span>");
				}
				/** reach */
				if ( data[GROUP_NAME] === null )
					$('td', row).eq(REACH).html("Publiek");
				else {
					if ( invitationCnt > 0 ) {
						total = parseInt(data[REACH]) + invitationCnt
						// display splitted
						$('td', row).eq(REACH).html(total + ' (' + data[REACH] + '+' + invitationCnt + ')');
						// display total only
						//$('td', row).eq(REACH).html(total);
					}
				}
				/** member response percentage */
				if ( data[GROUP_NAME] === null ) { // publieke vraag buiten publieke groep
					$('td', row).eq(MEMBER_RESP_PERC).html(totalAnswerCnt);
				} else { // this is a group
					if ( (memberAnswerCnt - data[REACH] ) > 0 ) { // fix member loss
						memberAnswerCnt = data[REACH];
					}
					if ( data[REACH] == 0 ) {
						$('td', row).eq(MEMBER_RESP_PERC).html( Excite.statResponseBar(0));
						data[MEMBER_RESP_PERC] = 0;
					}
					else {
						data[MEMBER_RESP_PERC] = Math.round( (memberAnswerCnt/data[REACH]) * 100);
						$('td', row).eq(MEMBER_RESP_PERC).html( Excite.statResponseBar( data[MEMBER_RESP_PERC] ) ) ;
					}
				}
				/** Date out */
				if ( data[DATE_OUT] == null ) {
					$('td', row).eq(DATE_OUT).html("");
				}
				
			},
		"language": {
				"url": languageUrl,

			},
		"columnDefs": [
			{
				// marker
				"targets": QUESTION_SRC,
				"defaultContent": "",
				"searchable": false,
				"bSortable" : false
			},
			{
				// the Stats button 
				"targets": STATB,
				"data": null,
				"defaultContent": "<button id='statsButton'>S</button>",
				"searchable": false,
				"bSortable" : false
			},
			{
				// Date in
				"targets": DATE_IN,
				"searchable": false,
				'type': 'string',
			},
			
			{
				// Groep
				"targets": GROUP_NAME,
				"searchable": false
			},

			{
				// Bereik
				"targets": REACH,
				"searchable": false,
				//sortable: false,
			},
			{
				// answer count
				"targets": RESPONSE_TOTAL,
				"visible": false,
				// this is needed for invisibility when responsive is true
				"className": "never", 
				"searchable": false
			},
			{
				// response %
				"targets": MEMBER_RESP_PERC,
				"searchable": false,
				"defaultContent": 0,
				//"iDataSort": response, // sorteer op hidden abs aantal column
				sortable : false,
				//"data": response,
			/*	"render": 	function render(data, type, row, meta ){
								if ( data == undefined ) return 0;
								//console.log("RENDER " + data + ' ' + type);
								return( data);
							} */

			},
			{
				// Date out
				"targets": DATE_OUT,
				"searchable": false
			},
			
			{
				// invisible question id kolom
				"targets": QUESTION_ID,
				"visible": false,
				// this is needed for invisibility when responsive is true
				"className": "never", 
				"searchable": false
			},
			{
				// invisible group userId kolom
				"targets": GROUP_OWNER_ID,
				"visible": false,
				// this is needed for invisibility when responsive is true
				"className": "never", 
				"searchable": false
			},
			{
				// invisible question userId kolom
				"targets": QUESTION_USER_ID,
				"visible": false,
				// this is needed for invisibility when responsive is true
				"className": "never", 
				"searchable": false
			},
			{
				// invisible question group_id kolom
				"targets": QUESTION_GROUP_ID,
				"visible": false,
				// this is needed for invisibility when responsive is true
				"className": "never", 
				"searchable": false
			},
			{	// invisible question QUESTION_CREATE_DATE kolom
				"targets": QUESTION_CREATE_DATE,
				"visible": false,
				// this is needed for invisibility when responsive is true
				"className": "never", 
				"searchable": false
			},
			
			{
				// invisible QUESTION_DELETED kolom
				"targets": QUESTION_DELETED,
				"visible": false,
				// this is needed for invisibility when responsive is true
				"className": "never", 
				"searchable": false
			},
			{
				// invisible GROUP_DELETED kolom
				"targets": GROUP_DELETED,
				"visible": false,
				// this is needed for invisibility when responsive is true
				"className": "never", 
				"searchable": false
			},
		 ],

	});

	/** less server access for search does not work correctly but gives an idea
	$('.dataTables_filter input')
		.unbind('keypress keyup')
		.bind('keypress keyup', function(e){
				if ($(this).val().length < 3 && e.keyCode != 13) return;
					table.fnFilter($(this).val());
		});
	**/

	$('#questionsDataTable tbody').on('click', 'button', function () {
		row = table.row( $(this).parents('tr') );
		var data = row.data();
		if ( data == null )
			alert("opgevouwen button; dat werkt niet");
		else {
			Excite.qu.newStatsReq = 1;
			if ( $('#newStatsCheckB').prop('checked') ) Excite.qu.newStatsReq = 0;
			if ( $('#shortStatsCheckB').prop('checked') ) {
				Excite.qu.statsWindow.disabledButton = ''; // init to be disbabled button in Stats Home window
				Excite.qu.ajaxGetStats(data[QUESTION_ID],data[QUESTION], null,null,null,'altTemplate');
			}
			else {
				Excite.qu.statsWindow.disabledButton = ''; // init to be disbabled button in Stats Home window
				Excite.qu.ajaxGetStats(data[QUESTION_ID],data[QUESTION], null);
			}
		}
	} );
	
	$('#questionsDataTable tbody').on('click', 'tr', function (e) {
	//console.log( e.target);
		if( e.target.id == 'statsButton' ) return;
		Excite.qu.clickedRow = $(this);
		row = table.row( this );
		var data = row.data();
		if ( data == null ) {
			alert("opgevouwen button; dat werkt niet");
			return;
		}

		Excite.qu.questionId = data[QUESTION_ID];
		Excite.qu.ajaxGetQ(data);
return;
/*
		Excite.qu.formInputs.uploadFile.val('');
		Excite.qu.formInputs.uploadFile.trigger('change');

		grp = $("#groupSelector").val();
		elem = $("#groupSelector option[value='" + grp + "']");
		if ( elem.parent().prop('label') != 'Actief' &&  ! $('#publicQuestionsB').prop('checked')) { submitB.hide();} // inactive groep

			
		elem = $("select[name='groups']");
		//console.log("jaaaaaaaaaaa " + elem.val() + " " + data[QUESTION_GROUP_ID]);
		if ( $('#publicQuestionsB').prop('checked') ) return; // no table refresh
		if ( elem.val() != data[QUESTION_GROUP_ID] ) {
			elem.val(data[QUESTION_GROUP_ID]);
			$('#questionsDataTable').DataTable().ajax.reload();
		}

		*/
	});
});

Excite.qu.handleClearFormButton = function (e) {
		e.preventDefault();
		Excite.qu.resetForm(true);
		Excite.qu.questionId = 0; // Browse mode off
}

Excite.qu.handleReuseQuestionButton = function (e) {
		$("input[name='submitb']").val("Bevestig");
		e.preventDefault();
		Excite.qu.reuse = true;
		Excite.qu.enableForm();
		Excite.qu.questionId = 0; // Browse mode off
}
Excite.confirmPageChange = function() { // called by main menu event handler in exciteShared.js
	//console.log("questions.confirm page change");
	Excite.tmp = true;
	$('#exciteForm :input').each(function(){
		if ( ! Excite.tmp ) return;
		elem = $(this);
		elemName = elem.attr("name");
		if ( elemName == 'question' ) {
			if ( elem.val().trim() != '' ) {
				Excite.tmp = false;
				//return;
			}	
		}
		//if ( elemName == 'user_image') { // TODO does this work?
		if ( elem === Excite.qu.formInputs.uploadFile) {
			if ( elem.val() != '' ) {
				Excite.tmp = false;
				//return;
			}
		}
		elemClass = elem.attr("class");
		if ( elemClass == 'answer' && elem.val().trim() != '' ) {
			//Excite.tmp = false;
			return;
		}
	});
	return Excite.tmp;
}

// functions for questions

Excite.qu.transposeAnswer = function ( first , second) { // switch answers up/down

	first = 'exciteAnswer' + first;
	second = 'exciteAnswer' + second;
	//alert(first + "" + second);
	tmp = $('#' + first).val().trim();
	$('#' + first).val($('#' + second).val().trim());
	$('#' + second).val(tmp);
}

Excite.qu.ajaxGetQ = function (tabData) {
	//console.log("GROUP " + groupId );
	var get = $.getJSON( "/questions/getQ", {
		questionId: tabData[QUESTION_ID]
	} );
	get.done(function( data, textStatus,jqXHR ) {
		if ( data == null ) { // TODO
			Excite.dialogAlert("<li>Bel de brandweer, er is een probleem.</li>");
			return;
		}
		Excite.qu.populateForm(data,tabData);
	});
}


Excite.qu.ajaxStoreQdates = function (questionId) {
	//console.log("GROUP " + groupId );
	dFrom = Excite.datepicker($( "#datepickerFrom" ));
	dTill = Excite.datepicker($( "#datepickerTill" ));
	dateIn = dFrom.datepicker('getDate');
	dateOut = dTill.datepicker('getDate');
	
	if ( dateOut != null )
		dateOut = dateOut.getTime();

	var get = $.getJSON( "questions/updateQdates", {
		dateIn: dateIn.getTime(),
		dateOut: dateOut,
		questionId: questionId,
	} );
	get.done(function( data, textStatus,jqXHR ) {
		if ( data == null ) { // TODO
			Excite.dialogAlert("<li>Bel de brandweer, er is een probleem.</li>");
			return;
		}
		Excite.dialogAlert(data.mess);
		$('#questionsDataTable').DataTable().ajax.reload();
	});
}
// not in use; we do it with the get above
Excite.qu.ajaxPostQdates = function() {
	dateIn = dateOut = 0;
	dTill = Excite.datepicker($( "#datepickerTill" ));
	dFrom = Excite.datepicker($( "#datepickerTill" ));

	var post = $.post("questions/updateQdates", {dateIn: dateIn, dateOut: dateOut});
	post.done(function( data, textStatus,jqXHR ) {
		if ( data == null ) { // TODO
			Excite.dialogAlert("<li>Bel de brandweer, er is een probleem.</li>");
			return;
		}
		Excite.dialogAlert(data);
	});
}
Excite.qu.populateForm = function(data,tabData) {
	qDeleted = false;
	if ( tabData[QUESTION_DELETED] > 0 ) {
		qDeleted = true;
	}
	
	submitB = $("input[name='submitb']");
	submitB.val('Bevestig');
	// first disable all inputs
	$('#exciteForm :input').prop('disabled', true);

	// re-enable buttons
	$("#eraseDate").prop('disabled', false);
	
	submitB.prop('disabled', false);
	$('#clearForm').prop('disabled', false);


	$('#reuseQuestion').prop('disabled', false);
	$('#reuseQuestion').show();	
	$('#delQuestion').prop('disabled', false);
	$('#delQuestion').show();
	
	if (qDeleted ) {
		$('#delQuestion').html('Zet terug in App');
		submitB.val("Verwijder Definitief");
	}
	else {
		$('#delQuestion').html('Verwijder uit App');
		submitB.val("Bevestig");
	}
	
	q = data.question[0];
	
	// show questions and answers
	$('#exciteQuestion').val(q.question);
	
	o = data.options;
	le = o.length;
	for ( i = 0 ; i < 6; ++i ) {
		select = '#exciteAnswer' + (i+1);
		if ( i < le ) {
			$(select).val(o[i].text);
		} else
			$(select).val('');
	}
	
	if ( q.image != null) { // Alle plaatjes passen binnen 4x3 ....
		height = $('#imageUploadPreview').css('height');
		width =  $('#imageUploadPreview').css('width');
		file = '/api/api/images/' + q.image;
		imgHTML = "<img width='" + width + " height='" + height + "' src='" + file + "'/>";
		$('#imageUploadPreview').html(imgHTML);
		$('#hiddenImageFile').val(q.image);
	} else $('#imageUploadPreview').html('<img> Geen afbeelding');

	// handle datepickers
	Excite.qu.initDateP( q.dateIn, q.dateOut, qDeleted );

	elem = $("select[name='groups']");

	if ( elem.val() != 0 ) { // reset datatable; TODO dit klopt niet
		//$('#questionsDataTable').DataTable().ajax.reload();
	}
	// finishing touch
	if ( $('#publicQuestionsB').prop('checked') ) {  // public question stuff
		if (qDeleted) {
			submitB.show();
		} else
			submitB.hide();
	}
return;
	//console.log("GROEP " + q.group_id);
	elem.val(q.group_id);
	//elem.prop('disabled', true);
}
Excite.qu.enableForm = function() {
	$('#exciteForm :input').prop('disabled', false);

	elem = $("select[name='groups']");
	elem.prop('disabled', false);
	elem.val(0);
	/*
	// no change trigger; houdt ingesnoerd
	elem.trigger('change');
	$('#imageUploadPreview').html('<img> Geen afbeelding');
	*/

	$('#reuseQuestion').hide();
	$('#delQuestion').hide();
	//$('#clearForm').show();
	$("input[name='submitb']").show();

	d = Excite.datepicker($( "#datepickerFrom" ));
	d.datepicker( "option", "disabled", false );
	d.datepicker('option', 'minDate', 0);
	d.datepicker('option', 'maxDate', 14);
	d.datepicker('setDate', '0');

	d = Excite.datepicker($( "#datepickerTill" ));
	d.datepicker( "option", "disabled", false );
	d.datepicker('option', 'minDate', 0);
	d.datepicker('setDate', '+30');
}

Excite.qu.resetForm = function(clearGroups) {
//console.log("ResetForm");
/*
	if ( Excite.qu.questionId == 0 ) {
		console.log("NO FORM RESET NEEDED ");
		return;
	} */
	//$('#dateFromDiv').find('label').html("Looptijd van");
	$("input[name='submitb']").val("Bevestig");
	elem = $("select[name='groups']");
	elem.prop('disabled', false);
	if ( clearGroups ) {
		if ( elem.val() != 0 ) {
			elem.val(0);
			elem.trigger('change');
		}
	
	} 
	$('#dateTillDiv').find('label').html("Looptijd tot"); // TODO nodig??

	//if ( elem.val() != 0 || Excite.qu.reuse)
	if (Excite.qu.reuse)
	{
		Excite.qu.reuse = false; // TODO even dit niet
		elem.val(0);
		elem.trigger('change');
	}
	Excite.qu.formInputs.uploadFile.val('');
	Excite.qu.formInputs.uploadFile.trigger('change');
	$('#imageUploadPreview').html('<img> Geen afbeelding');
	$('#hiddenImageFile').val('');
	$('#exciteQuestion').val('');
	$('#exciteQuestion').prop('disabled', false);
	$('#eraseImageB').prop('disabled', false);
	$('.answer').val('');
	$('.answer').prop('disabled', false);
	$('.navButton').prop('disabled', false);
	Excite.qu.formInputs.uploadFile.prop('disabled', false);

	//$('#allQuestions').hide();
	$('#reuseQuestion').hide();
	$('#delQuestion').hide();
	$('#clearForm').show();
	//$("input[name='submitb']").val("Bevestigen");
	$("input[name='submitb']").show();
	//elem.val(0);
	//if ( groupId > 0  )
	/*
		if (groupId != elem.val() ) {
			elem.val(groupId);
			$('#questionsDataTable').DataTable().ajax.reload();

		}
	*/

	d = Excite.datepicker($( "#datepickerFrom" ));
	d.datepicker( "option", "disabled", false );
	d.datepicker('option', 'minDate', 0);
	d.datepicker('option', 'maxDate', 14);
	d.datepicker('setDate', '0');

	d = Excite.datepicker($( "#datepickerTill" ));
	d.datepicker( "option", "disabled", false );
	d.datepicker('option', 'minDate', 0);
	d.datepicker('setDate', '+30');
}


Excite.qu.bindImageUploadEvent = function () {
	//console.log("BIND... ");
	viewElem = $('#imageUploadPreview');
	var defaultImageHTML = Excite.qu.defaultImageHTML = viewElem.html();
	// $('#user_image')
	Excite.qu.formInputs.uploadFile.unbind('change');
	Excite.qu.formInputs.uploadFile.change( function(e) {
		val = $(this).val();
		console.log('Change event ' + val);
		// Bootstrap/Leslie solution of File Chooser see questions_index.blade
		//$("#uploadFile").val(val.substring(12)); blijft in blade
		if ( val != '') {
			ret = Excite.imageUploadEventHandler(e, $(this), viewElem, 1, defaultImageHTML, $('#hiddenImageDataUrl'),{width:320,height: 240});
			//console.log("IMG result :" + ret + ' ' + $(this).val() + ' ' + $("#uploadFile").val());		
		} else {
			console.log("image reset to zero");
			$('#hiddenImageFile').val('');
		}
	});
}
// INIT Stuff
$(document).ready(function(){
	// COMPONENT PROPS kan bij Excite light achterwege blijven
	if ( Excite.userType == Excite.userTypes.LIGHT ) return;

	// SPECIALTY hide first 'up' nav button with this class attr name but keep the display block ...
	$(".noshow").css('visibility', 'hidden');

	//Excite.qu.submitbVal = $("input[name='submitb']").val();
	// init some Globals

	Excite.qu.formInputs = {};
	Excite.qu.formInputs.groupSelect = groupSelect = $("select[name='groups']");
	Excite.qu.formInputs.uploadFile = $('#user_image');
	
	// init for Question Browsing & Date Change
	Excite.qu.questionId = 0;

	$('#reuseQuestion').hide();
	$('#delQuestion').hide();
	//$('#clearForm').hide();
	$('#clearForm').on('click', function(e) {
		e.preventDefault();
		Excite.qu.handleClearFormButton(e);
		//$(this).hide();
	
	});
	
	$('#reuseQuestion').on('click', function(e) {
		e.preventDefault();
		Excite.qu.handleReuseQuestionButton(e);
		
	});
	
	$('#delQuestion').on('click', function(e) {
		e.preventDefault();
		delType = 0;
		if ( $(this).html().indexOf("Verwijder") > -1 )
			delType = 1;
		Excite.qu.delQ(delType);
	});

	// datepickers
	d = Excite.datepicker($( "#datepickerFrom" ));
	//d = Excite.datepicker($( "#datepickerFrom" ));
	d.datepicker('option','onClose' , function( dateText, picker ) {
			if ( picker.lastval == dateText ) return; // no change made
			// let the other one follow a change in least selectable start date
			$( "#datepickerTill" ).datepicker( "option", "minDate", new Date(dateText) );
		 });
	d.datepicker('setDate', '0');
	d.datepicker('option', 'maxDate', 14);
	
	
	d = Excite.datepicker($( "#datepickerTill" ));
	d.datepicker('option', 'minDate', '0');
	d.datepicker('setDate', '+30');





	// EVENTS
	groupSelect.change( function(event) {
		console.log("change event " + $(this).val());
		//Excite.qu.resetForm();
		if ( $('#publicQuestionsB').prop('checked')) {
			$('#publicQuestionsB').prop('checked', false);
		}
		if ( Excite.qu.reuse) { console.log("Reuse");
			//Excite.qu.formInputs.uploadFile.val('');
			//Excite.qu.formInputs.uploadFile.trigger('change');
		}
		$('#questionsDataTable').DataTable().ajax.reload();
	});

	Excite.qu.bindImageUploadEvent();
	
	// needed to prevent enter in input field to invoke a submit or transposeAnswer()
	$('#exciteForm :input').each(function(){
		if ( $(this).attr('class') != 'question') {
			$(this).on("keypress keydown keyup", function (e) {
				if (e.keyCode == 13) {
						e.preventDefault();
				}
			}); 
		} else {
				$(this).on("keypress keydown keyup", function (e) {
				if (e.keyCode == 13) {
						e.preventDefault();
						// Note that this implies no enters in text field; can be done otherwise
				}
			});
		
		}
	});
	// give the nav buttons their functions
	$('.navButton').each(function() {
	   var elem = $(this);
	   elem.on("click", function(event){
	   		event.preventDefault(); // prevent side-submits
			buttonName = event.target.name;
			if ( buttonName.substring(0, 2) == 'up') {	
				// go up
				row= parseInt(buttonName.substring(2, 3));
				Excite.qu.transposeAnswer( row, row - 1);
			} else {
				// go down
				row = parseInt(buttonName.substring (4, 5));				
				Excite.qu.transposeAnswer( row , row + 1);
			}
	   });	 
	});
	$('#eraseImageB').on( 'click', function(event) {
		event.preventDefault();
		defaultImageHTML = Excite.qu.defaultImageHTML;
		viewElem = $('#imageUploadPreview');
		if ( defaultImageHTML == undefined || defaultImageHTML == null || defaultImageHTML == '')
			viewElem.html('<img>');
		else
			viewElem.html(defaultImageHTML);
		$('#hiddenImageDataUrl').val('');
		elem = $('#user_image');
		elem.val('');
		elem.trigger('change');
	});
	// validate and submit when ok
	$("input[name='submitb']").on( 'click', function(event) {
		if ( $(this).val()  != "Bevestig" ) {
			event.preventDefault();
			Excite.qu.delQ(2);
			return;
		}
		if ( Excite.qu.questionId > 0 ) {
			event.preventDefault();
			if ( $( "#datepickerTill" ).val() != '' && $( "#datepickerTill" ).val()[0] == ' ') {
				Excite.dialogAlert("Kies of wis datum");
				return;
			}
			// Excite.qu.ajaxPostQdates();
			Excite.qu.ajaxStoreQdates(Excite.qu.questionId)
			return;
		}
		answerCnt = 0;
		errMess = '';
		fileMess = '';
		grp = $("#groupSelector").val();
		//if ( $("#groupSelector").find(':selected').val() == 0 ) {
		if ( grp == 0 ) {
				errMess = "<li>" + Excite.i18n('Kies een Actieve Groep.') + "</li>";
		} else {
			elem = $("#groupSelector option[value='" + grp + "']");
			if ( elem.parent().prop('label') != 'Actief' ) { errMess = "<li>" + Excite.i18n('Kies een Actieve Groep.') + "</li>";};
		}

		$('#exciteForm :input').each(function(){
			elem = $(this);
			elemName = elem.attr("name");
			if (elemName == 'question' ) {
				tmp = elem.val().trim();
				if ( tmp == '' )
					errMess += "<li>Vraag niet ingevuld.</li>";
				else if (tmp.length < 5 )
					errMess += "<li>Vraag te kort.</li>";
			}
			elemClass = elem.attr("class");
			if ( elemClass == 'answer' && elem.val().trim() != '' )
				answerCnt++;
		});
		if (answerCnt < 2 ) {
				errMess += "<li>Minstens twee antwoorden nodig.</li>";
		}
		
		errMess += fileMess;
		// errMess = ''; // uncomment this line for test server side validation
		if ( errMess != '') {
			Excite.dialogAlert(errMess);
			event.preventDefault();
		}
		// submits when not prevented
	});
	
	//$( Document ).tooltip();

});
Excite.qu.eraseDate = function (e) {
	e.preventDefault();
	d = Excite.datepicker($("#datepickerTill"));
	d.datepicker('setDate', '');
}
Excite.qu.delQ = function (type) {
	var get = $.getJSON( "/questions/delQ", {
		q: Excite.qu.questionId,
		t: type
	});
	get.done(function( data, textStatus,jqXHR ) {
		if ( data == null ) { // TODO
			Excite.dialogAlert("<li>Bel de brandweer, er is een probleem.</li>");
			return;
		}
		Excite.dialogAlert(data.mess);
		submitB = $("input[name='submitb']");
		dFrom = Excite.datepicker($( "#datepickerFrom" ));
		dTill = Excite.datepicker($( "#datepickerTill" ));
		dateIn = dFrom.datepicker('getDate');
		dateOut = dTill.datepicker('getDate');
		switch (type) {
			case 0: // was Zet terug in App /Verwijder definitief
				oldVal = dTill.val();
				Excite.qu.initDateP( dateIn, dateOut, false);
				dTill.val(oldVal);
				//Excite.qu.clickedRow.trigger('click');
				$('#delQuestion').html('Verwijder uit App');

				if ( ! dTill.prop('disabled') ) {
					submitB.val("Bevestig");
				} else
					submitB.hide();
				break;
			case 1: // was verwijder uit app
				$('#delQuestion').html('Zet terug in App');
				submitB.val("Verwijder Definitief");
				submitB.show();
				oldVal = dTill.val();
				$('#eraseDate').prop('disabled', true);
				dFrom.prop('disabled', true);
				dTill.prop('disabled', true);
				dFrom.datepicker( "option", "disabled", true );
				dTill.datepicker( "option", "disabled", true );
				dTill.val(oldVal);
				break;
			case 2: // delete definitely
				Excite.qu.resetForm(true);
		}
		$('#questionsDataTable').DataTable().ajax.reload();
	});
}

Excite.qu.bindPublicQuestionsB = function() {
	$('#publicQuestionsB').unbind('click');
	$('#publicQuestionsB').on('click', function(e) {
		Excite.qu.resetForm(true);
		if ( $(this).prop('checked') ) {
			// new status is checked
			Excite.qu.publicQuestionsB = true;
			console.log("au");			
		}
		$('#questionsDataTable').DataTable().ajax.reload();
	});
}

Excite.qu.initDateP = function ( dateIn, dateOut, qDeleted ) {
	dFrom = Excite.datepicker($( "#datepickerFrom" ));
	dateFrom = new Date(dateIn);
	dFrom.datepicker('option', 'minDate', dateFrom);
	dFrom.datepicker('setDate', dateFrom);
	if ( Excite.isBeforeToday(dateFrom) ) {
		dFrom.datepicker( "option", "disabled", true );
	} else {
		dFrom.prop('disabled', false);
		dFrom.datepicker( "option", "disabled", false );
		dFrom.datepicker('option', 'minDate', 0);
		dFrom.datepicker('option', 'maxDate', 14);
		dFrom.datepicker('setDate', 0);		
	}
	if ( qDeleted || $('#publicQuestionsB').prop('checked') ) {
		// overrule what's decided above
		dFrom.prop('disabled', true);
		dFrom.datepicker( "option", "disabled", true );
	}
	dateTill = null;
	if ( dateOut != null )
		dateTill = new Date(dateOut);
	dTill = Excite.datepicker($( "#datepickerTill" ));
	dTill.datepicker('option', 'minDate', 0);
	if ( qDeleted || $('#publicQuestionsB').prop('checked') ) {
		dTill.prop('disabled', true);
		dTill.datepicker( "option", "disabled", true );	
		$('#eraseDate').prop('disabled', true);
	} else {
		dTill.prop('disabled', false);
		dTill.datepicker( "option", "disabled", false );
		$('#eraseDate').prop('disabled', false);
	}

	if ( dateTill != null ) {
		month = dateTill.getMonth()+ 1;
		if ( month < 9 )
			month = '0' + month;
		date = dateTill.getDate();
		if ( date < 9 )
			date = '0' + date;
		val = ' ' + dateTill.getFullYear() + '-' + month + '-' + date;
		dTill.val(val);
	} else {
		dTill.val(' Kies of wis datum');
	}


}
//TODO fix: doet het niet goed zie guests voor juiste werking
  $(function() {
    $( '.hoverRow' ).tooltip({
      show: {
        effect: "slideDown",
        delay: 399
      }
    });
  });

