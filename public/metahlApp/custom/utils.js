var U_NAV = navigator.userAgent;
var U_NAV_LC = U_NAV.toLowerCase();
//console.log("Browser: " + U_NAV);
var U = {
	browser: U_NAV,
	isIE: (U_NAV_LC.indexOf("trident") > -1), // check IE true or false; Etc.
	isSafari: (U_NAV_LC.indexOf("safari") > -1 && U_NAV.indexOf("chrome") < 0),
	isChrome: (U_NAV_LC.indexOf("chrome") > -1),
	isFirefox: (U_NAV_LC.indexOf("firefox") > -1),
};
U.url = U.location = location.host;
U.urlParams = location.search;

if ( U.url == 'localhost:8100' || U.url == 'excite.app')
	U.url = 'http://excite.app';
	//U.url = 'https://yixow.com';
/*
else if ( U.url.indexOf('yixow.com') > -1 && U.url != 'test.yixow.com' && U.url != 'demo.yixow.com' )
	U.url = 'https://yixow.com'; */
else if ( U.url == 'test.yixow.com' )
	U.url = 'https://yixow.com';
else
	U.url = 'https://yixow.com';
console.log('BaseUrl: ' + U.url);

U.debug = 0;
if ( location.search.indexOf('?test') > -1 && U.url == 'https://yixow.com' )
	U.debug = 1;

U.fixBtnNames = function() {
	if ( ! U.debug ) return;
	//$('.qTasteBtn').html('TEST');
	fix = U.$$('.qTasteBtn');
	for ( i= 0;i<fix.length;++i)
		fix[i].innerHTML = 'TEST';
}

// create a JS 'Class' that can have a constructor function in a construct property
U.Class = function(methods) { 
    var exciteClass = function() {    
        this.construct.apply(this, arguments);          
    };     
    for (var property in methods) { 
       exciteClass.prototype[property] = methods[property];
    }          
    if (!exciteClass.prototype.construct) exciteClass.prototype.construct = function(){};          
    return exciteClass;    
};
// jQuery replacement
U.$ = function (selector, node) {
    if ( !node ) node = document;
    return node.querySelector(selector);
};
U.$$ = function (selector, node) {
    if ( ! node ) node = document;
    return node.querySelectorAll(selector); // returns Nodelist 'array' (alleen [] en .length)
}

// general Ajax call; plain vanilla JavaScript
U.ajaxCall = function(url,callback, method, params) {
    // initial default param checking
    if ( ! params ) params = {};
    if ( ! method ) {
        method = 'GET';
    } else {
        switch (method) {
            case 'GET':
            case 'POST':
                break;
            default:
                method = 'GET';
        }
    }
    // init request object
    if (window.XMLHttpRequest) {
        var xhttp = new XMLHttpRequest();
    } else {
        // code for IE6, IE5; not really needed anymore
        var xhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    // create event handler for the object
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            callback(JSON.parse(this.responseText));
        } else {
            if (this.readyState == 4) {
                console.log('Cannot load data');
                callback( null );
            }
        }
    };
    // put params in url query string: param1=value1&param2=value2.....
    var arg = '';
    for ( var key in params) {
        if ( arg !== '' ) arg += '&';
        arg += encodeURIComponent(key) + '=' + encodeURIComponent(params[key]);
    }
    if ( arg !== '' && method === 'GET') { // add query string to url
        url += '?' + arg;
        arg = '';
    }
    // go
    var async = true;
    xhttp.open(method, url, async );
    if ( method === 'POST') // arg not added to url; they will be POSTed
                            // under the hood like Form input params
         xhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhttp.send(arg);
};

// round a value to a given number of decimals; is not a JS Api standard
U.round = function round(value, decimals) {
  return Number(Math.round(value+'e'+decimals)+'e-'+decimals);
}

U.chkZipCode = function (zipCode) {
	var tmp = zipCode.trim().replace(/\s/g, '');
	if ( tmp == '' ) return '';
	if ( tmp.length < 4 ) return '';
	digits = tmp.substr(0,4);
	p = parseInt(digits);
	if ( isNaN(p) || p < 1000 )
		return '';
	return digits;
}

/**** Leaflet Map stuff **/
U.mapInstance = null; // global reference to the Map Class instance; used in
					  // map popup <button onclick='U.mapInstance.createLocLayer()'>
