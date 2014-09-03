(function($) {

	$.fn.CloudZoom.defaults.zoomWidth = 585;
	$.fn.CloudZoom.defaults.zoomHeight = 320;

})(jQuery);

(function($) {
    $(document).ready(function() {
        $('.emotion-banner-mapping').hover(function() {
            var $this = $(this),
                $next = $this.next('.banner-mapping-tooltip');

            $next.addClass('hover');
        }, function() {
            var $this = $(this),
                $next = $this.next('.banner-mapping-tooltip');

            $next.removeClass('hover');
        });
    });
})(jQuery);

/* Auto submit form for new configurator */
(function($) {
    $(document).ready(function() {
        $('*[data-auto-submit="true"]').bind('change', function(event) {
            this.form.submit();
        });
    });
})(jQuery);

/* Add class to the searchfield */
(function($) {

	$(document).ready(function() {
	
		$('#searchfield').bind('focus', function() {
			var $this = $(this);
			$this.parent('form').addClass('active'); 
		});
		
		$('#searchfield').bind('blur', function() {
			var $this = $(this);
			$this.parent('form').removeClass('active');
		});

        $('.thumb_box a').bind('click', function() {
            $('.thumb_box a').removeClass('active');
            $(this).addClass('active');
        });
        $('.thumb_box a:first-child').addClass('active')
	});
})(jQuery);

/* remove the value per click from the newsletter input in the footer */
(function($) {
	$(document).ready(function() {
	  $('#newsletter_input').click(function() {
	    if (this.value == this.defaultValue) {
	      this.value = '';
	    }
	  });
	  $('#newsletter_input').blur(function() {
	    if (this.value == '') {
	      this.value = this.defaultValue;
	    }
	  });
	});
})(jQuery);

/* tapped the compareresults to the bottom of the page */
(function($) {

    $(document).ready(function() {
        $(window).resize(function() {
	        var offset = $('#compareHighlight').offset();
	        if(offset) {
		        $('body #compareContainerResults').css({
			        'left': offset.left - 81,
			        'top': offset.top + $('#compareHighlight').height() + 25
		        });
	        }
	    });
        $('#compareContainerResults').appendTo($('body')).hide();
        $(window).trigger('resize');
        
        $('#compareHighlight').live('mouseleave', function() {
	        $('body #compareContainerResults').hide();
        });
				
	});
})(jQuery);



(function($) {

	/**
     * Helper method which checks with the user agent
     * of the browser if the user is using an iPad
     *
     * @public
     * @return boolean
     */
    $.isiPad = function() {
	    return navigator.userAgent.match(/iPad/i) != null;
    }

	if($.isiPad()) {
		$.fn.CloudZoom = function() { /** ... empty function to disable the cloud zoom plugin */ }
	}

    $(document).ready(function() {
        /** Show / hide service menu - iPad */
        $(window).resize(function() {
	        var offset = $('.my_options .service').offset();
	        
	        if(offset) {
		       $('body #servicenavi').css({
			        'left': offset.left - 81,
			        'top': offset.top + $('.my_options .service').height() + 20
		        }); 
	        }
	    });
        $('.my_options #servicenavi').appendTo($('body')).hide();
        $(window).trigger('resize');
        
        $('.my_options .service').bind('click',function() {
			$('body #servicenavi').toggle();
		});
        
        if($.isiPad()) {
			
			$('body').bind('click', function() {
	        	$('div#searchresults').slideUp();
	        });
        }

        /** Set overflow:scroll feature and set .text height **/
        $('.html-text-inner-element').each(function(){
            var $this = $(this),
                $innerEl = $this.find('.text'),
                $offset = $innerEl.offset().top - $this.offset().top + 20;

            if($this.height() < $innerEl.height()) {
                $innerEl.css('overflow-y', 'scroll');
                $innerEl.css('height', $this.height() - $offset + 'px');
            }
        });



        /** Auto suggestion on iOS devices */
        if($.isiPad()) {
	       	
	       	// ... and use the slimbox instead of the cloud zoom
            $("#zoom1, [rel^='lightbox']").slimbox();
            $('div.thumb_box a').bind('click', function (event) {
                event.preventDefault();
                $('a#zoom1').hide().attr('href', $(this).attr('href')).children().attr('src', $(this).attr('rev'));
                $('a#zoom1').fadeIn('slow');
                return false;
            });
        }
        
        $.compare.options.topLink = '#header';
    });

	//Refreshs the basket display
	$.basket.refreshDisplay = function () {
		$.ajax({
			'dataType': 'jsonp',
			'url': $.basket.options.viewport,
			'data': {
				'sAction': 'ajaxAmount'
			},
			'success': function (result) {
				// $('#shopnavi span.quantity')
				$('#shopnavi div.newbasket').html(result);
				$('div.ajax_basket').click(function () {
					if ($('.ajax_basket_result').hasClass('active')) {
						$('.ajax_basket_result').removeClass('active').slideToggle('fast');
					} else {
						$.basket.getBasket();
					}
				});
			}
		});
	};
	$.basket.getBasket = function () {
        if(!$($.basket.options.basketResult).length) {
        	$('<div>', {
        		'class': 'ajax_basket_result'
        	}).appendTo(document.body);
        }
        $($.basket.options.basketLoader).show();
        $.ajax({
            'data': {
                'sAction': 'ajaxCart'
            },
            'dataType': 'jsonp',
            'url': $.basket.options.viewport,
            'success': function (result) {
            	var offset = $($.basket.options.basketParent).offset();
            	$($.basket.options.basketResult).css({
            		'top': offset.top + 21,
            		'left': offset.left -($($.basket.options.basketResult).width() - $($.basket.options.basketParent).width() + ($.isiPad() ? -35 : 22))	// Hier die 20 aendern
            	});
                $($.basket.options.basketLoader).hide();
                if (result.length) {
                    $($.basket.options.basketResult).empty().html(result);
                } else {
                    $($.basket.options.basketResult).empty().html($.basket.options.emptyText);
                }
                $($.basket.options.basketParent).addClass('active');
                $($.basket.options.basketResult).addClass('active').slideDown('fast');
                $(document.body).bind('click.basket', function() {
					$($.basket.options.basketResult).removeClass('active').slideUp('fast');
					$($.basket.options.basketParent).removeClass('active');
					$(document.body).unbind('click.basket');
				});
            }
        });
    }
})(jQuery);


