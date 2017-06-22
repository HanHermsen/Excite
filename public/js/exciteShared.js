// Excite namespace; Global things become Excite props; this is a modest way to do it: no private functions
var EXCITE_NAV = navigator.userAgent.toLowerCase();
//console.log(EXCITE_NAV);
var Excite = {
	qu: {}, // questions sub namespace
	gu: {}, // guests sub namespace
	gr: {}, // groups sub namespace
	ex: {}, // express sub namespace
	y: {}, // yixow sub namespace
	// example of global property; Global function Excite properties are declared below...
	isIE: (EXCITE_NAV.indexOf("trident") > -1), // check IE true or false; used for statsWindow
	isSafari: (EXCITE_NAV.indexOf("safari") > -1 && EXCITE_NAV.indexOf("chrome") < 0),
	isChrome: (EXCITE_NAV.indexOf("chrome") > -1),
	isFirefox: (EXCITE_NAV.indexOf("firefox") > -1),
	miniStats: false, // made true when ministats needed
	userTypes: { EXTRA: 0,LIGHT: 0, EXPRESS: 1, EXCITE: 2},
	orderTypes: { EXPRESS: 1, EXCITE: 2 },	
};
// create a JS 'Class' that can have a constructor function in a construct property
Excite.Class = function(methods) { 
    var exciteClass = function() {    
        this.construct.apply(this, arguments);          
    };     
    for (var property in methods) { 
       exciteClass.prototype[property] = methods[property];
    }          
    if (!exciteClass.prototype.construct) exciteClass.prototype.construct = function(){};          
    return exciteClass;    
};


Excite.fileSizeCheck = function(inputFile) { // should be private
	try {
		if ( inputFile[0].files[0].size > 10000000 ) // 10 MB limit
			return false;
		return true;
	} catch (e) {
		// HTML 5 file size standard not supported
		// Older versions of: IE, iOS and Android Web Browsers
		// you must accept all sizes...
		return true;
	}
}

Excite.uploadCheck = function(elem) { // this is a private function
	fName = elem.val();
	if( fName.length == 0 ) return '';
	
	fileMess=''
	arr = fName.split('.');
	switch (arr[arr.length - 1].toLowerCase()) {
	case 'jpg':
	case 'jpeg':
	case 'png':
	case 'gif':
		break;
	default:
		fileMess = '<li>Alleen .png, .jpg of .gif image toegestaan.</li>';					
	}
	if ( ! Excite.fileSizeCheck(elem)) {
		fileMess += '<li>File is te groot; max 10 MB</li>';
	}
	/*
	if ( fileMess != '' ) {
		elem.val(''); // IE triggers on value change
		elem.trigger('change');	// for other browsers	
	} */
	return fileMess;
}


