
//{namespace name=backend/update_wizard/translation}
Ext.define('Shopware.apps.UpdateWizard.view.Start', {
    extend: 'Ext.panel.Panel',
    alias: 'widget.update-wizard-start',
    border: false,

    initComponent: function() {
        var me = this;

        me.items = me.createItems();
        me.dockedItems = [me.createToolbar()];
        this.callParent(arguments);
    },

    createToolbar: function() {
        var me = this;

        me.nextButton = Ext.create('PluginManager.container.Container', {
            html: '{s name="start_accept"}{/s}',
            cls: 'plugin-manager-action-button primary',
            margin: '20 0',
            minWidth: 260,
            handler: function() {
                me.fireEvent('update-wizard-display-login');
            }
        });

        return Ext.create('Ext.toolbar.Toolbar', {
            items: ['->', me.nextButton],
            dock: 'bottom',
            cls: 'toolbar update-wizard-toolbar'
        });
    },

    createItems: function() {
        var me = this;

        var feature1 = '{link file="themes/Backend/ExtJs/backend/_resources/resources/themes/images/shopware-ui/update_wizard/responsive.jpg"}';
        var feature2 = '{link file="themes/Backend/ExtJs/backend/_resources/resources/themes/images/shopware-ui/update_wizard/ekw.jpg"}';
        var feature3 = '{link file="themes/Backend/ExtJs/backend/_resources/resources/themes/images/shopware-ui/update_wizard/filter.jpg"}';

        me.features = Ext.create('Ext.container.Container', {
            cls: 'update-wizard-features text',
            margin: '45 0 30',
            defaults: {
                xtype: 'component',
                cls: 'feature'
            },
            items: [{
                html: '<div class="image"><img src="'+feature1+'" /></div>' +
                      '<h4 class="feature-title">{s name="responsive_feature"}{/s}</h4>'
            }, {
                html: '<div class="image"><img src="'+feature2+'" /></div>' +
                '<h4 class="feature-title">{s name="emotion_feature"}{/s}</h4>'
            }, {
                html: '<div class="image"><img src="'+feature3+'" /></div>' +
                '<h4 class="feature-title">{s name="filter_feature"}{/s}</h4>'
            }]
        });

        var icon = '{link file="themes/Backend/ExtJs/backend/_resources/resources/themes/images/shopware-ui/update_wizard/tour-icon.png"}';
        me.productTourLink = Ext.create('Ext.container.Container', {
            cls: 'update-wizard-tour-link text',
            html: '<a target="_blank" href="{s name="start_tour_link"}http://www.shopware.com/{/s}"><div class="image"><img src="'+icon+'" /><span>{s name="start_tour_label"}{/s}</span></div></a>'
        });

        return [me.features, me.productTourLink];
    }
});
