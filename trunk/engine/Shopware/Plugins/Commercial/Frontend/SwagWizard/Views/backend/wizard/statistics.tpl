<script type="text/javascript">
Shopware.Wizard.Statistics = Ext.extend(Ext.FormPanel,{
	title:'Statistik',
	//layout:'fit',
	//buttonAlign:'right',
	autoScroll: true,
	/*defaults: {
	    collapsible: false,
	    split: true,
	    fitToFrame: true,
	    //autoHeight: true,
	},*/
	initComponent: function() {

		//this.Overview = new Shopware.Wizard.StatisticsOverview({ Parent: this, wizardID: this.wizardID });
		
		this.ClicksStore = new Ext.data.Store({
			url: '{url action="getClicksStatistics"}',
			autoLoad: true,
			remoteSort: true,
			baseParams: { wizardID: this.wizardID },
			reader: new Ext.data.JsonReader({
				root: 'data',
				totalProperty: 'count',
				id: 'id',
				fields: ['name', 'abort', 'finish', 'basket', 'order'],
			})
		});
		
		this.Clicks = {
            xtype: 'stackedbarchart',
            store: this.ClicksStore,
            yField: 'name',
            xAxis: new Ext.chart.NumericAxis({
                stackingEnabled: true,
                minimum: 1,
                labelRenderer: function(value) { return Ext.util.Format.number(value, '0.000/i'); }
            }),
            series: [{
                xField: 'abort',
                displayName: 'Berater abgebrochen'
            },{
                xField: 'finish',
                displayName: 'Berater abgeschlossen'
            },{
                xField: 'basket',
                displayName: 'Warenkorb'
            },{
                xField: 'order',
                displayName: 'Bestellung'
            }]
        };
		
		
		this.items = [this.Clicks];
		        
		Shopware.Wizard.DetailStatistics.superclass.initComponent.call(this);
	}
});
</script>