@extends('master')

@section('content')
<style type="text/css">
	input#msg {
		width: 750px !important;
	}
</style>
{!! Form::open(array('action' => array('testController@push'))) !!}

Groups ID <br />{!! Form::text('pushId') !!}
<br /><br />
MSG<br /> {!! Form::text('pushMsg',null,array('id' => 'msg')) !!}
<br /><br /><br />
{!! Form::submit('Verstuur') !!}

{!! Form::close() !!}

@stop