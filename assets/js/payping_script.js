(function($){
    $("a.ppd_download_reports").on('click', function(){
		$('#ppd_spinner').addClass('is-active');
		$(this).attr('disabled', 'disabled');
        // set the data
        var data = {
             action: 'ppd_reports_action',
             security: payping_wp_dashboard.nonce,
             type: $(this).data(),
			 amount: $("input#amount").val(),
			 payerPhoneNumber: $("input#payerPhoneNumber").val(),
			 payerName: $("input#payerName").val(),
			 cardNumber: $("input#cardNumber").val()
        }

        $.ajax({
            type: 'post',
            url: payping_wp_dashboard.ajaxurl,
            data: data,
            success: function(response) {
                //output the response on success
				$("a#ppd_download_file").attr("href", response);
				$('#ppd_spinner').removeClass('is-active');
				$('a.ppd_download_reports').removeAttr('disabled');
				document.getElementById('ppd_download_file').click();
            },
            error: function(err){
				$('#ppd_spinner').removeClass('is-active');
				$('a.ppd_download_reports').removeAttr('disabled');
            }
        });
        return false;
    });
})(jQuery);