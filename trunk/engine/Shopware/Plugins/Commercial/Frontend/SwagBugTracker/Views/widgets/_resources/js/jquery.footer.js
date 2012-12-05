jQuery.noConflict();
(function($) {

	$(document).ready(function() {
		$.shopwareTwitter({
			'username': 'shopware_AG',
			'maxPosts': 4,
			'container': '#twitter_widget .inner_container',
			'scrollSpeed': 500,
			'interval': 8000,
			'height': 54
		});
	});

	var config = {
		'username': 'shopware_AG',
		'maxPosts': 4,
		'container': '.social_footer .right_footer .twitter .inner_container',
		'scrollSpeed': 500,
		'interval': 8000,
		'height': 65
	};

	/**
	 * Receives the tweets of a given user
	 *
	 * param:  {obj} settings - user settings
	 */
	$.shopwareTwitter = function(settings) {
		// extend user settings
		if (settings) {
			$.extend(config, settings)
		}

		var url = "https://twitter.com/status/user_timeline/" + config.username + ".json?count=" + config.maxPosts + "&callback=?";

		// get json string from twitter and call the callback function
		$.getJSON(url, function(data) {
			var container = $(config.container);
			container.hide();
			$.each(data, function(i, el) {
				var div = $('<div>', {
					'class': 'tweet',
					'html': $.formatTwitString(el.text)
				}).appendTo(container);
			});
			container.fadeIn();

			var currentTweet = 1;
			window.setInterval(function() {
				container.animate({
					'top': '-' + (config.height * currentTweet)
				}, config.scrollSpeed, function() {
					currentTweet++
					(currentTweet == config.maxPosts) ? currentTweet = 0 : currentTweet = currentTweet;
				});
			}, config.interval);
		});
	};

	/**
	 * Formats a tweet string
	 *
	 * @param: {string} str - Tweet
	 * @return: {string} - formated string
	 */
	$.formatTwitString = function(str) {
		str = ' ' + str;
		str = str.replace(/((ftp|https?):\/\/([-\w\.]+)+(:\d+)?(\/([\w/_\.]*(\?\S+)?)?)?)/gm, '<a href="$1" target="_blank">$1</a>');
		str = str.replace(/([^\w])\@([\w\-]+)/gm, '$1@<a href="https://twitter.com/$2" target="_blank">$2</a>');
		str = str.replace(/([^\w])\#([\w\-]+)/gm, '$1<a href="https://twitter.com/search?q=%23$2" target="_blank">#$2</a>');
		return str;
	};

})(jQuery);