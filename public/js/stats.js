
Excite.qu.statsWindow = {
		disabledButton: '',
};
Excite.qu.googleChartsLoaded = false;
Excite.qu.newStats = false; // set true to test; piechart: flickers and bad value position
// set statistics window props
 $(function() {
	$( "#statsWindow" ).dialog({
		modal: true,
		/*height: 860, //860*/
		width: 950, //970
		/*
		buttons: {
			Ok: function() {
			  $( this ).dialog( "close" );
			}
		},
		*/
		autoOpen: false,
	});
		$( "#sliderContaine" ).dialog({
		modal: false,
		/*height: 860, //860*/
		width: 290, //970
		/*
		buttons: {
			Ok: function() {
			  $( this ).dialog( "close" );
			}
		},
		*/
		autoOpen: true,
	});
		$( "#miniStatsWindow" ).dialog({
			modal: true,			
			width: 970,
			autoOpen: false,
		});
});
$(document).ready(function(){
	//$('.charts-tooltip').html('');
});

Excite.qu.ajaxGetMiniStats = function (questionId, questionText,showEmbedded, options) {
	// altTemplate: never used....
	//console.log( questionId + ' ' + questionText);
	//Excite.miniStats = true; // is dit nodig??
	viewType = 'Mini';
	if ( typeof options === 'undefined' )
		options = {emailAnswer: 0};
	if (typeof Excite.qu.altMiniStatsUrl === 'undefined')
		url = "/questions/getMiniStatsHTML";
	else {
		url = Excite.qu.altMiniStatsUrl;
		delete Excite.qu.altMiniStatsUrl;
	}
	
	var get = $.get( url, {
		questionId: questionId,
		questionText: questionText,
		statsType: null,
		viewType: viewType,
		//withAnswerOption: withAnswerOption,
		options: options,
	} );
	get.done(function( data, textStatus,jqXHR ) {
		if ( data == null ) { // TODO
			Excite.dialogAlert("<li>Bel de brandweer, er is een probleem.</li>");
			return;
		}
		if ( typeof showEmbedded === 'undefined' || ! showEmbedded ) {
			parent.$('#miniStatsWindow').html(data);
			//$('.charts-tooltip').html('');
			parent.$('#miniStatsWindow').dialog('open');
		} else {
			console.log("EMBEDDED");
			if ( showEmbedded )
				$('#miniStatsWindow').html(data);
		}
	});
}


Excite.qu.ajaxGetStats = function (questionId, questionText, statsType, viewType, backStatsType, altTemplate) {
	// console.log( 'ajaxGetStats ' + questionId + ' ' + questionText + ' ' + statsType);

	var get = $.get( "/questions/getStatsHTML", {
		questionId: questionId,
		questionText: questionText,
		statsType: statsType,
		viewType: viewType,
		backStatsType: backStatsType,
		altTemplate: altTemplate,
		newStatsReq: Excite.qu.newStatsReq,
	} );
	get.done(function( data, textStatus,jqXHR ) {
		if ( data == null ) { // TODO
			Excite.dialogAlert("<li>Bel de brandweer, er is een probleem.</li>");
			return;
		}
		/*
		if (typeof altTemplate !== 'undefined' ) {
			var x=window.open();
			x.document.open();
			x.document.write(data);
			return;
		} */
		$('#statsWindow').html(data);
		$('#statsWindow').dialog('open');
	});
}