Excite.imageUploadEventHandler = function(event,fileElem, viewElem, callCnt, defaultImageHTML,imageDateUrlElem, maxTargetSize) {
	// reset to '' of name within script is change too...
	if ( fileElem.val().trim() == '' ) { event.preventDefault(); return; }

	window.console.log("imageUploadEventHandler " + callCnt);

	mess = Excite.uploadCheck(fileElem);
	if ( mess != '' ){
		// something is wrong: bad filename extension or file too large
		Excite.dialogAlert(mess);
		if ( defaultImageHTML == undefined || defaultImageHTML == null || defaultImageHTML == '')
			viewElem.html('<img>');
		else
			viewElem.html(defaultImageHTML);
		event.preventDefault();
		fileElem.val(''); // IE triggers on value change
		fileElem.trigger('change');	// for other browsers
		imageDateUrlElem.val('');		
		return false;
	}
	try {
		var reader = new FileReader();
	} catch (e) {
		// no filereader support; f.i Windows Safari; other older Browsers on any platform;
		// global Browser FileReader coverage jan 2016: 89.97% http://caniuse.com/#feat=filereader
		Excite.dialogAlert("Voorvertoning plaatje kan niet. Te oude versie van gebruikte Web browser.");
		return true;
	}
	// put the image in the preview div in the callback of the onload event of the FileReader
	var img = new Image();
	var imageError = false;
	img.onerror = function(e) {
		Excite.dialogAlert("Dit is geen plaatje!");
		imageError = true;
		if ( defaultImageHTML == undefined || defaultImageHTML == null || defaultImageHTML == '')
			viewElem.html('<img>');
		else
			viewElem.html(defaultImageHTML);
		fileElem.val(''); // it's not possible to get back to previous... security!
		fileElem.trigger('change');
		imageDateUrlElem.val('');

		return;
	}
	reader.onload = function(e){
		img.src = this.result;
		setTimeout(handleImg, 100); // call after 100 milliscnds; prevents retries on img.width == 0 .....
		function handleImg () {
			if( imageError) return;
			// without the timeout above, recall was needed sometimes!! why? image not fully loaded yet...
			// IE/Chrome Win needed infrequently at most 1 retry; Firefox Win more frequently 1 retry too;
			// Safari on Mac needed more retries;
			// maybe this part has become useless now...; stays to solve unknown as well as bizar conditions
			if( img.width == 0 && callCnt <= 10 ) { // retry; never happens anymore?
				Excite.imageUploadEventHandler(event,fileElem, viewElem,++callCnt, defaultImageHTML, maxTargetSize);
				return;
			}
			if ( img.width == 0 ){ // too many tries; never happens?
				Excite.dialogAlert("Sorry: 10 x geprobeerd. Voorvertoning gaat niet lukken. Het plaatje staat wel klaar om te gaan gebruiken!");
				return;
			}
			// process the image
			var maxTargetWidth = 0;
			var maxTargetHeight = 0;
			var maxDisplayWidth = 0;
			var maxDisplayHeight = 0;
			maxDisplayWidth = viewElem.css('width').replace(/px/, '');
			maxDisplayHeight = viewElem.css('height').replace(/px/, '');
			if ( maxDisplayWidth == undefined || maxDisplayWidth == 0 ) maxDisplayWidth = 161;
			if ( maxDisplayHeight == undefined || maxDisplayHeight == 0) maxDisplayHeight = 101;
			if ( maxTargetSize == undefined ) {
				maxTargetWidth = maxDisplayWidth;
				maxTargetHeight = maxDisplayHeight;
			}
			else {
				maxTargetWidth = maxTargetSize.width;
				maxTargetHeight = maxTargetSize.height;
			}
			
			srcWidth = img.width;
			srcHeight = img.height;
			//console.log('src w, h ' + srcWidth + ' ' + srcHeight );			
			// need HTML 5 canvas to draw on
			canvas = document.createElement("canvas");
			canvas.webkitImageSmoothingEnabled = false;
			canvas.msImageSmoothingEnabled = false;
			canvas.imageSmoothingEnabled = false;
	
			// image scaling magic
			if ( srcWidth > maxTargetWidth || srcHeight > maxTargetHeight ) {
				var hRatio = maxTargetWidth / srcWidth    ;
				var vRatio = maxTargetHeight / srcHeight  ;
				var ratio  = Math.min ( hRatio, vRatio );
				newWidth = Math.floor(srcWidth*ratio);
				newHeight = Math.floor(srcHeight*ratio);
			} else { // keep original; no upscale is done
				newHeight = srcHeight;
				newWidth = srcWidth;
			}
			canvas.width = newWidth;
			canvas.height = newHeight;
			var ctx = canvas.getContext("2d");	
			// draw with on the fly scaling
			ctx.drawImage(img, 0,0, srcWidth, srcHeight, 0,0,newWidth, newHeight);
			// get result and show it
			img.src = canvas.toDataURL('image/png'); // argument gives preferred target MIME-type
													  // muste be png for image transparency
			viewElem.html(img);
			imageDateUrlElem.val(img.src); //XXXX
			// remove file from upload input field; so it will not be uploaded
			fileElem.val(''); // it's not possible to get back to previous... security!
			fileElem.trigger('change');
			//console.log("Result size :" + newWidth + 'x' + newHeight);
			hRatio = maxDisplayWidth / newWidth    ;
			vRatio = maxDisplayHeight / newHeight  ;
			var ratio  = Math.min ( hRatio, vRatio );
			displayWidth = Math.floor(newWidth*ratio);
			displayHeight = Math.floor(newHeight*ratio);
			imageElem = viewElem.children('img');
			imageElem.prop('height', displayHeight);
			imageElem.prop('width', displayWidth);
		}
	}
	// read the image
	try {
		reader.readAsDataURL(event.target.files[0]);
	} catch(e) { console.log("Help: imageUploadEventHandler"); return false; } // never reached sofar..
	return !imageError;
}