U.Map = U.Class( {
	map: null,
	callback: null,
	tileLayer: null,
	munData: null,
	locData: null,
	locLayer: null,
	mainPopup: null,
	containerId: null,
	zipMarker: null,
	construct:
		function(callback, mapData,containerId) {
			U.mapInstance = this; 
			this.callback = callback;
			this.munData = mapData.munScores;
			this.locData = mapData.locScores;
			this.containerId = containerId;
			this.map = L.map(containerId).setView(this.provCenters['NL-GR'], 9); // Groningen in het centrum
			// waar komen de map tiles (plaatjes) vandaan en give credits (it's free and not Google!)
			// gebruik https ivm prod; anders warnings in console van ten minste IE over mixed http en https content
			this.tileLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
				attribution: 'Map data &copy; <a href="http://openstreetmap.org" target="_blank">OpenStreetMap</a>',
				maxZoom: 20,
			}).addTo(this.map);
			this.provBorders();
			this.createMunLayer();
			this.map.on('popupopen', function(e) {
				callback.handlePopup(e.popup._source._mun,e.popup._source._loc);
			});
		},
	refresh: // not in use
		function () {
			this.tileLayer.addTo(this.map);
		},
	provBorders:
		function() {
			var map = this.map;
			U.ajaxCall("custom/json/NL-provBorders.json", function(data) {
				provBorders = L.geoJson(data, {
					style: function (feature) {
						return {
							color:'black',
							fill: false,
							fillColor: 'pink',
							fillOpacity: 0.1,
							weight: 1.3,
						};
					},
					//onEachFeature: function (feature, layer) { // werkt als fill is true
						//layer.bindPopup('<strong>' + feature.properties.Provincienaam + '</strong>');
					//},
				}).addTo(map);
				provBorders.bringToBack();
			});
		},
	createMunLayer:
		function() {
			var questionMarkIcon = L.icon( {
				iconUrl: 'custom/css/images/32.png',
			});
			var divIconNotShared = L.divIcon({html: "<p style='width: 110px;background-color: #0086b3'>" + "&nbsp;Niet publiek bekend" + "</p>", className: 'mapLabelDiv2'});
			var divIconUnknown = L.divIcon({html: "<p style='width: 85px;background-color: #0086b3'>" + "&nbsp;Niet gevonden" + "</p>", className: 'mapLabelDiv2'});
			layerGroup = L.featureGroup();
			data = this.munData;
			for ( i = 0;i<data.length;i++) {
			//console.log(data[i].name);
				if ( typeof data[i].lat !== 'undefined' && typeof data[i].lon !== 'undefined' ) {
					name = data[i].name;
					if (name == 'Woonplaats niet gedeeld' || name == 'Woonplaats onbekend'){
						if ( name == 'Woonplaats niet gedeeld' ) {
							alert = '<br /><span>Naam woonplaats afgeschermd door<br>petities.nl op verzoek ondertekenaar.</span>';
							label = 'Niet publiek bekend';
						} else {
							alert = '<br /><span>Meer plaatsen met de opgegeven<br>naam of onbekende plaatsnaam</span>';
							label = 'Niet gevonden';
						}
						marker = L.marker([data[i].lat, data[i].lon], {icon: questionMarkIcon})
						.bindPopup("<span style='font-size:10pt;font-weight:bold'>" + label + ' ' + data[i].cnt.toLocaleString() +'</span>' + alert);

					} else {
						alert = '';
						if (data[i].munAlert)
							alert = "<div style='height: 4px'></div>Score deels naar 'Niet gevonden'.<br />Let op 'Geschat' of 'Onbekend'<br />bij een plaatsnaam.";
						marker = L.marker([data[i].lat, data[i].lon])
						.bindPopup("<span style='font-size:10pt;font-weight:bold'>" + data[i].name + ' ' + data[i].cnt.toLocaleString() + "</span><br /><button class='catLabel' onclick='U.mapInstance.createLocLayer()'>Toon scores plaatsen</button><div><xmp style='display: none'>" + data[i].name + "</xmp></div>" + alert);
						marker._mun = data[i].name;
						marker._loc = null;
					}
					if ( data[i].name == 'Groningen' ) {
						this.mainPopup = marker;
					}
					if (data[i].name == 'Woonplaats niet gedeeld' || data[i].name == 'Woonplaats onbekend' )
						if ( data[i].name == 'Woonplaats onbekend')
							myIcon = divIconUnknown;
						else
							myIcon = divIconNotShared;
					else
						myIcon = L.divIcon({html: "<p style='width: 100px'>" + data[i].name + "</p>", className: 'mapLabelDiv'});
					marker2 = L.marker([data[i].lat, data[i].lon],{icon: myIcon});

					layerGroup.addLayer(marker);
					layerGroup.addLayer(marker2);
				} else {
					console.log("Bad lat/lon" + data[i].name);
				}
			}
			layerGroup.addTo(this.map);
			// doesn't work well 'Toon scores plaatsen' gives no response
			// this.mainPopup.openPopup();
		},
	createLocLayer:
		function() {
			var tmpElem = document.createElement('div');
			// cannot use a name parameter for cases like: S&#250;dwest-Frysl&#226;n
			// litteral arg is stored in a hidden <xmp></xmp> in the popup
			name = U.$('xmp').innerHTML;
			data = this.locData;

			if (this.locLayer != null)
				this.map.removeLayer(this.locLayer);
			var tmpIcon = L.icon( {
				iconUrl: 'custom/css/images/16.png',
			});
			layerGroup = L.featureGroup();
			var list = [];
			var j = 0;
			var cnt;
			for ( i = 0;i<data.length;i++) {
				if ( data[i].mun != name ) continue;
				cnt = data[i].cnt;
				flag = data[i].flag;
				if ( flag == '0' ) {
					if (cnt == 0) {
						cnt = '- ' +cnt + ' (Onbekend)';
					} else {
						cnt = '- ' +cnt + ' (Geschat)';
					}
				} else {
					cnt = data[i].cnt.toLocaleString();
				}
					
				if ( typeof data[i].locLat !== 'undefined' && typeof data[i].locLon !== 'undefined' ) {
					marker = L.marker([data[i].locLat, data[i].locLon],{icon: tmpIcon})
						.bindPopup(data[i].name + ' ' + cnt);
					layerGroup.addLayer(marker);
					//NEW; html entity conversion when needed
					locName = data[i].loc;
					if ( locName.indexOf("&#") > -1 ) {
						tmpElem.innerHTML = locName;
						locName = tmpElem.childNodes[0].nodeValue;
					}
					list[j++] = locName + ' ' + cnt;
					marker._mun = name;
					marker._loc = data[i].loc; 
				} else {
					console.log("Bad lat/lon" + data[i].name);
				}
			}
			//console.log(layerGroup);
			layerGroup.addTo(this.map);
			layerGroup.bringToFront();
			
			this.locLayer = layerGroup;
			this.callback.showLocList(list.sort());
		},
	zoomIn:
		function(lat, lon, zipCode) {
			if ( this.zipMarker != null )
				this.zipMarker.remove();
			var divIconZip = L.divIcon({html: "<p style='width: 45px;background-color: green;font-size: 12pt'>&nbsp;" + zipCode + "</p>", className: 'mapLabelDivZipCode'});
			latLng = L.latLng(lat, lon);
			//console.log("zoomIn");
			var tmpIcon = L.icon( {
				iconUrl: 'custom/css/images/16.png',
			});
			this.zipMarker = L.marker([lat, lon],{icon: divIconZip});
			this.zipMarker.addTo(this.map);
			zoom = 11;
			this.map.setView(latLng, zoom, {animate: true});
		},
	zoomOut: function() {
			zoom = 8;
			this.map.setView(this.provCenters['NL-UT'], zoom, {animate: true});
		},
	zoomStart: function() {
			zoom = 9;
			if ( this.zipMarker != null ) {
				this.zipMarker.remove();
				this.zipMarker = null;
			}
			if (this.locLayer != null) {
				this.map.removeLayer(this.locLayer);
				this.locLayer = null;
			}
			this.map.setView(this.provCenters['NL-GR'], zoom, {animate: true});
		},
	provCenters:
		{	'NL-GR': [53.2, 6.6],	// fixed from: 53.27260, 6.77911						
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
		},
});