Excite.qu.googleCharts = function(altTemplate) {
	//console.log("ALT1 " + altTemplate);
	Excite.qu.altTemplate = altTemplate;
	// yes you need a timeout; otherwise......; 300 milli seems to be a good interval
	if ( Excite.qu.newStats ) {
		if ( ! Excite.qu.googleChartsLoaded ) {
			console.log("Load!");
			setTimeout(function() {
				//google.charts.load('current', {packages: ['corechart', 'geochart']});
				google.charts.load('current', {packages: ['corechart', 'geochart']});
				google.charts.setOnLoadCallback(function(){drawPie(Excite.qu.altTemplate)});
				Excite.qu.googleChartsLoaded = true;
			},300);
		} else {
			console.log("hier");
			drawPie(Excite.qu.altTemplate);
		}
	} else {	// original was 1; 1.30 is only ok for miniStats; gives problems with other charts
		if ( Excite.miniStats ) { // use this for preventing tooltip flicker
			setTimeout(function(){google.load('visualization', '1.30', {'callback':drawPie, 'packages':['corechart']})}, 300);
		} else {
			setTimeout(function(){google.load('visualization', '1', {'callback':function(){drawPie(Excite.qu.altTemplate)}, 'packages':['corechart','geochart']})}, 300);
			/* if ( ! Excite.qu.googleChartsLoaded ) {
				//console.log("Load!");
				setTimeout(function() {
						//google.charts.load('current', {packages: ['corechart', 'geochart']});
						google.charts.load('current', {packages: ['corechart', 'geochart']});
						google.charts.setOnLoadCallback(drawPie);
						//google.charts.setOnLoadCallback(drawCharts);
						Excite.qu.googleChartsLoaded = true;
					},300);
			} else {
				//console.log("hier");
				drawPie();
			} */
		}
	}

	function drawPie (altTemplate) {

		if ( typeof altTemplate !== 'undefined' ) {
			drawAltBarChart();
			return;
		}
		 // handle no answers
		data = Excite.qu.pieData;
		total = 0;
		answ = '';
		//if ( Excite.miniStats ) {
			for (i = 1; i< data.length; i++ ) {
				total += data[i][1];
				answ += data[i][0] + ' 0%<br />';
				if(total > 0 ) break;
			}
			if ( total == 0 ) { /*
				text = "<h1>Geen antwoorden</h1>" + answ;
				$('#pieChart').html(text);
				if ( ! Excite.miniStats )
					drawCharts();
			   return; */
			   //Excite.miniStats = true;
			   data.push(['Er is nog geen antwoord gegeven', 1]);
			}
		//}

		pWidth = 400;
		pHeight = 250;
		chartArea = {left:20,top:20,bottom:0,width:"85%"}
		fontSz = 11;
		if ( Excite.miniStats ) {
			pWidth = 350;
			pHeight = 220;
			chartArea = {  width: "100%", height: "90%" };
		} else {
			if ( typeof altTemplate !== 'undefined' ) {
				pWidth = 900;
				pHeight = 700;
				chartArea = {  width: "100%", height: "100%" };
				fontSz = 30;				
			}
		}
		// the pie
		var pieDataTable = google.visualization.arrayToDataTable( Excite.qu.pieData );
		var pieOptions = {//'title':'Alle antwoorden',
				'sliceVisibilityThreshold':0, // vraag met 0 _wel_ naar de legenda!
				'width':pWidth,
				'height':pHeight,
				is3D: true,
				'chartArea': chartArea,
				legend: {maxLines: 3}, // doet dit wat?
				fontSize: fontSz,
			};
		// percentage only in small populations
		if ( Excite.qu.allGivenAnswerCount < Excite.qu.absLimit )				
			pieOptions.tooltip = { text: 'percentage'};
		if ( Excite.qu.newStatsReq )
			var pieChart = Excite.qu.pieChart = new google.visualization.PieChart(document.getElementById('pieChart'));
		else
			var pieChart = Excite.qu.pieChart = new google.visualization.PieChart(document.getElementById('pieChart'));
		if ( Excite.miniStats) {
			//pieOptions.legend = {position: 'top', maxLines: 3};
			//pieOptions.legend = {position: 'labeled', maxLines: 3};
		}
		pieChart.draw(pieDataTable,pieOptions);

		/*
		if ( typeof altTemplate === 'undefined' && !Excite.miniStats)
			drawCharts();
		else
			drawAltBarChart();
		*/
		if ( !Excite.miniStats)
			drawCharts();
	}
	function drawAltBarChart () {
		// the bar data
		data = Excite.qu.pieData;
		total = Excite.qu.allGivenAnswerCount;
		for (i = 0; i< data.length; i++ ) {
			if ( i == 0 ){
				//data[i][2] = "{role: 'tooltip', p: { html: true}}";
				data[i][2] = {role: 'annotation'};
				data[i][3] = {role: 'style'};
				data[i][4] = 101; // tmp for the sort
			} else {
				if ( data[i][1] == 0 )
					perc = 0;
				else {
					perc = Math.round( (data[i][1]/total)*100 );
					console.log(perc + ' ' + data[i][1] + ' ' + total);
				}
				data[i][2] = perc + '% ' + data[i][0];
				data[i][3] = "color: #DD2080; stroke-width: '180px'";
				data[i][4] = perc; // tmp for the sort
			}
		}
		Excite.qu.pieData = data.sort( function compare(a,b) {
			if( a[4] < b[4] ) return 1;
			if( a[4] > b[4] ) return -1;
			return 0;
		});
		for ( i = 0; i < Excite.qu.pieData.length ; i++)
			Excite.qu.pieData[i].pop(); // remove sort element; google charts cannnot handle it

		//console.log(Excite.qu.pieData);
		var barDataTable = google.visualization.arrayToDataTable( Excite.qu.pieData );

		var barOptions = {'title':'',
                     'width':900,
                     'height':1000,
					'legend': {'position': 'none'},
					'fontSize': 24,
					 bar: {groupWidth: "80%"},
					'vAxis': {textPosition: 'none'},
					/* 'vAxis': {viewWindowMode: 'maximized'}, */
					annotations: {
						style: 'point',
						textStyle: {
						  /* fontName: 'Times-Roman', */
						  fontSize: 20,
						  width: 1000,
						  bold: true,
						  /* italic: true, */
						  // The color of the text.
						  color: 'black',
						  // The color of the text outline.
						  /* auraColor: '#d799ae', */
						  // The transparency of the text.
						  /* opacity: 0.8 */
						},
					},
					chartArea: {
						left: 10,
						top: 30,
						width: '90%',
						/* backgroundColor: 'pink', */
					},
					
					};
		// percentage only in small populations
		var barChart = new google.visualization.BarChart(document.getElementById('pieChart'));
		barChart.draw(barDataTable,barOptions);
		return;
		barOptions.annotations.style = 'line';
		barOptions.annotations.stem = {length: '1000px', color: 'blue'};
		//barOptions.annotationWidth = 80;
		var barChart2 = new google.visualization.BarChart(document.getElementById('altBarChart'));
		barChart2.draw(barDataTable,barOptions);
	}
	function drawCharts() {
	//console.log("TEST " + Excite.miniStats );
		var answerBarDataTable = google.visualization.arrayToDataTable( Excite.qu.answerBarData );
		var answerBarOptions = {//'title':'Antwoorden met scores per {{$reqData['statsType']}}',
			 'width':500,
			 'height':300,
			 'isStacked': 'percent',
			 bar: {groupWidth: "50%"},
			 tooltip: {isHtml: false},
			 'legend': {'position': 'top' , maxLines: 20 },
			 'chartArea':{top:20},
			 fontSize: 11,
			 annotations: {
						alwaysOutside: true,
						style: 'point',
						textStyle: {
						  /* fontName: 'Times-Roman', */
						  fontSize: 11,
						  width: 1000,
						  //bold: true,
						  color: 'black',
						},

				},

			 //vAxis : {textPosition: 'in'},
			};
		var answerBarChart = new google.visualization.BarChart(document.getElementById('answerBarChart'));
		//var answerBarChart = new google.charts.Bar(document.getElementById('answerBarChart'));
		answerBarChart.draw(answerBarDataTable,answerBarOptions);

		// the category > answer chart;
		var categoryBarDataTable = google.visualization.arrayToDataTable( Excite.qu.categoryBarData );
		showBar2 = true;
		if ( typeof Excite.qu.categoryBarData2 === 'undefined' ){
			Excite.qu.categoryBarData2 = [];
			showBar2 = false;
		}
		var categoryBarDataTable2 = google.visualization.arrayToDataTable( Excite.qu.categoryBarData2 );
		delete Excite.qu.categoryBarData2;
		var categoryBarOptions = {//'title':'{{$reqData['statsType']}} met scores per antwoord',
			 'width':500,
			 'height':600,
			 isStacked: true,
			 tooltip: {isHtml: true},
			 'legend': {'position': 'none'},
			 'chartArea': {top:10, /*left:50*/},
			 fontSize: 11,
			 annotations: {
						alwaysOutside: true,
						style: 'point',
						textStyle: {
						  /* fontName: 'Times-Roman', */
						  fontSize: 11,
						  width: 1000,
						  //bold: true,
						  color: 'black',
						},

				},
			 //vAxis : {textPosition: 'in'},
			};
		var categoryBarOptions2 = {//'title':"Profiel niet gedeeld",
			 'width':400,
			 'height':100,
			 isStacked: true,
			 tooltip: {isHtml: true},
			 'legend': {'position': 'none'},
			 'chartArea': {top:10, width: '55%'},
			 fontSize: 11,
			 annotations: {
						alwaysOutside: true,
						style: 'point',
						textStyle: {
						  /* fontName: 'Times-Roman', */
						  fontSize: 11,
						  width: 1000,
						  //bold: true,
						  color: 'black',
						},
			},
			 //vAxis : {textPosition: 'in'},
		};
		// this is a hack!! horizontal axis with absolute numbers must be invisible in small populations
		// no option to remove it from the chart, so .. make it unreadable
		if ( Excite.qu.allGivenAnswerCount < Excite.qu.absLimit ) {
			categoryBarOptions.hAxis = { textStyle: { color: 'white', fontName: 'Acme', fontSize: 0, bold:false, italic:false } };
			categoryBarOptions2.hAxis = { textStyle: { color: 'white', fontName: 'Acme', fontSize: 0, bold:false, italic:false } };
		}
		var categoryBarChart = new google.visualization.BarChart(document.getElementById('categoryBarChart'));
		var categoryBarChart2 = new google.visualization.BarChart(document.getElementById('catBarChart2'));
		categoryBarChart.draw(categoryBarDataTable,categoryBarOptions);
		if( showBar2 )
			categoryBarChart2.draw(categoryBarDataTable2,categoryBarOptions2);
	}
	buttonProfileInit(Excite.qu.userProfile);
	if ( Excite.qu.statsWindow.disabledButton == '')
		Excite.qu.statsWindow.disabledButton = 'Geluk';
	$('#' + Excite.qu.statsWindow.disabledButton ).attr('disabled', 'disabled');


	function buttonProfileInit(up) {
		//Excite.userContact = (up['contact'] == null) ? 0: up['contact'];
		if (  ! up['any'] ) { // no profile keys found
			// mark all buttons
			elem = $('.statsButton');
			elem.attr('class', elem.attr('class') + ' forbiddenButton');
			return;
		}
		for (name in up) {
			switch (name) {
				case 'any':
				case 'user': // userId
					continue;
				case 'contact': //contactId not in use anymore
					continue;
			}
			if ( ! up[name] && Excite.userType == Excite.userTypes.LIGHT) {
				elem = $('#' + name);
				//elem.attr('style', 'border: 2px solid Black');
				elem.attr('class', elem.attr('class') + ' forbiddenButton');
			}
		}
	}
	Excite.qu.altRefreshButtonHandler = function(questionId,questionText) {
		Excite.qu.ajaxGetStats(questionId,questionText, null,null,null,'altTemplate');
	}

	Excite.qu.statsButtonHandler = function(questionId,questionText,statsType,viewType, backStatsType) {
		//console.log( 'statsButtonHandler ' + questionId + ' ' + questionText + ' ' + statsType);
		if ( Excite.userType == 0 ) { // Excite Light only
			if ( ! Excite.qu.userProfile['any'] || ! Excite.qu.userProfile[statsType] ) {
				xtra="deze waarde";
				statsT = statsType;
				if( statsType == "Postcode" ) {xtra = "Postcode"; statsT = 'Kaart'; }
				if (statsType == "Provincie" ){
					xtra = "Postcode";
				}
				statsType = Excite.qu.statsWindow.disabledButton;
				viewType = null; backStatsType = null; // modify possible Kaart Request parameters!!!
				errMsg =  'Je kunt de statistieken van <strong>' + statsT + "</strong> inzien als je " + xtra + " invult in je eigen profiel met de Yixow App op je telefoon";

				Excite.dialogAlert(errMsg, refresh);
				return;
			}
		}
		refresh();
		function refresh () {
			//console.log(statsType);
			//console.log(backStatsType);
			if ( statsType == "Postcode" ) {
				//Excite.dialogAlert("Kaart werkt niet altijd wegens onderhoud aan database.");
			}
			try {
				Excite.qu.ajaxGetStats(questionId,questionText,statsType, viewType, backStatsType);
			} catch (e) { console.log('Catched!')};
			if ( backStatsType == null )
				Excite.qu.statsWindow.disabledButton = statsType; // will be disbled after refresh
			else
				Excite.qu.statsWindow.disabledButton = backStatsType; // will be disabled after Back form GeoStats
		}
	}
}

