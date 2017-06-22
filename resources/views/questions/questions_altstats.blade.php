
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
	
	#pieChartContainer {
		width: 100%;
		align: center;
	}
	#chartsTitle {
		display:block;
		font-weight: bold;
		font-size: 24px;
		left-margin: 10px;
	}
	.tooltipLine, .tooltipFirst {
		font-size: 8pt;
	}
	.tooltipFirst {
		font-weight: bold;
	}
</style>
<script>
	// data is server side generated as Javascript object literal
	Excite.qu.userProfile = {!!$statsData->getUserProfileData()!!};
	{!!$statsData->getAllData()!!}
	Excite.qu.absLimit = {!!$statsData::ABS_LIMIT!!};

	// draw the stuff
	Excite.qu.googleCharts( '{{$reqData['altTemplate']}}' );
</script>

<h3 id='chartsTitle'>{{$reqData['questionText']}}</h3>
<button class='altRefreshButton' onclick="Excite.qu.altRefreshButtonHandler({{$reqData['questionId']}},'{{$reqData['argQuestionText']}}')">Refresh</button>
<div id='pieChartContainer'>
		<div id="pieChart"></div>
</div>
<div id='altBarChartContainer'>
		<div id="altBarChart"></div>
</div>
		