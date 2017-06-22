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
U.url = location.host;

if ( U.url == 'localhost:8100' || U.url == 'excite.app')
	U.url = 'http://excite.app';
	//U.url = 'https://yixow.com';
else if ( U.url == 'test.yixow.com' )
	//U.url = 'http://test.yixow.com';
	U.url = 'https://yixow.com';
else
	U.url = 'https://yixow.com';

console.log('BaseUrl: ' + U.url);


// create a JS 'Class' that can have a constructor function in a 'construct' property
U.Class = function(methods) { 
    var exciteClass = function() { 
        this.construct.apply(this, arguments);          
    }; 

    for (var property in methods) {
			exciteClass.prototype[property] = methods[property];
    }
	// private function protection method	
	exciteClass.prototype.privateBlock = function(f) {
			if (f.caller == null) {
				console.log("PrivateBlock " + f.name);
				return true;
			}
			else return false;
			/*
			var err = new Error();
			var regExp = new RegExp('utils.js', "gi");
			cnt = (err.stack.match(regExp) || []).length;
			console.log('func '+ f + ' ' + cnt + '\n'  + err.stack);
			return cnt > 2; */
		};
    if (!exciteClass.prototype.construct) exciteClass.prototype.construct = function(){};          
    return exciteClass;    
};

