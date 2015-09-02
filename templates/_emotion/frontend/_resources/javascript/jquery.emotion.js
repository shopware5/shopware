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
;(function ($, window) {
    /*global jQuery:false */
    'use strict';

    var pluginName = 'ajaxProductNavigation';

    $.extend($.easing, {
        easeOutBounce: function (x, t, b, c, d) {
            if ((t /= d) < (1 / 2.75)) {
                return c * (7.5625 * t * t) + b;
            } else if (t < (2 / 2.75)) {
                return c * (7.5625 * (t -= (1.5 / 2.75)) * t + .75) + b;
            } else if (t < (2.5 / 2.75)) {
                return c * (7.5625 * (t -= (2.25 / 2.75)) * t + .9375) + b;
            } else {
                return c * (7.5625 * (t -= (2.625 / 2.75)) * t + .984375) + b;
            }
        }
    });

    /**
     * Plugin constructor which merges the default settings
     * with the user configuration and sets up the DOM bridge.
     *
     * @param {HTMLElement} element
     * @param {Object} options
     * @constructor
     */
    function Plugin(element) {
        var me = this;

        me.$el = $(element);
        me._name = pluginName;
        me.opts = $.extend({}, me.defaults);

        me.init();
    }

    Plugin.prototype.defaults = {

        /**
         * Animation speed in milliseconds of the arrow fading.
         *
         * @type {Number}
         */
        arrowAnimSpeed: 500,

        /**
         * Selector for the product box in the listing.
         *
         * @type {String}
         */
        productBoxSelector: '.artbox',

        /**
         * Selector for the product details.
         * This element should have data attributes of the ordernumber and product navigation link.
         *
         * @type {String}
         */
        productDetailsSelector: '#detail',

        /**
         * Selector for the previous button.
         *
         * @type {String}
         */
        prevLinkSelector: 'a.article_back',

        /**
         * Selector for the next button.
         *
         * @type {String}
         */
        nextLinkSelector: 'a.article_next',

        /**
         * Selector for the breadcrumb back button.
         *
         * @type {String}
         */
        breadcrumbButtonSelector: 'a.article_back',

        /**
         * Selectors of product box childs in the listing.
         *
         * @type {Array}
         */
        listingSelectors: [
            '.artbox .title',
            '.artbox .artbox_thumb',
            '.artbox .actions .more'
        ]
    };

    Plugin.prototype.init = function () {
        var me = this,
            $el = me.$el,
            isListing = $el.hasClass('ctl_listing'),
            isDetail = $el.hasClass('ctl_detail'),
            opts = me.opts;

        if (!(isListing || isDetail)) {
            return;
        }

        me.urlParams = me.parseQueryString(location.href);

        if (isListing) {
            me.registerListingEventListeners();
            return;
        }

        me.$prevButton = $el.find(opts.prevLinkSelector);
        me.$nextButton = $el.find(opts.nextLinkSelector);
        me.$backButton = $el.find(opts.breadcrumbButtonSelector);
        me.$productDetails = $el.find(opts.productDetailsSelector);

        me.categoryId = ~~(me.urlParams.c || me.$productDetails.attr('data-category-id'));
        me.orderNumber = me.$productDetails.attr('data-ordernumber');
        me.productState = me.getProductState();

        // Clear the product state if the order numbers are not identical
        if (!$.isEmptyObject(me.productState) && me.productState.ordernumber !== me.orderNumber) {
            me.clearProductState();
            me.productState = {};
        }

        me.registerDetailEventListeners();
        me.getProductNavigation();
    };

    Plugin.prototype.parseQueryString = function (url) {
        var params = {},
            urlParts = (url + '').split('?'),
            queryParts,
            part,
            key,
            value,
            p;

        if (urlParts.length < 2) {
            return params;
        }

        queryParts = urlParts[1].split('&');

        for (p in queryParts) {
            if (!queryParts.hasOwnProperty(p)) {
                continue;
            }

            part = queryParts[p].split('=');

            key = decodeURIComponent(part[0]);
            value = decodeURIComponent(part[1] || '');

            params[key] = $.isNumeric(value) ? parseFloat(value) : value;
        }

        return params;
    };

    Plugin.prototype.registerListingEventListeners = function () {
        var me = this,
            selectors = me.opts.listingSelectors.join(', '),
            $listingEls = me.$el.find(selectors);

        $listingEls.on('click', $.proxy(me.onClickProductInListing, me));
    };

    Plugin.prototype.onClickProductInListing = function (event) {
        var me = this,
            opts = me.opts,
            $target = $(event.target),
            $parent = $target.parents(opts.productBoxSelector),
            params = $.extend({}, me.urlParams, {
                'categoryId': ~~($parent.attr('data-category-id')),
                'ordernumber': $parent.attr('data-ordernumber')
            });

        me.setProductState(params);
    };

    Plugin.prototype.registerDetailEventListeners = function () {
        var me = this;

        me.$prevButton.on('click', $.proxy(me.onArrowClick, me));
        me.$nextButton.on('click', $.proxy(me.onArrowClick, me));
    };

    Plugin.prototype.onArrowClick = function (event) {
        var me = this,
            $target = $(event.currentTarget);

        if (!$.isEmptyObject(me.productState)) {
            me.productState.ordernumber = $target.attr('data-ordernumber');
            me.setProductState(me.productState);
        }
    };

    Plugin.prototype.getProductState = function () {
        try {
            return JSON.parse(window.sessionStorage.getItem('lastProductState'));
        } catch (err) {
            return {};
        }
    };

    Plugin.prototype.setProductState = function (params) {
        try {
            window.sessionStorage.setItem('lastProductState', JSON.stringify(params));
            return true;
        } catch (err) {
            return false;
        }
    };

    Plugin.prototype.clearProductState = function () {
        try {
            window.sessionStorage.removeItem('lastProductState');
            return true;
        } catch (err) {
            return false;
        }
    };

    Plugin.prototype.getProductNavigation = function () {
        var me = this,
            url = me.$el.find('#detail').attr('data-product-navigation'),
            params = $.extend({}, me.productState, {
                'ordernumber': me.orderNumber,
                'categoryId': me.categoryId
            });

        if ($.isEmptyObject(params) || !url || !url.length) {
            return;
        }

        $.ajax({
            'url': url,
            'data': params,
            'method': 'GET',
            'dataType': 'json',
            'success': $.proxy(me.onProductNavigationLoaded, me)
        });
    };

    Plugin.prototype.onProductNavigationLoaded = function (response) {
        var me = this,
            opts = me.opts,
            $prevBtn = me.$prevButton,
            $nextBtn = me.$nextButton,
            prevProduct = response.previousProduct,
            nextProduct = response.nextProduct,
            animSpeed = opts.arrowFadeSpeed;

        if (typeof prevProduct === 'object') {
            $prevBtn.attr('data-ordernumber', prevProduct.orderNumber);

            $prevBtn
                .attr('href', prevProduct.href)
                .attr('title', prevProduct.name)
                .parents('div.article_back')
                .animate({
                    'left': 5
                }, animSpeed, 'easeOutBounce');
        } else {
            $prevBtn.remove();
        }

        if (typeof nextProduct === 'object') {
            $nextBtn.attr('data-ordernumber', nextProduct.orderNumber);

            $nextBtn
                .attr('href', nextProduct.href)
                .attr('title', nextProduct.name)
                .parents('div.article_next')
                .animate({
                    'right': 5
                }, animSpeed, 'easeOutBounce');
        } else {
            $nextBtn.remove();
        }
    };

    $.fn[pluginName] = function () {
        return this.each(function () {
            if (!$.data(this, 'plugin_' + pluginName)) {
                $.data(this, 'plugin_' + pluginName, new Plugin(this));
            }
        });
    };

    $(function () {
        $('body').ajaxProductNavigation();
    });
})(jQuery, window);