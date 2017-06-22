	<style>/*
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
			}*/
		</style>

	<script>
		Excite.qu.sliderGroupId = {{$groupId}};
		Excite.qu.sliderPaused = false;
		if (typeof Excite.qu.withAnswerByEmail === 'undefined' ) {
			Excite.qu.withAnswerByEmail = false;
		}
		Excite.qu.miniStatsOpts = {};

		if ( Excite.qu.withAnswerByEmail ) {
			Excite.qu.miniStatsOpts.emailAnswer = 1;
			Excite.qu.miniStatsOpts.showStats = 1;			
		} else {
			Excite.qu.miniStatsOpts.emailAnswer = 0;
			Excite.qu.miniStatsOpts.showStats = 1;				
		
		}
		Excite.qu.miniStatsOpts.groupId = Excite.qu.sliderGroupId;
		if ( Excite.qu.sliderGroupId && Excite.qu.withAnswerByEmail != 2) {
			Excite.qu.miniStatsOpts.showStats = 0;
		}

		var	slider = $('.slider').anyslider({
				animation: 'fade',
				interval: 6000,
				reverse: false,
				showControls: false,
				startSlide: 1
			});
		
		function pauseSlider() {
			Excite.qu.sliderPaused = true;
			slider.data('anyslider').pause();
		}

		function doStats(questionId, questionText, qImage) {
			Excite.qu.altMiniStatsUrl = 'https://www.yixow.com/questions/getMiniStatsHTML';
			$( "#miniStatsWindow" ).dialog({
				modal: true,
				//height: 295,
				width: 410,
				autoOpen: false,
				close: function( event, ui ) {
					slider.data('anyslider').play();
					Excite.qu.sliderPaused = false;
				}
			});
			//Excite.qu.ajaxGetMiniStats(questionId,questionText, false, Excite.qu.withAnswerByEmail);
			Excite.qu.miniStatsOpts.qImage = qImage; 
			Excite.qu.ajaxGetMiniStats(questionId,questionText, false, Excite.qu.miniStatsOpts);
		}
	</script>
	@if ( $groupId == 0 )
		<p>nieuwste vragen uit <b>{{$source}}</b></p>
	@else
		<p>vragen uit <b>{{$source}}</b></p>
	@endif
	<div class="slider">
		@foreach ($qu as $q)
		<div>
				<?php
					if ( $q->image != null )
						$qImage = 'https://www.yixow.com/api/api/images/' . $q->image;
					else
						$qImage = 'https://yixow.com/images/placeholder.png';
				
				?>
				@if ( $q->image != null )
					<div class="questionImage" style="background-image:url('https://www.yixow.com/api/api/images/{{$q->image}}')"></div>
				@else
					<div class="questionImage" style="background-image:url('{{ URL::to('images/placeholder.png') }}')"></div>
				@endif
			<h3>{{$q->question}}</h3>
			<p>{{$q->ago}}
			<!-- &nbsp;&nbsp;&nbsp;<span class='answerLink' onclick='doStats({{$q->id}}, "{{$q->question}}");pauseSlider()'><strong>[Beantwoorden]</strong></span> -->
			</p>
			<button class='browserStatsButton' onclick='doStats({{$q->id}}, "{{$q->question}}", "{{$qImage}}");pauseSlider()'>Bekijk statistieken</button>

		</div>
		@endforeach
	</div>
</div>
<script>
		if (Excite.qu.miniStatsOpts.groupId){
			if ( Excite.qu.miniStatsOpts.emailAnswer)
				$('.browserStatsButton').html('Bekijk antwoord opties');
			else
				$('.browserStatsButton').html('Bekijk antwoorden');
		} else if ( Excite.qu.miniStatsOpts.emailAnswer )
			$('.browserStatsButton').html('Bekijk statistieken; Geef antwoord');
</script>
