{if 1 != 1}<script>{/if}
Ext.define('Shopware.B2B.UserPrices',
{
	extend: 'Ext.ux.SimpleIFrame',
    initComponent: function(){
		Ext.apply(this, {
			title: 'Kundenindividuelle Preise',
			id: 'UserPrices',
			border: false,
			src: '{$BaseUri}/engine/backend/modules/userprice/index.php'
		});
		this.callParent(arguments);
	}
});
