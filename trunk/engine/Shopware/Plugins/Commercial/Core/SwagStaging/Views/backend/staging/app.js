Ext.define('Shopware.apps.Staging', {
    /**
     * Extends from our special controller, which handles the
     * sub-application behavior and the event bus
     * @string
     */
    extend:'Enlight.app.SubApplication',

    /**
     * The name of the module. Used for internal purpose
     * @string
     */
    name:'Shopware.apps.Staging',

    bulkLoad: true,
    loadPath: '{url action=load}',

    /**
     * Required controllers for sub-application
     * @array
     */
    controllers: ['Main'],

    /**
     * Requires models for sub-application
     * @array
     */
    models: ['Tables','Jobs','Job','Queue','Status','Profiles','Cols','Profile','Tests'],

    /**
     * Required views for this sub-application
     * @array
     */
    views: [ 'main.Window','main.Status','main.Table','main.Configuration','main.Newjob','main.Profiles','main.Profile','main.AssignCols'],

    /**
     * Required stores for sub-application
     * @array
     */
    stores: [ 'Tables','Jobs','Job','Queue','Status','Profiles','Cols','Profile','ProfilesCombo','Tests'],


    /**
     * @private
     * @return [object] mainWindow - the main application window based on Enlight.app.Window
     */
    launch: function() {
        var me = this,
            mainController = me.getController('Main');

        return mainController.mainWindow;
    }
});