U.chkZipCode = function (zipCode) {
	//var tmp = zipCode.trim().replace(/\s/g, '');
	var tmp = zipCode.trim();
	if ( tmp == '' ) return '';
	tmp = tmp.replace(/^0/,'');
	if ( tmp.length < 2 ) return '';
	if ( tmp.length > 4 ) tmp = tmp.substring(0,4);
	match = tmp.match(/^[1-9][0-9]+/);
	if ( match != null ) {
		if (match.length >= 1 &&  match[0].length > 1  )
			return match[0];
		else return '';
	}
	else
		return '';
}
// usage: new U.TypeAhead(inputElemId [, options [, listData]])
U.typeAheadInstance = null;
U.TypeAhead = U.Class( {
	mapList: {}, // alias list
	inputElem: null, // the text Field
	typeAhead: null, // TypeAhead instance
	list: [], // match candidate list
	listStartLength: 0,
	tmpElem: null,
	lastZip: '',
	construct: function(elemId, opts, data) {
		U.typeAheadInstance = this;
		this.tmpElem = document.createElement('div');
		if ( typeof opts == 'undefined' ) opts = null;
		if ( typeof data == 'undefined' ) {
			var thisClass = this;
			//get data with Ajax; init after async Ajax callback
			this.getLists( function(data) { thisClass.init(elemId, opts, data); } );
		} else {
			// data comes from caller; go on immediately
			this.init(elemId, opts, data);
		}
	},
	init: function(elemId, opts, data) {
	//console.log("init caller is: " + arguments.callee.caller.toString());
		if ( this.privateBlock(this.init) ) return;
		if (opts == null ) // default opts
			opts = {fulltext: false, minLength: 1, limit: false};
		if ( data == null ) // no data; failed Ajax call; be funny
			data = {locNames: ["'s-Gravenhage",'Scheveningen'],mapList: {'Scheveningen': "'s-Gravenhage"}};
		list = data.locNames;
		this.mapList = mapList = data.mapList;
		var that = this;
		Object.keys(mapList).forEach(function(key,index) {
			// map keys with html entitity names on display name
			that.mapName(key, deleteName = true);
		});
		for ( i = 0; i < list.length; i++) {
			list[i] = this.mapName(list[i], deleteName = false);
		}
		list.sort();
		//this.mapList = mapList;
		this.list = list;
		this.listStartLength = list.length;
		this.inputElem = document.getElementById(elemId);
		this.typeAhead = new TypeAhead(this.inputElem, list, opts);
		this.setListStyle();
	},
	mapName: function(name,deleteName) {
		val = name;
		mapList = this.mapList;
		if ( name.indexOf("&#") > -1 ) {
			// map names with html entitities on display name
			this.tmpElem.innerHTML = name;
			val = this.tmpElem.childNodes[0].nodeValue;
			if ( typeof mapList[val] == 'undefined' ) {
				// add to mapList when not yet there
				mapList[val] = name;
				if (deleteName) delete mapList[name];
			}
		}
		return val;
	},
	setListStyle: function() {  // alternative class for IE; cannot handle Scroll Bar
		if ( this.privateBlock(this.setListStyle) ) return;
		if ( U.isIE ) {
			document.getElementById('typeAheadList').className = 'typeAheadListIE';
		}
	},
	show: function(callback) { // for demo validate display
		res = this.eval();
		if ( res.displayName == null ) res.displayName = 'Not accepted';
		if ( res.dbKey == null ) res.dbKey = 'No match found!';
		callback.presentPopover(res.displayName,res.dbKey);
	},
	eval: function() {
		//console.log(this.typeAhead);
		if ( this.privateBlock(this.eval) ) return;
		reject = { displayName: null, dbKey: null};
		val = this.inputElem.value.trim();
		if ( val == '' || val.length < 3) return reject;
		valLC = val.toLowerCase();
		taList = this.typeAhead.list.items;
		taLength = taList.length;
		if ( taLength > 5) // arbitrary limit for too many hits
			return reject;
		dbName = null;
		if (taLength > 0 ) {
			found = false;
			for ( i = 0; i <taLength; i++ ) {
				match = taList[i].toLowerCase();
				if( valLC != match && "'" + valLC != match ) // try leading ' too!
					continue;
				else {
					val = dbName = taList[i];
					this.inputElem.value = val;
					found = true;
					break;
				}
			}
			if ( ! found ) {
				// return reject;
				if (taLength == 1) {
					val = dbName = taList[0];
					this.inputElem.value = val;
				} else {
					dbName = null;
				}
			} else
				dbName = val;
		} else { // empty list: full match available or no match at all
			if ( this.typeAhead.selected == val ) // full match
				dbName = val;
			else // no match at all
				dbName = null;
		}
		if ( ! (typeof this.mapList[val] == 'undefined') ) {
			// replace when needed by alias source
			dbName = this.mapList[val];
		}
		if ( dbName != null ) { // delete possible Postcode prefix; this works; but TODO is maplist OK; use that!
			x = dbName.replace(/^[1-9][0-9]+ /,'');
			if ( x != dbName) {
				//dbName = val = this.inputElem.value = x;
				dbName = x;
				val = val.replace(/^[1-9][0-9]+ /,'');
			}
		}
		return { displayName: val, dbKey: dbName };
	},
	deleteZipFromList: function() {
		if (this.list.length != this.listStartLength ) {   // delete current zipcode entries, if any
			this.list = list.slice(0, this.listStartLength);
		}
		this.lastZip = '';
		return this.list;9571
	},
	getLists: function(callback) { // plain vanilla Ajax call; no jQuery needed
		if ( this.privateBlock(this.getLists) ) return;
		var xhttp = this.getXhttp(callback,null);
		xhttp.open("GET", U.url + '/metahlApi/getLocNames', async = true);
		xhttp.send();
	},
	getZipNames: function(zip, callback) { // other plain vanilla Ajax call
		if ( this.privateBlock(this.getZipNames) ) return;
		this.lastZip = zip;
		var that = this;
		var xhttp = this.getXhttp(handle,[]);
		xhttp.open("GET", U.url + '/metahlApi/getZipNames?zip=' + zip, async = true);
		xhttp.send();

		function handle(data) {
			that.deleteZipFromList();
			for ( i = 0; i < data.length; i++ ) { // add found zipcode entries
				name = zip + ' ' + data[i].Plaats;
				name = that.mapName(name,deleteName = false);
				// if ( that.candidates.indexOf(name) == -1 ) // just to be sure; not really needed
				that.list.push(name);
			}			
			callback(that.list);
		}
	},
	getXhttp: function(callback,nullCallbackParam) {
		if ( this.privateBlock(this.getXhttp) ) return;
		if (window.XMLHttpRequest) {
			xhttp = new XMLHttpRequest();
		} else {
			// code for IE6, IE5; not really needed anymore
			xhttp = new ActiveXObject("Microsoft.XMLHTTP");
		}
		xhttp.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200) {
				callback(JSON.parse(this.responseText));
			} else {
				//console.log('status: ' + this.readyState + ' ' + this.status);
				if (this.readyState == 4) {
					console.log('Cannot load data');
					callback( nullCallbackParam);
				}
			}
		};
		return xhttp;
	},
});




