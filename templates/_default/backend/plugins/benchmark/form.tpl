{if 1 != 1}<script>{/if}
Ext.define('Ext.app.Monitor.Form',
{
	extend: 'Ext.form.Panel',
    height: 300,
	region: 'south',
	id: 'form',
    initComponent: function(){
		 this.items =
		 [
				  {
                xtype: 'fieldcontainer',
                combineErrors: true,
                msgTarget : 'side',
                layout: 'hbox',
                defaults: {
                    flex: 1,
                    hideLabel: false
                },
                items: [
                   {
                    xtype: 'textareafield',
                    fieldLabel: 'Query',
					name: 'query',
                    labelAlign: 'top',
                    flex: 1,
					border: 1,
					height: 200,
					width: 500,
                    margins: '0',
                    allowBlank: true
                },
				{
                    xtype: 'textareafield',
                    fieldLabel: 'Parameter',
					name: 'parameters',
                    labelAlign: 'top',
                    flex: 1,
					border: 1,
					height: 200,
					width: 500,
                    margins: '0',
                    allowBlank: true
                }
                ]
            }

	     ];
		 this.callParent(arguments);
	}
});