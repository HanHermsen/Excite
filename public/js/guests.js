
/*** DataTable stuff */

$(document).ready(function(){
/*
	$.ajaxSetup({
			headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
			data: { '_token': $('meta[name=_token]').attr('content') }
		});
*/
	Excite.gu.addListsToForm();
	// datatable checkbox ctrl feature flag
	//Excite.gu.cbFeature = (Excite.isChrome || Excite.isSafari);
	//Excite.gu.cbFeature = (location.hostname == 'test.yixow.com' || location.hostname == 'demo.yixow.com' || location.hostname == 'excite.app'); 
	Excite.gu.cbFeature = true;
	Excite.gu.cbList = {};
	sessionStorage.exciteHintCount = 0;
	
	Excite.gu.checkbox = function (uid) {
		this.uid = uid;
	}
	
	CHECKBOX = 0;
	EMAIL = 1;
	DATE_IN = 2;
	GROUP = 3;
	QUESTION_CNT = 4;
	RESPONSE_PERCENT = 5;
	RESPONSE_CNT = 6; // hide aantal antwoorden van een member binnen een group
	DATE_OUT = 7;
	MAPPED_UID = 8; // hidden; mapped short uid
	DISPLAY_NAME = 9; //hidden
	DISPLAY_EMAIL = 10; //hidden 0 or 1
	HIDDEN_EMAIL = 11; //hidden not in use; always: noReply@yixow.com
	GROUP_DELETED = 12; //hidden
	GROUP_ID = 13; //hidden
	
	MIN_RESPONSE = 0; // the real value
	
	PAGE_LENGTH = 500;
	if ( Excite.isFirefox )
		PAGE_LENGTH = 500;

	var table = Excite.gu.dataTable = $('#guestsDataTable').DataTable( {
		"ajax": {
				url: "/guests/getTableData",
				"data":   function(d,s) {
					d.groupId = $("select[name='groups']").val();
					},
			},
		"initComplete": function(settings, json) {

			//onsole.log( 'DataTables has finished its initialisation.' );

		},
		responsive: false,
		pageLength: PAGE_LENGTH,
		//paging: false,
		autoWidth: false,
		serverSide:true,
		//deferLoading: 10, // only load when called
		processing:true,
		sDom: 'ftrip',
        "scrollY":        "370px",
       // "scrollCollapse": true,
       //"paging":         false,
			// Scroller Plugin
			//deferRender:    true,
			scroller:       true,
		order: [[ 3, "asc" ], [1, 'asc']],
		search: {
			smart: false,
		},
		// "jQueryUI": true,
		"drawCallback": function (settings) {
							// group selected on start or page refresh after submit
							if ( Excite.gu.pageRefresh && Excite.qu.selectedVal != 0 ) {
								Excite.gu.ajaxGetLists(Excite.qu.selectedVal);
							} else {
								if(Excite.gu.cbFeature)
									Excite.gu.initCheckboxes();
							}
							Excite.gu.pageRefresh = false;
							Excite.gu.bindSelectGuest();
						},
		"initComplete": function(settings, json) {
			

		},
		"createdRow":
			function ( row, data, index ) {

				if ( Excite.gu.cbFeature) {
					if ( index == 0 ) {
						Excite.gu.cbList = {}; // start new checkbox list
					}
					// haal evt (afgeschermd) weg
					val = data[EMAIL].split(' ')[0];
					Excite.gu.cbList[val] = new Excite.gu.checkbox(data[MAPPED_UID]);
				}
				if ( data[QUESTION_CNT] - MIN_RESPONSE < 0 ) { $('td', row).eq(QUESTION_CNT).html("<10"); }
				if ( data[RESPONSE_CNT] == 0 )
					$('td', row).eq(RESPONSE_PERCENT).html( Excite.statResponseBar(0));
				else
					$('td', row).eq(RESPONSE_PERCENT).html( Excite.statResponseBar(Math.round(data[RESPONSE_CNT]/data[QUESTION_CNT] * 100)));
				// is hidden for now datum uit niet in db
				$('td', row).eq(DATE_OUT).html("??");
				
				elem = $('td', row).eq(CHECKBOX);
				elem.html(data[CHECKBOX]);
			},
					
		"language": {
				"url": "/js/DataTables/Dutch.json",
			},
		"columnDefs": [ {
				// the check box 
				"targets": CHECKBOX,
				"data": null,
				//"defaultContent": "",
				"searchable": false,
				"örderable": false,
				"sortable" : false
			},
			{
				// user email
				"targets": EMAIL,

			},
			{
				// Date in
				"targets": DATE_IN,
				"searchable": false,
				"searchable": false
			},
			
			{
				// Groep
				"targets": GROUP,
				"searchable": true,
				orderData: [ GROUP, EMAIL]
			},

			{
				// Aantal Vragen
				"targets": QUESTION_CNT,
				"searchable": false,
				"sortable" : false,
			},

			{
				// Response %
				"targets": RESPONSE_PERCENT,
				"searchable": false,
				"defaultContent": "PERCENTAGE",
				"sortable" : false,
				//"iDataSort": RESPONSE_CNT, // sorteer op hidden abs aantal column
			},
			{
				// response in aantal; hide
				"targets": RESPONSE_CNT,
				"searchable": false,
				"visible": false,
				// this is needed for invisibility when responsive is true
				"className": "never", 
			},
			
			{
				// Date out
				"targets": DATE_OUT,
				"searchable": false,
				"orderable" : false,
				"sortable" : false,
				"visible": false,
				// this is needed for invisibility when responsive is true
				"className": "never", 
			},
		/* all hidden from here
			{
				"targets": MAPPED_UID,
				"searchable": false,
				"orderable" : false,
				"sortable" : false,
				"visible": false,
				// this is needed for invisibility when responsive is true
				"className": "never", 
			},
			{
				"targets": DISPLAY_NAME,
				"searchable": false,
				"orderable" : false,
				"sortable" : false,
				"visible": false,
				// this is needed for invisibility when responsive is true
				"className": "never", 
			},
			{
				"targets": DISPLAY_EMAIL,
				"searchable": false,
				"orderable" : false,
				"sortable" : false,
				"visible": false,
				// this is needed for invisibility when responsive is true
				"className": "never", 
			},
			{
				"targets": HIDDEN_EMAIL,
				"searchable": false,
				"orderable" : false,
				"sortable" : false,
				"visible": false,
				// this is needed for invisibility when responsive is true
				"className": "never", 
			} */
					
		]
	});

});
/* example of multiple email list with locations of elem and hiddenElem of a List
 hiddenElem:  <input name="hiddenNewInvitationInput" class="form-control" id="hiddenNewInvitationInput" style="display: none;" type="hidden" value="leo@anl.nl,paulhendriks@ziggo.nl">
				<div class="multiple_emails-container">
					<ul class="multiple_emails-ul">
						<li class="multiple_emails-email"><a class="multiple_emails-close" href="#"><i>X</i></a><span class="email_name">leo@anl.nl</span></li>
						<li class="multiple_emails-email"><a class="multiple_emails-close" href="#"><i>X</i></a><span class="email_name">paulhendriks@ziggo.nl</span></li>
					</ul>
elem:				<input class="multiple_emails-input text-left" id="newInvitationList" style="display: none;" type="text"></div>


*/


