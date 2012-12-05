{if 1 != 1}<script>{/if}
Ext.define('Shopware.B2B.GroupPrices',
{
	extend: 'Ext.ux.SimpleIFrame',
    initComponent: function(){
		Ext.apply(this, {
			title: 'Kundengruppen Preise',
			id: 'GroupPrices',
			border: false,
			src: '{url controller=BusinessEssentials action=loadGroupPricesInnerFrame}'
		});
		this.callParent(arguments);
	}
});
