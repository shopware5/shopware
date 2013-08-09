//{block name="backend/component/controller/detail"}

/**
 *
 */
Ext.define('Shopware.detail.Controller', {
    extend: 'Enlight.app.Controller',

    /**
     * List of classes to mix into this class.
     * @type { Object }
     */
    mixins: {
        helper: 'Shopware.model.Helper'
    },

    /**
     * The statics object contains the shopware default configuration for
     * this component.
     *
     * @type { object }
     */
    statics: {
        displayConfig: {
            /**
             * Final class of the Shopware.window.Detail.
             * This class is required to get the alias of the component.
             *
             * @required
             * @type { string }
             */
            detailWindow: undefined,

            /**
             * Suffix alias for the different component events.
             * This alias must the same alias of the { @link Shopware.grid.Panel:eventAlias }  component.
             * If you don't know the alias you can output the alias of the grid panel as follow:
             * console.log("alias", me.eventAlias);
             *
             * If you haven't configured a custom event alias, the { @link Shopware.grid.Panel } creates
             * the event alias over the configured model.
             * @example
             * If you passed a store with an model named: 'Shopware.apps.Product.model.Product'
             * the { @link Shopware.grid.Panel } use "product" as event alias.
             *
             * @required
             * @type { string }
             */
            eventAlias: undefined
        },

        /**
         * Static function to merge the different configuration values
         * which passed in the class constructor.
         *
         * @param userOpts Object
         * @param displayConfig Object
         * @returns Object
         */
        getDisplayConfig: function (userOpts, displayConfig) {
            var config;

            config = Ext.apply({ }, userOpts.displayConfig, displayConfig);
            config = Ext.apply({ }, config, this.displayConfig);

            return config;
        },

        /**
         * Static function which sets the property value of
         * the passed property and value in the display configuration.
         *
         * @param prop
         * @param val
         * @returns boolean
         */
        setDisplayConfig: function (prop, val) {
            var me = this;

            if (!me.displayConfig.hasOwnProperty(prop)) {
                return false;
            }
            me.displayConfig[prop] = val;
            return true;
        }
    },


    /**
     * Class constructor which merges the different configurations.
     * @param opts
     */
    constructor: function (opts) {
        var me = this;

        me._opts = me.statics().getDisplayConfig(opts, this.displayConfig);
        me.callParent(arguments);
    },


    /**
     * Helper function to get config access.
     * @param prop string
     * @returns mixed
     * @constructor
     */
    getConfig: function (prop) {
        var me = this;
        return me._opts[prop];
    },

    /**
     * Initialisation of this component.
     * The function calls the internal function createListingWindow to open
     * the listing window.
     * After the window created the function adds the event controls
     * over the createControls function.
     */
    init: function () {
        var me = this;

        if (me.getConfig('eventAlias')) {
            me.control(me.createControls());
        }

        me.callParent(arguments);
    },

    reloadControls: function() {
        var me = this;

        if (me.getConfig('eventAlias')) {
            me.control(me.createControls());
        }
    },

    /**
     * Creates the control object which contains all event listener
     * definitions for this controller.
     *
     * This function requires the displayConfig.listingGrid parameter.
     * If this parameter isn't set, the function won't be called.
     *
     * @returns { Object }
     */
    createControls: function () {
        var me = this, alias, controls = {};

        alias = Ext.ClassManager.getAliasesByName(me.getConfig('detailWindow'));
        alias = alias[0];
        alias = alias.replace('widget.', '');
        controls[alias] = me.createDetailWindowControls();

        return controls;
    },


    createDetailWindowControls: function() {
        var me = this, events = {}, alias;

        alias = me.getConfig('eventAlias');

        events[alias + '-save'] = me.onSave;

        return events;
    },

    /**
     *
     * @param window { Shopware.window.Detail }
     * @param record { Shopware.data.Model }
     */
    onSave: function(window, record) {
        var me = this, proxy = record.getProxy(), data, form = window.formPanel;

        if (!form.getForm().isValid()) {
            return false;
        }
        form.getForm().updateRecord(record);

        proxy.on('exception', function (proxy, response, operation) {
            data = Ext.decode(response.responseText);
            if (data.violations && data.violations.length > 0) {
                me.createViolationMessage(data.violations);
                me.markFieldsAsInvalid(window, data.violations);
            }
        }, me, { single: true });

        record.save({
            success: function(result) {
                Shopware.Notification.createGrowlMessage('Success', 'Item saved successfully');
                window.loadRecord(result);
            }
        });
    },

    createViolationMessage: function(violations) {
        var template = '';

        Ext.each(violations, function(violation) {
            template += '<li style="line-height: 13px; padding: 3px 0"><b>' + violation.property + '</b>: ' + violation.message + '</li>';
        });

        template = '<ul>' + template + '</ul>';
        Shopware.Notification.createStickyGrowlMessage({
            title: 'Violation errors',
            text: template,
            width: 400
        });
    },

    markFieldsAsInvalid: function(window, violations) {
        var me = this;
        
        Ext.each(violations, function(violation) {
            var field = me.getFieldByName(window, violation.property);
            if (field) {
                field.focus();
                field.markInvalid(violation.message);
            }
        });
    },

    getFieldByName: function(window, fieldName) {
        var me = this, result = undefined,
            fields = window.formPanel.getForm().getFields();

        fields.each(function(field) {
            if (field.name === fieldName) {
                result = field;
                return false;
            }
        });
        return result;
    },

    /**
     * Helper function to prefix the passed event name with the event alias.
     *
     * @param name
     * @returns { string }
     */
    getEventName: function (name) {
        return this.getConfig('eventAlias') + '-' + name;
    }

});
//{/block}
