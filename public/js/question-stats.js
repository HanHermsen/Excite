function doStats(questionId, questionText) {
	
	Excite.qu.statsWindow.disabledButton = ''; // init to be disbabled button in Stats Home window
		$( "#miniStatsWindow" ).dialog({
			modal: true,			
			width: 410,
			autoOpen: false,
			close: function( event, ui ) {
				slider.data('anyslider').play(); 
			},
		});
	Excite.qu.ajaxGetMiniStats(questionId,questionText);
}