/* Google stats */
U.statsInstance = null;
/* Old: jQuery getScript; replaced by <script> on Page
U.statsApiLoaded = true;
U.getStatsApi = function () {
	if (U.statsApiLoaded) return;
	$.getScript("https://www.google.com/jsapi");
	U.statsApiLoaded = true;
} */
U.Stats = U.Class( {	
	construct:
		function(data,elemIds,allCnt,notSharedCnt) {
			if (U.statsInstance != null) {
				// this does not work container not yet available! 
				/* console.log("Redraw");
				U.drawStats(data,elemId, allCnt);
				return; */
			}
			U.statsInstance = this;
			//U.getStatsApi();
			thisClass = this;
			setTimeout(function() {google.load('visualization', '1', {'callback': function() {thisClass.drawStats(data,elemIds, allCnt, notSharedCnt)}, 'packages':['corechart','geochart']})}, 300);
		},

	drawStats:
		function(data,elemIds, allCnt, notSharedCnt) {
			e = U.$('#overall');
			e.innerHTML = '<strong>' + allCnt.toLocaleString() +  '</strong>  dat is <strong>' + U.round((allCnt/14790000) * 100, 2).toLocaleString() + '% </strong>van alle Nederlanders vanaf 18 jaar en ouder.';
			U.$('#notShared').innerHTML = 'Niet publiek bekend gemaakte adressen: <strong>' + notSharedCnt.toLocaleString() + '</strong>';
			this.drawBar(data.provScores,elemIds.barElemId);
			this.drawPie(data.provScores,elemIds.pieElemId);
			this.drawBar2(data.provScoresPop,elemIds.bar2ElemId);
		},
	drawBar:
		function(data,elemId) {
			// add annotations in the bars
			data[0][2] = {role: 'annotation'};
			//console.log(data);
			barDataTable = google.visualization.arrayToDataTable(data);
			barOptions = {'title':'',
				 'width':'320',
				 'height':500,
				'legend': {'position': 'none'},
				'fontSize': 11,
				 bar: {groupWidth: "80%"},
				//'vAxis': {textPosition: 'in'},
				//'vAxis': {viewWindowMode: 'maximized'},
				annotations: {
					alwaysOutside: false,
					style: 'point',
					textStyle: {
					  /* fontName: 'Times-Roman', */
					  fontSize: 11,
					  width: 1000,
					  //bold: true,
					  // The color of the text.
					  color: 'black',
					  // The color of the text outline.
					  /* auraColor: '#d799ae', */
					  // The transparency of the text.
					  /* opacity: 0.8 */
					},
				},
				chartArea: {
					left: 80,
					top: 10,
					width: '100%',
					//backgroundColor: 'pink',
				},
		
			};
			barChart = new google.visualization.BarChart(document.getElementById(elemId));
			barChart.draw(barDataTable,barOptions);
		},
	drawBar2:
		function(data,elemId) {
			// add annotations in the bars
			data[0][2] = {role: 'annotation'};
			//console.log(data);
			barDataTable = google.visualization.arrayToDataTable(data);
			barOptions = {'title':'',
				 'width':'330',
				 'height':550,
				'legend': {'position': 'none'},
				'fontSize': 11,
				 bar: {groupWidth: "80%"},
				//'vAxis': {textPosition: 'in'},
				//'vAxis': {viewWindowMode: 'maximized'},
				annotations: {
					alwaysOutside: true,
					//style: 'point',
					textStyle: {
					  /* fontName: 'Times-Roman', */
					  fontSize: 11,
					  //width: 1000,
					  //bold: true,
					  // The color of the text.
					  color: 'black',
					  // The color of the text outline.
					  /* auraColor: '#d799ae', */
					  // The transparency of the text.
					  /* opacity: 0.8 */
					},
				},
				chartArea: {
					left: 80,
					top: 10,
					width: '100%',
					//backgroundColor: 'pink',
				},
		
			};
			barChart = new google.visualization.BarChart(document.getElementById(elemId));
			barChart.draw(barDataTable,barOptions);
		},
	drawPie:
		function(data,elemId) {
			data2 = data.slice(0); // make a copy; otherwise the sort is done on the referenced data
			var text01 = data2[0][1];
			if ( ! U.isIE ) {
				data2.sort( function compare(a,b) { // sort from hi to low cnt
					if ( a[1] == text01 ||  b[1] == text01)
						return -1; // keep first legenda row at it's place
					if( a[1] < b[1] ) return 1;
					if( a[1] > b[1] ) return -1;
					return 0;
				});
			} else { //alternative for IE
				data2[0][1] = 17000000; // will keep first legenda row at it's place
				data2.sort( function compare(a,b) { // sort from hi to low cnt
					if( a[1] < b[1] ) return 1;
					if( a[1] > b[1] ) return -1;
					return 0;
				});	
				data2[0][1] = text01;
			}
			//console.log("Data sorted");
			//console.log(data2);
			pWidth = 380;
			pHeight = 330; //230
			chartArea = {left:5,top:10,bottom:0,width:"100%"};
			fontSz = 11;
			var pieDataTable = google.visualization.arrayToDataTable(data2);
			var pieOptions = {//'title':'Alle antwoorden',
					'sliceVisibilityThreshold':0, // alles in de legenda!
					'width':pWidth,
					'height':pHeight,
					is3D: true,
					'chartArea': chartArea,
					//legend: {position: 'labeled'},
					fontSize: fontSz,
				};
			pieOptions.tooltip = { text: 'percentage'};
			var pieChart = new google.visualization.PieChart(document.getElementById(elemId));
			pieChart.draw(pieDataTable,pieOptions);
	},
});

