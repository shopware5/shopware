$.plugin('swNotificationMessageClose', {

    defaults: {
        /**
         * @var selector for notification
         */
        notificationSelector: '.alert',

        /**
         * @var called url when x is pressed
         */
        link: ''
    },

    init: function () {
        this.applyDataAttributes();

        this.$alert = this.$el.closest(this.opts.notificationSelector);

        this._on(this.$el, 'click', $.proxy(this.closeMessage, this));
    },

    closeMessage: function (event) {
        event.preventDefault();

        $.post(this.opts.link);
        this.$alert.fadeOut();
    }
});
