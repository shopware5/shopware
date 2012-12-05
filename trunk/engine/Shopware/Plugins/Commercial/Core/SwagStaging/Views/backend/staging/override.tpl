{block name="backend/index/view/menu" append}
Ext.override(Shopware.apps.Index.view.Menu, {
    initComponent: function () {
        var me = this;
        me.callOverridden(arguments);
        me.add({
            xtype: 'box',
            cls: Ext.baseCSSPrefix + 'staging-hint',
            width: 152,
            height: 32,
            html: '<h1>Staging-System</h1>',
        });
    }
});
{/block}

{block name="backend/base/header/css" append}
<link rel="stylesheet" type="text/css" href="{link file="backend/_resources/styles/staging.css"}" />
{/block}