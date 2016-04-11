;(function($, window) {
    "use strict";

    /**
     * Shopware Address Selector Plugin.
     *
     * The plugin handles the address selection for various api endpoints.
     *
     * Example usage:
     * ```
     * <button class="btn select-address--btn" data-address-id="123" data-target="billing">
     *   Select address
     * </button>
     * ``
     */
    $.plugin('swAddressSelector', {

        /** Your default options */
        defaults: {
            /** @string action API endpoint for submitting */
            action: '',

            /** @string target Defines if it's used for billing or shipping address */
            target: null,

            /** @string windowTitle The title of the address selection */
            title: '',

            /** @int id Id of an address which should not be shown */
            id: null,

            /** @string selectButtonSelector Listener class which triggers the modal for address selection */
            selectButtonSelector: '.select-address--btn'
        },

        /**
         * Initializes the plugin
         *
         * @returns {Plugin}
         */
        init: function () {
            var me = this;

            me.applyDataAttributes();

            me.$el.on(me.getEventName('click'), $.proxy(me.onOpenSelection, me));

            $.publish('plugin/swAddressSelector/onRegisterEvents', [ me ]);
        },

        /**
         * onOpenSelection function for opening the selection modal. The available addresses will be
         * fetched as html from the api
         *
         * @method onOpenSelection
         */
        onOpenSelection: function (event) {
            var me = this,
                sizing = 'content';

            event.preventDefault();

            if (me.opts.action.length === 0) {
                return;
            }

            $.overlay.open({
                closeOnClick: false
            });

            $.loadingIndicator.open({
                openOverlay: false
            });

            if (window.StateManager._getCurrentDevice() === 'mobile') {
                sizing = 'auto';
            }

            $.publish('plugin/swAddressSelector/onBeforeAddressFetch', [ me, event ]);

            // Ajax request to fetch available addresses
            $.ajax({
                'url': window.controller['ajax_address_selector'],
                'data': {
                    target: me.opts.target,
                    id: me.opts.id
                },
                'success': function(data) {
                    $.loadingIndicator.close(function() {
                        $.subscribe('plugin/swModal/onOpen', $.proxy(me._onSetContent, me));

                        $.modal.open(data, {
                            width: '80%',
                            height: '80%',
                            additionalClass: 'select-address--modal',
                            sizing: sizing,
                            title: me.opts.title
                        });

                        $.unsubscribe('plugin/swModal/onOpen');
                    });

                    $.publish('plugin/swAddressSelector/onAddressFetchSuccess', [ me, event, data ]);
                }
            });
        },

        /**
         * Callback from $.modal setContent method
         *
         * @param event
         * @param $modal
         * @private
         */
        _onSetContent: function(event, $modal) {
            var me = this;

            me._registerPlugins();
            me._bindButtonAction($modal);
        },

        /**
         * Re-register plugins to enable them in the modal
         * @private
         */
        _registerPlugins: function() {
            window.StateManager
                .addPlugin('*[data-panel-auto-resizer="true"]', 'swPanelAutoResizer', {}, ['m', 'l', 'xl'])
                .addPlugin('*[data-preloader-button="true"]', 'swPreloaderButton');

            $.publish('plugin/swAddressSelector/onRegisterPlugins', [ this ]);
        },

        /**
         * Registers listeners for the click event on the "select address" buttons. The buttons contain the
         * needed data for the address selection. It then sends an ajax post request to the provided
         * action, defined by `data-action`
         *
         * @param $modal
         * @private
         */
        _bindButtonAction: function($modal) {
            var me = this;

            if (me.opts.action.length === 0) {
                return;
            }

            $.publish('plugin/swAddressSelector/onBeforeBindButtonAction', [ me, $modal ]);

            $modal._$content.find(me.opts.selectButtonSelector).on('click', function(event) {
                var $target = $(event.target),
                    actionData = {};

                event.preventDefault();

                actionData.target = $target.attr('data-target');
                actionData.addressId = $target.attr('data-address-id');

                $.publish('plugin/swAddressSelector/onBeforeSave', [ me, event, me.opts.action, actionData ]);

                // send data to api endpoint
                $.ajax({
                    url: me.opts.action,
                    method: 'POST',
                    data: actionData,
                    success: $.proxy(me.onSave, me)
                });
            });

            $.publish('plugin/swAddressSelector/onAfterBindButtonAction', [ me, $modal ]);
        },

        /**
         * Callback after the API has been called
         */
        onSave: function() {
            var me = this;

            $.publish('plugin/swAddressSelector/onAfterSave', [ me ]);

            window.location.reload();
        },

        /** Destroys the plugin */
        destroy: function () {
            this.$el.off(this.getEventName('click'));

            this._destroy();
        }
    });
})(jQuery, window);
