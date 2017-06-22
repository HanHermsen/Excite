Excite.y.QUESTIONS = 0;
Excite.y.GROUPS = 1;
Excite.y.GROUPS_OFFERED = 2;

//Excite.y.qTabId = '#qTab1';

$(document).on("pagecreate",function(event,data){

  //console.log('pagecreate ' + Excite.y.pageId);
  return;
   if ( Excite.y.pageId == Excite.y.QUESTIONS) {
	Excite.y.qTabSet(Excite.y.qTabId);
  }
  //alert("pageload event fired!\nURL: " + data.url);
});


/*
$(document).on("pagecontainerload",function(event,data){
  console.log('pageload ' + Excite.y.pageId);
   if ( Excite.y.pageId == Excite.y.QUESTIONS) {
	Excite.y.qTabSet(Excite.y.qTabId);
  }
  //alert("pageload event fired!\nURL: " + data.url);
});

$(document).on("pagecontainershow",function(event,data){
 console.log('pageshow ' + Excite.y.pageId);
  if ( Excite.y.pageId == Excite.y.QUESTIONS) {
	Excite.y.qTabSet(Excite.y.qTabId);
  }
  //alert("pageload event fired!\nURL: " + data.url);
}); */
/*
$(document).on("pagebeforechange",function(event,data){
	menuElem = Excite.y.menuElem;
	if ( typeof menuElem === 'undefined' ) return;
	ace = $('.menuSelect');
	if (Excite.y.menuElem.is( ace) ){
		console.log("Same same");
		//return;
	}
	ace.removeClass('menuSelect');
	menuElem.addClass('menuSelect');
	// menuElem.css("color: red;");
console.log('pagechange ' + Excite.y.pageId);
  console.log(Excite.y.menuElem);
  
}); ***********/
$( window ).on( "navigate", function( event, data ) {

  console.log( "nasvigate "+ data.state );

});

/** menu handling */
$('#nav-panel').on( "panelbeforeopen", function( event, ui ) {
	Excite.y.highlight(Excite.y.pageId); // set the highlight to current choice
} );

Excite.y.menuChoice = function (elem,c) {
	//menuElem = $('.menuSelect');
	//if (menuElem.is( elem) ){
	if (c == Excite.y.pageId ){
		//console.log("Same same; must close Panel");
		$('#nav-panel').panel('open'); // BUG status can be closed when visible in the app; so open it first
		$('#nav-panel').panel('close');
		return;
	}
	Excite.y.pageId = c;
	Excite.y.highlight(c);
	return;
}

Excite.y.highlight = function (pageId) {
	$('.menuSelect').removeClass('menuSelect');
	s = '#choice' + pageId + '> span';
	$(s).addClass('menuSelect');
	//$('#headerTitle').html('Vragen');
	//console.log($(s));
}
/*
Excite.y.qTabSet = function(id) {
console.log("Tabset: ", id);
Excite.y.qTabChoice(id);
}
Excite.y.qTabChoice = function(id) {
return;
console.log("TabChoice: " + id);
	Excite.y.qTabId = id;
	$('.qTab').removeClass('ui-btn-active ui-state-persist');
	$(id).addClass('ui-btn-active ui-state-persist');
} */