Excite.qu.provMap = function() {
	// load not needed in all circumstances; already loaded by preceding charts
	drawCharts();
	function drawCharts() {
		var nlOptions  = {
				region: 'NL',
				resolution: 'provinces',
				//displayMode: 'markers',
				//colorAxis: {colors: ['#00853f', 'black', '#e31b23']},
				colorAxis: {colors: ['OrangeRed', 'Orange', 'yellow', 'green', 'DarkGreen']},
				//backgroundColor: '#81d4fa',
				backgroundColor: 'white',
				//backgroundColor : { fill: 'white' , stroke: 'black', strokeWidth: 6},
				//datalessRegionColor: '#f8bbd0',
				datalessRegionColor: 'GainsBoro',
				//tooltip: {isHtml: true},
				width: 300,
				key: 678,
		};
		var nlDataTable = google.visualization.arrayToDataTable( Excite.qu.geoProvData );
		var chartNL = new google.visualization.GeoChart(document.getElementById('chartNL'));
		if ( !Excite.miniStats)
			google.visualization.events.addListener(chartNL, 'regionClick', eventHandler);
		chartNL.draw(nlDataTable, nlOptions);
		
		function eventHandler(e) {
			switch ( e.region ) {
				case 'NL-GR':								
				case 'NL-FR':
				case 'NL-DR':
				case 'NL-OV':
				case 'NL-GE':
				case 'NL-UT':
				case 'NL-NH':
				case 'NL-ZH':
				case 'NL-ZE':
				case 'NL-NB':
				case 'NL-LI':
				case 'NL-FL':
					Excite.qu.openMap.setView(Excite.qu.provCenter[e.region], 9, {animate: true});
					break;					
			}
		}; 
	}
}

