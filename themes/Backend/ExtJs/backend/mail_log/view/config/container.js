// {namespace name="backend/mail_log/view/config"}
// {block name="backend/mail_log/view/config/container"}
Ext.define('Shopware.apps.MailLog.view.config.Container', {

    extend: 'Ext.container.Container',
    alias:'widget.mail_log-config-tab-container',
    layout: 'fit',

    initComponent: function () {
        var me = this;

        me.registerEvents();
        me.items = [
            me.createFieldset()
        ];

        me.callParent(arguments);
    },

    registerEvents: function() {
        this.addEvents('save');
    },

    createFieldset: function() {
        var me = this;

        me.fieldSet = Ext.create('Ext.form.FieldSet', {
            padding: 0,
            margin: 0,
            border: false,
            layout: 'fit',
            defaults: {
                bodyPadding: 10,
            },
            items: [
                me.createFormPanel(),
            ],
        });

        return me.fieldSet;
    },

    createFormPanel: function() {
        var me = this;

        me.formPanel = Ext.create('Ext.form.Panel', {
            border: 0,
            items: me.createItems(),
            dockedItems: me.createDockedItems(),
        });

        return me.formPanel;
    },

    createItems: function () {
        var me = this;

        me.informationFieldset = {
            xtype: 'fieldset',
            labelWidth: 210,
            anchor: '100%',
            title: '{s name="fieldset_information_title"}{/s}',
            items: [
                Ext.create('Ext.container.Container', {
                    html: '{s name="fieldset_information_content_html"}{/s}',
                }),
            ],
        };

        me.settingsFieldset = {
            xtype: 'fieldset',
            labelWidth: 210,
            anchor: '100%',
            title: '{s name="fieldset_configuration_title"}Configuration{/s}',
            items: [
                {
                    name: 'mailLogActive',
                    xtype: 'checkbox',
                    labelWidth: 210,
                    anchor: '100%',
                    fieldLabel: '{s name="configuration_active_label"}{/s}',
                    helpText: '{s name="configuration_active_helptext"}{/s}',
                    inputValue: true,
                    uncheckedValue: false,
                },
                {
                    name: 'mailLogCleanupMaximumAgeInDays',
                    xtype: 'numberfield',
                    labelWidth: 210,
                    anchor: '100%',
                    fieldLabel: '{s name="configuration_max_age_label"}{/s}',
                    helpText: '{s name="configuration_max_age_helptext"}{/s}',
                },
                {
                    name: 'mailLogActiveFilters',
                    xtype: 'combobox',
                    multiSelect: true,
                    labelWidth: 210,
                    anchor: '100%',
                    fieldLabel: '{s name="configuration_filter_label"}{/s}',
                    helpText: '{s name="configuration_filter_helptext"}{/s}',
                    displayField: 'label',
                    valueField: 'name',
                    store: Ext.create('Shopware.apps.MailLog.store.Filter'),
                },
            ],
        };

        return [
            me.informationFieldset,
            me.settingsFieldset,
        ];
    },

    createDockedItems: function() {
        var me = this;

        return Ext.create('Ext.toolbar.Toolbar', {
            dock: 'bottom',
            ui: 'shopware-ui',
            cls: 'shopware-toolbar',
            items: [
                {
                    xtype: 'tbfill',
                },
                me.createSaveButton(),
            ],
        });
    },

    createSaveButton: function() {
        var me = this;

        return Ext.create('Ext.button.Button', {
            cls: 'primary',
            text: '{s name="configuration_button_save"}{/s}',
            handler: function () {
                me.fireEvent('save', me.formPanel);
            }
        });
    },

});
// {/block}