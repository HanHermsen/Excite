$(document).ready(function(){
	$('#lat').prop('type','hidden');
	$('#lng').prop('type','hidden');
	$('#hiddenZipCode').prop('type','hidden');

	Excite.ex.bindSubmit();
	lat = $('#lat').val();
	lng = $('#lng').val();
	selectedRegion = 0;
	expressGroups = [];
	$("#exciteForm :input").attr("disabled", false);
	
	
	$('#addGroup').on('click', function(e) {
		e.preventDefault();
						
		selectedRegion = 0;
		$('#addGroupContainer').show();
		map.invalidateSize();		
		$("#groupName").val("");
		$("#labelName").val("");
		$("#zipCode").val("");
		$("#areaSelector").val("5");
		$("#periodSelector").val("3");
		$('#price').text("90");
		$("#hiddenZipCode").val("");
		$("#lat").val("undefined");
		$("#lng").val("0");
		$("#region1").prop('checked', false);
		$("#region2").prop('checked', false);
		$("#zipCode").attr("disabled", true);
		$("#zipOkB").attr("disabled", true);
		$("#areaSelector").attr("disabled", true);
		
		$("#exciteForm :input").attr("disabled", true);
		
		// show info
		$('#columnExpressInfo').hide();
		$('#columnExpressInfoOverlay').show();
	});
	
	$('#cancelNewGroupBtn').on('click', function(e) {
		e.preventDefault();
		$("#exciteForm :input").attr("disabled", false);
		$('#addGroupContainer').hide();
		Excite.ex.resetMap();
		
		// hide info
		$('#columnExpressInfo').show();
		$('#columnExpressInfoOverlay').hide();
	});
	$('#saveNewGroupBtn').on('click', function(e) {
		e.preventDefault();
		
		mess = '';
		lastMess = '';
		if ( $('#groupName').val().trim() == "") {
			mess = "<li>Groepsnaam</li>";
		}
		if ( $('#labelName').val().trim() == "") {
			mess += "<li>Labelnaam</li>";
		}
		if (selectedRegion == 0) {
			mess += "<li>Nederland of bereik op postcode</li>";
		}
		else if(selectedRegion == 2 && $('#hiddenZipCode').val() == "") {
			if($('#zipCode').val().trim() != "") {
				lastMess += "<li>Klik op 'Toon op Kaart' om uw regio op de kaart weer te geven.</li>";
			}
			else {
				mess += "<li>Kaartlocatie</li>";
			}			
		}

		if ( mess != '') {
			mess = "Vereiste velden:<br>" + mess;
		}
		if ( lastMess != '') {
			mess += '<br><br>' + lastMess;
		}
		
		if ( mess != '') {
			Excite.dialogAlert(mess);
			return;
		}
		
		var result = {};
		$.each($('#expressForm').serializeArray(), function( index, value ) {
		  result[value.name] = value.value;
		});
		result['periodHuman'] = $('select#periodSelector option:selected').text();
		result['price'] = $('#price').text();
		if(selectedRegion == 1) {
			// override area if Netherlands was selected (as requested by Han)
			result['area'] = 0;
		}
		expressGroups.push( result );
		//console.log(expressGroups);
		
		Excite.ex.generateOrderLines();
		
		$("#exciteForm :input").attr("disabled", false);
		$('#addGroupContainer').hide();
		Excite.ex.resetMap();
		
		$("#addGroup").attr("disabled", true);
		$("#addGroup").css("opacity", 0.2);
		$("#addGroup").css("cursor", "default");
		
		// hide info
		$('#columnExpressInfo').show();
		$('#columnExpressInfoOverlay').hide();
	});
	
	$("#region1, #region2").change(function () {
        if ($("#region1").prop("checked") == true) {
            // Netherlands
            selectedRegion = 1;
            $("#zipCode").attr("disabled", true);
			$("#zipOkB").attr("disabled", true);
			$("#areaSelector").attr("disabled", true);
			
			// zoom and select map			
			Excite.ex.openMap.setView(Excite.ex.provCenter['NL-UT'], 7);
			newLatLng = Excite.ex.latLng;
			if ( newLatLng == null ) {
				//newLatLng = L.latLng(52.09061, 5.12143);
				Excite.ex.colorProvB.addTo(Excite.ex.openMap);
				$('#population').val( Number(17000000).toLocaleString());
				Excite.ex.setPrice();
				return;
			}
			Excite.ex.newLayer(newLatLng, 0);
			Excite.ex.setPrice();
			Excite.ex.prevPopulation = $('#hiddenPopulationVal').val();
			$('#population').val( Number(17000000).toLocaleString()); //XXXX			
        }
        else {
            // Zipcode
            var removeMapOverlay = false;
            if(selectedRegion == 1) {
	            removeMapOverlay = true;
            }            
            selectedRegion = 2;
            $("#zipCode").attr("disabled", false);
			$("#zipOkB").attr("disabled", false);
			$("#areaSelector").attr("disabled", false);
			
			// zoom and select map
			if (removeMapOverlay) {
				Excite.ex.openMap.removeLayer(Excite.ex.colorProvB);
				Excite.ex.openMap.off('click', Excite.ex.onMapClick);
				Excite.ex.openMap.on('click', Excite.ex.onMapClick);
				$('#hiddenPopulationVal').val(Excite.ex.prevPopulation);
				$('#population').val( Number(Excite.ex.prevPopulation).toLocaleString());
			}
			Excite.ex.setPrice();
			if ( Excite.ex.latLng == null ) 
				return; // no center selected yet
			
			val = $('#areaSelector').val();
			Excite.ex.newLayer(Excite.ex.latLng, val);
			Excite.ex.ajaxGetZipCode( Excite.ex.latLng);
        }
    });
});