// declare a Class for the List Object; for Class() see: exciteShared.js
// this is a simple approach; no inheritance nor private props

Excite.gu.List = Excite.Class( {
	construct:
		function(elem, hiddenElem, options) { //constructor
			this.elem = elem;
			this.hiddenElem = hiddenElem;
			this.list = [];
			this.startList = [];
			this.startCount = 0;
			this.canDelete = true;
			this.doSort = false;
			if ( typeof options !== 'undefined') {
				if ( options.doSort !== undefined ) this.doSort = options.doSort;
				if ( options.canDelete !== undefined ) this.canDelete = options.canDelete;
			}
			showEmptyAddrList(this.elem);
		},
	init:
		function(input) { // public
			this.clear();
			for ( i = 0 ; i < input.length; ++i ) {
				if ( input[i].email != '' && input[i].email != null) { //TODO
					//Er kan of kon een leeg email address terugkomen uit db bij invites
					this.list.push(input[i].email);
					if ( Excite.gu.cbFeature) {
						// haal evt (afgeschermd) weg
						e = input[i].email.split(' ')[0];
						if ( Excite.gu.cbList[e] !== undefined) {
							//Excite.gu.cbList[e].hide = true;
							tmp = "[id^=mappedUid" + Excite.gu.cbList[e].uid + ']';
							$(tmp).hide();
						}
					}
				}
			}
			/* dont do this on init
			if( this.doSort)
				this.list.sort();
			*/
			this.startCount = this.list.length;
			// for the view
			this.elem.val(this.list);
			this.elem.trigger(jQuery.Event('keyup', {which: 9, keyCode: 9}));
			// altijd dit ook bijwerken!!
			this.hiddenElem.val(this.list);
			if ( ! this.canDelete ) {
				this.disableDel();
			}
			this.startList = this.list;
			if (this.startCount == 0 ) return;
			Excite.gu.setListCount(this);
			//TODO
			if ( this.list.length == 0 ) {
					showEmptyAddrList(this.elem);
			}
		},
	restart:
		function(cbHideList) { // public
			//console.log("RESTART " + this.elem.prop('id') + ' ' + this.startCount + ' ' + this.count() + ' ' + cbHideList);
			if ( this.startCount == this.count() ) // no changes
				return;

			this.list = this.startList;
			// for the view
			this.elem.prev().html('');
			this.elem.val(this.list);
			this.elem.trigger(jQuery.Event('keyup', {which: 9, keyCode: 9}));
			// altijd dit ook bijwerken!!
			this.hiddenElem.val(this.list);
			if ( ! this.canDelete ) {
				this.disableDel();
			}
			Excite.gu.setListCount(this);
			if ( Excite.gu.cbFeature ) {
				l = Excite.gu.cbList;
				for ( i = 0 ; i<cbHideList.length; i++ ) {
					addr = cbHideList[i];
					if ( $.inArray(addr, this.startList ) != -1 )
						this.hideCheckbox(addr);
					//if ( l[cbHideList[i]] === undefined ) continue;
					//l[cbHideList[i]].hide = true;
				}
			}
			return;
		},

	removeAllAddrs: // for reinvite all
		function() {
			l = this.list;
			this.list = [];
			this.elem.prev().html('');
			this.elem.val([]);
			this.elem.trigger(jQuery.Event('keyup', {which: 9, keyCode: 9}));
			this.hiddenElem.val([]);
			Excite.gu.setListCount(this);
			if ( Excite.gu.cbFeature) {
				cbl = Excite.gu.cbList;
				for( i = 0; i < l.length; i++ ) {
					addr = l[i];
					if ( cbl[addr] === undefined ) continue;
					tmp = "[id^=mappedUid" + cbl[addr].uid + "]";
					$(tmp).show();
					$(tmp).prop('checked', true);
				}
			}	
		},
	delAddr: // delete with trigger to list
			function(addr) {
			newList = [];
			for ( i = 0 ; i< this.list.length ; i++ ) {
				if ( this.list[i] != addr )
					newList.push(this.list[i]);
			}
			//console.log("NEEEEEEEEEEEE " + newList);
			this.list = newList;
			Excite.gu.setListCount(this);
			
			this.elem.prev().html('');
			this.elem.val(this.list);
			this.elem.trigger(jQuery.Event('keyup', {which: 9, keyCode: 9}));
			// altijd dit bijwerken!!!!
			this.hiddenElem.val(this.list);
			// undefined is possible for remove from Uitnodigingen

			if ( Excite.gu.cbFeature) {
				this.uncheckBox(addr);
				l = Excite.gu.cbList;
				if (l[addr] !== undefined) {
					//l[addr].hide = false;
					//this.showCheckbox(addr);
				}
			}
		},
	
	removeAddr: //delete with no trigger to list
		function(addr) { // should be private
			//if ( ! this.hasAddr(addr) ) {
				//return;
			//}
			//onsole.log("Removeaddr ", addr);
			newList = [];
			for ( i = 0 ; i< this.list.length ; i++ ) {
				if ( this.list[i] != addr )
					newList.push(this.list[i]);
			}
			this.list = newList;
			Excite.gu.setListCount(this);
			//onsole.log("New List ", this.list);
			/* dit niet doen is al removed in multiple emails....
			this.elem.val(this.list);
			this.elem.trigger(jQuery.Event('keyup', {which: 9, keyCode: 9}));
			// altijd dit bijwerken!!!!
			this.hiddenElem.val(this.list); */
			// undefined is possible for remove from Uitnodigingen

			if ( Excite.gu.cbFeature) {
				this.uncheckBox(addr);
				l = Excite.gu.cbList;
				if (l[addr] !== undefined) {
					//l[addr].hide = false;
					//this.showCheckbox(addr);
				}
			}
		},
	resetAddr:
		// called from exciteShared.js for invitationList
		// after delete from newInvitationList
		function (addr) {
			if ( $.inArray(addr, this.startList ) == -1 )
				return;
			$('#ajaxSpinner').show();
			var thisList = this;
			setTimeout( function () {
				thisList.addList([addr]);
				if ( Excite.gu.cbFeature) {
					if (Excite.gu.cbList[addr] !== undefined ) {
						uid = Excite.gu.cbList[addr].uid;
						tmp = "[id^=mappedUid" + uid + "]";
						$(tmp).prop('checked', false);
						$(tmp).hide();	
					}								
				}
				$('#ajaxSpinner').hide();
			}, 100);
		},
	addList:
		function(list) { 
			if( list.length == 0 )
				return 0;
			listName = this.elem.prop('id');
			//if ( listName != 'newInvitationList' && listName != 'doDeleteList')
				//return;
			//console.log ("Yes " + listName);
			newList = list;
			newInv = false;
			if ( listName ==  'newInvitationList' ) newInv = true;
			currentL = this.list;
			if( newInv ) {
				//list = Excite.gu.removeMultiple(list, currentL.concat(Excite.gu.memberList.getList()).concat(Excite.gu.invitationList.getList()).concat(Excite.gu.doDeleteList.getList()).concat([Excite.gu.groupOwnerEmail])) ;
				list = Excite.gu.removeMultiple(list, currentL.concat(Excite.gu.memberList.getList()).concat(Excite.gu.invitationList.getList()).concat([Excite.gu.groupOwnerEmail])) ;
				if (list.length == 0 ) return 0;
				for ( i = 0; i < list.length; i++ ) {
				//console.log("JAAAAAAAAAAAAAAA");
					if( Excite.gu.doDeleteList.hasAddr(list[i]) ) {
						Excite.gu.doDeleteList.delAddr(list[i]);
					}
				}
			}

			retVal = list.length;
			list = list.concat(currentL);
			if (this.doSort)
				list.sort();
			this.clear();
			this.list = list;
			this.elem.val(list);
			this.elem.trigger(jQuery.Event('keyup', {which: 9, keyCode: 9}));
			// altijd ook bijwerken
			this.hiddenElem.val(list);
			if ( ! this.canDelete )
				this.disableDel();
			Excite.gu.setListCount(this);
			if ( Excite.gu.cbFeature && newInv) {
				for ( i = 0; i < newList.length; i++ ) {
					addr = newList[i];
					if (Excite.gu.cbList[addr] !== undefined ) {
						uid = Excite.gu.cbList[addr].uid;
						//Excite.gu.cbList[addr].check = true;
						tmp = "[id^=mappedUid" + uid + "]";
						$(tmp).show();
						$(tmp).prop('checked', true);		
					}								
				}
					
			}
			return retVal;
		},
	clear:	function() {
				this.list = [];
				this.startCount = 0;
				this.elem.prev().html('');
				this.elem.val([]);
				this.elem.trigger(jQuery.Event('keyup', {which: 9, keyCode: 9}));
				this.hiddenElem.val([]);
				Excite.gu.setListCount(this);
				//showEmptyAddrList(this.elem);
			},
	count:	function () { return this.list.length },
	toString: function () {
				out = '';
				le = this.list.length;
				for ( i = 0 ; i < le; ++i ) {
					comma = '';
					if (i < le - 1 ) comma =',';
					out += this.list[i] + comma;
				}
				out += "\n elem: " + this.elem.val();
				out += "\n hiddenElem: " + this.hiddenElem.val();
				out += "\n startCount: " + this.startCount;
				out += "\n count: " + this.count();
				out += "\n doSort: " + this.doSort;
				out += "\n canDelete: " + this.canDelete;
				return out;
			},
	getList: function() { return this.list; },
	getElem: function() { return this.elem; },

	initCheckboxes:
		function (doInvitations) {
			cbList = Excite.gu.cbList;
			
			if ($.isEmptyObject(cbList) ) return;
			
			for ( var em in cbList ) {
				uid = cbList[em].uid;
				sel = "[id^=mappedUid" + uid + "]";
				if ( doInvitations && $.inArray(em, Excite.gu.newInvitationList.list ) != -1) {
					$(sel).prop('checked', true);
					//cbList[em].check = true;
				}
				if ($.inArray(em, this.list) != -1 ) {
					//cbList[em].hide = true;
					$(sel).hide();
				}	
			}		
		},
	disableDel: function () {
		this.elem.prev().children().find('a').each( function () {
			$(this).removeAttr('href');
			$(this).text('');
		});
	},
	uncheckBox: function (addr) {
		l = Excite.gu.cbList;
		if ( l[addr] === undefined ) return;
		tmp = "[id^=mappedUid" + l[addr].uid + "]";
		//l[addr].checked = false;
		$(tmp).prop('checked', false);
		return;
	},

	hideCheckbox: function(addr) {
		l = Excite.gu.cbList;
		if ( l[addr] === undefined ) return;
		tmp = "[id^=mappedUid" + l[addr].uid + "]";
		$(tmp).hide();
		return;
	},
	showCheckbox: function(addr) {
		l = Excite.gu.cbList;
		if ( l[addr] === undefined ) return;
		tmp = "[id^=mappedUid" + l[addr].uid + "]";
		$(tmp).show();
		return;
	},
	hasAddr: function(addr) {
		return ($.inArray(addr, this.list) != -1 );
	}
});


