{if 1 != 1}<script>{/if}
Ext.define('Shopware.B2B.ArticlePermissions',
{
	extend: 'Ext.ux.SimpleIFrame',
    initComponent: function(){
		Ext.apply(this, {
			title: 'Artikel + Kategorien nach Kundengruppe',
			id: 'ArticlePermissions',
			border: false,
			src: '{url controller=BusinessEssentials action=loadArticlePermissionsInnerFrame}'
		});
		this.callParent(arguments);
	}
});
