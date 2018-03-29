
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

    title: '{s name="settings/title"}Benchmark Settings{/s}',

    initComponent: function () {
        this.items = this.createItems();

        // Is removed in controller/main.js
        this.setLoading(true);

        this.callParent(arguments);
    },

    /**
     * @returns { Ext.form.Panel }
     */
    createItems: function () {
        var me = this,
            activationFieldSet = this.createActivationFieldSet(),
            deActivationFieldSet = this.createDeactivationFieldSet();

        return [
            this.createNoticeContainer(),
            Ext.create('Ext.form.Panel', {
                flex: 1,
                name: 'benchmark-settings-panel',
                bodyPadding: 10,
                items: [
                    activationFieldSet,
                    deActivationFieldSet,
                    this.createSettingsFieldSet(),
                    this.createBusinessFieldSet(),
                    this.createInfoFieldSet()
                ],

                /**
                 * @param { Object } settingsData
                 */
                loadSettingsRecord: function (settingsData) {
                    var active = settingsData.data.active;

                    if (active === null) {
                        this.disable();
                        me.blockMessage.show();
                        return;
                    }

                    deActivationFieldSet[~~(active) === 1 ? 'show' : 'hide']();
                    activationFieldSet[~~(active) === 0 ? 'show' : 'hide']();

                    this.loadRecord(settingsData);
                }
            })];
    },

    /**
     *
     * @returns { Ext.container.Container }
     */
    createNoticeContainer: function () {
        this.blockMessage = Shopware.Notification.createBlockMessage('{s name="settings/notice/message"}Whoopsy. Head over to the Benchmark Overview first.{/s}', 'notice');
        this.blockMessage.style = { marginBottom: 0 };
        this.blockMessage.hide();

        return this.blockMessage;
    },

    /**
     * @returns { Ext.form.FieldSet }
     */
    createActivationFieldSet: function () {
        var me = this;

        return Ext.create('Ext.form.FieldSet', {
            title: '{s name="settings/fieldsets/activation/title"}Participate{/s}',
            name: 'activationFieldSet',
            hidden: true,
            items: Ext.create('Ext.container.Container', {
                layout: 'hbox',
                items: [
                    {
                        xtype: 'container',
                        html: '{s name="settings/fieldsets/activation/text"}I would like to participate.{/s}',
                        style: {
                            fontWeight: 'bold',
                            fontSize: '11px',
                            color: '#475c6a'
                        },
                        flex: 1
                    }, {
                        xtype: 'button',
                        text: '{s name="settings/fieldsets/activation/button"}Participate now{/s}',
                        cls: 'primary',
                        flex: 1,
                        handler: function () {
                            me.fireEvent('activateBenchmark');
                        }
                    }
                ]
            })
        });
    },

    /**
     * @returns { Ext.form.FieldSet }
     */
    createDeactivationFieldSet: function () {
        var me = this;

        return Ext.create('Ext.form.FieldSet', {
            title: '{s name="settings/fieldsets/deactivation/title"}Sign off{/s}',
            name: 'deactivationFieldSet',
            hidden: true,
            items: Ext.create('Ext.container.Container', {
                layout: 'hbox',
                items: [
                    {
                        xtype: 'container',
                        html: '{s name="settings/fieldsets/deactivation/text"}I want to stop the service.{/s}',
                        style: {
                            fontWeight: 'bold',
                            fontSize: '11px',
                            color: '#475c6a'
                        },
                        flex: 1
                    }, {
                        xtype: 'button',
                        text: '{s name="settings/fieldsets/deactivation/button"}Stop the service{/s}',
                        cls: 'primary',
                        flex: 1,
                        handler: function () {
                            me.fireEvent('deactivateBenchmark');
                        }
                    }
                ]
            })
        });
    },

    /**
     * @returns { Ext.form.FieldSet }
     */
    createSettingsFieldSet: function () {
        var me = this;

        return Ext.create('Ext.form.FieldSet', {
            title: '{s name="settings/fieldsets/settings/title"}Settings{/s}',
            items: Ext.create('Ext.container.Container', {
                layout: 'vbox',
                width: '100%',
                items: [{
                    xtype: 'numberfield',
                    fieldLabel: '{s name="settings/fieldsets/settings/ordersBatchSize"}Transmitted orders per request{/s}',
                    name: 'ordersBatchSize',
                    minValue: 0,
                    labelWidth: 200,
                    width: 300,
                    flex: 1
                }, {
                    xtype: 'container',
                    flex: 1,
                    width: '100%',
                    items: [{
                        xtype: 'button',
                        cls: 'primary',
                        style: {
                            position: 'absolute',
                            right: '0px'
                        },
                        text: '{s name="settings/fieldsets/settings/save"}Save settings{/s}',
                        handler: function () {
                            me.fireEvent('saveSettings');
                        }
                    }]
                }]
            })
        });
    },

    /**
     * @returns { Ext.form.FieldSet }
     */
    createBusinessFieldSet: function () {
        return Ext.create('Ext.form.FieldSet', {
            title: '{s name="settings/fieldsets/business/title"}Business{/s}',
            name: 'businessFieldSet',
            items: [{
                xtype: 'businessfield',
                name: 'business',
                fieldLabel: '{s name="settings/fieldsets/business/label"}Chosen business{/s}',
                labelWidth: 200,
                store: Ext.create('Shopware.apps.Benchmark.store.Business')
            }]
        });
    },

    /**
     * @returns { Ext.form.FieldSet }
     */
    createInfoFieldSet: function () {
        return Ext.create('Ext.form.FieldSet', {
            title: '{s name="settings/fieldsets/information/title"}Last updates{/s}',
            defaults: {
                labelWidth: 200
            },
            items: [{
                xtype: 'displayfield',
                name: 'lastSent',
                fieldLabel: '{s name="settings/fieldsets/information/lastSent"}Last update date{/s}',
                fieldStyle: {
                    color: '#475c6a'
                },
                setValue: function (val) {
                    val = Ext.util.Format.date(val) + ' ' + Ext.util.Format.date(val, timeFormat);

                    Ext.form.field.Display.prototype.setValue.apply(this, arguments);
                }
            }, {
                xtype: 'displayfield',
                name: 'lastOrderNumber',
                fieldLabel: '{s name="settings/fieldsets/information/lastOrderNumber"}Last transmitted order (number){/s}',
                fieldStyle: {
                    color: '#475c6a'
                }
            }]
        });
    }
});
//{/block}
