<script type="text/javascript">
Shopware.Wizard.Filter = Ext.extend(Ext.TabPanel,{
	closable:true,
	activeTab: 0,
    //bodyBorder: false,
    border: false,
    //autoScroll:true,
    //plain:true,
    //hideBorders:false,
    defaults:{ autoScroll: true},
	initComponent: function() {
		
		this.items = [];
		this.items[0] = this.Form = new Shopware.Wizard.FilterForm({ Parent: this, wizardID: this.wizardID, filterID: this.filterID, typeID: this.typeID });
		if(this.typeID<7) {
			this.items[1] = this.Products = new Shopware.Wizard.FilterProducts({ Parent: this, wizardID: this.wizardID, filterID: this.filterID, typeID: this.typeID });
		}
	    Shopware.Wizard.Filter.superclass.initComponent.call(this);
	    Wizard.Tabs.add(this).show();
	}
});
</script>