Excite.handlePageChangeEvent = function(event) { // should be private
	if ( typeof Excite.confirmPageChange == 'function' ) {
		// call to function in page dedicated script; if no function: page change permitted
		if (Excite.confirmPageChange() ) {
			// just do it
			return;
		} else {
			// ask user
			Excite.pageChangeAlert(
			"Er zijn gegevens ingevuld, die verloren gaan bij een overgang naar een andere pagina.<br /><br /><strong>Toch naar die andere pagina?</strong>",
			event.data.url);
			event.preventDefault();
			return;
		}
	}
}

Excite.pageChangeAlert = function( msg , url, callMeBackToo) {
	$( "#js-dialog-message" ).dialog({
		modal: true,
		buttons: {
					Nee: function() {
							// need callback function;  async response!
							callback(false, $(this));
						},
					Ja: function() {
							callback(true, $(this));
						},

				},
		autoOpen: false
	});
	$( "#js-dialog-message" ).children().html(msg);
	$( "#js-dialog-message" ).dialog('open');
	
	function callback(pageChangeOk, dialog) {
			if (typeof callMeBackToo == 'function' ) { // guests.js only
				callMeBackToo(pageChangeOk, dialog, url);
				return;
			}
			dialog.dialog( "close" );
			if (pageChangeOk) {
				// do the page change
					location.href = url;
				return;
			}
	}
}

Excite.statResponseBar = function(perc) { // vrij domme functie
	if (perc == -1 )
		return '';
	 if (perc == 0 ) {
		 gray = "||||||||||||||||||||"
		 color = "";
		//return '';
	 } else { // for 81 - 100%
		 color = "||||||||||||||||||||";
		 gray = "";
	}
	 if (perc <= 20 && perc > 0) {
		color = '||||';
		gray = "||||||||||||||||";
	}
	 if ( perc > 20 && perc <=40 ) {
		color = "||||||||";
		gray = "||||||||||||";
	}
	 if ( perc > 40 && perc <=60 ) {
		color = "||||||||||||";
		gray = "||||||||";
	}
	 if ( perc >60 && perc <= 80 ) {
		color = "||||||||||||||||";
		gray = "||||";
	}
	return "<span style='color:darkgreen'><strong>" + color + "</strong></span>" + "<span style='color:#CCC'><strong>" + gray + "</strong></span>";
}

Excite.isValidPhoneNr = function(phone) {
	var tmp = phone.trim().replace(/[\s-\.]/g, '');
	if ( isNaN(tmp) )
		return false;
	//console.log('tttt ', tmp);
	return true;
}

Excite.isValidKvk = function(kvk,elem) {
	// digits only
	var isnum = /^\d+$/.test(kvk);
	if ( ! isnum ) {
		return false;
	}
	p = parseInt(kvk); // removes leading zeros
	if ( isNaN(p) || p < 1000000 || p> 99999999 ) // min 7 max 8 cijfers
		return false;
	val = '' + p; 
	if ( val.length == 7 ) {
		val = '0' + val;
	}	
	if ( elem !== undefined ) {
			elem.val(val);
	}
	//return true; // no kvk table on test_yixow
	return Excite.ajaxIsValidKvk(val);
}