Excite.gu.ajaxGetLists = function (groupId) {
	try {
		if ( Excite.gu.dataTable.ajax.json().recordsTotal  > 1000 && ! $('#ajaxSpinner').is(":visible") ) {
			$('#ajaxSpinner').show();
		}
	} catch(e) {
		$('#ajaxSpinner').show();
	}
	//onsole.log("GROUP " + groupId );
	var get = $.getJSON( "/guests/getGroupMembers", {
		groupId: groupId
	});
	get.done(function( data, textStatus,jqXHR ) {
		if ( data == null ) { // TODO
			Excite.dialogAlert("<li>Bel de brandweer, er is een probleem.</li>");
			return;
		}

		// reset checkboxes in DataTable
		if ( Excite.gu.cbFeature) $('.selectGuest').show();
		
		Excite.gu.newInvitationList.clear();
		Excite.gu.doDeleteList.clear();
		
		Excite.gu.memberList.init(data['members']);

		Excite.gu.invitationList.init(data['invitations']);

		setTimeout( function () {$('#ajaxSpinner').hide();}, 400);
		//showLists('ajax');
	});
}

Excite.gu.setListCount = function (list) {
	id = list.elem.prop('id');
	switch (id) {
		case 'memberList':
			cntElem = $('#memberCnt');
			break;
		case 'invitationList':
			cntElem = $('#invitationCnt');
			break;
		case 'doDeleteList':
			cntElem = $('#deleteCnt');
			break;
		case 'newInvitationList':
			cntElem = $('#newInvitationCnt');
			break;
		default:
			return;
	}
	if (list.count() == 0 )
		cntElem.html('');
	else
		cntElem.html(list.count());
}

