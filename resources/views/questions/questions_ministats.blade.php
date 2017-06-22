 <!-- <script>
 if ( Excite.qu.newStats ) {
	//console.log("INCLUDE");
	if ( ! Excite.qu.googleChartsLoaded )
		$.getScript("https://www.gstatic.com/charts/loader.js");
} else {
	$.getScript("https://www.google.com/jsapi");
}
 </script> -->
<!-- <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script> -->
<!-- old style still in use -->
<script type="text/javascript" src="https://www.google.com/jsapi"></script>

<style>
	.charts-tooltip > div {
		position: relative;
		z-index: 10000;
	}
	/* for new version */
	.goog-tooltip > div {
		position: relative;
		z-index: 10000;
	}
	#chartNL path {
		stroke-width: 1; /* control the countries borders width */
		stroke: white; /* choose a color for the border */
	}
	#pieChartContainer , #chartNLContainer { // for size settings
		box-sizing: border-box;
		/* needed as long as this 'new' standard is not broadly supported as such */
		-webkit-box-sizing: border-box;
		-moz-box-sizing: border-box;
	}
	.flexContainer {
		display: flex;
		flex-direction: row;
		flex-wrap: wrap;
		/*border-style: solid;
		border-color: green;
		border-width: 5px; */
	}
	/* voor de kinderen */
	.flexBox {
	/*
		border-style: solid;
		border-color: red;
		border-width: 2px;
		*/
	}
	#pieChartContainer {
		overflow: hidden;
		width: 400px;
	}
	#chartNLContainer {
		margin: auto;
		overflow: hidden;
		width: 240px;
		height: 210px;
	}
	
	#pieChartContainer {
		margin: auto;
		height: 240px;
	}

	#chartTitle {
		display:block;
		font-weight: bold;
		left-margin: 10px;
	}

	.tooltipLine, .tooltipFirst {
		font-size: 8pt;
	}
	.tooltipFirst {
		font-weight: bold;
	}
	h3 {
		padding: 0px;
		margin: 0px;
	}

	#sendQbyEmailB {
		background-color: #FF6C2A;
		height: 30px;
		width: 150px;
		text-transform: none;
		margin-top: 5px;
	}
	#sendQbyEmailBtext {
		color: white;
		font-style: normal;
	}
@if ( isset($reqData['options']['showStats']) &&  ! $reqData['options']['showStats'] )
	#miniStatsWindow {
		line-height: 20px;
		font-size: 10pt;
	}
	#miniStatsWindow input {
		display: inline; /*needed for anl Wordpress*/
		padding: 0px 0px;
	}
	#miniStatsWindow button {
		padding-top: 5px;  /*needed for anl Wordpress */
	}
@endif
	
</style>
@if ( ! isset($reqData['options']['showStats']) || $reqData['options']['showStats'] )
<script>
	Excite.miniStats = true;
	// data is server side generated as Javascript object literal
	Excite.qu.userProfile = {any: false, user: 0, contact: 1,};

	Excite.qu.pieData = {!!$statsData->getPieData()!!};
	Excite.qu.allGivenAnswerCount = {!!$statsData->getAllGivenAnswerCount()!!};
	Excite.qu.absLimit = 100000; //hack: always percentages in Pie
	// draw the stuff
	Excite.qu.googleCharts();
	//Excite.qu.geoProvData = {$statsData->getGeoProvData()};
	//Excite.qu.provMap(); // show this map

</script>
@endif
<!--
<h3 id='chartsTitle'>{{$reqData['questionText']}}</h3> -->
	<!-- Chart divs -->
<div class='flexContainer'>
@if ( isset($reqData['options']['showStats']) &&  ! $reqData['options']['showStats'] )
<div class='flexbox'>
	<ul>
	@foreach ($answerOptions as $opt)
		<li>{{$opt->text}}</li>
	@endforeach
	</ul>
