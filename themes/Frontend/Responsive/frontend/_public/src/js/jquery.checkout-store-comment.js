;(function($, window) {
    'use strict';

    /**
     * Store Checkout Comment Plugin
     *
     * This Plugin stores the content of the users comment when the user
     * clicks out of the comment field. This is in case the user performs
     * an action that causes a page reload. The Plugin will then populate the
     * comment field when the page is reloaded.
     */
    $.plugin('swStoreCheckoutComment', {

        init: function () {
            var me = this,
                comment;

            me.storage = StorageManager.getStorage('session');
            comment = me.storage.getItem('checkoutComment');
            me.$el.val(comment);

            me.registerEvents();
        },

        registerEvents: function () {
            var me = this;

            me._on(me.$el, 'blur', $.proxy(me.storeComment, me));

            $.publish('plugin/swStoreCheckoutComment/onRegisterEvents', [ me ]);

        },
        
        storeComment: function () {
            var me = this;

            var comment = me.$el.val();

            me.storage.setItem('checkoutComment', comment);

            $.publish('plugin/swStoreCheckoutComment/storeComment', [ me ]);
        },

        destroy: function() {
            var me = this;

            me._destroy();
        }

    });
})(jQuery, window);