Excite.qu.showMap = function() {
		Excite.qu.provCenter = {
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
		Excite.qu.newLayer = function (dataArr, layer) {
			if ( layer != null ) { // ja deze is al berekend; just display it
				layer.addTo(Excite.qu.openMap);
				return layer;
			}
			//document.body.style.cursor = 'wait'; // is niet nodig;
			var circles = new L.layerGroup();
			for ( i = 1; i < dataArr.length; i++ ) {
		if ( typeof dataArr[i][1] === 'undefined' ) {
			console.log('Lon undefined: ' + dataArr[i][2]);
			continue;
		}
				c = L.circle([dataArr[i][0] /*Lat*/, dataArr[i][1] /*Lon*/], dataArr[i][3] /*circle radius*/, {
				color: dataArr[i][4],
				fillColor: dataArr[i][4],
				fillOpacity: 0.5,
				});
				// wat komt in de popup?
				c.bindPopup(dataArr[i][2]);
				circles.addLayer(c);
			}
			circles.addTo(Excite.qu.openMap);
			//document.body.style.cursor = 'auto';
			return circles;		
		};

		Excite.qu.openMap = L.map('openMap').setView(Excite.qu.provCenter['NL-UT'], 8); // Utrecht in het centrum
		// waar komen de map tiles (plaatjes) vandaan en give credits (it's free and not Google!)
		// gebruik https ivm prod; anders warnings in console van ten minste IE over mixed http en https content
		L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
			attribution: 'Map data & imagery &copy; <a href="http://openstreetmap.org" target="_blank">OpenStreetMap</a> contributers, <a href="http://creativecommons.org/licenses/by-sa/2.0/" target="_blank">CC BY-SA</a> ',
			maxZoom: 20, // was 18
		}).addTo(Excite.qu.openMap);

		// get geoJson text file with Provinces borders
		var get = $.get( "/js/json/NL-provBorders.json");
		get.done(function( data, textStatus,jqXHR ) {
			if ( data == null ) { // TODO
				Excite.dialogAlert("<li>provBorders: Bel de brandweer, er is een probleem.</li>");
				return;
			}
			Excite.qu.provBorders = L.geoJson(data, {
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
			}).addTo(Excite.qu.openMap);
			Excite.qu.provBorders.bringToBack();
		});

		if( ! Excite.isIE) { // Gemeentegrenzen IE/Leaflet cannot handle this correctly
			// get geoJson text file
			var get = $.get( "/js/json/NL-munBorders.json");
			get.done(function( data, textStatus,jqXHR ) {
				if ( data == null ) { // TODO
					Excite.dialogAlert( "<li>munBorders: Bel de brandweer, er is een probleem.</li>");
					return;
				}
				Excite.qu.munBorders = L.geoJson(data, {
					style: function (feature) {
							return {
								color:'Red',
								fill: true,
								fillColor: 'pink',
								fillOpacity: 0.1,
								weight: 2,
								dashArray: '3,3',
							};
					},
					onEachFeature: function (feature,layer) {
							layer.bindPopup(feature.properties.Gemeentenaam);
					},

				});
				// dit gaat alleen na addTo()
				// munBorders.bringToBack();

			});
		} else { // disable gemeentegrenzen Button in IE
			$('#munBordersB').attr('disabled', 'disabled');
			$('#munBordersB').attr('style', 'background-color: #C0C0C0;');
		}
		if ( location.hostname != 'test.yixow.com' && location.hostname != 'excite.app' ) {
			$('#munBordersB').hide();
			//$('#extraInfo').hide();
			$('#extraInfo').html('<br /><br /><br /><br /><br />');
		}
		// welke layers zijn al berekend? eerst nog niks.....
		// mun: gemeente, loc: plaats, zip: full postcode; zip4: alleen 4 cijfers postcode
		Excite.qu.layers = { 'mun': null, 'loc': null, 'zip':null, 'zip4': null};
		// begin met gemeenten
		Excite.qu.layer = Excite.qu.layers.mun = Excite.qu.newLayer(Excite.qu.mapDataArr['mun'], null);
		
	/* for future use
	function ajaxGetMapDataAsJson() {
		$.ajax( {
			url: "/questions/getMapDataAsJson",
			dataType: 'json',
			async: false, // cannot be async; needed for assign to function scope mapData that is used afterwards
			data: {
				questionId: {{$reqData['questionId']}},
			},
		}).done(function( data, textStatus,jqXHR ) {
				mapData = data;
		});
	}	
	//levert iets vertraging op na klik op Kaart Button; was geen oplossing voor eerst te trage IE
	//daar werd gelukkig iets anders op gevonden
	//nog steeds dus gewoon de data als object literal opnemen in het Script;
	var mapData;
	ajaxGetMapDataAsJson();

	Excite.qu.mapDataArr['mun'] = mapData.mun;
	Excite.qu.mapDataArr['loc'] = mapData.loc;
	Excite.qu.mapDataArr['zip'] = mapData.zip;
	Excite.qu.mapDataArr['zip4'] = mapData.zip4;
	*/
}
// for remote usage of 'nieuwste vragen' in groep of publiek
// starts or refreshes the slider
Excite.qu.ajaxGetSlider = function (emailAddr) {
	if ( typeof Excite.qu.sliderPaused === 'undefined')
		Excite.qu.sliderPaused = false;
	if ( Excite.qu.sliderPaused ) return; // no refresh when paused
	url = "https://www.yixow.com/qbrowser";			
	var get = $.get( url, {
		option: 2,
		email: emailAddr, // unieke key string voor StatsRemotePermissions
								// typisch gebruik: het address waarmee de beheerder van groepen lid is van Yixow
								// als niet gevonden komen de publieke vragen
	} );
	get.done(function( data, textStatus,jqXHR ) {
		if ( data == null ) { // TODO
			alert("Bel de brandweer, er is een probleem.");
			return;
		}
		$('#sliderContainer').html(data);
	});
}

