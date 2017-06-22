var EXCITE_NAV = navigator.userAgent.toLowerCase();
console.log("Browser: " + EXCITE_NAV);

var Y = {
	isIE: (EXCITE_NAV.indexOf("trident") > -1), // check IE true or false; used for statsWindow
	isSafari: (EXCITE_NAV.indexOf("safari") > -1 && EXCITE_NAV.indexOf("chrome") < 0),
	isChrome: (EXCITE_NAV.indexOf("chrome") > -1),
	isFirefox: (EXCITE_NAV.indexOf("firefox") > -1),
};

Y.test= function(arg) {
	console.log("Au " + arg);
}

Y.fileSizeCheck = function(inputFile) { // should be private
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

Y.uploadCheck = function(elem) { // this is a private function
	fName = elem.value;
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
	if ( ! Y.fileSizeCheck(elem)) {
		fileMess += '<li>File is te groot; max 10 MB</li>';
	}
	/*
	if ( fileMess != '' ) {
		elem.val(''); // IE triggers on value change
		elem.trigger('change');	// for other browsers	
	} */
	return fileMess;
}


Y.imageUploadEventHandler = function(event,fileElem, viewElem, callCnt, defaultImageHTML,imageDateUrlElem, maxTargetSize) {
	// reset to '' of name within script is change too...
	console.log(fileElem);
	if ( fileElem.value.trim() == '' ) { event.preventDefault();return; }

	window.console.log("imageUploadEventHandler " + callCnt);

	mess = Y.uploadCheck(fileElem);
	if ( mess != '' ){
		// something is wrong: bad filename extension or file too large
		//Excite.dialogAlert(mess);
		if ( defaultImageHTML == undefined || defaultImageHTML == null || defaultImageHTML == '')
			viewElem.html('<img>');
		else
			viewElem.html(defaultImageHTML);
		event.preventDefault();
		//fileElem.val(''); // IE triggers on value change
		//fileElem.trigger('change');	// for other browsers
		imageDateUrlElem.value='';		
		return {ok:false,mess:mess};
	}
	try {
		var reader = new FileReader();
	} catch (e) {
		// no filereader support; f.i Windows Safari; other older Browsers on any platform;
		// global Browser FileReader coverage jan 2016: 89.97% http://caniuse.com/#feat=filereader
		//Excite.dialogAlert("Voorvertoning plaatje kan niet. Te oude versie van gebruikte Web browser.");
		return true;
	}
	// put the image in the preview div in the callback of the onload event of the FileReader
	var img = new Image();
	var imageError = false;
	img.onerror = function(e) {
		//Excite.dialogAlert("Dit is geen plaatje!");
		console.log("IMAGE ERROR");
		imageError = true;
		return;
		if ( defaultImageHTML == undefined || defaultImageHTML == null || defaultImageHTML == '')
			viewElem.innerHTML = '';
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
			if( imageError) {console.log("IMAGE ERROR 2");return};
			// without the timeout above, recall was needed sometimes!! why? image not fully loaded yet...
			// IE/Chrome Win needed infrequently at most 1 retry; Firefox Win more frequently 1 retry too;
			// Safari on Mac needed more retries;
			// maybe this part has become useless now...; stays to solve unknown as well as bizar conditions
			if( img.width == 0 && callCnt <= 10 ) { // retry; never happens anymore?
				Y.imageUploadEventHandler(event,fileElem, viewElem,++callCnt, defaultImageHTML, maxTargetSize);
				return;
			}
			if ( img.width == 0 ){ // too many tries; never happens?
				//Excite.dialogAlert("Sorry: 10 x geprobeerd. Voorvertoning gaat niet lukken. Het plaatje staat wel klaar om te gaan gebruiken!");
				return;
			}
			// process the image
			var maxTargetWidth = 0;
			var maxTargetHeight = 0;
			var maxDisplayWidth = 0;
			var maxDisplayHeight = 0;
			//maxDisplayWidth = viewElem.css('width').replace(/px/, '');
			//maxDisplayHeight = viewElem.css('height').replace(/px/, '');
			// TODO this is tmp
			maxDisplayWidth = 320;
			maxDisplayHeight = 240;
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

			imageDateUrlElem.value = img.src; //XXXX
			// remove file from upload input field; so it will not be uploaded
			fileElem.value = null; // it's not possible to get back to previous... security!
			//fileElem.trigger('change');
			//console.log("Result size :" + newWidth + 'x' + newHeight);
			hRatio = maxDisplayWidth / newWidth    ;
			vRatio = maxDisplayHeight / newHeight  ;
			var ratio  = Math.min ( hRatio, vRatio );
			displayWidth = Math.floor(newWidth*ratio);
			displayHeight = Math.floor(newHeight*ratio);
			viewElem.innerHTML = "<img src='" + img.src + "' height='" + displayHeight + "' width='" + displayWidth + "'>";
			console.log(viewElem);
		}
	}
	// read the image
	try {
		if(imageError) return false;
		reader.readAsDataURL(event.target.files[0]);
	} catch(e) { console.log("Help: imageUploadEventHandler"); return false; } // never reached sofar..
	return !imageError;
}