</div>
@else
	<div class= 'flexBox' id='pieChartContainer'>
		<div id="pieChart"></div>
	</div>
@endif
	<!--
		<div class= 'flexBox' id='chartNLContainer'>
			<div id="chartNL"></div>
		</div> -->
</div> <!-- end flexContainer -->
<!-- if ( isset($reqData['withAnswerOption']) &&  $reqData['withAnswerOption'] ) -->
@if ( isset($reqData['options']['emailAnswer']) && $reqData['options']['emailAnswer'] )
	@if ( isset($reqData['options']['groupId']) && $reqData['options']['groupId'] == 0)
	Je kunt deze vraag via email beantwoorden als je dat niet eerder deed.
	@else
	<strong>Ik wil deze vraag beantwoorden.<br>
	Stuur een uitnodiging om mee te doen.</strong>
	@endif
<br /><br />
Mijn email address: <input id='emailAddr' type=text />
<button id='sendQbyEmailB'><span id='sendQbyEmailBtext'>Verstuur</span></button>
<script>
	Excite.qu.emailQuestionId = {{$reqData['questionId']}};
	$('#sendQbyEmailB').on('click', function(e) {
		e.preventDefault();
		var thisBut = $(this);
		thisBut.attr("disabled", true);
		var sorry = "<li>Sorry: in development</li>";

		errMsg = '';
		ea = $('#emailAddr').val().trim();
		if (ea.length == 0) {
			$('#emailAddr').val('');
			errMsg += '<li>Vul email address in</li>';
		}
		else {
			if (! Excite.isValidEmailAddress(ea)) {
				errMsg += '<li>Email address niet goed</li>'
			};
		}
		if ( errMsg == '' ) {
			url = "https://www.yixow.com/qbrowser";			
			var get = $.get( url, {
				option: 'qByEmail',
				email: ea,
				questionId: Excite.qu.emailQuestionId,
				sliderGroupId: Excite.qu.sliderGroupId,
				qImage: '{{$reqData['options']['qImage']}}',
			});
			get.done(function( data, textStatus,jqXHR ) {
				if ( data == null ) { // TODO
					alert("Bel de brandweer, er is een probleem.");
					thisBut.removeAttr("disabled");
					return;
				}
				error='';
				if(! data.ok)
					error= 'Overwachte fout: ';
				
				Excite.dialogAlert("<li>" + error + data.msg + '</li>');
				thisBut.removeAttr("disabled");
				$('#miniStatsWindow').dialog('close');
			});
		}
		else {
			Excite.dialogAlert(errMsg);
			thisBut.removeAttr("disabled");
		}
	});
</script>
@if (false)
<br />
		<div id='videoPlayerContainer' class='flexBox'>
		<video controls  width='100%'>
			   <source src="https:/www.yixow.com/video/King Crimson Elephant Talk.mp4" type="video/mp4">
			   <!-- Firefox does not support mp4; it autoselects the .webm variant that follows here
					conversion of the mp4 src is done by VLC Media Player on Win 7 with default settings-->
			  <!-- <source src="/video/MaartenBiesheuvel.webm" type="video/webm"> -->
				<h2>Your browser does not support the video tag.</h2>
		</video>
		</div>
@endif
@else
<div style='font-size: 10pt;line-height: 18px;margin-bottom: 5px'>Je kunt deze vraag beantwoorden met de Yixow App op een Iphone of een Android telefoon.</div>
	<a href="https://itunes.apple.com/nl/app/yixow-open-in-opinie.-zelf/id999394569?mt=8"><img alt="Download in App Store" src="https://www.yixow.com/images/appstore_ios.svg" style='width: 100px'></a>
	<a href="https://play.google.com/store/apps/details?id=nl.montanamedia.yixow&amp;hl=nl"><img alt="Download in Google Play" src="https://www.yixow.com/images/google-play-badge.png" style='width: 100px'></a>
@endif



		