Excite.ex.generateOrderLines = function () {
	$( "#orderLines" ).empty();
	
	if(expressGroups.length == 0) {
		$("#orderLines").html("Maak eerst een groep aan.");
	}
	var totalPrice = 0;
	
	$.each( expressGroups, function( i, value ){
		//alert( "Index #" + i + ": " + l );
		
		var $div = $("<div>", {class: "groupBlock"});
		//$div.click(function(){ /* ... */ });		
		var $divInner = $("<div>", {class: "groupBlockBar"});
		$div.append($divInner);
		var $pInner1 = $("<p>").html(value["GroupName"] + ", " + value["LabelName"]);
		$divInner.append($pInner1);
		var $pInner2 = $("<p>", {class: "alignRight"});
		$divInner.append($pInner2);
		var $deleteBtn = $("<a>", {href: "#"}).html("<img src=\"/images/icon-close-round.svg\" height=\"18\">");
		$pInner2.append($deleteBtn);
		$deleteBtn.click(function(e){
			e.preventDefault();
			expressGroups.splice(i, 1);
			Excite.ex.generateOrderLines();
			$("#addGroup").attr("disabled", false);
			$("#addGroup").css("opacity", 1.0);
			$("#addGroup").css("cursor", "pointer");
		});
		var $pInner3 = $("<p>", {class: "alignRight", style: "margin-right:0px !important;"}).html("&euro; " + value["price"] + ",-");
		$divInner.append($pInner3);
		var $pInner4 = $("<div>", {style: "clear:both"});
		$divInner.append($pInner4);
		
		var $p1 = $("<p>", {class: "alignLeft"}).html("Looptijd:<br>" + value["periodHuman"]);
		$div.append($p1);
		if(value["region"] == "custom") {
			var $p2 = $("<p>", {class: "alignRight", style: "text-align:right"}).html("postcode: " + value["hiddenZipCode"] + "<br>" + value["area"] + " km");
			$div.append($p2);
		}
		else {
			var $p2 = $("<p>", {class: "alignRight", style: "text-align:right"}).html("Nederland");
			$div.append($p2);
		}
				
		$("#orderLines").append($div);
		
		totalPrice += parseInt(value["price"]);
	});
	
	$('#priceTotal').text( totalPrice );
}