Excite.ajaxIsValidKvk = function(kvk) {
	var retVal = false;
	$.ajax({
		type: "GET",
		url: "/porder/isValidKvk",
		data: { kvk: kvk },
		async: false,
		success : function(data) {
			retVal = data.result;
		}
	});
	return retVal;
}

// experimental
Excite.isValidEmailAddress2 = function (ea) {
	l = ea.split('@');
	if( l.length != 2 ) return false;
	
	loc = l[0];
	dom = l[1];
	v = Excite.ajaxIsValidDomain(dom);
	if ( v != dom ) return true;
	return false;
	
}

Excite.ajaxIsValidDomain = function(dom) {
	var retVal = false;
	$.ajax({
		type: "GET",
		url: "/porder/isValidDomain",
		data: { dom: dom },
		async: false,
		success : function(data) {
			retVal = data.result;
		}
	});
	return retVal;
}
Excite.isValidEmailAddress = function (emailAddress) {
    var pattern = new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);
    return pattern.test(emailAddress);
}

Excite.chkZipCode = function (zipCode) {
	var tmp = zipCode.trim().replace(/\s/g, '');
	if ( tmp == '' ) return '';
	if ( tmp.length < 4 ) return '';
	digits = tmp.substr(0,4);
	/*
	letters = '';
	if ( tmp.length > 5 )
		letters = tmp.substr(4 ,2);
	*/
	p = parseInt(digits);
	if ( isNaN(p) || p < 1000 )
		return '';
	/*
	p = parseInt(letters);
	if ( ! isNaN(p) )
		letters = '';
	*/
	return digits;
}

Excite.dialogAlert = function (msg , callBack) {
		locationId = 'js-dialog-message';
		d = $( "#" + locationId );
		d.dialog({
			modal: true,
			buttons: {
					Ok: function() {
						$( this ).dialog( "close" );
					}
			},
			close: function( event, ui ) {
						//$( this ).dialog( "close" );
						if (typeof callBack == 'function' ) { // for stats refresh after dialog
							callBack();
						}
				},
			autoOpen: false
		});
		d.children().html(msg);
		d.dialog('open');
}

Excite.yesNoDialog = function (msg , callBack) {
	$( "#js-dialog-message" ).dialog({
		modal: true,
		buttons: {
					Nee: function() {
							// need callback function;  async response!
							callBack(false, $(this));
						},
					Ja: function() {
							callBack(true, $(this));
						},

				},
		autoOpen: false
	});
	$( "#js-dialog-message" ).children().html(msg);
	$( "#js-dialog-message" ).dialog('open');
}

Excite.choiceDialog = function (msg , buttons, width) {
	if ( width == undefined ) width = 300;
	$( "#js-dialog-message" ).dialog({
		modal: true,
		buttons: buttons,
		autoOpen: false,
		width: width
	});
	$( "#js-dialog-message" ).children().html(msg);
	$( "#js-dialog-message" ).dialog('open');
}

