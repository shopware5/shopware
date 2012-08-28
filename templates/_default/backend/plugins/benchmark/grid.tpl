{if 1 != 1}<script>{/if}
Ext.define('Ext.app.Monitor.Grid',
{
	extend: 'Ext.grid.Panel',
    height: 300,
    initComponent: function(){
		//S executionDate, SUM(time) AS executionTime, COUNT(id) AS executionCount,query,parameters,route
        Ext.regModel('Bench',{
			fields: [
				'executionDate','executionTime', 'executionCount','executionQueriesPSession','query','parameters','route','executionMin','executionSessions','executionAvg','executionMax'
			]
	    });

		// create the Data Store
		this.store = Ext.create('Ext.data.Store', {
			model: 'Bench',
			autoLoad: true,
			remoteSort: true,
			proxy: {
				// load using HTTP
				type: 'ajax',
				url: '{url action=getQueries}',
				// the return will be XML, so lets set up a reader
				reader: {
					type: 'json',
					// records will have an "Item" tag
					root: 'result',
					totalRecords: 'total'
				}
			}
		});
//'executionDate','executionTime', 'executionCount','query','parameters','route'
        Ext.apply(this, {
            store: this.store,
            stripeRows: true,
			region: 'center',
			tbar: Ext.create('Ext.Toolbar',
			{
				items: ['-',
				Ext.create('Ext.button.Button',{
					text: 'Log leeren',
					handler: function()
					{
						Ext.Ajax.request({
							url: '{url action=deleteLog}',
							success: function(response){
								this.up().up().store.load();
							},
							scope:this
						});
					}
				})
				]
			}
			),
			// paging bar on the bottom
			bbar: Ext.create('Ext.PagingToolbar', {
				store: this.store,
				displayInfo: true,
				displayMsg: 'Displaying queries {literal}{0} - {1} of {2}{/literal}',
				emptyMsg: "No queries to display",
				items:[
				'Suche',
				Ext.create('Ext.form.field.Text',{
				    id: 'usersearch',
					listeners: {
		    			'render': { fn:function(ob){
		    				ob.el.on('keyup', function(){
		    					var search = Ext.getCmp("usersearch");
		    					this.store.proxy.extraParams = { "search": search.getValue()};
		    					this.store.load({ params: { "search": search.getValue() }});
		    				}, this, { buffer:500 });
		    			}, scope:this }
		    			}
				})
				]
			}),
            columnLines: true,
            columns: [
            {
				xtype:'actioncolumn',
				text   : 'Optionen',
				width:50,
				items: [{
					icon: '{link file="engine/backend/img/default/icons/table.png"}',  // Use a URL in the icon config
					tooltip: 'Edit',
					handler: function(grid, rowIndex, colIndex) {
						var rec = grid.getStore().getAt(rowIndex);

					},
					style: 'cursor:pointer'
				}]
			},
            {
                text   : 'Datum',
                width: 150,
                dataIndex: 'executionDate'
            },
			{
                text   : 'Zeit kumuliert',
  				width: 80,
                dataIndex: 'executionTime'
            },
			{
                text   : 'Zeit min.',
                width: 80,
                dataIndex: 'executionMin'
            },
			{
                text   : 'Zeit max.',
                width: 80,
                dataIndex: 'executionMax'
            },
			{
                text   : 'Zeit avg.',
                width: 80,
                dataIndex: 'executionAvg'
            },
			{
                text   : 'Anzahl',
                width: 80,
                dataIndex: 'executionCount'
            },
			{
                text   : 'Query',
                flex   : 1,
                dataIndex: 'query'
            },
			{
                text   : 'Parameter',
                width: 120,
                dataIndex: 'parameters'
            },
            {
                text   : 'Requests',
                width: 80,
                dataIndex: 'executionSessions',
                sortable: false
            },
            {
                text   : 'q.p. Request',
                width: 80,
                dataIndex: 'executionQueriesPSession',
                sortable: false
            },
			{
                text   : 'Route',
                width: 80,
                dataIndex: 'route'
            }


			],
			listeners: {
                selectionchange: function(model, records) {
                    if (records[0]) {
                        //this.up('form').getForm().loadRecord(records[0]);
						this.form.getForm().loadRecord(records[0]);
                    }
                }
            }
        });

        this.callParent(arguments);
    }
}
);