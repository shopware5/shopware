
/**
 * This function handles the basket voucher slider
 * It updated the interface to display the current conversion and set the new values into the input fields
 */
(function($) {
	$(document).ready(function() {
		var slider = $('.basket_slider'),
			max = $('#slider_max').val(),
			currency = $('.current_conversion'),
			points = $( "#voucher_points"),
			value = $( "#voucher_value");

		$( ".slider" ).slider({
			value: 1,
			min: 1,
			max: max,
			step: 1,
			slide: function( event, ui ) {

				//calcualte the new value and format the currency
				var newValue = ui.value / slider.find('#conversion_factor').val(),
				    curr = str_replace("0,00" , newValue.toFixed(2),  slider.find('#currency_display').val());

				//hide the info text
				slider.find('.slider-info-top, .slider-info-bottom').hide();

				//set the new values into the display and input fields
				currency.html(ui.value + 'P. / ' + curr);
				points.val(ui.value);
				value.val(newValue.toFixed(2));
			}
		});

	});

	/**
	 * Helper function to find and replace a string
	 * @param search
	 * @param replace
	 * @param subject
	 */
	function str_replace(search, replace, subject) {
		return subject.split(search).join(replace);
	}

})(jQuery);