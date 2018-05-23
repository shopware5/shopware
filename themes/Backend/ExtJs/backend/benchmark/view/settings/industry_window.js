
//{namespace name="backend/benchmark/main"}
//{block name="backend/benchmark/view/settings/industry_window"}
Ext.define('Shopware.apps.Benchmark.view.settings.IndustryWindow', {
    extend: 'Enlight.app.Window',
    alias: 'widget.benchmark-settings-industry-window',

    height: 130,
    width: 320,

    title: '{s name="settings/industry_window/title"}Change industry{/s}',

    initComponent: function () {
        this.items = Ext.create('Ext.panel.Panel', {
            layout: 'anchor',
            bodyPadding: 10,
            border: 0,
            items: this.createIndustryCombo()
        });

        this.dockedItems = this.createBottomBar();

        this.callParent(arguments);
    },

    /**
     * @returns { Ext.form.field.ComboBox }
     */
    createIndustryCombo: function () {
        var me = this;
        me.industryCombo = Ext.create('Ext.form.field.ComboBox', {
            store: Ext.create('Shopware.apps.Benchmark.store.Industry'),
            fieldLabel: '{s name="settings/industry_window/label"}Choose industry{/s}',
            emptyText: '{s name="settings/industry_window/empty"}Please choose your industry{/s}',
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

        return me.industryCombo;
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
            text: '{s name="settings/industry_window/save"}Save industry{/s}',
            handler: function () {
                me.fireEvent('saveIndustry', me, me.industryCombo.getValue());
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
