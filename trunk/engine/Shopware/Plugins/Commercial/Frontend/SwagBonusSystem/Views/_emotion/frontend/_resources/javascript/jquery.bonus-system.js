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
        var priceBlock = $('.block-prices tbody tr:first td:last').text();

        if (priceNormal.length) {
            $('#detail .buy_for .item .price-holder').html(priceNormal);
        } else if (pricePseudo.length) {
            $('#detail .buy_for .item .price-holder').html(pricePseudo);
        } else if (priceBlock.length) {
            $('#detail .buy_for .item .price-holder').html(priceBlock);
            var quantities = [];
            $.each($('.block-prices tbody tr'), function(index, element) {
                var html = $(element).find('td:first').clone();
                html.find('span').remove();
                quantities.push(~~html.html());
            });
            quantities = array_reverse(quantities);
            var prices = [];
            $.each($('.block-prices tbody tr'), function(index, element) {
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
				table = $('.block-prices tbody'),
                newPoints = 0,
                priceBlock = $('.block-prices tbody tr:first td:last').text();

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
            if (newPoints > 1) {
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
				$opened.fadeIn("fast", function() {
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
				'top': offset.top + $me.outerHeight() + 18,
				'left': offset.left
			});
			//display the container
			links.fadeIn("fast");
		});

		//trace the window click event to collapse the drop down menu
		$(window).bind('click', function() {
			if(!prevent) {
				$('body').find('.link_container.active').fadeOut("fast", function() {
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

