//{namespace name=backend/plugins/staging/main}
Ext.define('Shopware.apps.Staging.view.main.Newjob', {
    extend: 'Enlight.app.Window',
    title: '{s name=new_job/title}Add new job{/s}',
    alias: 'widget.staging-main-newjob',
    border: false,
    modal: true,
    autoShow: true,
    height: 300,
    width: 400,

	snippets:{
		cancel: '{s name=new_job/tb_cancel}Cancel{/s}',
		pause: '{s name=new_job/pause_job}Pause Job{/s}',
		create: '{s name=new_job/create_btn}Create{/s}',
		profile_desc: '{s name=new_job/profile_desc}Profil description{/s}',
		profile_text: '{s name=new_job/profile_text}text{/s}',
		select_profile: '{s name=new_job/select_profile}Select profile{/s}',
		master_to_staging: '{s name=new_job/master_to_staging}New master to staging job{/s}',
		staging_to_master: '{s name=new_job/staging_to_master}New staging to master job{/s}',
		notice: '{s name=new_job/notice}notice{/s}'
	},

    /**
     * Initializes the component and builds up the main interface
     *
     * @return void
     */
    initComponent: function() {
        var me = this;
        me.addEvents("addNewJob");
        me.items = [
           me.getProfileSelect(),
           me.getProfileInfoBox()
        ];
        me.dockedItems = [
            me.createNoticeContainer(),
            {
            xtype: 'toolbar',
            dock: 'bottom',
            ui: 'shopware-ui',
            cls: 'shopware-toolbar',
            items: ['->', {
                text: me.snippets.cancel,
                cls: 'secondary',
                scope: me,
                handler: me.close
            }, me.getSaveButton()]
        }];
        me.callParent(arguments);
        me.buttons = [
            {
                text: me.snippets.pause
            }
        ]
    },
    getSaveButton: function(){
        var me = this;
        me.saveButton = Ext.create('Ext.button.Button',{
            text: me.snippets.create,
            action: 'createjob',
            cls: 'primary',
            disabled: true,
            handler: function(btn) {
            	btn.setDisabled(true);
                this.fireEvent('addNewJob',this.strategySelectCombo.store.findRecord('id',this.strategySelectCombo.getValue()),this);
            },
            scope:this

        });
        return me.saveButton;
    },
    getProfileInfoBox: function(){
        var me = this;
        me.profileInfoBox = Ext.create('Ext.panel.Panel',{
            title: me.snippets.profile_desc,
            height: 80,
            layout: 'fit',
            html: me.snippets.profile_text,
            autoScroll:true

        });
        return me.profileInfoBox;
    },
    getProfileSelect: function(){
        var me = this;
            me.strategySelectCombo = Ext.create('Ext.form.field.ComboBox',{
                xtype: 'combobox',
                ref: 'profileSelect',
                allowBlank: true,
                fieldLabel: me.snippets.select_profile,
                valueField: 'id',
                displayField: 'text',
                editable: false,
                store: me.profileSelector,
                listeners: {
                    change: function(field,value){

                        var value = field.store.findRecord('id',value);

                        value = value.data.profileDescription;
                        this.profileInfoBox.update(value);
                        this.saveButton.enable();

                    },
                    scope: this
                }
            });
            return this.strategySelectCombo;
    },
    createNoticeContainer: function() {
        var me = this;
        if (me.isMaster){
            var notification = Shopware.Notification.createBlockMessage(me.snippets.master_to_staging, me.snippets.notice);
        }else {
            var notification = Shopware.Notification.createBlockMessage(me.snippets.staging_to_master, me.snippets.notice);
        }
        notification.margin = '10 5';
        return notification;
    }
});
