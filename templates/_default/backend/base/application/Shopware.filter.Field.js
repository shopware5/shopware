
//{namespace name=backend/application/main}

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
