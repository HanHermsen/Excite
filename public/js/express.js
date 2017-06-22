/*** DataTable stuff */

$(document).ready(function(){
Excite.ex.isEgo = (window.location.href.indexOf("/ego") > -1);

	if( ! Excite.ex.isEgo ) return;
		
		GROUP_NAME = 0;
		LABEL_NAME = 1;
		ZIPCODE = 2;
		RADIUS = 3;
		PERIOD = 4;
		START_DATE = 5;
		END_DATE = 6;

	var table = Excite.ex.table = $('#contractDataTable').DataTable( {
		"ajax": "/ego/getContractTableData",
		responsive: false,
		serverSide:true,
		processing:true,
		sDom: 'ftip',
		"scrollY":        "370px",
        "scrollCollapse": true,
        "paging":         false,
		order: [[ GROUP_NAME, "asc" ]],
		"createdRow": function ( row, data, index ) {
				if ( data[RADIUS] == 0 ) {
					$('td', row).eq(RADIUS).html('NL');
				}
			},
		"language": {
				"url": "/js/DataTables/Dutch.json"

			},

		"columnDefs": [ {
				// Group name 
				"targets": GROUP_NAME
			},
			{
				// Label name
				"targets": LABEL_NAME,
				"searchable": false
			},
			{
				// ZIPCODE
				"targets": ZIPCODE,
				"searchable": false
			},
			
			{
				// RADIUS
				"targets": RADIUS,
				"searchable": false
			},

			{
				// PERIOD
				"targets": PERIOD,
				//"visible": false,
				// this is needed for invisibility when responsive is true
				//"className": "never", 
				"searchable": false
			},

			{
				// START_DATE
				"targets": START_DATE,
				"searchable": false,
				sortable: false,
			},
			{
				// END_DATE
				"targets": END_DATE,
				//"visible": false,
				// this is needed for invisibility when responsive is true
				//"className": "never",                         
				//"searchable": false
			}
					
		 ],
	});


});







Excite.ex.provCenter = {
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
};

