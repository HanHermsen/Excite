@extends('master')

@section("customStyleHead")
	<link rel="stylesheet" href="css/settings.css" />
@stop

@section('content')
{!! Form::open(array('url' => '/settings','method' => 'post','class' => 'centered')) !!}
	{!! csrf_field() !!}
<div class="container-fixed">
    <div class="row">
        <div class="col-md-4">

        	{!! Form::label(trans('messages.RegisterCompany')) !!}
			{!! Form::text('company', (isset($viewSettings->compname) ? $viewSettings->compname : '') ,array('size' => '20','disabled' => true)) !!}

			{!! Form::label(trans('messages.RegisterKvk')) !!}
	    	{!! Form::text('kvk', (isset($viewSettings->coc) ? $viewSettings->coc : '') ,array('size' => '20','disabled' => true)) !!}

	    	{!! Form::label(trans('messages.RegisterPhone')) !!}
			{!! Form::text('phone', (isset($viewSettings->teloffice) ? $viewSettings->teloffice : '') ,array('size' => '20')) !!}

			{!! Form::label(trans('messages.RegisterEmail')) !!}
			{!! Form::text('email', (isset($viewSettings->email) ? $viewSettings->email : '') ,array('size' => '20')) !!}
		</div>
        <div class="col-md-4">
        	{!! Form::label(trans('messages.RegisterFirstname')) !!}
		    {!! Form::text('firstname', (isset($viewSettings->firstname) ? $viewSettings->firstname : '') ,array('size' => '20')) !!}

			{!! Form::label(trans('messages.RegisterLastname')) !!}
		    {!! Form::text('lastname', (isset($viewSettings->surname) ? $viewSettings->surname : '') ,array('size' => '20')) !!}

		    {!! Form::label(trans('messages.RegisterDisplayname')) !!}
		    {!! Form::text('displayname', (isset($viewSettings->display_name) ? $viewSettings->display_name : '') ,array('size' => '20')) !!}
			<br />
			{!! Form::reset('Annuleer', ['class' => 'cancelBtn']) !!}
			{!! Form::submit(trans('messages.NewGroupSubmit'), ['class' => 'submitBtn']) !!}	
			{!! Form::close() !!}	
		</div>

    </div>
    
 <hr />
	{!! Form::open(array('url' => '/settings/changepwd','method' => 'post')) !!} 
	{!! csrf_field() !!}
    <div class="row">
        <div class="col-md-4">

        	{!! Form::label(trans('messages.ChangePassword')) !!}
			{!! trans('messages.RegisterPassword') !!}
			{!! Form::password('password') !!}
			<br />
			{!! trans('messages.RegisterPasswordConfirm') !!}
			{!! Form::password('password_confirmation') !!}

		</div>
		<div class="col-md-4 marginFix">
			{!! Form::reset('Annuleer', ['class' => 'cancelBtn']) !!}
			{!! Form::submit(trans('messages.NewGroupSubmit'), ['class' => 'submitBtn']) !!}	
			{!! Form::close() !!}		
		</div>

    </div>
</div>

@stop