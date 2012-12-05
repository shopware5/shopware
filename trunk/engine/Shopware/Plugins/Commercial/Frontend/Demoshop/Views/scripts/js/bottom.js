"use_strict";
/**
* jQuery Cookie plugin
*
* Copyright (c) 2010 Klaus Hartl (stilbuero.de)
* Dual licensed under the MIT and GPL licenses:
* http://www.opensource.org/licenses/mit-license.php
* http://www.gnu.org/licenses/gpl.html
*
*/
jQuery.cookie = function (key, value, options) {

    // key and at least value given, set cookie...
    if (arguments.length > 1 && String(value) !== "[object Object]") {
        options = jQuery.extend({}, options);

        if (value === null || value === undefined) {
            options.expires = -1;
        }

        if (typeof options.expires === 'number') {
            var days = options.expires, t = options.expires = new Date();
            t.setDate(t.getDate() + days);
        }

        value = String(value);

        return (document.cookie = [
            encodeURIComponent(key), '=',
            options.raw ? value : encodeURIComponent(value),
            options.expires ? '; expires=' + options.expires.toUTCString() : '', // use expires attribute, max-age is not supported by IE
            options.path ? '; path=' + options.path : '',
            options.domain ? '; domain=' + options.domain : '',
            options.secure ? '; secure' : ''
        ].join(''));
    }

    // key and possibly options given, get cookie...
    options = value || {};
    var result, decode = options.raw ? function (s) { return s; } : decodeURIComponent;
    return (result = new RegExp('(?:^|; )' + encodeURIComponent(key) + '=([^;]*)').exec(document.cookie)) ? decode(result[1]) : null;
};

(function($) {
	
	/**
	 * $.fn.idle
	 *
	 * This plugin transforms the lame setTimeout()
	 * into the beloved jQuery syntax
	 *
	 * @author: s.pohl <klarstil@googlemail.com>
	 * @date: 2011-08-27
	 * @copyright: MIT
	 *
	 * @param {int} time in milliseconds
	 * @return this
	 */
	$.fn.idle = function(time) {
	
		var obj = $(this);
		obj.queue(function() {
			setTimeout(function() {
				obj.dequeue();
			}, time);
		});
		
		/** Return this for jQuery's chaining support */
		return this;
	};

})(jQuery);

(function($) {
	$(document).ready(function() {
		$('.demo-theme-switcher').themeSwitcher();
	});
	
	$.fn.themeSwitcher = function() {
		
		/** Loop through all incoming elements and return this for jQuery's chaining support */
		return this.each(function() {
			var $this = $(this), timeout = null;
			
			/** Add tooltip and event listener which opens a overlay with a message */
			$this.find('.color-box').bind('click', function(event) {
				var $me = $(this);
				$('#tiptip_holder').remove();
			
				event.preventDefault();
			
				var overlay = $('<div>', {
					'class': 'theme-switcher-overlay',
					'css': {
						'opacity': 0,
						'background': '#000',
						'position': 'fixed',
						'left': 0, 'top': 0,
						'width': '100%', 'height': '100%',
						'z-index': 10000
					}
				}).appendTo($('body'));
				
				var text = $('<div>', {
					'class': 'theme-switcher-text',
					'html': 'Das Farbtemplate wird an Ihre Bed&uuml;rfnisse angepasst'
				}).appendTo(overlay);
				
				overlay.fadeTo('fast', 0.9);
				
				window.setTimeout(function() {
					window.location.href = $me.find('a').attr('href');
				}, 200);
			});
			
			/** Event listeners which opens/closes the panel */
			$this.hover(function() {
			
				timeout = window.setTimeout(function() {
					$this.animate({ 'left': 0 }, 200);
					$this.find('.handle').html('&lt;');
				}, 150);
				
			}, function() {
				
				if(timeout) {
					clearTimeout(timeout);
					timeout = null;
				}
			
				$this.animate({ 'left': -36 }, 200);
				$this.find('.handle').html('&gt;');
			});
			
		});
	};
})(jQuery);

/**
 * JQUERY BOTTOM BAR PLUGIN
 *
 * @author: s.pohl <stp@shopware.de>
 * @copyright: shopware AG (c) 2011
 * @date: 2011-08-11
 */