Excite.ex.showMap = function(color) {
	map = Excite.ex.openMap = L.map('openMap').setView(Excite.ex.provCenter['NL-UT'], 7); // Utrecht in het centrum
	// waar komen de map tiles (plaatjes) vandaan en give credits (it's free and not Google!)
	// gebruik https ivm prod; anders warnings in console van ten minste IE over mixed http en https content
	L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
		attribution: '&copy; <a href="http://openstreetmap.org" target="_blank">OpenStreetMap</a>',
		maxZoom: 20, // was 18
	}).addTo(map);

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

		Excite.ex.provBorders = Excite.ex.defaultProvB = L.geoJson(data, {
			style: function (feature) {
					return {
						//color:'#323940',
						color:'black',
						fill: false,
						//fillColor: 'pink',
						//fillOpacity: 0.1,
						weight: 1.3,
						//dashArray: '1,1',
					};
			},
			//onEachFeature: function (feature, layer) { // NO not this
					//layer.bindPopup('<strong>' + feature.properties.Provincienaam + '</strong>');
			//},
		}).addTo(map);
		Excite.ex.provBorders.bringToBack();

	});
		
	map.on('click', Excite.ex.onMapClick);
}
Excite.ex.zoom = function (latLng, radKm) {
		if ( $('#areaSelector').val() == 0 || radKm == 0.1) return;
		zoom = 10;
		if (radKm == 25 ) zoom = 9;
		if (radKm == 10 ) zoom = 10;
		if (radKm == 5 ) zoom = 11;
		Excite.ex.openMap.setView(latLng, zoom, {animate: true});
}
Excite.ex.newLayer = function (latLng, radius) {

	layer = Excite.ex.layer;
	//console.log('Newlayer ' + latLng + ' rad ' + radius + ' layer ' + layer);
	if ( radius == 0) {
		//console.log("NEDERLAND");
		Excite.ex.colorProvB.addTo(Excite.ex.openMap);
		$('#population').val( Number(Excite.ex.populNl).toLocaleString());
		$('#hiddenPopulationVal').val(Excite.ex.populNl);
		Excite.ex.setPrice();
	}
	radKm = radius;
	radius = radius * 1000;
	if ( Excite.ex.latLng != null && latLng.equals(Excite.ex.latLng) ) {
		Excite.ex.circle.setRadius(radius);
		Excite.ex.setPrice();
		if ( radKm > 0.1 ) {
			Excite.ex.zoom(latLng, radKm);
		}
		return;
	}
	
	if ( layer != null ) { // kan weg
	//console.log("REMOVE ");
		Excite.ex.openMap.removeLayer(layer);
	}
	var circles = new L.layerGroup();

	Excite.ex.latLng = latLng;
	lat = latLng.lat;
	lng = latLng.lng;
	$('#lat').val(lat);
	$('#lng').val(lng);

	Excite.ex.layer = circles;
	var c = Excite.ex.circle = L.circle(latLng, radius, {
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
	circles.addTo(Excite.ex.openMap);
	map.off('click', Excite.ex.onMapClick);
	map.on('click', Excite.ex.onMapClick);
	Excite.ex.zoom(latLng, radKm);
	return circles;		
}
Excite.ex.onMapClick = function (e) {
	$('#zipCode').val('');
	//console.log( $('#areaSelector').val() + ' ' + lat + ',' + lng);
	latLng = e.latlng;
	//Excite.ex.openMap.off('click', Excite.ex.onMapClick);
	Excite.ex.ajaxGetZipCode( latLng );
}
Excite.ex.getZipCallBack= function(latLng, data) {
	if (data.zipCode != '' ) {
		Excite.ex.newLayer(latLng, $('#areaSelector').val());
		Excite.ex.latLng = latLng;
		//Excite.ex.setPrice();
		$('#hiddenZipCode').val(data.zipCode);
		$('#hiddenPopulationVal').val(data.population);
		$('#hiddenRange').val(data.range);
		$('#population').val( Number(data.population).toLocaleString());
	} else Excite.dialogAlert("Ongeschikt centrum van het gebied");
}
Excite.ex.ajaxGetZipCode = function(latLng) {
	url = "/ego/getZipCode";
	if ( ! Excite.ex.isEgo) url = "/porder/getZipCode";
	var get = $.getJSON( url , {
		lat: latLng.lat,
		lng: latLng.lng,
		area: $('#areaSelector').val(),
	} );
	get.done(function( data, textStatus,jqXHR ) {
		if ( data == null ) { // TODO
			Excite.dialogAlert("<li>Bel de brandweer, er is een probleem.</li>");
			return;
		}
		Excite.ex.getZipCallBack(latLng, data);
	});
}
Excite.ex.ajaxGetPrice = function () {
	if ( Excite.ex.orderType == Excite.userTypes.EXCITE ) return;
	url = "/ego/getPrice";
	if ( ! Excite.ex.isEgo) url = "/porder/getPrice";
	var get = $.getJSON( url , {
		lat: $('#lat').val(),
		lng: $('#lng').val(),
		area: $('#areaSelector').val(),
		period: $('#periodSelector').val(),
	} );
	get.done(function( data, textStatus,jqXHR ) {
		if ( data == null ) { // TODO
			Excite.dialogAlert("<li>Bel de brandweer, er is een probleem.</li>");
			return;
		}
		Excite.ex.priceList = data.priceList;
		Excite.ex.populNl = data.populationNl;
		Excite.ex.setPrice();
	});
}

Excite.ex.setPrice = function () {
	area = $('#areaSelector').val();
	period = $('#periodSelector').val();
	if ( period == 12 ) {
		$('#price').val( Excite.ex.priceList[12][area]);
	}
	else {
		$('#price').val(Excite.ex.priceList[1][area] * period);
	}
}

Excite.ex.ajaxGetGroupNames = function () {
	url = "/ego/getGroupNames";
	var get = $.getJSON( url);
	get.done(function( data, textStatus,jqXHR ) {
		if ( data == null ) { // TODO
			Excite.dialogAlert("<li>Bel de brandweer, er is een probleem.</li>");
			return;
		}
		//console.log('DATA');
		//console.log(data);
		Excite.ex.groupList = data;
	});
}

Excite.ex.ajaxGetLatLng = function (zip) {
	url = "/ego/getLatLng";
	if ( ! Excite.ex.isEgo) url = "/porder/getLatLng";
	var get = $.getJSON( url , {
		zipCode: zip,
	} );
	get.done(function( data, textStatus,jqXHR ) {
		if ( data == null ) { // TODO
			Excite.dialogAlert("<li>Bel de brandweer, er is een probleem.</li>");
			return;
		}
		if ( ! data.zipFound ) {

			Excite.dialogAlert("Postcode" + $('#zipCode').val() + " niet gevonden; centrum niet verplaatst.");
			$('#zipCode').val('');
			return;
		}
		latLng = L.latLng(data.lat, data.lng);
		Excite.ex.ajaxGetZipCode( latLng );
		//$('#lat').val(data.lat);
		//$('#lng').val(data.lng);
		//latLng = L.latLng(data.lat, data.lng);
		
		//Excite.ex.newLayer(latLng, $('#areaSelector').val());
			
	});
}
Excite.ex.resetForm = function() {
	//$(":input").val(''); kan nog niet in combinatie met Montana
	$("#exciteForm :input").attr("disabled", false);
	$('#lat').val('undefined');
	$('#lng').val(0);
	$('#price').val('');
	$('#areaSelector').val(5);
	$('#periodSelector').val(1);
	$("input[name='GroupName']").val('');
	$("input[name='LabelName']").val('');
	$('#zipCode').val('');
	$('#population').val('');
	$('#hiddenPopulationVal').val('');
	$('#hiddenZipCode').val('');

	if ( Excite.ex.isEgo) {
		$('#ConfirmSubmitTxt').css('visibility', 'hidden');
		$("#resetB").css('visibility', 'visible');
		$("#exciteSubmitB").html("Bevestig");
		Excite.ex.table.ajax.reload();
	}
	if ( Excite.ex.isEgo || Excite.ex.orderType == Excite.orderTypes.EXPRESS ) {
		Excite.ex.resetMap();
		Excite.ex.setPrice();
	}
		

}
Excite.ex.resetMap = function() {
	if ( Excite.ex.layer != null )
		Excite.ex.openMap.removeLayer(Excite.ex.layer);
	Excite.ex.openMap.removeLayer(Excite.ex.colorProvB);
	Excite.ex.layer = Excite.ex.latLng = null;
	Excite.ex.openMap.on('click', Excite.ex.onMapClick);
	Excite.ex.openMap.setView(Excite.ex.provCenter['NL-UT'], 7);
}
$(document).ready(function(){
	Excite.ex.orderType = null;
	if( ! Excite.ex.isEgo ) {
		Excite.ex.orderType = Excite.orderTypes.EXPRESS;
		if ( ! $('#hiddenZipCode').length ) {
			console.log("JaJa"); // no Map just bind submit
			Excite.ex.orderType = Excite.orderTypes.EXCITE;
			Excite.ex.bindSubmit();
			return;
		}
		$('#lat').prop('type','text');
		$('#lng').prop('type','text');
		$('#hiddenZipCode').prop('type','text');
	}	

	Excite.ex.bindSubmit();
	lat = $('#lat').val();
	lng = $('#lng').val();

	$('#ConfirmSubmitTxt').css('visibility', 'hidden');
	Excite.ex.latLng = null;
	Excite.ex.layer = null;
	Excite.ex.showMap(false);
	Excite.ex.prevArea = 5;
	Excite.ex.prevPopulation = 0;

	Excite.ex.ajaxGetPrice();
	if ( Excite.ex.isEgo )
		Excite.ex.ajaxGetGroupNames();
	else
		Excite.ex.groupList = [];


	$('#areaSelector').on('click', function(e) {
		Excite.ex.prevArea = $(this).val();
	});
	
	$('#areaSelector').change(function(e) {
		
		if ( Excite.ex.prevArea == 0 ) {
			Excite.ex.openMap.removeLayer(Excite.ex.colorProvB);
			Excite.ex.openMap.off('click', Excite.ex.onMapClick);
			Excite.ex.openMap.on('click', Excite.ex.onMapClick);
			$('#hiddenPopulationVal').val(Excite.ex.prevPopulation);
			$('#population').val( Number(Excite.ex.prevPopulation).toLocaleString());
		}

		val = $(this).val();
		if ( val == 0 ) { // heel Nederland
			Excite.ex.openMap.setView(Excite.ex.provCenter['NL-UT'], 7);
			//Excite.ex.colorProvB.addTo(Excite.ex.openMap);

			newLatLng = Excite.ex.latLng;
			if ( newLatLng == null ) {
				//newLatLng = L.latLng(52.09061, 5.12143);
				Excite.ex.colorProvB.addTo(Excite.ex.openMap);
				$('#population').val( Number(Excite.ex.populNl).toLocaleString());
				$('#hiddenPopulationVal').val(Excite.ex.populNl);
				Excite.ex.setPrice();
				return;
			}
			Excite.ex.newLayer(newLatLng, 0);
			Excite.ex.setPrice();
			Excite.ex.prevPopulation = $('#hiddenPopulationVal').val();
			$('#population').val( Number(Excite.ex.populNl).toLocaleString()); //XXXX
			$('#hiddenPopulationVal').val(Excite.ex.populNl);
			return;
		}
		Excite.ex.setPrice();
		if ( Excite.ex.latLng == null ) return; // no center selected yet
		Excite.ex.newLayer(Excite.ex.latLng, val);
		Excite.ex.ajaxGetZipCode( Excite.ex.latLng);

	});
	
	$('#periodSelector').change(function(e) {
			Excite.ex.setPrice();
	});

	
	$("#resetB").on('click', function(e) {
		e.preventDefault();
		Excite.ex.resetForm();
		e.preventDefault();
	});

	$('#zipOkB').on('click', function(e) {
		e.preventDefault();
		input = $('#zipCode').val();
		if ( zip = Excite.chkZipCode(input) ) {
			Excite.ex.ajaxGetLatLng(zip);
			$('#zipCode').val('');
		}
		else {
			Excite.dialogAlert("Postcode niet in orde");
		}
			
	});
	
	// prevent Enter to invoke zipOkB action except for zipCode input field
	$("#exciteForm :input").keypress(function (e) {
		if ($(this).attr('id') == 'zipCode') return;
		if(   e.which === 13) {
		  e.preventDefault();
		}
	});
/* Portal fix for login IE; */	
	$('#logininputs').find('input').keypress(function (e) {
		form = $('#login').find('form');
		if( e.which === 13) {
		  form.submit();
		}
	});
});

Excite.ex.bindResetForm2Submit = function () {
	$('#exciteSubmitB').unbind('click');
	$('#exciteSubmitB').on ('click', function(e) {
		event.preventDefault();
		Excite.ex.resetForm();
		Excite.ex.bindSubmit();
	});
}
Excite.ex.bindSubmit = function () {
	$('#exciteSubmitB').unbind('click');
	$('#exciteSubmitB').on ('click', function(e) {

		mess = '';
		lastMess = '';

		if ( Excite.ex.isEgo || Excite.ex.orderType == Excite.orderTypes.EXPRESS) {
			if ( $('#areaSelector').val() != 0 && Excite.ex.latLng == null ) {
				lastMess += "<li>Een gebied kiezen</li>";
			}
			elem = $("#exciteForm input[name='GroupName']");
			if (elem.length) {
				if ( elem.val().trim() == '' ) {
					mess += '<li>Vul groepsnaam in.</li>';
				} else {
					chkName = $("input[name='GroupName']").val().trim().replace(/  +/g, ' ').toLowerCase();
					if ( $.inArray(chkName, Excite.ex.groupList) > -1 ) {
						lastMess += '<li>Groepsnaam bestaat al.</li>';
					}
				}
			}
			elem = $("input[name='LabelName']");
			console.log("TESTMY " + elem.val());
			if ( elem.length && elem.val().trim() == '' ) {
				mess += '<li>Vul labelnaam in.</li>';
			}
		} 
		if ( ! Excite.ex.isEgo ) {
			elm = $("input[name='company']");
			if (elm.val().trim() == '' ) {
				mess += '<li>Bedrijfsnaam</li>';				
			}
			kvk = $("input[name='kvk']").val().trim();
			if (kvk == '' ) {
				mess += '<li>KvK-nummer</li>';
			} else {
				if ( ! Excite.isValidKvk(kvk,$("input[name='kvk']")) )
					lastMess += "<li>Kvk nummer niet in orde</li>";
			}
			if ($("input[name='firstname']").val().trim() == '' ) {
				mess += '<li>Voornaam</li>';
			}
			if ($("input[name='lastname']").val().trim() == '' ) {
				mess += '<li>Achternaam</li>';
			}
			phone = $("input[name='phone']").val().trim();
			if ( phone == '' ) {
				mess += '<li>Telefoonnummer</li>';
			} else {
				if ( ! Excite.isValidPhoneNr(phone) )
					lastMess += "<li>Telefoonnummer niet in orde</li>";			
			}
			email = $("#exciteForm input[name='email']").val().trim();
			if (email == '' ) {
				mess += '<li>Gebruikersnaam/ E-mailadres</li>';
			} else {
				if ( ! Excite.isValidEmailAddress(email) )
					lastMess += "<li>Gebruikersnaam/Email address niet in orde</li>";
			}
			if ($("input[name='displayname']").val().trim() == '' ) {
				mess += '<li>Display name</li>';
			}
			pw = $("#exciteForm input[name='password']").val().trim();
			if ( pw == '' ) {
				mess += '<li>Wachtwoord</li>';
			} else { 
				if( pw.length < 6 )
					lastMess += '<li>Wachtwoord moet minstens 6 tekens lang zijn.</li>';
					
			}
			if ($("input[name='password_confirmation']").val().trim() == '' ) {
				mess += '<li>Herhaal Wachtwoord</li>';
			}
			if ($("input[name='password']").val().trim() != $("input[name='password_confirmation']").val().trim()) {
				lastMess += '<li>Wachtwoorden zijn niet gelijk</li>';
			}	
		}
		if ( mess != '' )
			mess = 'Niet ingevulde verplichte velden:<br/>' + mess;
		if (lastMess != '') {
			if ( mess != '' )
				mess += '<br />En verder:<br />' + lastMess;
			else
				mess = lastMess;
		}
		//mess = '';
		if ( mess != '') {
			Excite.dialogAlert(mess);
			e.preventDefault();
			return;
		}
		e.preventDefault();
		Excite.ex.ajaxPostForm();
		
	});
}
Excite.ex.ajaxPostForm = function() {
	if( ! Excite.ex.isEgo ) {
		/* uit document ready van voor de ajax post van het form
			$('#ConfirmSubmitTxt').css('visibility', 'visible');
			$("#resetB").css('visibility', 'hidden');
			Excite.ex.PrevArea = $('#areaSelector').val();
			$("#exciteForm :input").attr("disabled", true);
			$("#exciteSubmitB").attr("disabled", false);
			$("#exciteSubmitB").html("Ok");
			if ( $('#areaSelector').val() == 0) {
				Excite.ex.showMap(true);
			} else {
				Excite.ex.showMap(false);
				newLatLng = L.latLng(lat, lng);
				//console.log("new " + newLatLng + ' rad ' + $('#areaSelector').val());
				Excite.ex.newLayer(newLatLng, $('#areaSelector').val());	
			}
			Excite.ex.openMap.off('click', Excite.ex.onMapClick);
		*/
		url = '/porder'; //werkt alleen in local dev env, niet op test
		url = '/express';
	} else url = '/ego';
	var post = $.post(url, $('#exciteForm').serialize());
	post.done(function( data, textStatus,jqXHR ) {
		if ( data == null ) { // TODO
			Excite.dialogAlert("<li>Bel de brandweer, er is een probleem.</li>");
			return;
		}
		Excite.dialogAlert(data.msg, function() {
			if ( ! Excite.ex.isEgo ) { 
				console.log('hieer');
				if ( data.error) return;
				// poets formulier nog ff schoon
				Excite.ex.resetForm();
				location.href = 'ego';
			}
		});
		if( data.error) return;
		if ( Excite.ex.isEgo ) {
			$('#ConfirmSubmitTxt').css('visibility', 'visible');
			$("#resetB").css('visibility', 'hidden');
			Excite.ex.PrevArea = $('#areaSelector').val();
			$("#exciteForm :input").attr("disabled", true);
			$("#exciteSubmitB").attr("disabled", false);
			$("#exciteSubmitB").html("Ok");
			Excite.ex.openMap.off('click', Excite.ex.onMapClick);
			Excite.ex.bindResetForm2Submit();
			Excite.ex.ajaxGetGroupNames();
			return;
		}
		
	});
	return;
}