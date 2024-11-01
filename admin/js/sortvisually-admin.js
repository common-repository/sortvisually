(function( $ ) {
	'use strict';

	$('.sortvisually_settings select.select2').select2()

	$('.copy_btn').on('click', function(e){
		e.preventDefault();
		var copyText = document.getElementById("request_url");
		/* Select the text field */
		copyText.select();
	  
		/* Copy the text inside the text field */
		document.execCommand("copy");
		$('.copy_info').fadeIn()

		setTimeout(function(){
			$('.copy_info').fadeOut()
		}, 3000)
	})
})( jQuery );