;(function($) {
    "use strict";

    $.plugin('clicker', {

        /** Your default options */
        defaults: {
            foo: 'bar'
        },

        /** Plugin constructor */
        init: function () {
            var me = this;

            console.log('init');

            me._on(me.$el, 'click', $.proxy(me.onElementClick, me));
        },

        onElementClick: function (event) {
            var me = this;

            console.log('click', me);
            console.log('instance', me instanceof $.PluginBase);

            event.preventDefault();
        }
    });
})(jQuery);