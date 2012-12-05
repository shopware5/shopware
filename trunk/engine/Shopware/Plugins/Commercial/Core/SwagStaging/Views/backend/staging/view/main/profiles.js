//{namespace name=backend/plugins/staging/main}
Ext.define('Shopware.apps.Staging.view.main.Profiles', {
    extend:'Ext.panel.Panel',
    alias:'widget.staging-main-profiles',
    border: 0,
    bodyPadding: 10,
    collapsed: false,
    isMaster: false,
    activeProfile: 0,
    title: '{s name=profiles/title}Profiles{/s}',
    layout: {
        type: 'border'
    },
    initComponent:function () {
        var me = this;
        me.items = [
            me.getTree(me.profileStore),
            me.getPanel()
        ];
       // me.addEvents('deleteJob');
        me.callParent(arguments);
    },
    getPanel: function(){
        var me = this;
        return {
            xtype:'staging-main-table',
            store: me.tableStore
        };
    },
    getTree: function(store){
        return Ext.create('Ext.tree.Panel',{
           width: 230,
           title: '{s name=profiles/title}Profiles{/s}',
           region: 'west',
           store: store,
           alias : 'widget.staging-profiles-tree',
           rootVisible:false,
           dockedItems: [
               {
                   xtype: 'toolbar',
                   dock: 'top',
                   items: [
                       {
                           xtype: 'button',
                           text: '{s name=profiles/button_add}Add{/s}',
                           action: 'addNewProfile'
                       },
                       {
                          xtype: 'button',
                          text: '{s name=profiles/button_edit}Edit{/s}',
                          action: 'editProfile',
                          scope: this
                       },
                       {
                           xtype: 'button',
                           text: '{s name=profiles/button_delete}Delete{/s}',
                           action: 'deleteProfile',
                           scope:this
                       }
                   ]
               }
           ]
        });
    }
});