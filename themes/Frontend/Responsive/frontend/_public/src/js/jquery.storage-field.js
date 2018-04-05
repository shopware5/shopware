;(function($, window) {
    'use strict';

    /**
     * Storage Field Plugin
     *
     * The plugin stores the content of a form field in the local storage of the browser.
     * This is in case the user performs an action that causes a page reload.
     * The Plugin will then populate the field when the page is reloaded
     */
    $.plugin('swStorageField', {

        defaults: {

            /**
             * Select the type of local storage in which the value schould be stored.
             *
             * @property storageType ( session | local )
             * @type {String}
             */
            storageType: 'session',

            /**
             * Define a prefix for the storage key.
             *
             * @property storageKeyPrefix
             * @type {String}
             */
            storageKeyPrefix: 'sw-local-',

            /**
             * Define a specific storage key name.
             * If this is not defined the name attribute of the field is used.
             *
             * @property storageKeyName
             * @type {String}
             */
            storageKeyName: null,

            /**
             * Define the event on which the value should be stored to the storage.
             *
             * @property storeEvent
             * @type {String}
             */
            storeEvent: 'blur'
        },

        init: function () {
            var me = this;

            me.applyDataAttributes();

            me.storage = window.StorageManager.getStorage(me.opts.storageType);

            me.storageKey = me.getStorageKey();

            me.$form = me.getParentForm();

            me.setFieldValueFromStorage();
            me.registerEvents();

            $.publish('plugin/swStorageField/init', [ me ]);
        },

        getStorageKey: function () {
            var me = this,
                fieldName = me.$el.attr('name'),
                key = me.opts.storageKeyPrefix;

            if (me.opts.storageKeyName !== null) {
                key += me.opts.storageKeyName.toLowerCase();

            } else if (fieldName && fieldName.length) {
                key += fieldName.toLowerCase();
            }

            $.publish('plugin/swStorageField/getStorageKey', [ me, key ]);

            return key;
        },

        getParentForm: function () {
            var me = this,
                $form = me.$el.parents('form');

            // The field is just a pseudo field for another field
            if (me.$el.is('[data-selector]')) {
                $form = $(me.$el.attr('data-selector')).parents('form');
            }

            $.publish('plugin/swStorageField/getParentForm', [ me, $form ]);

            return ($form.length > 0) ? $form : null;
        },

        setFieldValueFromStorage: function () {
            var me = this,
                value = me.storage.getItem(me.storageKey);

            if (value && value.length) {
                me.$el.val(value);
                
                // When the field is just a pseudo field also fill the original field.
                if (me.$el.is('[data-selector]')) {
                    $(me.$el.attr('data-selector')).val(value);
                }
            }

            $.publish('plugin/swStorageField/setFieldValueFromStorage', [ me ]);
        },

        registerEvents: function () {
            var me = this;

            me._on(me.$el, me.opts.storeEvent, $.proxy(me.storeValue, me));

            if (me.$form && me.$form !== null) {
                me._on(me.$form, 'submit', $.proxy(me.onFormSubmit, me));
            }

            $.publish('plugin/swStorageField/onRegisterEvents', [ me ]);
        },

        storeValue: function () {
            var me = this,
                value = me.$el.val();

            me.storage.setItem(me.storageKey, value);

            $.publish('plugin/swStorageField/storeValue', [ me ]);
        },

        onFormSubmit: function () {
            var me = this;

            me.storage.removeItem(me.storageKey);

            $.publish('plugin/swStorageField/onFormSubmit', [ me ]);
        },

        destroy: function() {
            var me = this;

            me._destroy();
        }
    });

})(jQuery, window);