Excite.gu.initCheckboxes = function() {
	Excite.gu.memberList.initCheckboxes(true);
	Excite.gu.invitationList.initCheckboxes(false);
}
// tmp
function showLists(from) {
	console.log("FROM: " + from);
	console.log("MEMBERS");
	console.log(Excite.gu.memberList.toString());
	console.log("INVITES");
	console.log(Excite.gu.invitationList.toString());
	//console.log("NEW");
	//console.log(Excite.gu.newInvitationList.toString());
	//console.log("DELETE");
	//console.log(Excite.gu.doDeleteList.toString());		
}


	function showEmptyAddrList(elem) {
		elem.prev().html('<li><em><br /><br /><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Er zijn nog geen adressen.</em></li>');
	}
	function delEmptyAddrList(elem) {
		elem.prev().html('');
	}
	
	Excite.gu.removeMultiple = function(list, chkList) {
		// remove equal addresses
		newList = [];
		for ( i = 0; i<list.length ; i++) {		
			if ( $.inArray(list[i],newList) == -1) 
				if( $.inArray(list[i],chkList) == -1)
					newList.push(list[i]);
		}
		//onsole.log( " list " + list + " new list " + newList + " chkList " + chkList);
		return newList;
	}
	
	// use page change test for group change
	Excite.gu.confirmGroupChange = function() {
		return Excite.confirmPageChange;
	}
	// called by main menu Event handler in exciteShared.js.
	Excite.confirmPageChange = function() { // We bekijken alleen de lists en de input Textarea
		//showLists("CONFIRM");
		if ( Excite.gu.pendingListChanges() ) return false;
		// check content of input TextField
		if ( $('#emailInput').val().trim().length != 0 )
			return false;
		return true;
	}
	
	Excite.gu.pendingListChanges = function() {
		if (Excite.gu.newInvitationList.count() != 0 )
			return true;
		// check deleted members
		//console.log("memberList " + Excite.gu.memberList.startCount + ' ' + Excite.gu.memberList.count());
		if (Excite.gu.memberList.startCount != Excite.gu.memberList.count() )
			return true;
		// check deleted invites
		//console.log("invitationList " + Excite.gu.invitationList.startCount + ' ' + Excite.gu.invitationList.count());
		if (Excite.gu.invitationList.startCount != Excite.gu.invitationList.count() )
			return true;
		if ( Excite.gu.doDeleteList.count() > 0)
			return true;
		return false;
	}

