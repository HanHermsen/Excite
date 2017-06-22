$(document).ready(function(){
	// any answer selected? 
	
	var selectedOptionId = "";
	
	$("#submitB").on( 'click', function(event) {
		event.preventDefault();
		
		if (selectedOptionId == "") {
			alert("Kies een antwoord");
			return;
		}
		
		// Form submit here
		$("#submitB").prop('disabled', true);
		
		var csrfToken = $("[name='_token']").val();
		var token = $("[name='hiddenEmailLinkOption']").val();
		var jqxhr = $.post( "/question-by-email", { hiddenEmailLinkOption: token, selectedOptionId: selectedOptionId, _token: csrfToken }, function( data ) {
			if (data.error != "") {
				// error found
				alert( data.error );
			}
			else {
				// show statistics
				var form = $("form[name=\"question_by_email_form\"]")
				form.hide();
				var totalAnswers = 0;
				$.each(data.statistics, function( index, value ) {
					totalAnswers += data.statistics[index].amount;
				});
				$.each(data.statistics, function( index, value ) {
					//alert( index + ": " + value );
					var answer = data.statistics[index];
					var percentage = Math.round(answer.amount / totalAnswers *100);
					var myAnswerClass = "";
					if (parseInt(answer.id) == parseInt(selectedOptionId)) {
						myAnswerClass = " my_answer";
					}
					
					$( "#question_by_email_inner" ).append( '<div class="answer_row'+myAnswerClass+'"><div class="answer_circle"><p>'+percentage+'&#37;</p></div><div class="answer_bar_container"><p class="answer_text">'+answer.text+'</p><div class="answer_bar" style="width:'+percentage+'%;"></div></div></div>' );
				});
						
			}			
		}, "json")
		.fail(function() {
			alert( "Er ging iets mis bij het beantwoorden van de vraag, probeer het later nog eens." );
			$("#submitB").prop('disabled', false);
		});
		
	});
	$("#submitB").prop('disabled', true);
	
	$(".radioB").on( 'click', function(event) {
		selectedOptionId = $(this).attr("data-id");
			
		// deselect all buttons
		$(".radioB").each( function() {
			$(this).removeClass( "selectedOption" );
		});
		
		// select this button
		$(this).addClass( "selectedOption" );
		
		$("#submitB").prop('disabled', false);
	});
});