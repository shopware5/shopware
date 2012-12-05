<div class="bonus_system_slider"><!-- Filled by a jQuery plugin --></div>

<script type="text/javascript">
(function($) {
	$(document).ready(function() {
		$('.bonus_system_slider').ajaxSlider('ajax', {
			'url': unescape('{"{url controller=BonusSystem action=slider perPage=$BonusSliderPerPage forceSecure} "|escape:url}'),
			'title': '{s namespace="frontend/bonus_system/recommendation" name="BonusSystemSliderHeadline"}Bonusartikel{/s}',
			'headline': true,
			'navigation': false,
			'scrollSpeed': 800,
			'rotate': false,
			'containerCSS': { 'marginTop': '12px', 'marginBottom': '15px' }
		});
	});
})(jQuery);
</script>
