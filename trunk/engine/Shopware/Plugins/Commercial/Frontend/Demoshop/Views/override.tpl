{block name="backend/index/view/menu" append}
Ext.override(Shopware.apps.Index.view.Menu, {
    initComponent: function () {
        var me = this;
        me.callOverridden(arguments);
        me.add({
            xtype: 'box',
            cls: Ext.baseCSSPrefix + 'staging-hint',
            width: 400,
            height: 32,

            html: '<h2>Demoshop with restricted access - Register own demo with full access<br/><a href="http://www.shopware.de/demozugang-jetzt-anfordern/?sCategory=374">Register own demo</a></h2>',
        });
    }
});
{/block}
{block name="backend/base/header/css" append}
<link rel="stylesheet" type="text/css" href="{link file="_resources/styles/staging.css"}" />
{/block}