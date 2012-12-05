{if 1 != 1}<script>{/if}
Ext.define('Shopware.B2B.TplConfigVariables',
{
	extend: 'Ext.grid.Panel',
    initComponent: function(){
        Ext.regModel('TplVariables',{
			fields: [
				// set up the fields mapping into the xml doc
				// The first needs mapping, the others are very basic
				'id','variable','description'
			]
	    });

		// create the Data Store
		this.store = Ext.create('Ext.data.Store', {
			model: 'TplVariables',
			autoLoad: true,
			proxy: {
				// load using HTTP
				type: 'ajax',
				url: '{url controller=BusinessEssentials action=getTemplateVariables}',
				// the return will be XML, so lets set up a reader
				reader: {
					type: 'json',
					// records will have an "Item" tag
					root: 'data',
					totalRecords: 'count'
				}
			}
		});
		
        Ext.apply(this, {
            height: this.height,
			title: 'Template-Variablen',
            store: this.store,
            stripeRows: true,

			listeners: {


				/** Workaround to fix the annoying resize bug in extjs 4 */
				'resize': function(pnl, width, height) {
					var fieldsets = Ext.ComponentQuery.query('fieldset'),
						offset = 25;	// Padding, margin, scrollbar width etc.
					Ext.each(fieldsets, function(el, index) {
						el.setWidth(width - offset);
					});
				},
				'edit': function(edit,e){
						Ext.Ajax.request({
							url: '{url action=insertTemplateVariable}',
							params: {
								id: e.record.data.id, field: e.field, value: e.record.data[e.field]
							},
							success: function(response){
								var data = Ext.JSON.decode(response.responseText);
								e.record.data['id'] = data.id;
								e.record.commit();
							}
						});
				}
			},
			dockedItems: [
			{
				xtype: 'toolbar',
				items: [
					{
						xtype: 'button',
						text: 'Neue Variable hinzufügen',
						handler: function(){
							 var r = Ext.create('TplVariables', {
								variable: 'NewVariable',
								description: 'Enter Description...'
							});
							this.store.insert(0, r);
							cellEditing.startEditByPosition({ row: 0, column: 0});
						},
						scope: this
					},
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
			id: 'TplConfigVariables',
            columnLines: true,
		    plugins: [
				Ext.create('Ext.grid.plugin.CellEditing', {
					clicksToEdit: 1
				})
			],
            columns: [
			{
                text   : 'Variablen-Name',
                width: 150,
                dataIndex: 'variable',
				editor: {
					allowBlank: false
				}
            },
			{
				text   : 'Beschreibung',
				width: 250,
				dataIndex: 'description',
				editor: {
					allowBlank: false
				}
			},
			{
                text   : 'Optionen',
                width: 150,
				xtype: 'actioncolumn',
				items: [
				{
					icon: '{link file="backend/b2bessentials/_resources/images/delete.png"}',  // Use a URL in the icon config
					style: 'cursor:pointer',
					handler: function(grid, rowIndex, colIndex) {
						Ext.Ajax.request({
							url: '{url action=deleteTemplateVariable}',
							params: {
								id: grid.getStore().getAt(rowIndex).get('id')
							},
							success: function(response){
								grid.getStore().load();
							}
						});
					}
				}
				]
            }
			]
        });

        this.callParent(arguments);
    },
	afterRender: function(){
		this.getView().on('render', function(view) {
			view.tip = Ext.create('Ext.tip.ToolTip', {
			target: view.el, // The overall target element.
			delegate: view.itemSelector, // Each grid row causes its own seperate show and hide.
			trackMouse: true, // Moving within the row should not hide the tip.
			renderTo: Ext.getBody(), // Render immediately so that tip.body can be referenced prior to the first show.
			listeners: { // Change content dynamically depending on which element triggered the show.
			beforeshow: function updateTipBody(tip) {
				tip.update('Definieren Sie hier Template-Variablen, die Sie flexibel für verschiedene Kundengruppen konfigruieren können.');
			}
			}
		});
		});
		this.callParent(arguments);
	}
}
);