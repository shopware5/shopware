;(function($, window) {
    'use strict';

    $.plugin('ajaxWishlist', {
        defaults: {
            iconCls: 'icon--check',
            counter: '.notes--quantity',
            text: 'Gemerkt'
        },

        init: function() {
            var me = this;

            me.applyDataAttributes();

            me.$counter = $(me.opts.counter);

            me.registerEvents();
        },

        registerEvents: function() {
            var me = this;

            me.$el.on(me.getEventName('click'), '.action--note, .link--notepad', $.proxy(me.triggerRequest, me));
        },

        triggerRequest: function(event) {
            var me = this,
                $target = $(event.currentTarget),
                href = $target.attr('href');

            event.preventDefault();

            $.getJSON(href, $.proxy(me.responseHandler, me, $target));
        },

        responseHandler: function($target, response) {
            var me = this;

            if (!response.success) {
                return false;
            }

            me.$counter.html(response.notesCount);
            me.animateElement($target);
        },

        animateElement: function($target) {
            var me = this,
                $icon = $target.find('i'),
                originalIcon = $icon[0].className,
                $text = $target.find('.action--text');

            $target.toggleClass('js--is-animating');
            $text.html($target.attr('data-text') || me.opts.text);
            $icon.removeClass(originalIcon).addClass(me.opts.iconCls);
        }
    });
})(jQuery, window);