$(document).ready(function(){

	d = Excite.datepicker($( "#datepicker" ));
	d.datepicker('setDate', new Date());

	// Group option select list
	selectList = $("select[name='groups']");
	selectList.removeAttr('onchange');

	Excite.gu.pageRefresh = true;
	Excite.qu.selectedVal = selectList.val();
	if (selectList.val() != 0 ) { // Meteen met groep starten i.p.v Kies een Group kan ook
		true; // called after draw of datatable otherwise problems with dialog on long memeber list....
		// Excite.gu.ajaxGetLists(selectList.val());
	}
	Excite.gu.groupOwnerEmail = $('#userEmailAddress').html().trim().toLowerCase();


	// EVENT handling


	// get the current value of Groep; can be used as prevVal in Change event ???
	var prevVal = 0;
	selectList.on( 'click', function(event) {
		prevVal = $("select[name='groups'] option:selected").val();
	});
	
	selectList.change( function(event) {
		selectList = $(this);
		//selectElem = $("select[name='groups'] option:selected");
		Excite.qu.selectedVal = selectedVal = selectList.val();
		//console.log( 'Group change: new ' + selectedVal + ' old ' + prevVal );
		//prevOptionElem = $("select[name='groups'] option[value=" + prevVal + ']' );

		function doGroupChange (selectedVal) {
			$('.selectGuest').prop('checked', false);
			$('.selectGuest').show();
			if (selectedVal != 0 ) {
				Excite.gu.ajaxGetLists(selectedVal);
				return;
			}
			$('#ajaxSpinner').show();
			setTimeout( function () {
				Excite.gu.memberList.clear();
				Excite.gu.newInvitationList.clear();
				Excite.gu.invitationList.clear();
				Excite.gu.doDeleteList.clear();
				$('#guestsDataTable').DataTable().ajax.reload();
				$('#ajaxSpinner').hide(); }, 100);
				
		}
		// check pending changes
		if ( Excite.gu.confirmGroupChange() )
		{
			doGroupChange(selectedVal);
			return;
			
		}
		msg = "Naar een andere groep kan, maar dan gaan de lopende wijzigingen verloren.<br>Toch naar andere group?";
		Excite.pageChangeAlert(msg , prevVal, function(ok, dialog, prevVal) {
			// async call back function;
			// so prevVal must be saved as call arg and be returned in the callback 
			dialog.dialog("close");
			if (ok) { // do the group change; use Global var voor selectedVal: is callback
				doGroupChange(Excite.qu.selectedVal);
				return;
			}
			else {
				// set option list stuff and vars back to previous
				//console.log ("nee dat mag niet" + prevVal);
				$("select[name='groups']").val(prevVal);
				selectedVal = Excite.qu.selectedVal = prevVal;
				return;
			}				
		});
	});


	// needed to prevent enter in input field to invoke a submit
	$('#exciteForm :input').each(function(){
			if ( $(this).attr('id') != 'emailInput') {
				$(this).on("keypress keydown keyup", function (e) {
					if (e.keyCode == 13 || e.which == 13 ) {
							e.preventDefault();
					}
				});
			}
	});
	// handle typed in text on input
	$('#emailInput').on('keydown', function(e) { // TODO
		if ($("select[name='groups']").val() == 0 ) {
			e.preventDefault();
			Excite.dialogAlert("Kies eerst een groep");
		}
		//32 spatie 13 Enter
		if ( 	e.keyCode == 13 || e.which == 13 ||
				e.keyCode == 32 || e.which == 32 ) {
				// need timeout; otherwise the char is not yet available
				setTimeout( function () {Excite.qu.processInput('');}, 100);
		}
	});
	
	// Handle a Paste in the email input textfield
	// cannot use jQuery here: does not support clipboard; back to the basics...
	document.getElementById('emailInput').addEventListener('paste', function (event) {
		event.preventDefault(); // suppress default Paste behavior .... and grab the Paste
		if ($("select[name='groups']").val() == 0 ) {
			Excite.dialogAlert("Kies eerst een groep");
			return;
		}
		var pastedText = '';
		try { // Some Android Web Browsers seem to have problems with this... 
			if (window.clipboardData && window.clipboardData.getData) { // IE only
				pastedText = window.clipboardData.getData('Text').trim();
			} else if (event.clipboardData && event.clipboardData.getData) { // other Web Browsers
				pastedText = event.clipboardData.getData('text/plain').trim();
			}
		} catch(err) { return; }
		if ( pastedText == '' || pastedText == undefined ) return;
		Excite.qu.processInput(pastedText);
    });
	// this button does not exist
	$('#refeshEmailInput').on( 'click', function(event) {
		event.preventDefault();
		$('#emailInput').val('');
	});
	// handling the tolist button; not in use anymore on latest version
	$("#toListB").on( 'click', function(event) {
		event.preventDefault(); // suppress Form submit !!
		if ( $("select[name='groups']").val() == 0 ) {
			errMess = "<li>Kies een groep.</li>";
			Excite.dialogAlert( errMess);
			return;
		}
		Excite.qu.processInput('');

	});

	$("#cancelB").on( 'click', function(event) {
		//onsole.log("Cancel 0 ");
		event.preventDefault();
		if ( $("select[name='groups']").val() == 0 ) {
			return;
		}
		if ( Excite.gu.newInvitationList.count() == 0 && Excite.gu.doDeleteList.count() == 0 && $('#emailInput').val().trim() == '')
			return;

		var doDeleteList = Excite.gu.doDeleteList.list;

		//onsole.log("Cancel ");
		mess = '';
		if ( Excite.gu.newInvitationList.count() != 0 )
			mess += "<input id='clearInvitations' name='clearInvitations' type='checkbox' style='width: 16pt;' >Maak Uitnodigen ongedaan<br />";
		if ( Excite.gu.doDeleteList.count() != 0 )
			mess += "<input id='clearDelete' name='clearDelete' type='checkbox' style='width: 16pt;'>Maak Verwijderen ongedaan<br />";
		if ( $('#emailInput').val().trim() != '' )
			mess += "<input id='clearEmailInput' name='clearEmailInput' type='checkbox' style='width: 16pt;'>Wis Invoer mailadressen";

		Excite.dialogAlert( mess,function() {
			$("#js-dialog-message").dialog('close');
			if ( $('#clearEmailInput').prop('checked') )
				$('#emailInput').val('');
			clearDel = false;
			clearInv = false;
			if ( $('#clearDelete').prop('checked') ) {
				Excite.gu.doDeleteList.clear();
				clearDel = true;
			}
			if ( $('#clearInvitations').prop('checked') ) {
				invList = Excite.gu.newInvitationList.list;
				clearInv = true;
				$('#ajaxSpinner').show();
				setTimeout( function() {
					Excite.gu.newInvitationList.clear();
					$('.selectGuest').prop('checked', false);
					$('#ajaxSpinner').hide();
				}, 100);
			}
			if( ! clearDel ) {
				if (clearInv) {
					$('#ajaxSpinner').show();
					setTimeout( function() {
						Excite.gu.invitationList.restart(invList);
						$('#ajaxSpinner').hide();
					}, 100);
				}
				return;
			}
			$('#ajaxSpinner').show();
			setTimeout( function() {
				list = doDeleteList;
				memDel = [];
				invDel = [];
				//console.log("Dodelete " + list);
				for ( i = 0 ; i < list.length ; i++ ) {
					em = list[i];
					if ( em[0] == '.' )
						memDel.push(em.substr(1,em.length));
					else
						invDel.push(em);		
				}
				//console.log("memDel " + memDel);
				//console.log("invdel " + invDel);
				Excite.gu.memberList.restart(memDel);
				Excite.gu.invitationList.restart(invDel);
				$('#ajaxSpinner').hide();
			}, 200);
		});
	});

	// validate Form and submit when no problems
	$("#exciteSubmitB").on( 'click', function(event) {
		// validate
		errMess = '';
		emailInput = false;
		if ( $('#emailInput').val().trim().length != 0 ) { //TODO
			emailInput = true;
		}
		if ( ! Excite.gu.pendingListChanges() ) {
			// er zijn geen wijzigingen in de lists
			// check content of input TextField
			//console.log("HIER " + $('#emailInput').val().trim().length);
			if ( emailInput ) { //TODO
				errMess += '<li>Er zijn geen wijzigingen.</li>';
			} else
				errMess = '<li>Er zijn geen wijzigingen.</li>';
			Excite.dialogAlert(errMess);
			event.preventDefault();
			return;
		}
		// test of een groep is geselecteerd
		if ( $("select[name='groups']").val() == 0 ) {
			errMess += "<li>Kies een groep.</li>";
		}
		
		//errMess = ''; // uncomment this line to test php validation
			
		if ( errMess != '') {
			Excite.dialogAlert(errMess);
			// no submit!
			event.preventDefault();
			return;
		}

		$('#ajaxSpinner').show();

		$(this).hide();
		// plain Form submit here if not prevented
	});
	
	$("#reinviteAll").on('click', function(e) {
		e.preventDefault();
		if ( Excite.gu.invitationList.count() == 0) return;

		Excite.gu.reinviteAll();
	});

});

