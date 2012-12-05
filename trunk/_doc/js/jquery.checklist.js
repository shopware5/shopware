(function($) {
	
	$(document).ready(function() {
		$('.checkbox').parents('li').attr('data-done', 'false');
		$('.checkbox').bind('click', function(event) {
			event.preventDefault();
		
			var $this = $(this),
				parent = $this.parents('li'),
				checked = parent.attr('data-done');
				
			if(checked == 'true') {
				parent.attr('data-done', 'false');
				parent.removeClass('checked');
				parent.find('pre').slideDown('fast');
			} else {
				parent.attr('data-done', 'true');
				parent.addClass('checked');
				parent.find('pre').slideUp('fast');
			}
		})
	});

})(jQuery);