<script type="text/javascript">
Shopware.Wizard.DetailStatistics = Ext.extend(Ext.FormPanel,{
	title:'Berater-Statistik',
	layout:'border',
	buttonAlign:'right',
	autoScroll: true,
	defaults: {
	    collapsible: false,
	    split: true,
	    fitToFrame: true,
	    //autoHeight: true,
	},
	initComponent: function() {
		if(!this.wizardID) {
			this.disabled = true;
		}
		
		this.Orders = new Shopware.Wizard.StatisticsOrders({ Parent: this, wizardID: this.wizardID });
		this.Overview = new Shopware.Wizard.StatisticsOverview({ Parent: this, wizardID: this.wizardID });
		this.items = [this.Orders, this.Overview];
		        
		Shopware.Wizard.DetailStatistics.superclass.initComponent.call(this);
	}
});
</script>