Excite.gu.reinviteAll = function() {
		$('#ajaxSpinner').show();
		Excite.gu.tmp = $(this);
		Excite.gu.tmp.css('visibility', 'hidden');
		setTimeout( function () {
			l = Excite.gu.invitationList.getList();
			Excite.gu.invitationList.removeAllAddrs();
			Excite.gu.newInvitationList.addList(l);
			$('#ajaxSpinner').hide();
			Excite.gu.tmp.css('visibility', 'visible');
		},100);
		return;
}

Excite.gu.removeAllInvitations = function() {
	$('#ajaxSpinner').show();
	setTimeout( function () {
		l = Excite.gu.invitationList;
		Excite.gu.invitationList.removeAllAddrs();
		Excite.gu.doDeleteList.addList(l);
		$('#ajaxSpinner').hide();
	},100);	
}
Excite.qu.processInput = function(input) {
	isPaste = false;
	if ( input != '' ) isPaste = true;
	tVal = $('#emailInput').val(); // get current content of the field
	input = tVal + ' ' + input;
	if ( input.trim() == '' ) return;

	// make \n canonical separator; replaced alternatives: ; , whitespace
	s = input.replace(/(;+|,+|\s+)/gm,'\n');
	// throw away useless ascii and non-ascii
	s = s.replace(/([\x00-\x09]+|[\x0B-\x1F]+|[^\x00-\x7F]+)/gm,'');
	// remove empty lines
	s = s.replace(/\n+/gm,'\n');
	if ( s == '\n' )  { $('#emailInput').val('');return;}
	inList = [];
	inList = s.split('\n'); // create array of the \n separated input
	badList = [];
	goodList = [];
	ownerAddr = $('#userEmail').text().toLowerCase();
	for (i = 0; i < inList.length; i++ ) {
		ea = inList[i];
		if ( ea == '') continue;
		if ( Excite.isValidEmailAddress(ea)) {
			ea = ea.toLowerCase();
			//l = Excite.gu.cbList;
			//if ( ea != ownerAddr ) 	// group owner can not become a member
				goodList.push(ea);
		} else { // show bad input with @- except when too short
			if ( ea.indexOf("@") > -1 && ea.length > 3 ) {
				if ( $.inArray(ea,badList) == -1)
					badList.push(ea);
			}
		}
	}
	mess='<li>Geen nieuwe adressen gevonden</li>';;
	if ( goodList.length > 0 ) {
		cnt = Excite.gu.newInvitationList.addList(goodList);
		if ( cnt > 0 ) mess='';
		else {
			if ( !isPaste) {
				mess = " staat al in lijst";
				if ( goodList[0] == Excite.gu.groupOwnerEmail )
					mess = ': eigenaar groep kan geen lid worden';
				var re = new RegExp(goodList[0],"g");
				tmp = $('#emailInput').val().replace(re,'');
				$('#emailInput').val(tmp);
				Excite.dialogAlert(goodList[0] + mess);
				return;
			}
		
		}
	}
	if ( badList.length != 0) {
		val = badList.toString(); // is comma separated
		val = val.replace(/,/g,"\n") + '\n';
		$('#emailInput').val(val);
		mess += '<li>Onjuiste adressen staan nog in de invoerlijst</li>';
		if( isPaste )
			Excite.dialogAlert(mess);
		return;
		/*
		// \n prepend needed?
		length = tVal.length;
		if ( length > 0 && tVal.charAt(length - 1) != '\n'.charAt(0) ) {
			//no \n at bottom of the field; prepend!
			val = '\n' + val;
		}
		*/		
	}
	// when arriving here: it was all garbage
	if ( isPaste && mess != '')
		Excite.dialogAlert(mess);
	$('#emailInput').val('');
}

