(function($) {
	$(document).ready(function() {
		$('.account .password input, .account .email input,').each(function() { var $me = $(this); $me.unbind('keyup').unbind('blur'); });
	});
})(jQuery);



/**
 * AJAX Validation
 * for Shopware
 *
 * Shopware AG (c) 2011
 */
(function($) {

	/** Plugin starter */
	$(document).ready(function() {
		$('.account .password :input').accountValidation();
		$('.account .email input').accountValidation();
	});

	/**
	 * Shopware UI - Account validation
	 *
	 * This jQuery plugin checks the given
	 * mail address and the given password
	 * against our criterions. Additionally we're
	 * checking the mail address against the server side
	 * to avoid account hijacking
	 *
	 * Example usage:
	 * $('[selector]').accountValidation([settings]);
	 *
	 * @param {obj} settings - user settings
	 * @return {obj} jQuery object basend on the given selector
	 */
	$.fn.accountValidation = function(settings) {

		/** Extend the default configuration with the provided user settings */
		if(settings) $.extend($.accountValidation.config, settings);

		/** Return this for jQuery's chaining support */
		return this.each(function() {
			var $me = $(this);

			/** Disable the submit button */
			if(!$.browser.msie && parseInt($.browser.version) != 6) {
				$me.parents('form').find('input[type=submit]').attr('disabled', 'disabled').css('opacity', 0.5);
			}

			/** Event listener which checks on every keystroke the password and it's iteration */
			$me.bind('keyup', function() {
				if($me.attr('id') == 'newpwdrepeat' && $me.val().length == $('#newpwd').val().length) {
					$me.triggerHandler('blur');
				}
				if($me.attr('id') == 'newmailrepeat' && $me.val().length == $('#newmail').val().length) {
					$me.triggerHandler('blur');
				}
			});

			/** Event listener which checks the given mail addresses or the given password against the server side */
			$me.bind('blur', function() {
				var error = false;

				if(!$me.val().length) { error = true; }

				if(($me.attr('id') == 'newpwd' || $me.attr('id') == 'newpwdrepeat') && !error) {
					$.accountValidation.checkPasswd($me);
				}

				if(($me.attr('id') == 'neweailrepeat' || $me.attr('id') == 'newmail') && !error) {
					$.accountValidation.checkEmail($me);
				}
			});
		});
	};

	$.accountValidation = {

		/** Default configuration */
		config: {
			errorCls: 'instyle_error',
			successCls: 'instyle_success'
		},

		/**
		 * Simple method which sets the configured error class to the given element
		 *
		 * @param {obj} $el - jQuery object of the element which will become invalid
		 * @return {obj} $el - jQuery object of the passed element
		 */
		setError: function($el) {
			$el.removeClass($.accountValidation.config.successCls).addClass($.accountValidation.config.errorCls);

			return $el;
		},

		/**
		 * Simple method which sets the configured success class to the given element
		 *
		 * @param {obj} $el - jQuery object of the element which will become valid
		 * @return {obj} $el - jQuery object of the passed element
		 */
		setSuccess: function($el) {
			$el.removeClass($.accountValidation.config.errorCls).addClass($.accountValidation.config.successCls);

			return $el;
		},

		/**
		 * Validates the password
		 *
		 * @param {obj} object of the repeat field
		 * @return void
		 */
		checkPasswd: function($repeat) {
			var $form = $repeat.parents('form');

			var str = '';

			$form.find('input[type=password]').each(function(i, el) {
				var $el = $(el), name = $el.attr('name');

				if(str.length) { str += '&'; }
				str += 'register[personal][' + name + ']='+$el.val();
			});

			str = encodeURI(str);

			$.accountValidation.ajaxValidation('ajax_validate_password', str, $form);
		},

		/**
		 * Validates the email address
		 *
		 * @param {obj} object of the repeat field
		 * @return void
		 */
		checkEmail: function($repeat) {
			var $form = $repeat.parents('form');

			var str = '';
			$form.find('input[type=text]').each(function(i, el) {
				var $el = $(el), name = $el.attr('name');

				if(str.length) { str += '&'; }
				str += 'register[personal][' + name + ']='+$el.val();
			});

			str = encodeURI(str);

			$.accountValidation.ajaxValidation('ajax_validate_email', str, $form);
		},

		/**
		 * Validates the given elements against the server side
		 * and determines on the base of the request response
		 * if the given elements are valid or invalid.
		 *
		 * @param {str} action - the action which will be called server side
		 * @param {str} data - the data string which will be send to the server
		 * @param {obj} $form - jQuery object of the form
		 * @return void
		 */
		ajaxValidation: function(action, data, $form) {

			$.ajax({
				'data': 'action=' + action + '&' + data,
				'type': 'post',
				'dataType': 'json',
				'url': $.controller.ajax_validate,
				'success': function(result) {

					$.each(result.error_flags, function(key, val) {
						if(val) {
							$.accountValidation.setError($('input[name=' + key + ']'));
						} else {
							$.accountValidation.setSuccess($('input[name=' + key + ']'));
						}
					});

					if(!result.success) {

						if(!$.isEmptyObject(result.error_flags)) {

							$(document.body).find('#ajax-validate-error').remove();

							// Get first element in form
							var first = $form.find('input:first');

							// Output error message
							var err = $('<div>', {
								'class': 'error',
								'id': 'ajax-validate-error',
								'html': result.error_messages[0],
								'css': {
									'display': 'none',
									'position': 'absolute',
									'top': first.offset().top,
									'left': first.offset().left + first.outerWidth() + 30,
									'width': 200,
									'zIndex': 100
								}
							}).prependTo($(document.body)).fadeIn('fast');

							window.setTimeout(function() {
								err.remove();
							}, 4000);

							// Check for IE6 to prevent a displaying issue
							if(!$.browser.msie && parseInt($.browser.version) != 6) {
								$form.find(':submit').attr('disabled', 'disabled').css('opacity', 0.5);
							}
						}
					}  else {

						$form.find('input[type=password], input[type=text]').each(function(i, el) {
							$(document.body).find('#ajax-validate-error').remove();

							// Check for IE6 to prevent a displaying issue
							if(!$.browser.msie && parseInt($.browser.version) != 6) {

								$form.find('input[type=submit]').removeAttr('disabled').css('opacity', 1);
							}
						});
					}
				}
			});
		}
	};
})(jQuery);

