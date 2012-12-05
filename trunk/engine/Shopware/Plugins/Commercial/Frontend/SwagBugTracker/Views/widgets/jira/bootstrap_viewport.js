Ext.define('Shopware.app.Application', {
    extend:'Ext.app.Application',
    name:'Shopware',
    singleton:true,
    autoCreateViewport:false,
    launch:function () {
//        this.viewport = Ext.create('Shopware.container.Viewport');
//        this.viewport.hideDesktopSwitcher();
        this.callParent(arguments);
        this.addSubApplication({
            name:"Shopware.apps.Jira",
//            targetName:"Shopware.apps.Index",
            controller:null,
            params:[]
        });
    }
});
Ext.Loader.setConfig({
    enabled:true,
    disableCaching:true,
    disableCachingParam:'no-cache',
    disableCachingValue:1337603464
});
Ext.Loader.setPath('Shopware.apps', '/Widgets', '?file=app_viewport');