Excite.gu.bindSelectGuest = function() {
	$('.selectGuest').on('click', function(e) {
		if ($("select[name='groups']").val() == 0 ) {
			Excite.dialogAlert("Kies eerst een groep");
			$(this).prop('checked', false);
			return;
		}

		email = $(this).attr('email');
		uid = $(this).attr('uid');
		display_email = $(this).attr('display_email');
		if (display_email == 0 ) {
				email += '/' + uid;
		}
		//console.log("hiero " + display_email + ' ' + uid);
		if ( ! $(this).prop('checked') ) { // new status is unchecked
			// remove form inv list
			list = Excite.gu.newInvitationList;
			elem = Excite.gu.newInvitationList.elem.prev(); // this is the ul
			elem.find('span').each( function () {
					if ( $(this).html() == email ) {
						//the X span
						$(this).prev().trigger('click');
						list.removeAddr(email);
						list.hiddenElem.val(list.list);
					}

			});
			//Excite.gu.cbList[email].check = false;
			return;
		}
		// new status is checked
		//Excite.gu.cbList[email].check = true;
		retval = Excite.gu.newInvitationList.addList([email.trim()]);
		// is already in the list; cannot add: uncheck; in cbFeature version this box is hidden
		// and cannote be clicked
		if(retval == 0 ) { $(this).prop('checked', false); }
	});
}

