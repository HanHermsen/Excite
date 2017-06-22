		<meta charset="utf-8">
		<meta name="description" content="" />
		<meta name="keywords" content="" />
		<meta name="robots" content="all" />
		<meta name="author" content="YixBow" />
		<meta name="dcterms.rights" content="Copyright &copy; 2016 Yixbow BV" />
		<meta name="dc.language" content="nl" />
		<meta name="revisit-after" content="7" />
		<meta name="rating" content="general" />
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0" />
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
		{!! HTML::script('js/jquery.equalheights.min.js') !!}	
		{!! HTML::script('js/jquery.anyslider.js') !!}	
		<script language="javascript" type="text/javascript">
		
			var slider;
			$(window).load(function() {
				$("#menu_button").click( function(event){
					$("#menu").toggle();
				});
				if ($("#slogan").css("display") != "none" ){
					$('.column').equalHeights();
					$('.equalHeights').equalHeights();					
				};
				
				slider = $('.slider').anyslider({
		            animation: 'fade',
		            interval: 6000,
		            reverse: false,
		            showControls: false,
		            startSlide: 1
		        });
			});
			
			$(window).resize(function(){
				if ($("#slogan").css("display") != "none" ){
					$('.column').height('auto');
					$('.column').equalHeights();
					$('.equalHeights').height('auto');
					$('.equalHeights').equalHeights();
				};
			});	
			
			function pauseSlider() {
				slider.data('anyslider').pause();
			}
			$(document).ready(function(){
				/* Portal fix for login IE; works everywhere */	
				$('#logininputs').find('input').keypress(function (e) {
					form = $('#login').find('form');
					if( e.which === 13) {
					  form.submit();
					}
				});
			});
			
		</script>
		{!! HTML::style('css/portal.css') !!}
		{!! HTML::style('jquery/jquery-ui.min.css') !!}
		
		<style>
			.ui-widget-header {
			    background: none !important;
			    border: none !important;
			}
			.ui-dialog { 
				z-index: 1001 !important ;
				font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
				font-weight: 200 !important;				
			}
			.ui-state-default {
			    background: none!important;
			}
			.ui-button-text-only {
				margin: 0 !important;
				padding: 0 !important;
			}
			.ui-dialog-buttonset button {
				background-color: #e1007a !important;
				color: #ffffff !important;
				border:0px !important;
				height: 24px !important;
				margin: 0 !important;
				padding: 0 !important;
				font-size: 10pt !important;
				font-weight: 200 !important;
			}
			.ui-button-text {
				padding:0px !important;
			}
		</style>
		
		<title>Yixow - @yield('title')</title>