Excite.ajaxOrderExciteTrial = function () {
	var get = $.get( "/ego/exciteTrial", {} );
	get.done(function( data, textStatus,jqXHR ) {
		if ( data == null ) { // TODO
			Excite.dialogAlert("<li>Bel de brandweer, er is een probleem.</li>");
			return;
		}
		if ( data.error ) { // error
			Excite.dialogAlert(data.msg, function callback() {
				//location.reload();
				location.href = '/groups/';
			});
			return;
		}
		Excite.dialogAlert(data.msg, function callback() {
				location.href = '/groups/';
		});
	});
}
// Shared Main Menu Event Handler
$(document).ready(function(){
	$('.sidebar-nav').find('a').each(function(){
			if ( $(this).hasClass('SubMenuActive') ) {
				$(this).on('click', function(event) { event.preventDefault()});
				return;
			}
			
			if( $(this).hasClass('Disabled') ) {
				prop = $(this).prop('href').match(/groups/);
				if ( prop == null ) {
					$(this).on('click', function(event) { // Gasten
						event.preventDefault();
						if ( Excite.userType == Excite.userTypes.LIGHT ) {
							Excite.dialogAlert("Gasten kunnen worden uitgenodigd en beheerd met een professioneel account.<br>We informeren je graag over onze zakelijke dienstverlening.");
							/* Guests may be invited and managed in a professional account. We like to inform you about our business services.
							*/
						} else {
							Excite.dialogAlert("Gasten kunnen worden uitgenodigd en beheerd met een Excite abonnement.<br>We informeren je graag hierover.");
						}
					});
					return;
				} else { //Groepen
					$(this).on('click', function(event) {
						event.preventDefault();
						Excite.dialogAlert("Groepen kunnen worden aangemaakt en beheerd met een professioneel account.<br>We informeren je graag over onze zakelijke dienstverlening.");
						/*
						Groups can be created and managed in a professional account. We like to inform you about our business services.
						*/
					});
					return;
				}
				return;
			}
			//if ( $(this).hasClass('SubMenu')) {
				$(this).on('click', {url: $(this).prop('href')} , function(event) {
					Excite.handlePageChangeEvent(event);
				});
				return;
		//	} else { // logo
		//		$(this).on('click', function(event) { event.preventDefault()});
		//	}
	});
	/*
	$('#inviteLink').on('click', {url: $('#inviteLink').prop('href')} , function(event) {
		Excite.handlePageChangeEvent(event);
	});
	*/
	
	$('#exciteTrial').on('click' , function(event) {
		event.preventDefault();
		Excite.yesNoDialog("Je wilt Yixow eXcite gratis een maand op proef gebruiken. Bij het proefabonnement is een gratis adviesgesprek inbegrepen om de waarde van 'Question Marketing' nog beter te benutten. We nemen contact met je op om te vragen wanneer je daar gebruik van wilt maken. Gaan doen?", function(val, dialog) {
			if (val) {
				dialog.dialog('close');
				Excite.ajaxOrderExciteTrial();
				
			} else dialog.dialog('close');
			
		});
	});
});

// return elem; attach datepicker with std props when not yet available
Excite.datepicker = function(elem) {
	if ( elem === undefined )
		return null;
	if ( elem.hasClass('hasDatepicker') ) {
		// picker already attached
		//console.log("already there " + elem.prop('id'));
		return elem;
	}
	//console.log("attach " + elem.prop('id'));
	elem.datepicker({
	  showOn: "button",
	  buttonImage: "images/calendar.gif",
	  buttonImageOnly: true,
	  minDate: 0,
	  dateFormat: 'yy-mm-dd',
	});
	return elem;
}

Excite.isBeforeToday = function (date) {
	if ( date == null ) return false;
	today = new Date();
	if ( date < today ) return true
	// you need extra test on equality
	//if ( date >= today || (date.getFullYear() == today.getFullYear() && date.getDate() == today.getDate() && date.getMonth() == today.getMonth() )) return false;
	return false;
}

Excite.lang = 'nl';
Excite.i18n = function(s) {
if ( typeof Excite.i18nTable === 'undefined' ) return s;
	if ( typeof Excite.i18nTable[s] === 'undefined' )
		return s + '**';
	if ( typeof Excite.i18nTable[s][Excite.lang] === 'undefined' ) {
		if ( Excite.lang == 'nl' )
			return s;
		else
			return s + '*';
	}
	return Excite.i18nTable[s][Excite.lang];
}
/*
$.getScript("js/i18nTable.js", function(){

   //alert("Script loaded but not necessarily executed.");

}); */

// create Shared dialog message for php validation Error or Ok responses; must open automatically;
// see also master blade; div #dialog-message only shows up on Page when some server side Dialog must be displayed
$(document).ready(function() {
	$( "#dialog-message" ).dialog({
	  modal: true,
	  buttons: {
		Ok: function() {
		  $( this ).dialog( "close" );
		}
	  },
	});
});