Excite.gu.addListsToForm = function () {
	// add multiple-emails component to hidden input fields
	//console.log("newInvitationval : " + $('#hiddenNewInvitationInput').val());
	$('#hiddenNewInvitationInput').multiple_emails("Basic");	
	newInvitationList = $('.multiple_emails-input');
	newInvitationList.prop('id', 'newInvitationList');
	// hide the generated input field of the component!
	newInvitationList.hide();
	Excite.gu.newInvitationList = new Excite.gu.List(newInvitationList,$('#hiddenNewInvitationInput'), { canDelete: true, doSort: true } );
	Excite.gu.newInvitationList.init([]);

	$('#hiddenMemberInput').multiple_emails("Basic");
	$('.multiple_emails-input').each( function(){
		if ( $(this).prop('id') != 'newInvitationList' )
			$(this).prop('id', 'memberList');
	});
	memberList = $('#memberList');
	// hide it
	memberList.hide();
	Excite.gu.memberList = new Excite.gu.List(memberList,$('#hiddenMemberInput'), { doSort: true });
	
	$('#hiddenInvitationInput').multiple_emails("Basic");
	$('.multiple_emails-input').each( function(){
		if ( $(this).prop('id') != 'memberList' && $(this).prop('id') != 'newInvitationList')
			$(this).prop('id', 'invitationList');
	});
	invitationList = $('#invitationList');
	// hide it
	invitationList.hide();
	Excite.gu.invitationList = new Excite.gu.List(invitationList, $('#hiddenInvitationInput'), { doSort: true });
	
	$('#hiddenDoDeleteInput').multiple_emails("Basic");
	$('.multiple_emails-input').each( function(){
		if ( $(this).prop('id') != 'memberList' && $(this).prop('id') != 'newInvitationList' && $(this).prop('id') != 'invitationList')
			$(this).prop('id', 'doDeleteList');
	});
	doDeleteList = $('#doDeleteList');
	// hide it
	doDeleteList.hide();
	
	Excite.gu.doDeleteList = new Excite.gu.List(doDeleteList, $('#hiddenDoDeleteInput'), {canDelete: true, doSort: true});
	Excite.gu.doDeleteList.init([]);
}
/*** use jQuery tooltip **/
  $(function() {
    $( ".hoverRow" ).tooltip({
      show: {
        effect: "slideDown",
        delay: 300
      }
    });
  });