Excite.qu.sliderCtrl = function (closeWidth) {
	var CLOSE_LIMIT = closeWidth;
	jQuery( "#sliderWindow" ).dialog({
		modal: false,
		width: 320,
		//height: 440;
		autoOpen: false,
		position: { my: 'left top+50', at: 'top', }, // dit is duistere stuff voor mij
	});
	Excite.qu.closedByResize = false;
	if ( jQuery(window).width() > CLOSE_LIMIT )
		{jQuery( "#sliderWindow" ).dialog('open');}
	else Excite.qu.closedByResize = true; // in fact: never opened yet... handle as if closed by resize

	jQuery(window).resize(function(event) {
		var sliderW = jQuery( "#sliderWindow" );
		if (  jQuery(window).width() > CLOSE_LIMIT ) {
			if ( Excite.qu.closedByResize ) { //reopen when formerly closed by resize
				sliderW.dialog('open');
				Excite.qu.closedByResize = false;
				return;
			}
		} else { // <= CLOSE_LIMIT
			if ( sliderW.dialog('isOpen')) { // not closed by user
				sliderW.dialog('close');
				Excite.qu.closedByResize = true;
				return;
			}
		}
		// behavior when above conditions not satisfied
		if( ! sliderW.dialog('isOpen')) return; // user closed Dialog
		// reopen in resized window otherwise
		sliderW.dialog('option', "position: { my: 'center', at: 'top', }");
		sliderW.dialog('close');
		sliderW.dialog('open');
	});
}