/**
 * Selectbox replacement
 *
 * Copyright (c) 2012, shopware AG
 */
(function($) {
	$(document).ready(function() {
		$('select').fancySelect();
	});
	
	$.fn.fancySelect =  function() {
		
		function createTemplate(width, text) {
            if(width < 50) {
                width = 50;
            }
			var outer = $('<div>', { 'class': 'outer-select' }).css('width', width),
				inner = $('<div>', { 'class': 'inner-select' }).appendTo(outer),
				text = $('<span>', { 'class': 'select-text', 'html': text }).appendTo(inner);
				
			return outer;
		}
	
		return this.each(function() {
			var $this = $(this),
				initalWidth = $this.is(':hidden') ? $this.width() + 3 : $this.width() + 15,
				selected = $this.find(':selected'),
				initalText = selected.html(),
				template = createTemplate(initalWidth, initalText);
			
			template.insertBefore($this);
			$this.appendTo(template).width(initalWidth);

            if($this.hasClass('instyle_error')) {
                template.addClass('instyle_error');
            }
			
			template.bind('mouseenter', function() {
				$(this).addClass('hovered');
			});
			template.bind('mouseleave', function() {
				$(this).removeClass('hovered');
			});
			
			$this.bind('change', function() {
				var $select = $(this),
					selected = $select.find(':selected');
					
				template.find('.select-text').html(selected.html());
			})
		});
	}
})(jQuery);


/**
 * SwagButtonSolution
 *
 * Copyright (c) 2012, shopware AG
 */
(function($) {
	$(document).ready(function() {
		$('.agb_cancelation input[name=sAGB]').change(function() {
			$('.agb-checkbox').val($(this).is(':checked'))
		});
		
		$('.agb_cancelation input[name=sNewsletter]').change(function() {
			$('.newsletter-checkbox').val($(this).is(':checked'))
		});
	});
})(jQuery);

/**
 * Overview button progressive enhancement
 * using the {@link window.sessionStorage} to provide a personalized link with the currently active
 * filter properties for the HTTP cache.
 *
 * Copyright (c) 2014, shopware AG
 */
