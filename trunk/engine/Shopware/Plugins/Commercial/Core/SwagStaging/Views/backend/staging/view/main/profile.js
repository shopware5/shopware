//{namespace name=backend/plugins/staging/main}
Ext.define('Shopware.apps.Staging.view.main.Profile', {
    extend: 'Enlight.app.Window',
    title: '{s name=profile/title}Update profile{/s}',
    alias: 'widget.staging-main-profile',
    border: false,
    modal: true,
    autoShow: true,
    height: 400,
    closeAction: 'destroy',
    width: 400,

    /**
     * Initializes the component and builds up the main interface
     *
     * @return void
     */
    initComponent: function() {
        var me = this;
        me.typeStore = new Ext.data.SimpleStore({
            fields: ['id', 'description'],
            data: [['master', '{s name=profile/master_staging}Master > Staging{/s}'], ['slave', '{s name=profile/staging_master}Staging > Master{/s}']]
        });
        this.formPanel = me.getForm();
        me.items = [
            this.formPanel
        ];

        me.formPanel.loadRecord(me.record);
        me.addEvents('saveProfile');
        me.dockedItems = [
            {
            xtype: 'toolbar',
            dock: 'bottom',
            ui: 'shopware-ui',
            cls: 'shopware-toolbar',
            items: ['->', {
                text: '{s name=profile/tb_cancel}Cancel{/s}',
                cls: 'secondary',
                scope: me,
                handler: me.close
            }, {
                text: '{s name=profile/tb_save}Save{/s}',
                action: 'updateprofile',
                cls: 'primary',
                handler: function(btn) {
                    me.fireEvent('saveProfile', me.record, me.formPanel);
                }
            }]
        }];
        me.callParent(arguments);

    },
    getForm: function(){
      return Ext.create('Ext.form.Panel',{
            "autoHeight": true,
            "items": [
                {
                  xtype: 'hiddenfield',
                  name: 'id'
                },
                {
                    "xtype": "combobox",
                    "anchor": "100%",
                    "fieldLabel": "{s name=profile/profile_type}Profile-Type{/s}",
                    allowBlank: false,
                    name: 'profileAssignment',
                    valueField: 'id',
                    displayField: 'description',
                    store : this.typeStore,
                    editable: false
                },
                {
                    xtype: "textfield",
                    anchor: "100%",
                    allowBlank: false,
                    name: 'profileText',
                    fieldLabel: "{s name=profile/profile_name}Profile-Name{/s}"
                },
                {
                    xtype: 'sliderfield',
                    name: 'jobsPerRequest',
                    minValue: 1,
                    increment: 1,
                    value: 20,
                    defaultValue: 5,
                    maxValue: 50,
                    fieldLabel: "{s name=profile/jobs_per_request}Jobs per request{/s}",
                    anchor: "100%"
                },
                {
                    "xtype": "textareafield",
                    "anchor": "100%",
                    allowBlank: false,
                    name: 'profileDescription',
                    "fieldLabel": "{s name=profile/desc}Description{/s}"
                }
            ]
        });
    }
});
