
//{namespace name=backend/base/color_components}

//{block name="backend/base/color_window"}

Ext.define('Shopware.color.Window', {
    extend: 'Enlight.app.SubWindow',

    title: '{s name="color_window_title"}Color selection{/s}',
    width: 377,
    height: 490,

    layout: {
        type: 'vbox',
        align: 'stretch'
    },

    resizable: false,
    maximizable: false,
    minimizable: false,
    closable: false,

    initComponent: function () {
        var me = this;

        me.items = me.createItems();
        me.dockedItems = me.createDockedItems();

        me.callParent(arguments);
    },

    createItems: function () {
        var me = this;

        me.formPanel = Ext.create('Ext.form.Panel', {
            flex: 1,
            items: [
                me.createColorSelection(),
                me.createSelectedColorField()
            ]
        });

        return [
            me.formPanel
        ];
    },

    createColorSelection: function() {
        var me = this;

        me.colorSelection = Ext.create('Shopware.form.field.ColorSelection', {
            parentWindow: me
        });

        me.colorSelection.on('color-changed', function(window, value) {
            me.selectedColorField.setValue(value);
        });

        return me.colorSelection;
    },

    createSelectedColorField: function() {
        var me = this;

        me.selectedColorField = Ext.create('Shopware.form.field.ColorField', {
            pickerButton: false,
            editable: false,
            value: me.value,
            fieldLabel: '{s name="color_field_label"}Selected color{/s}',
            margin: 10
        });

        return me.selectedColorField;
    },

    createDockedItems: function () {
        var me = this;

        return [ me.createToolbar() ];
    },

    createToolbar: function () {
        var me = this;

        me.toolbar = Ext.create('Ext.toolbar.Toolbar', {
            dock: 'bottom',
            items: me.createToolbarItems()
        });

        return me.toolbar;
    },

    createToolbarItems: function () {
        var me = this;

        return [
            '->',
            me.createCancelButton(),
            me.createApplyButton()
        ];
    },

    createCancelButton: function () {
        var me = this;

        me.cancelButton = Ext.create('Ext.button.Button', {
            text: '{s name=color_window_cancel}Cancel{/s}',
            cls: 'small',
            handler: function () {
                me.destroy();
            }
        });

        return me.cancelButton;
    },

    createApplyButton: function () {
        var me = this;

        me.applyButton = Ext.create('Ext.button.Button', {
            text:  '{s name=color_window_apply}Apply{/s}',
            cls: 'primary small',
            handler: function () {
                me.fireEvent('apply-color', me, me.selectedColorField.getValue());
            }
        });

        return me.applyButton;
    }

});

//{/block}
