
<style>
	/* tooltip visibility */
	.charts-tooltip > div {
		position: relative;
		z-index: 10000;
	}
	.goog-tooltip > div {
		position: relative;
		z-index: 10000;
	}
	
	#chartNL path {
		stroke-width: 1; /* control the countries borders width */
		stroke: white; /* choose a color for the border */
	}
	#pieChartContainer , #statsButtonContainer, #answerBarChartContainer, #categoryBarChartContainer { // for size settings
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
	#pieChartContainer , #statsButtonContainer {
		/*overflow: hidden;*/
		width: 400px;
	}
	#answerBarChartContainer, #categoryBarChartContainer {
		overflow: hidden;
		width: 500px;
	}
	
	#pieChartContainer , #categoryBarChartContainer {
		height: 450px;
	}
	#statsButtonContainer, #answerBarChartContainer {
		height: 250px;
	}
	
	#statsButtonSet {
		display: block;
		margin-top: 10px;
		margin-left: 25px;
		margin-bottom: 20px;
	}
	#chartTitle {
		display:block;
		font-weight: bold;
		left-margin: 10px;
	}

	button.statsButton {
		margin: 3px;
		padding: 2px;
		border: 0px;
		width: 110px;
	}
	.statsButton:disabled {
		background-color: #FF7020;
		/*color: black; */
	}
	.forbiddenButton {
		background-color: #C0C0C0;
	}
	button.statsMapButton {	
		/* Yixow orange background-color: #FF6C2A; */
	}
	button.statsRefreshButton {	
		background-color: DarkGreen;
	}	
	.tooltipLine, .tooltipFirst {
		font-size: 8pt;
	}
	.tooltipFirst {
		font-weight: bold;
	}
	h3 {
		font-size: 16pt;
		/*font-weight: bold;*/
		margin-top: 10px;
		padding-top: 0px;
		border-top: 0px;
		margin-bottom: 20px;
	}
</style>
<script>
	// data is server side generated as Javascript object literal
	Excite.qu.userProfile = {!!$statsData->getUserProfileData()!!};

	{!!$statsData->getAllData()!!}
	
	Excite.qu.absLimit = {!!$statsData::ABS_LIMIT!!};
	if (typeof Excite.qu.emailAnswerCount !== 'undefined' ) { // new stats test
		delete Excite.qu.emailAnswerCount; // for other clicks to start afresh
	} else {
		$('#notSharedBarTitle').html('');
		$('#catBarSuffix').html('met scores per Antwoord');
	}
	// draw the stuff
	Excite.qu.googleCharts();
</script>

<h3 id='chartsTitle'>{{$reqData['questionText']}} - {{$reqData['statsType']}} - {{$reqData['debug']}}</h3>
	<!-- Chart divs and Buttons -->
<div class='flexContainer'>
	<div class= 'flexBox' id='pieChartContainer' style='position: relative'>
		<div id='chartTitle'><strong>&nbsp;&nbsp;&nbsp;&nbsp;Alle antwoorden;</strong> Totaal: {!!$statsData->getAllGivenAnswerCount()!!}</div>
		<!-- <span id="pieResult">
			&nbsp;&nbsp;&nbsp;&nbsp;Totaal: {!!$statsData->getAllGivenAnswerCount()!!} </span>
		<p></p> -->
		<div id="pieChart" style="padding-left: 10px"></div>
		<div id="bar2Container" style="position: absolute; bottom: 26px;">
			<div id='notSharedBarTitle' class='chartTitle'  style="padding-top: 5px"><strong>&nbsp;&nbsp;&nbsp;&nbsp;{{$reqData['statsType']}} respondent: onbekend</strong></div>
			<div id="catBarChart2"></div>
		</div>
	</div>
	<div class='flexBox' id="categoryBarChartContainer">
		<div id='catBarChartTitle'><strong>&nbsp;&nbsp;&nbsp;&nbsp;{{$reqData['statsType']}} <span id='catBarSuffix'>respondent: bekend</span></strong></div>
		<div id="categoryBarChart"></div>
	</div>
	<div class='flexBox' id='statsButtonContainer'>
		<div  id='chartTitle'><strong>&nbsp;&nbsp;&nbsp;&nbsp;Hoe zijn antwoorden verdeeld over</strong></div>
		<div id='statsButtonSet'>
		@foreach (['Geslacht', 'Leeftijd','Sterrenbeeld'] as $statsType)
			<button id='{{$statsType}}' class='statsButton' onclick="Excite.qu.statsButtonHandler({{$reqData['questionId']}},'{!! $reqData['argQuestionText'] !!}','{{$statsType}}')">{{$statsType}}</button>
		@endforeach
		<br />
		@foreach (['Opleiding', 'Studierichting', 'Titel'] as $statsType)
			<button id='{{$statsType}}' class='statsButton' onclick="Excite.qu.statsButtonHandler({{$reqData['questionId']}},'{!!$reqData['argQuestionText']!!}','{{$statsType}}')">{{$statsType}}</button>
		@endforeach
		<br />
		@foreach (['Werksector', 'Expertise', 'Inkomen'] as $statsType)
			<button id='{{$statsType}}' class='statsButton' onclick="Excite.qu.statsButtonHandler({{$reqData['questionId']}},'{!!$reqData['argQuestionText']!!}','{{$statsType}}')">{{$statsType}}</button>
		@endforeach
		<br />
		@foreach (['Transport', 'Woonsituatie', 'Gezin'] as $statsType)
			<button id='{{$statsType}}' class='statsButton' onclick="Excite.qu.statsButtonHandler({{$reqData['questionId']}},'{!!$reqData['argQuestionText']!!}','{{$statsType}}')">{{$statsType}}</button>
		@endforeach
		<br />
		@foreach (['Geloof', 'Gezondheid', 'Geluk'] as $statsType)
			<button id='{{$statsType}}' class='statsButton' onclick="Excite.qu.statsButtonHandler({{$reqData['questionId']}},'{!!$reqData['argQuestionText']!!}','{{$statsType}}')">{{$statsType}}</button>
		@endforeach
		<br />
		@foreach (['Provincie'] as $statsType)
			<button id='{{$statsType}}' class='statsButton' onclick="Excite.qu.statsButtonHandler({{$reqData['questionId']}},'{!!$reqData['argQuestionText']!!}','{{$statsType}}')">{{$statsType}}</button>
		@endforeach
		<button id='Kaart' class='statsButton statsMapButton' onclick="Excite.qu.statsButtonHandler({{$reqData['questionId']}},'{!!$reqData['argQuestionText']!!}','Postcode', 'Geo', '{{$reqData['statsType']}}')">Kaart</button>
		<!--
		<button id='Refresh' class='statsButton statsRefreshButton' onclick="Excite.qu.ajaxGetStats({{$reqData['questionId']}},'{{$reqData['argQuestionText']}}','{{$reqData['statsType']}}')">Refresh</button> -->
		</div>
	</div>
	<div class='flexBox' id="answerBarChartContainer">
		<div id='chartTitle'><strong>&nbsp;&nbsp;&nbsp;&nbsp;Antwoorden met scores per {{$reqData['statsType']}}</strong></div>
		<div id="answerBarChart"></div>
	</div>
</div> <!-- end flexContainer -->
				

		