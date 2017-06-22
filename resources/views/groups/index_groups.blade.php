@extends('master')

@section("customStyleHead")
<style>
	#openMap {
		position: absolute;
		height: 250px; /*660*/
		width: 200px;
		left: 0px;
		top: 45px;
	}
</style>
    <link rel="stylesheet" type="text/css" href="/css/groups.css" />
	<link rel="stylesheet" type="text/css" href="/css/leaflet.css" />
	<link rel="stylesheet" type="text/css" href="/css/farbtastic.css"  />
@stop

@section("customScriptHead")
    <script src="/js/groups.js"></script>
	<script src="/js/leaflet.js"></script>
	<script src="/js/farbtastic.js"></script>
@stop

@section('content')

<div id='exciteFormContainer'> <!-- deze container wordt gebruikt voor Ajax HTML view file refreshes van het bovenste deel van de Page -->
@include('groups.form_groups')
</div>
<hr />
<table width="100%" class="display responsive" id="groupsDataTable" cellspacing="0">
	<thead>
		<tr>
			<th>Groep</th>
			<th>{{ trans('messages.gTableDateIn') }}</th>
			<th>Gasten</th>
			<th>Vragen</th>
			<th>Response</th>
			<th>A</th>
			<th>{{ trans('messages.gTableDateOut') }}</th>
		</tr>
	</thead>
<tbody>

</tbody>
</table>

@stop