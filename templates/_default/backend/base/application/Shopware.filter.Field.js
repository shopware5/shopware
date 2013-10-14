
//{namespace name=backend/application/main}
//{block name="backend/application/Shopware.filter.Field"}
Ext.define('Shopware.filter.Field', {

    /**
     * The parent class that this class extends
     * @type { String }
     */
    extend: 'Ext.form.FieldContainer',

    /**
     * Specifies the padding for this component. The padding can be a single numeric value to apply to all
     * sides or it can be a CSS style specification for each style, for example: '10 5 3 10' (top, right, bottom, left).
     *
     * @type { int|string }
     */
    padding: 10,

    /**
     * Important: In order for child items to be correctly sized and positioned, typically a layout manager must be
     * specified through the layout configuration option.
     * The sizing and positioning of child items is the responsibility of the Container's layout manager which
     * creates and manages the type of layout you have in mind. For example:
     * If the layout configuration is not explicitly specified for a general purpose container (e.g. Container or Panel)
     * the default layout manager will be used which does nothing but render child components sequentially into the
     * Container (no sizing or positioning will be performed in this situation).
     *
     * @type { Object }
     */
    layout: {
        type: 'hbox',
        align: 'stretch'
    },

    /**
     * A custom style specification to be applied to this component's Element. Should be a valid argument to Ext.Element.applyStyles.
     * @type { String }
     */
    style: 'background: #fff',

    /**
     * List of classes to mix into this class.
     * @type { Object }
     */
    mixins: {
        helper: 'Shopware.model.Helper'
    },


    /**
     * Instance of the prepended checkbox. The checkbox is used
     * to enable or disable the filter field. A disabled filter field
     * returns no value if you call the updateRecord or getValues function
     * of the form panel.
     *
     * @type { Ext.form.field.Checkbox }
     */
    checkbox: undefined,

    /**
     * Instance of the passed field object which will be displayed
     * as filter field.
     * This field is created from the { @link Shopware.listing.FilterPanel } and
     * will be wrapped with this component to enable or disable the filter values.
     * @type { Ext.form.field.Field }
     */
    field: undefined,

    /**
     * Get the reference to the class from which this object was instantiated. Note that unlike self, this.statics()
     * is scope-independent and it always returns the class from which it was called, regardless of what
     * this points to during run-time.
     *
     * The statics object contains the shopware default configuration for
     * this component. The different shopware configurations are stored
     * within the displayConfig object.
     *
     * @type { object }
     */
    statics: {
        /**
         * The statics displayConfig contains the default shopware configuration for
         * this component.
         * To set the shopware configuration, you can use the configure function and set an object as return value
         *
         * @example
         *      Ext.define('Shopware.apps.Product.view.filter.Field', {
         *          extend: 'Shopware.filter.Field',
         *          configure: function() {
         *              return {
         *                  ...
         *              }
         *          }
         *      });
         */
        displayConfig: {
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

        me.checkbox = Ext.create('Ext.form.field.Checkbox', {
            width: 28,
            margin: '2 0 0 0'
        });

        me.checkbox.on('change', function(checkbox, value) {
            var field = me.items.items[1];
            if (!field) return false;

            if (value) {
                field.enable();
            } else {
                field.disable()
            }
        });

        me.field.flex = 1;
        me.field.labelWidth = 100;
        me.field.disabled = true;
        me.field.margin = 0;

        me.items = [
            me.checkbox,
            me.field
        ];

        me.callParent(arguments);
    }
});

//{/block}