;(function($, window, undefined) {
     /*global jQuery:false */
    "use strict";

    var pluginName = 'ajaxProductNavigation',
        defaults = {
            arrowAnimSpeed: 500
        },
        listingSelectors = [
            '.artbox .title',
            '.artbox .artbox_thumb',
            '.artbox .actions .more'
        ];

    var isNumeric = function(obj) {
        return !$.isArray(obj) && (obj - parseFloat(obj) + 1) >= 0;
    };

    var parseQueryString = function(url) {
        var qparams = {},
            parts = (url || '').split('?'),
            qparts, qpart,
            i=0;

        if(parts.length <= 1){
            return qparams;
        }

        qparts = parts[1].split('&');
        for (i in qparts) {
            var key, value;

            qpart = qparts[i].split('=');
            key = decodeURIComponent(qpart[0])
            value = decodeURIComponent(qpart[1] || '');
            qparams[key] = (isNumeric(value) ? parseFloat(value, 10) : value);
        }

        return qparams;
    };

    /**
     * Plugin constructor which merges the default settings
     * with the user configuration and sets up the DOM bridge.
     *
     * @param { HTMLElement } element
     * @param { Object } options
     * @returns { Void }
     * @constructor
     */
    function Plugin(element) {
        var me = this;

        me.$el = $(element);
        me._name = pluginName;

        me.init();
    }

    Plugin.prototype.init = function() {
        var me = this;

        me._mode = (function() {
            if(me.$el.hasClass('ctl_listing')) {
                return 'listing';
            } else if(me.$el.hasClass('ctl_detail')) {
                return 'detail';
            }
            return undefined;
        })();

        if(!me._mode) {
            return false;
        }

        me.registerCustomEasing();

        if(me._mode === 'listing') {
            me.registerListingEventListeners(listingSelectors);
        } else {
            var params = parseQueryString(window.location.href);

            // ...the url wasn't called through the listing
            if(!params.hasOwnProperty('c')) {
                me.clearCurrentProductState();
                return;
            }

            me.getProductNavigation();
        }
    };

    Plugin.prototype.registerCustomEasing = function() {
        var me = this;

        $.extend($.easing, {
            easeOutBounce: function (x, t, b, c, d) {
                if ((t/=d) < (1/2.75)) {
                    return c*(7.5625*t*t) + b;
                } else if (t < (2/2.75)) {
                    return c*(7.5625*(t-=(1.5/2.75))*t + .75) + b;
                } else if (t < (2.5/2.75)) {
                    return c*(7.5625*(t-=(2.25/2.75))*t + .9375) + b;
                } else {
                    return c*(7.5625*(t-=(2.625/2.75))*t + .984375) + b;
                }
            }
        });

        return me;
    };

    Plugin.prototype.registerListingEventListeners = function(selectors) {
        var me = this;

        selectors = selectors.join(', ');
        me.$el.find(selectors).bind('click.' + pluginName, $.proxy(me.onProductLinkInListing, me));
    };

    Plugin.prototype.onProductLinkInListing = function(event) {
        var me = this,
            params = parseQueryString(window.location.href),
            $target = $(event.target),
            $parent = $target.parents('.artbox'),
            categoryId = parseInt($parent.attr('data-category-id'), 10),
            orderNumber = $parent.attr('data-ordernumber');

        if(categoryId && isNumeric(categoryId) && !isNaN(categoryId)) {
            params.categoryId = categoryId;
        }

        if(orderNumber && orderNumber.length) {
            params.ordernumber = orderNumber;
        }

        me.saveCurrentProductState(params);
    };

    Plugin.prototype.saveCurrentProductState = function(params) {
        try {
            window.sessionStorage.setItem('lastProductState', JSON.stringify(params));
            return true;
        } catch(err) {
            return false;
        }
    };

    Plugin.prototype.restoreCurrentProductState = function() {
        try {
            return JSON.parse(window.sessionStorage.getItem('lastProductState'));
        } catch(err) {
            return {};
        }
    };

    Plugin.prototype.refreshCurrentProductState = function() {
        var me = this,
            orderNumber = me.$el.find('#detail').attr('data-ordernumber'),
            params = me.restoreCurrentProductState();

        if(orderNumber && orderNumber.length) {
            params.ordernumber = orderNumber;
        }
        me.saveCurrentProductState(params);

        return params;
    };

    Plugin.prototype.clearCurrentProductState = function() {
        try {
            window.sessionStorage.removeItem('lastProductState');
            return true;
        } catch(err) {
            return false;
        }
    };

    Plugin.prototype.getProductNavigation = function() {
        var me = this,
            params = me.refreshCurrentProductState(),
            url;

        if($.isEmptyObject(params)) {
            return false;
        }
        url = me.$el.find('#detail').attr('data-product-navigation');

        if(!url || !url.length) {
            return false;
        }

        $.ajax({
            'url': url,
            'data': params,
            'method': 'GET',
            'dataType': 'json',
            'success': $.proxy(me.setProductNavigation, me)
        })
    };

    Plugin.prototype.setProductNavigation = function(response) {
        var me = this,
            prevLink = me.$el.find('a.article_back'),
            nextLink = me.$el.find('a.article_next');

        if(response.hasOwnProperty('previousProduct')) {
            var previousProduct = response.previousProduct;

            prevLink
                .attr('href', previousProduct.href)
                .attr('title', previousProduct.name)
                .parents('div.article_back')
                .animate({
                    'left': 5
                }, defaults.arrowAnimSpeed, 'easeOutBounce');
        } else {
            prevLink.remove();
        }

        if(response.hasOwnProperty('nextProduct')) {
            var nextProduct = response.nextProduct;

            nextLink
                .attr('href', nextProduct.href)
                .attr('title', nextProduct.name)
                .parents('div.article_next')
                .animate({
                    'right': 5
                }, defaults.arrowAnimSpeed, 'easeOutBounce');
        } else {
            nextLink.remove();
        }

        return true;
    };

    $.fn[pluginName] = function () {
        return this.each(function () {
            if (!$.data(this, 'plugin_' + pluginName)) {
                $.data(this, 'plugin_' + pluginName, new Plugin( this ));
            }
        });
    };

    $(function() {
        $('body').ajaxProductNavigation();
    });
})(jQuery, window);