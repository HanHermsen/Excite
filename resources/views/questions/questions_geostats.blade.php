

<style>
	#chartNL path {
		stroke-width: 1; /* control the countries borders width */
		stroke: white; /* choose a color for the border */
	}
	#openMap {
		position: absolute;
		height: 660px; /*660*/
		width: 670px;
		left: 250px;
		/*top: 120px;*/
	}
	#chartNL {
		position: absolute;
		width: 300px;
		/*left: -100px;*/
	}
	#switchButtons {
		position: absolute;
		left: 125px;
		top: 500px;
		width: 150px;
	}
	.swButton {
		float:left;
		margin-left:5px;
		width: 90px;
	}
	button.swButton:disabled {
		background-color: #FF7020;
	}
	h3 {
		font-size: 16pt;
		margin-bottom: 10px;
	}
	#mapContainer {
		height: 700px;
	}
	
</style>
<script>
	Excite.qu.mapDataArr = {};
	
	// alle arrays zijn beschikbaar; wordt server side als object literal in deze code gegenereerd
	// een test met ajax json GET was geslaagd, maar levert geen verbetering op; slechts wat extra delay na klik op Kaart
	
	{!!$statsData->getAllData()!!}
	
	Excite.qu.provMap(); // show prov map
	Excite.qu.showMap(); // show big map
	$('#extraInfo').html("Info:<br />" + Excite.qu.extraInfo);
	$('#geoUnshared').html("Woonplaats onbekend: " + Excite.qu.geoUnshared);

	// Button handlers; we keep this code here
	$('#munB').attr('disabled', 'disabled'); // showMap starts with Gemeenten
	Excite.qu.mapSwitcher = function (to) {
		// check for (layer == current layer) not needed here, since that button is disabled
		Excite.qu.openMap.removeLayer(Excite.qu.layer);
		Excite.qu.layer = Excite.qu.layers[to] = Excite.qu.newLayer(Excite.qu.mapDataArr[to],Excite.qu.layers[to]);
		$(".swButton").removeAttr('disabled');
		$('#' + to + 'B').attr('disabled', 'disabled');
	}
	Excite.qu.munBordersOn = false;
	Excite.qu.munBordersToggle = function () {
		if (Excite.qu.munBordersOn) {
			Excite.qu.openMap.removeLayer(Excite.qu.munBorders);
			Excite.qu.provBorders.bringToBack();
			Excite.qu.munBordersOn = false;
		} else {
			Excite.qu.munBorders.addTo(Excite.qu.openMap);
			Excite.qu.munBorders.bringToBack();
			Excite.qu.provBorders.bringToBack();
			Excite.qu.munBordersOn=true;
		}
	}

</script>
<button class='swButton' onclick="Excite.qu.ajaxGetStats({{$reqData['questionId']}},'{!!$reqData['argQuestionText']!!}','{{$reqData['backStatsType']}}')">Terug</button><br />
<h3 id='chartsTitle'>{{$reqData['questionText']}} - spreiding antwoorden -</h3>
<span id='geoUnshared'></span><br />
<div id='mapContainer'>
<div id="chartNL"></div>
<div id='openMap'></div>
<br /><br />
<div id='switchButtons'>
	<div id='extraInfo'></div><br /></br /><br />
	<button id='munB' class='swButton' onclick='Excite.qu.mapSwitcher("mun")'>Gemeenten</button><br />
	<button id='locB' class='swButton' onclick='Excite.qu.mapSwitcher("loc")'>Plaatsen</button><br />
	<!-- <button id='zipB' class='swButton' onclick='Excite.qu.mapSwitcher("zip")'>Postcode</button><br /> -->
	<button id='zip4B' class='swButton' onclick='Excite.qu.mapSwitcher("zip4")'>Postcode</button><br />
	<br /><br />
	<button id='munBordersB' onclick='Excite.qu.munBordersToggle()'>Gemeentegrenzen Aan/Uit</button><br /><br />
</div>
</div>


		