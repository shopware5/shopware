
//{namespace name=backend/application/main}

Ext.define('Shopware.listing.InfoPanel', {
    extend: 'Ext.panel.Panel',

    alias: 'widget.listing-info-panel',

    /**
     * List of classes to mix into this class.
     * @type { Object }
     */
    mixins: {
        helper: 'Shopware.model.Helper'
    },

    region: 'east',
    width: 200,
    cls: 'detail-view',
    collapsible: true,
    layout: 'fit',

    title: '{s name="info_panel/title"}Detailed information{/s}',

    /**
     * Override required!
     * This function is used to override the { @link #displayConfig } object of the statics() object.
     *
     * @returns { Object }
     */
    configure: function() {
        return { };
    },

    /**
     * Get the reference to the class from which this object was instantiated.
     * Note that unlike self, this.statics() is scope-independent and it always
     * returns the class from which it was called, regardless of what this points to during run-time
     *
     * @type { Object }
     */
    statics: {

        /**
         * The displayConfig contains the default shopware configuration for
         * this component.
         * To set the shopware configuration, you can use the configure function and set an object as return value
         *
         * @example
         *      Ext.define('Shopware.apps.Product.view.listing.extension.Info', {
         *          extend: 'Shopware.listing.InfoPanel',
         *          configure: function() {
         *              return {
         *                  model: 'Shopware.apps.Product.model.Product,
         *                  ...
         *              }
         *          }
         *      });
         */
        displayConfig: {
            model: undefined,
            fields: {  },
            emptyText: '{s name="info_panel/empty_text"}No record selected.{/s}'
        },

        /**
         * Static function to merge the different configuration values
         * which passed in the class constructor.
         * @param { Object } userOpts
         * @param { Object } definition
         * @returns Object
         */
        getDisplayConfig: function (userOpts, definition) {
            var config = { };

            if (userOpts && typeof userOpts.configure == 'function') {
                config = Ext.apply({ }, config, userOpts.configure());
            }
            if (definition && typeof definition.configure === 'function') {
                config = Ext.apply({ }, config, definition.configure());
            }
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

        me._opts = me.statics().getDisplayConfig(opts, this);
        me.callParent(arguments);
    },


    /**
     * Helper function to get config access.
     *
     * @param prop string
     * @returns mixed
     * @constructor
     */
    getConfig: function (prop) {
        var me = this;
        return me._opts[prop];
    },

    /**
     * The initComponent template method is an important initialization step for a Component.
     * It is intended to be implemented by each subclass of Ext.Component to provide any needed constructor logic.
     * The initComponent method of the class being created is called first, with each initComponent method up the hierarchy
     * to Ext.Component being called thereafter. This makes it easy to implement and, if needed, override the constructor
     * logic of the Component at any step in the hierarchy.
     * The initComponent method must contain a call to callParent in order to ensure that the parent class'
     * initComponent method is also called.
     * All config options passed to the constructor are applied to this before initComponent is called, so you
     * can simply access them with this.someOption.
     */
    initComponent: function() {
        var me = this;

        me.gridPanel = me.listingWindow.gridPanel;

        me.items = me.createItems();

        me.addEventListeners();

        me.callParent(arguments);
    },

    addEventListeners: function() {
        var me = this;

        me.gridPanel.on(me.gridPanel.eventAlias + '-selection-changed', function(grid, selModel, records) {
            var record = { };
            if (records.length > 0) {
                record = records.shift();
            }
            me.updateInfoView(record);
        });
    },

    createItems: function() {
        var me = this, items = [];

        items.push(me.createInfoView());

        return items;
    },

    createInfoView: function(){
        var me = this;

        me.infoView = Ext.create('Ext.view.View', {
            tpl: me.createTemplate(),
            flex: 1,
            style: 'color: #6c818f;font-size:11px',
            emptyText: '<div style="font-size:13px; text-align: center;">' + me.getConfig('emptyText') + '</div>',
            deferEmptyText: false,
            itemSelector: 'div.item',
            renderData: []
        });

        return me.infoView;
    },

    createTemplate: function() {
        var me = this, fields = [], model, keys, field, config,
            configFields = me.getConfig('fields');

        if (me.getConfig('model')) {
            model = Ext.create(me.getConfig('model'));
            keys = model.fields.keys;
            if (Object.keys(configFields).length > 0) keys = Object.keys(configFields);

            Ext.each(keys, function(key) {
                field = me.getFieldByName(model.fields.items, key);
                config = configFields[key];

                if (Ext.isObject(config) || (Ext.isString(config) && config.length > 0)) {
                    fields.push(config);
                } else {
                    fields.push(me.createTemplateForField(model, field));
                }
            });
        }

        return new Ext.XTemplate(
            '<tpl for=".">',
                '<div class="item" style="">',
                    fields.join(''),
                '</div>',
            '</tpl>'
        );
    },

    createTemplateForField: function(model, field) {
        return '<p style="padding: 2px"><b>' + field.name +':</b> {literal}{' + field.name + '}{/literal}</p>'
    },


    updateInfoView: function(record) {
        var me = this;

        if (record.data) {
            me.infoView.update(record.data);
        } else {
            me.infoView.update(me.infoView.emptyText);
        }

        return true;
    }
});