(function($) {

	/** Plugin starter */
	$(document).ready(function() {
		$('.bottom-bar-wrapper').bottomBar();
		
		/** Send ajax request if the user clicks on the "submit" button */
		$('.bottom-bar-wrapper form').bind('submit', function(event) {
			var $this = $(this);
		
			/** Stop default behavior */
			event.preventDefault();
			
			/** Send ajax request */
			$.ajax({
				'dataType': 'json',
				'method': $this.attr('method'),
				'url': $this.attr('action'),
				'beforeSend': function() {
				
					/** Show ajax loader */
					$this.find('.ajax-loader').css('display', 'block');
				},
				'success': function(response) {
					
					/** Hide ajax loader */
					$this.find('.ajax-loader').css('display', ' none');
					
					console.log(response);
				},
				'error': function(xhr) {
				
					/** Hide ajax loader */
					$this.find('.ajax-loader').css('display', ' none');
					
					/** Error reporting */
					console.group('AJAX-Request - ' + $this.attr('action'));
					console.log('AJAX-Request failed.');
					console.log('xhr:', xhr);
					console.log('status:', xhr.statusText);
					console.groupEnd();
				}
			});
		});
	});
	
	/**
	 * $.fn.bottomBar
	 *
	 * This function creates the actual bottom bar and
	 * handles all related DOM events 
	 *
	 * Example usage:
	 * $('[selector]').bottomBar([settings]);
	 *
	 * Configuration options
	 *
	 * - {str} closeCls - class if the bottom bar is closed
	 * - {str} openCls - class if the bottom bar is opened
	 * - {int} barHeight - height of the bottom bar
	 * - {int} slideInSpeed - the animation speed which will be used if the user opens the bottom bar
	 * - {int} slideOutSpeed - the animation speed which will be used if the user closes the bottom bar
	 * - {int} fadeSpeed - the animation speed which will be used for the "show"-button
	 * - {int}timeout - the delay to show the toolbar
	 *
	 * @param {obj} settings
	 * @return {obj} this 
	 */
	$.fn.bottomBar = function(settings) {
		
		/** Default configuration */
		var config = {
			'closeCls': 'closed',
			'openCls': 'opened',
			'barHeight': 40,
			'slideInSpeed': 275,
			'slideOutSpeed': 200,
			'fadeSpeed': 300,
			'timeout': 200
		};
		
		/** Extend default config with user settings */
		if(settings) { $.extend(config, settings); }
		
		/** Declaration */
		var $this = $(this), 
			$header = $(this).find('.header'),
			bottomHeight = $this.outerHeight(),
			timeout = null;
		
		/** Set height of the bottom height */
		$this.css({
			'bottom': -(bottomHeight - config.barHeight)
		}).addClass(config.closeCls);
		
		/** Startup animation */
		if($.isEmptyObject($.cookie('firstVisit'))) {
			$this.idle(1000).animate({
				'bottom': 0
			}, config.slideInSpeed).removeClass(config.closeCls).addClass(config.openCls);
			
			$header.fadeOut(config.fadeSpeed);
			
			$this.idle(3000).animate({
				'bottom': -(bottomHeight - config.barHeight)
			}, 400).removeClass(config.openCls).addClass(config.closeCls);
			$header.fadeIn(config.fadeSpeed);
			
			var date = new Date();
			
			$.cookie('firstVisit', date.getTime(), { expires: 7 });
	
		}		
		/** Add event listeners for the hover event */
		$this.hover(function() {
			timeout = window.setTimeout(function() {
			
				/** Show bottom bar content and fadeout the "show" button */
				$this.animate({
					'bottom': 0
				}, config.slideInSpeed).removeClass(config.closeCls).addClass(config.openCls);
				$header.fadeOut(400);
			}, config.timeout);
		}, function() {
		
			/** Clear delay to prevent flicking bugs */
			if(timeout) {
				clearTimeout(timeout);
				timeout = null;
			}
			
			/** Hide bottom bar content and fadein the "show" buttom */
			$this.animate({
				'bottom': -(bottomHeight - config.barHeight)
			}, config.slideOutSpeed).removeClass(config.openCls).addClass(config.closeCls);
			$header.fadeIn(config.fadeSpeed);
		});
		
		/** Support for jQuery's method chaining */
		return this;
	};
})(jQuery);

 /*
 * TipTip
 * Copyright 2010 Drew Wilson
 * www.drewwilson.com
 * code.drewwilson.com/entry/tiptip-jquery-plugin
 *
 * Version 1.3   -   Updated: Mar. 23, 2010
 *
 * This Plug-In will create a custom tooltip to replace the default
 * browser tooltip. It is extremely lightweight and very smart in
 * that it detects the edges of the browser window and will make sure
 * the tooltip stays within the current window size. As a result the
 * tooltip will adjust itself to be displayed above, below, to the left 
 * or to the right depending on what is necessary to stay within the
 * browser window. It is completely customizable as well via CSS.
 *
 * This TipTip jQuery plug-in is dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 */
