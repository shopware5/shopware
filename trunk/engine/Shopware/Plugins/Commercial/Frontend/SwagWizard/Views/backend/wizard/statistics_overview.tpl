<script type="text/javascript">
Shopware.Wizard.StatisticsOverview = Ext.extend(Ext.grid.GridPanel,{
	title:'Übersicht',
	region: 'west',
	//margins: '5 0 5 0',
	//minSize: 100,
	//ddGroup: 'secondGridDDGroup',
	//enableDragDrop: true,
	stripeRows: true,
	border: false,
	columns: [
		{ id:'name', dataIndex: 'name', header: "Name", width: 250, sortable: true },
		{ id:'count', dataIndex: 'count', header: "Anzahl", width: 250, sortable: true, align: 'right' },
		{ id:'percent', dataIndex: 'percent', header: "Prozent", width: 250, sortable: true, align: 'right' }
	],
	initComponent: function() {
		this.store = new Ext.data.Store({
			url: '{url action="getWizardStatistics"}',
			autoLoad: true,
			remoteSort: true,
			baseParams: { wizardID: this.wizardID },
			reader: new Ext.data.JsonReader({
				root: 'data',
				totalProperty: 'count',
				id: 'id',
				fields: [
					'id', 'name', 'count', 'percent'
				]
			})
		});
		Shopware.Wizard.StatisticsOverview.superclass.initComponent.call(this);
	}
});
</script>
