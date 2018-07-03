
//{namespace name="backend/benchmark/main"}
//{block name="backend/benchmark/view/settings/window"}
Ext.define('Shopware.apps.Benchmark.view.settings.Window', {
    extend: 'Enlight.app.Window',
    alias: 'widget.benchmark-settings-window',
    height: 450,
    width: 440,
    layout: {
        type: 'vbox',
        pack: 'start',
        align: 'stretch'
    },

    title: '{s name="settings/title"}Shopware BI Settings{/s}',

    initComponent: function () {
        this.items = this.createItems();

        this.callParent(arguments);
    },

    loadRecord: function (record) {
        this.down('form[name=benchmark-settings-panel]').loadRecord(record);
    },

    /**
     * @returns { Ext.form.Panel[] }
     */
    createItems: function () {
        var me = this,
            shopFieldSet = this.createShopFieldSet();

        return [
            Ext.create('Ext.form.Panel', {
                flex: 1,
                name: 'benchmark-settings-panel',
                bodyPadding: 10,
                items: [
                    shopFieldSet,
                    this.createIndustryFieldSet(),
                    /*{if {acl_is_allowed privilege=manage}}*/
                    this.createBusinessPlanFieldSet()
                    /*{/if}*/
                ],

                /**
                 * @param { Object } settingsData
                 */
                loadSettingsRecord: function (settingsData) {
                    var active = settingsData.data.active;

                    me.deActivationContainer[~~(active) === 1 ? 'show' : 'hide']();
                    me.activationContainer[~~(active) === 0 ? 'show' : 'hide']();

                    this.loadRecord(settingsData);
                }
            })];
    },

    /**
     * @returns { Ext.form.FieldSet }
     */
    createShopFieldSet: function () {
        this.activationContainer = this.createActivationContainer();
        this.deActivationContainer = this.createDeactivationContainer();

        return Ext.create('Ext.form.FieldSet', {
            title: '{s name="settings/fieldsets/shop/title"}Shop selection{/s}',
            name: 'shopFieldSet',
            items: [
                this.createShopSelection(),
                this.activationContainer,
                this.deActivationContainer
            ]
        });
    },

    /**
     * @returns { Ext.form.field.ComboBox }
     */
    createShopSelection: function () {
        var me = this;

        me.comboBox = Ext.create('Ext.form.field.ComboBox', {
            anchor: '100%',
            name: 'shopSelection',
            displayField: 'shopName',
            valueField: 'shopId',
            editable: false,
            store: Ext.create('Shopware.apps.Benchmark.store.ShopConfigs').load(function () {
                me.fireEvent('shopConfigLoaded', this, me.comboBox);
            }),
            fieldLabel: 'Shop',
            listeners: {
                select: function (combo, records) {
                    me.fireEvent('configSelected', me, combo, records[0]);
                }
            }
        });

        return me.comboBox;
    },

    /**
     * @returns { Ext.container.Container }
     */
    createActivationContainer: function () {
        var me = this;

        return Ext.create('Ext.container.Container', {
            layout: 'hbox',
            name: 'activationContainer',
            style: {
                margin: '20px 0'
            },
            hidden: true,
            items: [
                /*{if {acl_is_allowed privilege=manage}}*/
                {
                    xtype: 'container',
                    html: '{s name="settings/fieldsets/shop/activation_text"}I would like to participate.{/s}',
                    style: {
                        fontWeight: 'bold',
                        fontSize: '11px',
                        color: '#475c6a'
                    },
                    flex: 1
                },
                {
                    xtype: 'button',
                    text: '{s name="settings/fieldsets/shop/activation_button"}Participate now{/s}',
                    cls: 'primary',
                    flex: 1,
                    handler: function () {
                        me.fireEvent('activateBenchmark');
                    }
                }
                /*{/if}*/
            ]
        });
    },

    /**
     * @returns { Ext.form.FieldSet }
     */
    createDeactivationContainer: function () {
        var me = this;

        return Ext.create('Ext.container.Container', {
            layout: 'hbox',
            name: 'deActivationContainer',
            style: {
                margin: '20px 0'
            },
            hidden: true,
            items: [
                /*{if {acl_is_allowed privilege=manage}}*/
                {
                    xtype: 'container',
                    html: '{s name="settings/fieldsets/shop/deactivation_text"}I want to stop the service.{/s}',
                    style: {
                        fontWeight: 'bold',
                        fontSize: '11px',
                        color: '#475c6a'
                    },
                    flex: 1
                }, {
                    xtype: 'button',
                    text: '{s name="settings/fieldsets/shop/deactivation_button"}Stop the service{/s}',
                    cls: 'primary',
                    flex: 1,
                    handler: function () {
                        me.fireEvent('deactivateBenchmark');
                    }
                }
                /*{/if}*/
            ]
        });
    },

    /**
     * @returns { Ext.form.FieldSet }
     */
    createIndustryFieldSet: function () {
        return Ext.create('Ext.form.FieldSet', {
            title: '{s name="settings/fieldsets/industry/title"}Industry{/s}',
            name: 'industryFieldSet',
            items: [{
                xtype: 'industryfield',
                name: 'industry',
                fieldLabel: '{s name="settings/industry_window/label"}Choose industry{/s}',
                labelWidth: 200,
                store: Ext.create('Shopware.apps.Benchmark.store.Industry')
            }]
        });
    },

    /**
     * @returns { Ext.form.FieldSet }
     */
    createBusinessPlanFieldSet: function () {
        var me = this,
            b2bText = '<b>{s name="settings/fieldsets/business_plan/b2bText"}Business to Business{/s} ({s name="settings/fieldsets/business_plan/b2bShortText"}B2B{/s})</b>',
            b2cText = '<b>{s name="settings/fieldsets/business_plan/b2cText"}Business to Consumer{/s} ({s name="settings/fieldsets/business_plan/b2cShortText"}(B2C){/s})</b>';

        return Ext.create('Ext.form.FieldSet', {
            title: '{s name="settings/fieldsets/business_plan/title"}Business plan{/s}',
            name: 'typeFieldSet',
            items: [
                Ext.create('Ext.form.RadioGroup', {
                    columns: 1,
                    name: 'typeRadios',
                    items: [
                        {
                            name: 'type',
                            inputValue: 'b2c',
                            boxLabel: b2cText
                        },
                        {
                            name: 'type',
                            inputValue: 'b2b',
                            boxLabel: b2bText
                        },
                    ]
                }),
                /*{if {acl_is_allowed privilege=manage}}*/
                {
                    xtype: 'button',
                    text: '{s name="settings/fieldsets/business_plan/save_button"}Save{/s}',
                    cls: 'primary',
                    style: {
                        float: 'right'
                    },
                    handler: function () {
                        me.fireEvent('saveType');
                    }
                }
                /* {/if}*/
            ]
        });
    }
});
//{/block}
