<script type="text/javascript">
Shopware.Wizard.StatisticsOrders = Ext.extend(Ext.grid.GridPanel,{
	title:'Übersicht',
	region: 'center',
	//enableDragDrop: true,
	stripeRows: true,
	border: false,
	columns: [
		{ dataIndex: 'request_date', header: "Beratung am", width: 120, sortable: true, renderer: Ext.util.Format.dateRenderer('d.m.Y H:i:s') },
		{ dataIndex: 'order_date', header: "Bestellung am", width: 120, sortable: true, renderer: Ext.util.Format.dateRenderer('d.m.Y H:i:s') },
		{ dataIndex: 'ordernumber', header: "Bestellnummer", width: 120, sortable: true },
		{ dataIndex: 'customer', header: "Kunde", width: 120, sortable: true },
		{ dataIndex: 'status', header: "Status", width: 120, sortable: true },
		{ dataIndex: 'amount', header: "Umsatz", width: 120, sortable: true, align: 'right', renderer: Ext.util.Format.numberRenderer('0.000,00 &euro;/i') },
		{ dataIndex: 'id', header: "&nbsp;", width: 50, sortable: true, align: 'right', renderer: function(value) {
			return '<a target="_blank" onclick="parent.parent.loadSkeleton(\'orders\',false,{ \'id\':'+value+' })" class="ico sticky_note_pin">&nbsp;</a>';
		} }
	],
	initComponent: function() {
		this.store = new Ext.data.Store({
			url: '{url action="getWizardSales"}',
			autoLoad: true,
			remoteSort: true,
			baseParams: { wizardID: this.wizardID },
			reader: new Ext.data.JsonReader({
				root: 'data',
				totalProperty: 'count',
				id: 'id',
				fields: [
					'id', 'ordernumber', 'customerID', 'customer', 'status', 'amount',
					{ name: 'order_date', type: 'date', dateFormat: 'timestamp' },
					{ name: 'request_date', type: 'date', dateFormat: 'timestamp' }
				]
			})
		});
		Shopware.Wizard.StatisticsOrders.superclass.initComponent.call(this);
	}
});
</script>
