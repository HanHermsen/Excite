@extends('master')

@section('content')

<style>
 .iCol {
    float:left;
    width: 200px;
    padding:5px;
 }
 .iHeader {
    font-weight: bold;
    height: 20px;
    background-color: #3F3F3F;
    color: #FFF;
 }
.iBreak {
    clear:both;
}
</style>

@if(!empty($invited))

    Openstaande uitnodigingen
    <hr />
    <div class="iCol iHeader">Group naam</div>
    <div class="iCol iHeader">Datum</div>
    <div class="iCol iHeader">Accepteren</div>
    <div class="iCol iHeader">Weigeren</div>
    <div class="iBreak"></div>

    @foreach($invited as $inv)
        <div class="iCol">{{$inv->name}}</div>
        <div class="iCol">{{$inv->created_at}}</div>
        <div class="iCol"><a href="/guests/invite/accept/{{$inv->id}}/{{$inv->group_id}}"><img src="../images/accept.png" /></a></div>
        <div class="iCol"><a href="/guests/invite/delete/{{$inv->id}}"><img src="../images/cancel.png" /></a></div>
        <div class="iBreak"></div>
    @endforeach

@else
    Geen openstaande uitnodigingen.
@endif

@stop