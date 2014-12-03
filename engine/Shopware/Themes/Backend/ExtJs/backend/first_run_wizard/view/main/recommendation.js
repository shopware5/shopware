/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

/**
 * Shopware First Run Wizard - Recommendations tab
 *
 * @category  Shopware
 * @package   Shopware
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */

//{namespace name=backend/first_run_wizard/main}
//{block name="backend/first_run_wizard/view/main/recommendation"}

Ext.define('Shopware.apps.FirstRunWizard.view.main.Recommendation', {
    extend: 'Ext.container.Container',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.first-run-wizard-recommendation',

    /**
     * Name attribute used to generate event names
     */
    name:'recommendation',

    overflowY: 'auto',

    snippets: {
        content: {
            title: '{s name=recommendation/content/title}Recommendations{/s}',
            recommendedPluginsMessage: '{s name=recommendation/content/recommended_plugins_message}Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.{/s}',
            integratedPluginsMessage: '{s name=recommendation/content/other_plugins_message}Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.{/s}'
        }
    },

    initComponent: function() {
        var me = this;

        me.items = [
            {
                xtype: 'container',
                border: false,
                bodyPadding: 20,
                style: 'font-weight: 700; line-height: 20px;',
                html: '<h1>' + me.snippets.content.title + '</h1>'
            },
            {
                xtype: 'container',
                border: false,
                bodyPadding: 20,
                style: 'margin-bottom: 10px;',
                html: '<p>' + me.snippets.content.recommendedPluginsMessage + '</p>'
            },
            me.createRecommendedPluginsListing(),
            {
                xtype: 'container',
                border: false,
                bodyPadding: 20,
                style: 'margin-bottom: 10px; margin-top: 10px;',
                html: '<p>' + me.snippets.content.integratedPluginsMessage + '</p>'
            },
            me.createIntegratedPluginsListing()
        ];

        me.callParent(arguments);
    },

    createRecommendedPluginsListing: function() {
        var me = this;

        me.recommendedPluginsStore = Ext.create('Shopware.apps.FirstRunWizard.store.RecommendedPlugin');
        me.recommendedPluginsListing = Ext.create('Shopware.apps.PluginManager.view.components.Listing', {
            store: me.recommendedPluginsStore,
            width: 632
        });

        me.recommendedPluginsStore.on('load', function(store, records) {
            me.recommendedPluginsListing.setLoading(false);
        });

        me.content = Ext.create('Ext.container.Container', {
            items: [
                me.recommendedPluginsListing
            ]
        });

        return me.content;
    },

    createIntegratedPluginsListing: function() {
        var me = this;

        me.integratedPluginsStore = Ext.create('Shopware.apps.FirstRunWizard.store.IntegratedPlugin');
        me.integratedPluginsListing = Ext.create('Shopware.apps.PluginManager.view.components.Listing', {
            store: me.integratedPluginsStore,
            scrollContainer: me,
            width: 632
        });

        me.integratedPluginsStore.on('load', function(store, records) {
            me.integratedPluginsListing.setLoading(false);
        });

        me.content = Ext.create('Ext.container.Container', {
            items: [
                me.integratedPluginsListing
            ]
        });

        return me.content;
    },

    refreshData: function() {
        var me = this;

        me.recommendedPluginsListing.setLoading(true);
        me.integratedPluginsListing.setLoading(true);

        me.recommendedPluginsListing.resetListing();
        me.integratedPluginsListing.resetListing();

        me.recommendedPluginsStore.load();
        me.integratedPluginsStore.load();
    }
});

//{/block}
