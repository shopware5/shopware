
//{namespace name="backend/benchmark/main"}
//{block name="backend/benchmark/view/settings/business_window"}
Ext.define('Shopware.apps.Benchmark.view.settings.BusinessWindow', {
    extend: 'Enlight.app.Window',
    alias: 'widget.benchmark-settings-business-window',

    height: 130,
    width: 320,

    title: '{s name="settings/business_window/title"}Change business{/s}',

    initComponent: function () {
        this.items = Ext.create('Ext.panel.Panel', {
            layout: 'anchor',
            bodyPadding: 10,
            border: 0,
            items: this.createBusinessCombo()
        });

        this.dockedItems = this.createBottomBar();

        this.callParent(arguments);
    },

    /**
     * @returns { Ext.form.field.ComboBox }
     */
    createBusinessCombo: function () {
        var me = this;
        me.businessCombo = Ext.create('Ext.form.field.ComboBox', {
            store: Ext.create('Shopware.apps.Benchmark.store.Business'),
            fieldLabel: '{s name="settings/business_window/label"}Choose business{/s}',
            emptyText: '{s name="settings/business_window/empty"}Please choose your business{/s}',
            anchor: '100%',
            displayField: 'name',
            valueField: 'id',
            listeners: {
                change: function (el, value) {
                    if (value === null || value === '') {
                        me.saveButton.disable();
                        return;
                    }

                    me.saveButton.enable();
                }
            }
        });

        return me.businessCombo;
    },

    /**
     * @returns { Ext.toolbar.Toolbar }
     */
    createBottomBar: function () {
        var me = this;

        me.saveButton = Ext.create('Ext.button.Button', {
            xtype: 'button',
            disabled: true,
            cls: 'primary',
            action: 'save',
            text: '{s name="settings/business_window/save"}Save business{/s}',
            handler: function () {
                me.fireEvent('saveBusiness', me, me.businessCombo.getValue());
            }
        });

        return Ext.create('Ext.toolbar.Toolbar', {
            dock: 'bottom',
            items: [{
                xtype: 'tbfill'
            }, me.saveButton]
        });
    }
});
//{/block}
