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
 * using the { @link sessionStorage } to provide a personalized link with the currently active
 * filter properties for the HTTP cache.
 *
 * Copyright (c) 2013, shopware AG
 */
;(function($, window, undefined) {
     /*global jQuery:false */
    "use strict";

    var pluginName = 'httpCacheFilters',
        sessionStorage = window.sessionStorage,
        defaults = {
            mode: 'listing'
        };

    /**
     * Plugin constructor which merges the default settings
     * with the user configuration and sets up the DOM bridge.
     *
     * @param { HTMLElement } element
     * @param { Object } options
     * @returms { Void }
     * @constructor
     */
    function Plugin(element, options) {
        var me = this;

        me.element = element;
        me.opts = $.extend({}, defaults, options);
        me._defaults = defaults;
        me._name = pluginName;
        me.hasSessionStorageSupport = me.isSessionStorageSupported();

        me.init();
    }

    /**
     * Initialized the plugin, checks if { @link sessionStorage } is
     * supported and sets up the event listener.
     *
     * @returns { Boolean } Falsy, if { @link sessionStorage } isn't supported, otherwise truthy
     */
    Plugin.prototype.init = function() {
        var me = this,
            mode;

        // Check if the browser support { @link sessionStorage }
        if(!me.hasSessionStorageSupport) {
            return false;
        }

        // Terminate if we're on the category listing or on the detail page
        mode = $(me.element).hasClass('ctl_detail') ? 'detail' : 'listing';
        if(mode === 'listing') {
            $('.artbox .artbox_thumb, .artbox .title, .artbox .buynow').on('click.' + pluginName, $.proxy(me.onOpenDetailPage, me));
            $('.filter_properties .close a').on('click.' + pluginName, $.proxy(me.onResetFilterOptions, me));
        } else {
            me.restoreState();
        }

        return true;
    };

    /**
     * Event callback which will be fired when the user wants to open up
     * the detail page.
     *
     * The method just proxies the method { @link #saveCurrentState }.
     *
     * @event `click`
     * @returns { Void }
     */
    Plugin.prototype.onOpenDetailPage = function() {
        var me = this;
        me.saveCurrentState();
    };

    /**
     * Event callback which will be fired when the user wants to
     * reset a filter property group.
     *
     * The method reads out the url of the reset link and save it
     * to the { @link sessionStorage }.
     *
     * @param { Event } event
     * @return { Void }
     */
    Plugin.prototype.onResetFilterOptions = function(event) {
        var me = this,
            $this = $(event.currentTarget),
            url = $this.attr('href');

        me.saveCurrentState(url);
    };

    /**
     * Saves the passed url to the { @link sessionStorage } using
     * the { @link pluginName } as the key of the entry.
     *
     * @param { String } [url] - URL, which should be saved.
     * @returns { Boolean }
     */
    Plugin.prototype.saveCurrentState = function(url) {
        var me = this,
            itemValue = url || window.location.href;

        if (me.hasSessionStorageSupport) {
            sessionStorage.setItem(pluginName, itemValue);
        }

        return true;
    };

    /**
     * Restores a state from the `sessionStorage` on the
     * detail page and removes the entry to prevent
     * strange behaviors of the overview link.
     *
     * @returns { Boolean } Truthy, if all went well, otherwise falsy
     */
    Plugin.prototype.restoreState = function() {
        var me = this,
            item = me.hasSessionStorageSupport && sessionStorage.getItem(pluginName);

        if(!item) {
            return false;
        }

        $('.article_overview a').attr('href', item);

        sessionStorage.removeItem(pluginName);
        return true;
    };

    /**
     * Returns whether or not the sessionStorage is available and works - SW-7524
     *
     * @returns {boolean}
     */
    Plugin.prototype.isSessionStorageSupported = function () {
        var testKey = 'test';

        if (!sessionStorage) {
            return false;
        }

        try {
            sessionStorage.setItem(testKey, '1');
            sessionStorage.removeItem(testKey);
            return true;
        } catch (error) {
            return false;
        }
    };

    /** Lightweight plugin starter */
    $.fn[pluginName] = function ( options ) {
        return this.each(function () {
            if (!$.data(this, 'plugin_' + pluginName)) {
                $.data(this, 'plugin_' + pluginName,
                new Plugin( this, options ));
            }
        });
    };

    /** Fire up the plugin */
    $(function() {
        $('body').httpCacheFilters();
    });
})(jQuery, window);