function array_reverse (array, preserve_keys) {
    // Return input as a new array with the order of the entries reversed
    //
    // version: 1109.2015
    // discuss at: http://phpjs.org/functions/array_reverse    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: Karol Kowalski
    // *     example 1: array_reverse( [ 'php', '4.0', ['green', 'red'] ], true);
    // *     returns 1: { 2: ['green', 'red'], 1: 4, 0: 'php'}
    var isArray = Object.prototype.toString.call(array) === "[object Array]",        tmp_arr = preserve_keys ? {} : [],
        key;

    if (isArray && !preserve_keys) {
        return array.slice(0).reverse();    }

    if (preserve_keys) {
        var keys = [];
        for (key in array) {            // if (array.hasOwnProperty(key)) {
            keys.push(key);
            // }
        }
                var i = keys.length;
        while (i--) {
            key = keys[i];
            // FIXME: don't rely on browsers keeping keys in insertion order
            // it's implementation specific            // eg. the result will differ from expected in Google Chrome
            tmp_arr[key] = array[key];
        }
    } else {
        for (key in array) {            // if (array.hasOwnProperty(key)) {
            tmp_arr.unshift(array[key]);
            // }
        }
    }
    return tmp_arr;
}


/**
 * This function handles the quantity change on the detail page
 * If the user don't have enough points for the changed quantity the radio boxes will hide
 */
