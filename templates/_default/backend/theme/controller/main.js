
Ext.define('Shopware.apps.Theme.controller.Main', {
    extend: 'Enlight.app.Controller',

    refs: [
        { ref: 'listing', selector: 'theme-listing' },
        { ref: 'listingView', selector: 'theme-listing dataview' },
        { ref: 'infoPanel', selector: 'theme-listing-info-panel' }
    ],

    init: function() {
        var me = this;

        me.control({
            'theme-listing dataview': {
                selectionchange: me.onSelectTheme
            },
            'theme-listing': {
                'assign-theme': me.onAssignTheme,
                'preview-theme': me.onPreviewtheme
            }

        });

        me.mainWindow = me.getView('list.Window').create({ }).show();
    },

    onAssignTheme: function() {

    },

    onPreviewTheme: function() {

    },

    onSelectTheme: function(view, records) {
        var me = this;

        console.log("select", arguments);
        var record = Ext.create('Shopware.apps.Theme.model.Theme');

        me.getListing().previewButton.disable();
        me.getListing().assignButton.disable();

        if (records.length > 0) {
            record = records.shift();
            me.getListing().previewButton.enable();
            me.getListing().assignButton.enable();
        }

        me.getInfoPanel().updateInfoView(record);
    }
});