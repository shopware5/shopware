{if 1 != 1}<script>{/if}
Ext.define('Shopware.B2B.ManageGroups',
{
	extend: 'Ext.ux.SimpleIFrame',
    initComponent: function(){
		Ext.apply(this, {
			title: 'Kundengruppen Preise',
			id: 'ManageGroups',
			border: false,
			src: '{$BaseUri}/engine/backend/modules/presetting/customergroups.php'
		});
		this.callParent(arguments);
	}
});
