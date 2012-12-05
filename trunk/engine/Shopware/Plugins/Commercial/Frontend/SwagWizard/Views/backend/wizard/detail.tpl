<script type="text/javascript">
Shopware.Wizard.Detail = Ext.extend(Ext.TabPanel,{
	closable:true,
	activeTab: 0,
    //bodyBorder: false,
    border: false,
    autoScroll:true,
    //plain:true,
    //hideBorders:false,
    defaults:{ autoScroll: true },
	initComponent: function() {
		this.Form = new Shopware.Wizard.DetailForm({ Parent: this, shopID: this.shopID, wizardID: this.wizardID });
		this.Products = new Shopware.Wizard.DetailProducts({ Parent: this, wizardID: this.wizardID });
		this.Categories = new Shopware.Wizard.DetailCategories({ Parent: this, wizardID: this.wizardID });
		this.Statistics = new Shopware.Wizard.DetailStatistics({ Parent: this, wizardID: this.wizardID });
		this.items = [this.Form, this.Products, this.Categories, this.Statistics];	    
	    Shopware.Wizard.Detail.superclass.initComponent.call(this);
	    Wizard.Tabs.add(this).show();
	}
});
</script>