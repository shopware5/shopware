<script type="text/javascript">
View = Ext.extend(Ext.Viewport, {
	layout: 'border',
	forceLayout: true,
	initComponent: function() {
		this.Tree =  new Shopware.Wizard.Tree;
		//this.Statistics = new Shopware.Wizard.Statistics;

		this.Tabs = new Ext.TabPanel({
	        region: 'center',
	        //layout: 'fit',
	        activeTab: 0,
	        //bodyBorder: false,
	        //border: false,
	        //autoScroll:true,
	        //plain:true,
	        //hideBorders:false,
	        defaults:{ autoScroll: true},
	        items: []
	    });

		this.items = [this.Tree, this.Tabs];
        View.superclass.initComponent.call(this);
	}
});
Shopware.Wizard.View = View;
</script>