(function($){$.fn.tipTip=function(options){var defaults={activation:"hover",keepAlive:false,maxWidth:"200px",edgeOffset:3,defaultPosition:"bottom",delay:400,fadeIn:200,fadeOut:200,attribute:"title",content:false,enter:function(){},exit:function(){}};var opts=$.extend(defaults,options);if($("#tiptip_holder").length<=0){var tiptip_holder=$('<div id="tiptip_holder" style="max-width:'+opts.maxWidth+';"></div>');var tiptip_content=$('<div id="tiptip_content"></div>');var tiptip_arrow=$('<div id="tiptip_arrow"></div>');$("body").append(tiptip_holder.html(tiptip_content).prepend(tiptip_arrow.html('<div id="tiptip_arrow_inner"></div>')))}else{var tiptip_holder=$("#tiptip_holder");var tiptip_content=$("#tiptip_content");var tiptip_arrow=$("#tiptip_arrow")}return this.each(function(){var org_elem=$(this);if(opts.content){var org_title=opts.content}else{var org_title=org_elem.attr(opts.attribute)}if(org_title!=""){if(!opts.content){org_elem.removeAttr(opts.attribute)}var timeout=false;if(opts.activation=="hover"){org_elem.hover(function(){active_tiptip()},function(){if(!opts.keepAlive){deactive_tiptip()}});if(opts.keepAlive){tiptip_holder.hover(function(){},function(){deactive_tiptip()})}}else if(opts.activation=="focus"){org_elem.focus(function(){active_tiptip()}).blur(function(){deactive_tiptip()})}else if(opts.activation=="click"){org_elem.click(function(){active_tiptip();return false}).hover(function(){},function(){if(!opts.keepAlive){deactive_tiptip()}});if(opts.keepAlive){tiptip_holder.hover(function(){},function(){deactive_tiptip()})}}function active_tiptip(){opts.enter.call(this);tiptip_content.html(org_title);tiptip_holder.hide().removeAttr("class").css("margin","0");tiptip_arrow.removeAttr("style");var top=parseInt(org_elem.offset()['top']);var left=parseInt(org_elem.offset()['left']);var org_width=parseInt(org_elem.outerWidth());var org_height=parseInt(org_elem.outerHeight());var tip_w=tiptip_holder.outerWidth();var tip_h=tiptip_holder.outerHeight();var w_compare=Math.round((org_width-tip_w)/2);var h_compare=Math.round((org_height-tip_h)/2);var marg_left=Math.round(left+w_compare);var marg_top=Math.round(top+org_height+opts.edgeOffset);var t_class="";var arrow_top="";var arrow_left=Math.round(tip_w-12)/2;if(opts.defaultPosition=="bottom"){t_class="_bottom"}else if(opts.defaultPosition=="top"){t_class="_top"}else if(opts.defaultPosition=="left"){t_class="_left"}else if(opts.defaultPosition=="right"){t_class="_right"}var right_compare=(w_compare+left)<parseInt($(window).scrollLeft());var left_compare=(tip_w+left)>parseInt($(window).width());if((right_compare&&w_compare<0)||(t_class=="_right"&&!left_compare)||(t_class=="_left"&&left<(tip_w+opts.edgeOffset+5))){t_class="_right";arrow_top=Math.round(tip_h-13)/2;arrow_left=-12;marg_left=Math.round(left+org_width+opts.edgeOffset);marg_top=Math.round(top+h_compare)}else if((left_compare&&w_compare<0)||(t_class=="_left"&&!right_compare)){t_class="_left";arrow_top=Math.round(tip_h-13)/2;arrow_left=Math.round(tip_w);marg_left=Math.round(left-(tip_w+opts.edgeOffset+5));marg_top=Math.round(top+h_compare)}var top_compare=(top+org_height+opts.edgeOffset+tip_h+8)>parseInt($(window).height()+$(window).scrollTop());var bottom_compare=((top+org_height)-(opts.edgeOffset+tip_h+8))<0;if(top_compare||(t_class=="_bottom"&&top_compare)||(t_class=="_top"&&!bottom_compare)){if(t_class=="_top"||t_class=="_bottom"){t_class="_top"}else{t_class=t_class+"_top"}arrow_top=tip_h;marg_top=Math.round(top-(tip_h+5+opts.edgeOffset))}else if(bottom_compare|(t_class=="_top"&&bottom_compare)||(t_class=="_bottom"&&!top_compare)){if(t_class=="_top"||t_class=="_bottom"){t_class="_bottom"}else{t_class=t_class+"_bottom"}arrow_top=-12;marg_top=Math.round(top+org_height+opts.edgeOffset)}if(t_class=="_right_top"||t_class=="_left_top"){marg_top=marg_top+5}else if(t_class=="_right_bottom"||t_class=="_left_bottom"){marg_top=marg_top-5}if(t_class=="_left_top"||t_class=="_left_bottom"){marg_left=marg_left+5}tiptip_arrow.css({"margin-left":arrow_left+"px","margin-top":arrow_top+"px"});tiptip_holder.css({"margin-left":marg_left+"px","margin-top":marg_top+"px"}).attr("class","tip"+t_class);if(timeout){clearTimeout(timeout)}timeout=setTimeout(function(){tiptip_holder.stop(true,true).fadeIn(opts.fadeIn)},opts.delay)}function deactive_tiptip(){opts.exit.call(this);if(timeout){clearTimeout(timeout)}tiptip_holder.fadeOut(opts.fadeOut)}}})}})(jQuery);