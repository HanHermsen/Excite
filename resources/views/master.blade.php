<!DOCTYPE html>
<html lang="en">

<head>

	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	
	<title>Yixow</title>
	
	<!-- Bootstrap Core CSS -->
	{!! HTML::style('css/bootstrap.min.css') !!}
	
	<!-- Custom CSS -->
	{!! HTML::style('css/style.css') !!}
	{!! HTML::style('css/sidebar.css') !!}

	{!! HTML::style('css/DataTables/jquery.dataTables.css') !!}
	{!! HTML::style('css/dataTables.responsive.css') !!}
	{!! HTML::style('jquery/jquery-ui.min.css') !!}
	
	@section('customStyleHead')
	@show
	
	{!! HTML::script('jquery/external/jquery/jquery.js') !!}
	{!! HTML::script('jquery/jquery-ui.min.js') !!}
	{!! HTML::script('js/DataTables/jquery.dataTables.min.js') !!}
	{!! HTML::script('js/dataTables.responsive.js') !!}
	{!! HTML::script('js/exciteShared.js') !!}
	
	@section('customScriptHead')       
	@show
	<?php
			$uid = Auth::user()->id;
			$userType = Excite\Models\CustomerDbModel::getUserType();
			echo "<script>Excite.userType = $userType; Excite.userId = $uid</script>\n";			
	?>
</head>
<body>
<script src="https://www.w3counter.com/tracker.js?id=101225"></script>			
<?php 
	$guestView = '';
	$groupView = '';
	$expressGui = false;
	if ($userType == Excite\Models\CustomerDbModel::LIGHT) {
		$guestView = $groupView = ' Disabled'; 
	} else {
		if( $userType == Excite\Models\CustomerDbModel::EXPRESS ) {
			$guestView = ' Disabled';
			$expressGui = true;
		}
	}
	if ( $expressGui ) {
		$hasExcite = Excite\Models\CustomerDbModel::checkExciteTrialContract();

		if(empty($hasExcite))
			$hasExcite = false;
		else
			$hasExcite = true;
	}
?>

<!-- Top Nav Bar -->
<nav class="navbar navbar-default">
  <div class="container-fluid"> 
  	
    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
	   <div class="Header-info">
	   <?php $h = gethostname() ;$d =$_SERVER['DOCUMENT_ROOT']; $s = $_SERVER['HTTP_HOST']; if ( strpos( $s, 'yixow.com') === false || strpos( $s, 'test.yixow') === 0 || strpos( $s, 'demo.yixow') === 0 || strpos( $s, 'prod.yixow') === 0 ) echo("$h $s $d<br />"); ?>
	   @if( ($expressGui && ! $hasExcite) )
	   		{!! HTML::link('#', 'Ontdek ook de uitgebreide mogelijkheden van Yixow eXcite, gratis een maand op proef.', array('id' => 'exciteTrial')) !!}
	   @endif
	   @if ( $userType == Excite\Models\CustomerDbModel::LIGHT )
	   Met een abonnement kun je meer uit Yixow halen. Op de website vind je meer informatie bij Abonnementen.
	   @endif
	   </div>
      <ul class="nav navbar-nav navbar-right">
        <li class="dropdown">
        
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><span class="icon-menu"><img src="/images/icon-user.png" /></span><span id='userEmailAddress'> {{ Auth::user()->email }} </span><span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href="/settings"><span class="glyphicon glyphicon-cog"></span> Instellingen</a></li>
			@if ($expressGui)
				<li><a href="/ego"><span class="glyphicon glyphicon-cog"></span> Abonnementen</a></li>
			@endif
            <li role="separator" class="divider"></li>
            <li><a href="/auth/logout"><span class="glyphicon glyphicon-log-out"></span> {{ trans('messages.uLogout') }}</a></li>
          </ul>
        </li>
      </ul>
    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>

<div id="wrapper">

    <!-- Sidebar -->
    <div id="sidebar-wrapper">
        <ul class="sidebar-nav">
            <li class="sidebar-brand">
            	<img src="/images/yixowlogo.png" />
				<?php
					if ($userType == Excite\Models\CustomerDbModel::LIGHT) {
						echo '<span class="menuLogoText">&nbsp;&nbsp;&nbsp;&nbsp;e<em>X</em>tra</span>';
					} elseif ($userType == Excite\Models\CustomerDbModel::EXPRESS) {
						echo '<span class="menuLogoText">&nbsp;&nbsp;&nbsp;&nbsp;e<em>X</em>press</span>';
					} else {
						echo '<span class="menuLogoText">&nbsp;&nbsp;&nbsp;&nbsp;e<em>X</em>cite</span>';
					}
				?>
            </li>
            <li>
                <a class="<?php if (Request::path() == "questions" || Request::path() == "/") {echo " SubMenuActive";} ?>" href="/questions"><span class="icon-menu"><img src="/images/icon-questions.png" /></span> {{ trans('messages.MenuQuestions') }}</a>
            </li>
            <li>
                <a class="<?php if (Request::path() == "guests") {echo " SubMenuActive";} echo $guestView; ?>" href="/guests"><span class="icon-menu"><img src="/images/icon-guests.png" /></span> {{ trans('messages.MenuGuests') }}</a>
            </li>
            <li>
                <a class="<?php if (Request::path() == "groups") {echo " SubMenuActive";} echo $groupView; ?>" href="/groups"><span class="icon-menu"><img src="/images/icon-groups.png" /></span> {{ trans('messages.MenuGroups') }}</a>
            </li>

        </ul>
    </div>
    <!-- /#sidebar-wrapper -->

    <!-- Page Content -->
    <div id="page-content-wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>
    <!-- /#page-content-wrapper -->

</div>
<!-- /#wrapper -->

<!-- Bootstrap Core JavaScript -->
<script src="/js/bootstrap.min.js"></script>

@if (count($errors) > 0)
	<div id="dialog-message">
		<ul>
		@foreach ($errors->all() as $error)
			<li>{{ $error }}</li>
		@endforeach
		</ul>
	</div>
@endif
<div id='js-dialog-message'>
<ul>
</ul>
</div>

</body>

</html>