Excite.ex.setPrice = function () {
	if(selectedRegion == 1) {
		area = 0;
	}
	else {
		area = $('#areaSelector').val();
	}
	period = $('#periodSelector').val();
	if ( period == 12 ) {
	//console.log ( Excite.ex.priceList[12] + ' ' + Excite.ex.priceList[12][area]);
		$('#price').text( Excite.ex.priceList[12][area]);
	}
	else {
	//console.log ( Excite.ex.priceList[1] + ' ' + Excite.ex.priceList[1][area]);
		$('#price').text(Excite.ex.priceList[1][area] * period);
	}
}

Excite.ex.onMapClick = function (e) {
	// disable map clicks if user didn't select zipcode/map click
	if (selectedRegion == 2) {
		$('#zipCode').val('');
		//console.log( $('#areaSelector').val() + ' ' + lat + ',' + lng);
		latLng = e.latlng;
		//Excite.ex.openMap.off('click', Excite.ex.onMapClick);
		Excite.ex.ajaxGetZipCode( latLng );
	}
}

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

		// express subscription needs at least 1 group
		if ($("input[name='subscriptionType']").val() == "express" && expressGroups.length == 0) {
			lastMess += "<li>Voeg definities voor een groep toe</li>";
		}		
		if ( ! Excite.ex.isEgo ) {
			if ($("input[name='company']").val().trim() == '' ) {
				mess += '<li>Bedrijfsnaam</li>';
			}
			kvk = $("input[name='kvk']").val().trim();
			if (kvk == '' ) {
				mess += '<li>KvK-nummer</li>';
			} else {
				if ( ! Excite.isValidKvk(kvk) )
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
				mess += '<li>Telefoonnummer (zakelijk)</li>';
			} else {
				if ( ! Excite.isValidPhoneNr(phone) )
					lastMess += "<li>Telefoonnummer (zakelijk) niet in orde</li>";			
			}
			email = $('#emailAdress').val().trim();
			if (email == '' ) {
				mess += '<li>E-mailadres (zakelijk)</li>';
			} else {
				if ( ! Excite.isValidEmailAddress(email) )
					lastMess += "<li>E-mailadres (zakelijk) niet in orde</li>";
			}
			if ($("input[name='displayname']").val().trim() == '' ) {
				mess += '<li>Weergavenaam</li>';
			}
			pw = $('#password1').val().trim();
			if ( pw == '' ) {
				mess += '<li>Wachtwoord</li>';
			} else { 
				if( pw.length < 6 )
					lastMess += '<li>Wachtwoord moet minstens 6 tekens lang zijn.</li>';
					
			}
			if ($('#password2').val().trim() == '' ) {
				mess += '<li>Herhaal Wachtwoord</li>';
			}
			//console.log("pass: "+$("input[name='password']").val().trim() + " passconf:" + $("input[name='password_confirmation']").val().trim());
			if ($('#password1').val().trim() != $('#password2').val().trim()) {
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
		url = '../express';
	} else url = '../ego';
	
	// combine form values with express group
	var postValues;	
	if ($("input[name='subscriptionType']").val() == "express") {
		var result = expressGroups[0];
		$.each($('#exciteForm').serializeArray(), function( index, value ) {
		  result[value.name] = value.value;
		});
		postValues = $.param( result );
	}
	else {
		postValues = $('#exciteForm').serialize()
	}
	//console.log("postValues: "+postValues);
	//return;
		
	var post = $.post(url, postValues);
	post.done(function( data, textStatus,jqXHR ) {
		if ( data == null ) {
			Excite.dialogAlert("<li>Er is iets mis gegaan, sorry voor het ongemak.</li>");
			return;
		}
		Excite.dialogAlert(data.msg, function() {
			if ( ! Excite.ex.isEgo ) { 
				//console.log('hier');
				if ( data.error) return;
				// poets formulier nog ff schoon
				Excite.ex.resetForm();
				location.href = '../ego';
			}
		});
		
		/*if ( Excite.ex.isEgo ) {
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
		}*/
		
	});
	return;
}