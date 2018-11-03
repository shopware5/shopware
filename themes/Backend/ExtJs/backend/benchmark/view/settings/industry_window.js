
//{namespace name="backend/benchmark/main"}
//{block name="backend/benchmark/view/settings/industry_window"}
Ext.define('Shopware.apps.Benchmark.view.settings.IndustryWindow', {
    extend: 'Enlight.app.Window',
    alias: 'widget.benchmark-settings-industry-window',

    height: 140,
    width: 320,

    title: '{s name="settings/industry_window/title"}Change industry{/s}',

    /**
     * Used by settings.js controller to display an additional text above the industry combo
     */
    additionalText: '',

    /**
     * Used by settings.js controller to call a custom callback after industry is saved
     */
    customCallback: Ext.emptyFn,

    initComponent: function () {
        var items = [];

        if (this.additionalText) {
            items.push(Ext.create('Ext.form.field.Display', {
                fieldStyle: 'color: #61677f; font-weight: bold; margin-bottom: 5px;',
                value: this.additionalText
            }));
        }

        items.push(this.createIndustryCombo());

        this.items = Ext.create('Ext.panel.Panel', {
            layout: 'anchor',
            bodyPadding: 10,
            border: 0,
            items: items
        });

        this.dockedItems = this.createBottomBar();

        this.callParent(arguments);
    },

    /**
     * @returns { Ext.form.field.ComboBox }
     */
    createIndustryCombo: function () {
        var me = this,
            industryStore = Ext.create('Shopware.apps.Benchmark.store.Industry');

        me.industryCombo = Ext.create('Ext.form.field.ComboBox', {
            store: industryStore,
            queryMode: 'local',
            fieldLabel: '{s name="settings/industry_window/label"}Choose industry{/s}',
            emptyText: '{s name="settings/industry_window/empty"}Please choose your industry{/s}',
            anchor: '100%',
            displayField: 'name',
            valueField: 'id',
            editable: false,
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

        if (this.additionalText) {
            industryStore.removeAt(0);
        }

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
                me.fireEvent('saveIndustry', me, me.industryCombo.getValue(), me.customCallback);
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
