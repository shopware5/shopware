//{block name="backend/config/controller/form" append}
Ext.define('Shopware.apps.Config.controller.License', {
    override: 'Shopware.apps.Config.controller.Form',
    init: function() {
        var me = this;
        me.callOverridden(arguments);
        me.getStore('form.License');
    }
});
//{include file="backend/config/view/form/license.js"}
//{include file="backend/config/model/form/license.js"}
//{include file="backend/config/store/form/license.js"}
//{/block}