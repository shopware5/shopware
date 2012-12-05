{if 1 != 1}<script>{/if}
Ext.define('Shopware.B2B.TplConfig',
{
	extend: 'Ext.grid.Panel',
    initComponent: function(){
        Ext.regModel('TplConfig',{
			fields: [
				// set up the fields mapping into the xml doc
				// The first needs mapping, the others are very basic
				'id','description','function',{foreach from=$customerGroups item=customerGroup}'{$customerGroup.groupkey}'{if !$customerGroup@last},{/if}{/foreach}
			]
	    });

		// create the Data Store
		this.store = Ext.create('Ext.data.Store', {
			model: 'TplConfig',
			autoLoad: true,
			proxy: {
				// load using HTTP
				type: 'ajax',
				url: '{url controller=BusinessEssentials action=getTemplateConfiguration}',
				// the return will be XML, so lets set up a reader
				reader: {
					type: 'json',
					// records will have an "Item" tag
					root: 'result',
					totalRecords: 'total'
				}
			}
		});
		
        Ext.apply(this, {
            height: this.height,
			title: 'Template-Konfiguration',
            store: this.store,
            stripeRows: true,

			listeners: {
				scope: this,


				/** Workaround to fix the annoying resize bug in extjs 4 */
				'resize': function(pnl, width, height) {
					var fieldsets = Ext.ComponentQuery.query('fieldset'),
						offset = 25;	// Padding, margin, scrollbar width etc.
					Ext.each(fieldsets, function(el, index) {
						el.setWidth(width - offset);
					});
				}
			},
			id: 'TplConfig',
            columnLines: true,
			dockedItems: [
			{
				xtype: 'toolbar',
				items: [
					{
						xtype: 'button',
						text: 'Reload',
						handler: function(){
							this.store.load();
						},
						scope: this
					}
				]
			}
			],
			statics: { {literal}
			tpl: new Ext.XTemplate('<td class="x-grid3-col x-grid3-cell x-grid3-td-{id} x-   selectable {css}" style="{style}" tabIndex="0" {cellAttr}>',
			   '<div class="x-grid3-cell-inner x-grid3-col-{id}" {attr}>{value}</div>',
				'</td>'
			)},{/literal}
            columns: [
			{
                text   : 'Variable',
                flex: 1,
                dataIndex: 'description',
				editor: {
					allowBlank: false
				}

            },
			{foreach from=$customerGroups item=customerGroup}
			{
				text: '{$customerGroup.description}',
				width: 80,
				xtype: 'checkcolumn',
				grid: this,
				listeners: {
					'checkchange': function (column,rowindex,checked){
						var customergroup = column.dataIndex;
						var variable = this.grid.getStore().getAt(rowindex).get('function');
						var value = checked;
						
						Ext.Ajax.request({
							url: '{url action=updateTemplateVariableConfig}',
							params: {
								customergroup: customergroup,
								variable: variable,
								value: value
							},
							success: function(response){
								
							}
						});
						
						return true;
					}
				},
				dataIndex: '{$customerGroup.groupkey}'
			}
			{if !$customerGroup@last},{/if}
			{/foreach}
			]
        });

        this.callParent(arguments);
    }
}
);