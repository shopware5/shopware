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
 * Shopware First Run Wizard - Demo data tab
 *
 * @category  Shopware
 * @package   Shopware
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */

//{namespace name=backend/first_run_wizard/main}
//{block name="backend/first_run_wizard/view/main/demo_data"}

Ext.define('Shopware.apps.FirstRunWizard.view.main.DemoData', {
    extend: 'Ext.container.Container',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.first-run-wizard-demo-data',

    /**
     * Name attribute used to generate event names
     */
    name:'demo-data',

    overflowY: 'auto',

    snippets: {
        content: {
            title: '{s name=demo_data/content/title}Demo Data{/s}',
            message: '{s name=demo_data/content/message}Want to see Shopware in action right away? Install a demo data set. With it, you will be able to explore all the features that will make your Shopware shop an online success. Please note that demo data sets are for testing purposes only, and should not be installed in or modified for a production environment.{/s}',
            noPlugins: '{s name=demo_data/content/noPlugins}No plugins found{/s}'
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
                html: '<p>' + me.snippets.content.message + '</p>'
            },
            me.createStoreListing(),
            me.createNoResultMessage()
        ];

        me.callParent(arguments);
    },

    createStoreListing: function() {
        var me = this;

        me.communityStore = Ext.create('Shopware.apps.FirstRunWizard.store.DemoPlugin');
        me.storeListing = Ext.create('Shopware.apps.PluginManager.view.components.Listing', {
            store: me.communityStore,
            scrollContainer: me,
            width: 632
        });

        me.communityStore.on('load', function(store, records) {
            if (records.length <= 0) {
                me.content.setVisible(false);
                me.noResultMessage.setVisible(true);
            }
            me.storeListing.setLoading(false);
        });

        me.content = Ext.create('Ext.container.Container', {
            items: [
                me.storeListing
            ]
        });

        return me.content;
    },

    createNoResultMessage: function() {
        var me = this;

        me.noResultMessage = Ext.create('Ext.Component', {
            style: 'margin-top: 30px; font-size: 20px; text-align: center;',
            html: '<h2>' + me.snippets.content.noPlugins + '</h2>',
            hidden: true
        });

        return me.noResultMessage;
    },

    refreshData: function() {
        var me = this;

        me.content.setVisible(true);
        me.noResultMessage.setVisible(false);
        me.storeListing.setLoading(true);
        me.storeListing.resetListing();
        me.communityStore.load();
    }
});

//{/block}
