Ext.define('Shopware.apps.Theme.view.detail.elements.ColorPicker', {
    extend: 'Ext.form.FieldContainer',
    alias: 'widget.theme-color-picker',
    cls: 'theme-custom-field',

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

    initComponent: function () {
        var me = this;

        me.inputField = me.createInputField();
        me.colorField = me.createColorField();

        me.items = [
            me.inputField,
            me.colorField
        ];

        if (me.value) {
            me.inputField.setValue(me.value);
            me.valueChanged(me.value);
        }

        //listen to change event to change the color field background.
        me.inputField.on('change', function(field, newValue) {
            me.valueChanged(newValue);
        });

        me.callParent(arguments);
    },

    createInputField: function () {
        var me = this;

        return Ext.create('Ext.form.field.Text', {
            name: me.name,
            flex: 1
        });
    },

    createColorField: function () {
        return Ext.create('Ext.form.field.Text', {
            width: 30,
            readOnly: true
        });
    },

    getValue: function () {
        return this.inputField.getValue();
    },

    setValue: function (value) {
        var color = '#fff';
        if (value) {
            color = value;
        }
        this.valueChanged(color);

        return this.inputField.setValue(value)
    },

    getSubmitData: function () {
        return this.inputField.getSubmitData();
    },

    valueChanged: function(value) {
        this.colorField.setFieldStyle('background: ' + value);
    }

});
