

//{namespace name=backend/application/main}

//{block name="backend/application/Shopware.data.Model"}
Ext.define('Shopware.data.Model', {

    /**
     * The parent class that this class extends
     * @type { String }
     */
    extend: 'Ext.data.Model',

    /**
     * Define default proxy and reader to use every time the Shopware.data.reader.Application.
     * This own reader contains an ext js fix for deep association loading
     */
    proxy: {
        type: 'ajax',
        reader: {
            type: 'application'
        }
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
         * The displayConfig contains the default shopware configuration for this component.
         * To set the shopware configuration, you can use the configure function and set an object as return value
         *
         * @example
         *      Ext.define('Shopware.apps.Product.model.Product', {
         *          extend: 'Shopware.data.Model',
         *          configure: function() {
         *              return {
         *                  listing: 'Shopware.apps.Product.view.list.Product',
         *                  ...
         *              }
         *          }
         *      });
         */
        displayConfig: {

            /**
             * @required - For the base model.
             *
             * Class of the php controller
             * This property is required for the CRUD operations of the Shopware.data.Model.
             * The proxy api reference first on the base controller. The base controller
             * path will be replaced with this controller name.
             *
             * @example
             * PHP Controller = Shopware_Controllers_Backend_Article
             * value of this property => 'article'
             *
             * @type { String }
             */
            controller: undefined,

            /**
             * Listing component of this model.
             * The component configuration is used for the detail window of an application.
             * Listing components are used for OneToMany associations.
             * As default shopware creates a { @link Shopware.grid.Panel }.
             * Each view definition is required if you want to support
             * that other applications can be linked to your models.
             *
             * @type { String }
             */
            listing: 'Shopware.grid.Panel',

            /**
             * Detail component of this model.
             * The component configuration is used for the detail window of an application.
             * Detail components are used for OneToOne associations or for the base record of a detail window.
             * As default shopware creates a { @link Shopware.model.Container }.
             * Each view definition is required if you want to support
             * that other applications can be linked to your models.
             *
             * @type { String }
             */
            detail:  'Shopware.model.Container',

            /**
             * Related component of this model.
             * The component configuration is used for the detail window of an application.
             * Related components are used for ManyToMany associations.
             * As default shopware creates a { @link Shopware.grid.Association }.
             * Each view definition is required if you want to support
             * that other applications can be linked to your models.
             *
             * @type { String }
             */
            related: 'Shopware.grid.Association',

            /**
             * Field component of this model.
             * The component configuration is used for the detail window of an application.
             * Field components are used for ManyToOne associations.
             * As default shopware creates a { @link Shopware.form.field.Search }.
             * Each view definition is required if you want to support
             * that other applications can be linked to your models.
             *
             * @type { String }
             */
            field:   'Shopware.form.field.Search',

            /**
             * Model proxy which defines
             * the urls for the CRUD actions.
             */
            proxy: {
                type: 'ajax',
                api: {
                    detail:  '{url controller="base" action="detail"}',
                    create:  '{url controller="base" action="create"}',
                    update:  '{url controller="base" action="update"}',
                    destroy: '{url controller="base" action="delete"}'
                },
                reader: {
                    type: 'application',
                    root: 'data',
                    totalProperty: 'total'
                }
            }
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
     * Override required!
     * This function is used to override the { @link #displayConfig } object of the statics() object.
     *
     * @returns { Object }
     */
    configure: function() {
        return { };
    },

    /**
     * Class constructor.
     * Used to convert the proxy api urls.
     *
     * @param config
     */
    constructor: function (config) {
        var me = this;

        me._opts = me.statics().getDisplayConfig(config, this);
        me.convertProxyApi();
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
     * Helper function which removes the base controller
     * path of the store api urls.
     * The base controller will be remove with the
     * configured controller name.
     */
    convertProxyApi: function () {
        var me = this, value;

        if (!me.getConfig('controller')) {
            return;
        }

        me.setProxy(me.getConfig('proxy'));

        Object.keys(me.proxy.api).forEach(function (key) {
            value = me.proxy.api[key] + '';
            value = value.replace(
                '/backend/base/', '/backend/' + me.getConfig('controller') + '/'
            );
            me.proxy.api[key] = value;
        });
    },


    /**
     * Helper function to load the model detail data.
     * This function creates a temp store with the
     * model proxy to send an ajax request on the controller
     * detailAction.
     * This action is used to load the model detail data.
     *
     * @param options object
     * @returns Shopware.data.Model
     */
    reload: function (options) {
        var me = this, proxy = me.proxy, callback = null;

        if (!Ext.isString(proxy.api.detail)) {
            if (options && Ext.isFunction(options.callback)) {
                options.callback(me);
            } else {
                return this;
            }
        }
        proxy.api.read = proxy.api.detail;

        var store = Ext.create('Ext.data.Store', {
            model: (typeof me.__proto__ === 'object') ? me.__proto__.$className : me.modelName,
            proxy: me.proxy
        });

        store.getProxy().extraParams = me.getReloadExtraParams();

        if (options && Ext.isFunction(options.callback)) {
            callback = options.callback;
        }

        options.callback = function (records, operation) {
            var record = records[0];
            if (Ext.isFunction(callback)) {
                callback(record, operation);
            }
        };

        try {
            store.load(options);
        } catch (e) {
            return e;
        }
    },

    getReloadExtraParams: function() {
        var me = this;

        return {
            id: me.get('id')
        }
    }
});
//{/block}
