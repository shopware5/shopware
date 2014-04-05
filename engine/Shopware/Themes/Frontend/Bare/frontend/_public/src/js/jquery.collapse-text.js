;(function($, window, document, undefined) {
    "use strict";

    var pluginName = 'collapseText',
        defaults = {
            truncateRange: 160,
            truncateChar: ' ',
            ellipsis: ' ...',
            previewTextCls: 'category--preview-text',
            fullTextCls: 'category--full-text',
            showMoreCls: 'category--show-more',
            showMoreText: 'Mehr lesen',
            showLessText: 'Weniger anzeigen',
            slideSpeed: 400
        };

    /**
     * Plugin constructor which merges the default settings with the user settings.
     *
     * @param {HTMLElement} element - Element which should be used in the plugin
     * @param {Object} userOpts - User settings for the plugin
     * @constructor
     */
    function Plugin(element, userOpts) {
        var me = this;

        me.$el = $(element);
        me.opts = $.extend({}, defaults, userOpts);

        me._defaults = defaults;
        me._name = pluginName;

        me.init();
    }

    /**
     * Initializes the plugin and adds the necessary
     * classes to get the plugin up and running.
     */
    Plugin.prototype.init = function() {
        var me = this;

        me.text = me.$el.html();
        me.truncatedText = me.truncate(
            me.text,
            me.opts.truncateRange,
            me.opts.truncateChar,
            me.opts.ellipsis
        );

        me.showMoreText = (me.$el.attr('data-collapse-show-more') !== undefined) ? me.$el.attr('data-collapse-show-more') : me.opts.showMoreText;
        me.showLessText = (me.$el.attr('data-collapse-show-less') !== undefined) ? me.$el.attr('data-collapse-show-less') : me.opts.showLessText;

        me.createElements();
        me.registerEvents();
    };

    /**
     * Registers all necessary event handlers.
     */
    Plugin.prototype.registerEvents = function() {
        var me = this;

        me.showMore.on('click.' + pluginName, function(e) {
            e.preventDefault();
            me.togglePreviewText();
        });
    };

    /**
     * Creates the dynamic DOM elements used by the plugin.
     */
    Plugin.prototype.createElements = function() {
        var me = this;

        me.$el.html('');

        me.fullText = $('<div>', { 'class': me.opts.fullTextCls, 'html': me.text }).appendTo(me.$el);
        me.previewText = $('<div>', { 'class': me.opts.previewTextCls + ' is--active', 'html': me.truncatedText }).appendTo(me.$el);
        me.showMore = $('<a>', { 'class': me.opts.showMoreCls, 'html': me.showMoreText}).appendTo(me.$el);
    };

    /**
     * Toggles the preview text view state.
     */
    Plugin.prototype.togglePreviewText = function() {
        var me = this;

        if (me.fullText.hasClass('is--active')) {
            me.fullText.fadeOut(me.opts.slideSpeed, function() {
                me.fullText.removeClass('is--active');
                me.previewText.addClass('is--active');
                me.showMore.html(me.showMoreText);
            });
        } else {
            me.previewText.removeClass('is--active');
            me.showMore.html(me.showLessText);
            me.fullText.fadeIn(me.opts.slideSpeed, function() {
                me.fullText.addClass('is--active');
            });
        }
    };

    /**
     * Truncates a string to a given length of chars.
     * Breaks the string only at the specified char.
     * Also adds an ellipsis at the end of the string if wanted.
     *
     * @param string
     * @param length
     * @param truncateChar
     * @param ellipsis
     * @returns {string}
     */
    Plugin.prototype.truncate = function(string, length, truncateChar, ellipsis) {
        var truncatedString,
            reg = new RegExp('(?=[' + truncateChar + '])'),
            words = string.split(reg),
            wordCount = 0;

        truncatedString = words.filter(function(word) {
            wordCount += word.length;
            return wordCount <= length;
        }).join('');

        if (ellipsis !== undefined) {
            truncatedString += ellipsis;
        }

        return truncatedString;
    };

    /**
     * Destroys the initialized plugin completely, so all event listeners will
     * be removed and the plugin data, which is stored in-memory referenced to
     * the DOM node.
     */
    Plugin.prototype.destroy = function() {
        var me = this;

        me.showMore.off('click.' + pluginName);
        me.$el.html(me.text).removeData('plugin_' + pluginName);
    };

    $.fn[pluginName] = function ( options ) {
        return this.each(function () {
            if (!$.data(this, 'plugin_' + pluginName)) {
                $.data(this, 'plugin_' + pluginName,
                    new Plugin( this, options ));
            }
        });
    };

})(jQuery, window, document);