(function($) {
	$(document).ready(function() {
		var quantityBox = $('#detail #sQuantity'),
			radioGrp = $('#detail .buy_for'),
			earningDisplayField = $('#detail .points_for_article .image');

        if (earningDisplayField.length == 0) {
            return;
        }

		var priceNormal = $('.article_details_price').text();
        var pricePseudo = $('.article_details_price2 strong').text();
        var priceBlock = $('#article_details tbody tr:first td:last').text();

        if (priceNormal.length) {
            $('#detail .buy_for .item .price-holder').html(priceNormal);
        } else if (pricePseudo.length) {
            $('#detail .buy_for .item .price-holder').html(pricePseudo);
        } else if (priceBlock.length) {
            $('#detail .buy_for .item .price-holder').html(priceBlock);
			var quantities = [];
			$.each($('#article_details tbody tr'), function(index, element) {
				var html = $(element).find('td:first').clone();
				html.find('span').remove();
				quantities.push(~~html.html());
			});
			quantities = array_reverse(quantities);
			var prices = [];
			$.each($('#article_details tbody tr'), function(index, element) {
				var html = $(element).find('td:last').text();
				html = html.trim();
				prices.push(html);
			});
			prices = array_reverse(prices);
		}

		//trace change event
		quantityBox.change(function() {
			//calculate the new sum of required points
			var quantity = this.value,
				pointSum = quantity * $('#detail #points_per_unit').val(),
				table = $('#article_details tbody'),
                newPoints = 0,
                priceBlock = $('#article_details tbody tr:first td:last').text();


			// Block price
			if(table.length) {
                $('#detail .buy_for .item .price-holder').html(priceBlock);
				for(var idx in quantities) {
					var blockPrice = quantities[idx];
					if(quantity >= blockPrice) {
						$('#detail .buy_for .item .price-holder').html(prices[idx]);
						return false;
					}
				}
			}

            newPoints = quantity * $('#detail #earning_points_per_unit').val();
            if(newPoints > 1) {
                newPoints = Math.round(newPoints);
            } else {
                newPoints = 0;
            }

			//update the display field how much points the user will get for this quantity
			earningDisplayField[0].firstChild.textContent = newPoints;


			//if user don't have enough points, hide the radios
			if(pointSum > $('#detail #user_points').val()) {
				if($('#detail .buy_for #money').length > 0 ) {
					$('#detail .buy_for #money')[0].checked = true;
					radioGrp.fadeOut("fast");
				}
			} else {
				radioGrp.fadeIn("fast");
			}
		});
	});
})(jQuery);


/**
 * This function is a helper function for the bonus system template
 * To stretch the bonus item image over the whole row it must remove from the form and append to the table row
 */
(function($) {
	$(document).ready(function() {
		var tableRow = $('.table_row');

		tableRow.each(function(i, el) {
			var $me = $(this),
				$isBonusItem = false;

			//bonus voucher and bonus articles has this flags
			$isBonusItem = $me.find('.bonus_image');
			if($isBonusItem.length > 0 ) {
				$me.addClass('bonus_item');
				$isBonusItem.remove();
				$isBonusItem.prependTo($me);
			}
		});
	});
})(jQuery);


/**
 * This function open the drop down menu in the header
 */
(function($) {
	$(document).ready(function() {
		var headerContainer = $('#header .user_points'),
			prevent = false;

		//trace the click event
		headerContainer.click(function(){
			//get the link container which will be display
			var links = headerContainer.find('.link_container').clone(),
				$me = $(this),
				$opened = $('body').find('.link_container.active');

			//if already opened, close the container
			if($opened.length > 0) {
				$opened.slideUp("fast", function() {
					$opened.remove();
				});
				return;
			}

			//flag to prevent the window click event
			prevent = true;
			links.addClass('active');
			links.appendTo("body");

			//get the offset
			var offset = $me.offset();
			links.css({
				'position': 'absolute',
				'top': offset.top + $me.outerHeight() - 2,
				'left': offset.left
			});
			//display the container
			links.slideDown("fast");
		});

		//trace the window click event to collapse the drop down menu
		$(window).bind('click', function() {
			if(!prevent) {
				$('body').find('.link_container.active').slideUp("fast", function() {
					$('body').find('.link_container.active').remove();
				});
			}
			prevent = false;
		});

	});
})(jQuery);
(function($) {
	//Refreshs the basket display
    $.basket.refreshDisplay = function () {

        $.ajax({
            'dataType': 'jsonp',
            'url': $.basket.options.viewport,
            'data': {
            	'sAction': 'ajaxAmount'
            },
            'success': function (result) {
				var basket, bonus;
				$.each($(result), function(i, el) {
					if(i === 0) {
						$($.basket.options.basketDisplay).html($(el).html());
					} else {
						$('.bonus-system-display').html($(el).html());
					}
				});
            }
        })
	};
})(jQuery);

