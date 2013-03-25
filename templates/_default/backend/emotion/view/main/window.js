/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 *
 * @category   Shopware
 * @package    UserManager
 * @subpackage View
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/emotion/view/main}

/**
 * Shopware UI - Media Manager Main Window
 *
 * This file contains the business logic for the User Manager module. The module
 * handles the whole administration of the backend users.
 */
//{block name="backend/emotion/view/main/window"}
Ext.define('Shopware.apps.Emotion.view.main.Window', {
	extend: 'Enlight.app.Window',
    title: '{s name=window/title}Emotion{/s}',
    alias: 'widget.emotion-main-window',
    border: false,
    autoShow: true,
    layout: 'fit',
    height: '90%',
    width: 800,
    stateful: true,
    stateId: 'emotion-main-window',

    /**
     * Object which are used in this component
     * @Object
     */
    snippets: {
        tab: {
            overview: '{s name=window/tab/overview}Overview{/s}',
            custom_grids: '{s name=window/tab/custom_grids}Grids management{/s}',
            custom_templates: '{s name=window/tab/custom_templates}Templates management{/s}',
            expert_settings: '{s name=window/tab/expert_settings}Expert settings{/s}'
        }
    },

    /**
     * Initializes the component and builds up the main interface
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.items = me.createTabPanel();

        me.callParent(arguments);
    },

    /**
     * Creates the tab panel which holds off the different
     * areas of the emotion module.
     *
     * @returns { Ext.tab.Panel } the tab panel which contains the different areas
     */
    createTabPanel: function() {
        var me = this;

        me.tabPanel = Ext.create('Ext.tab.Panel', {
            items: [ me.createOverviewTab(), me.createExpertSettingsTab() ]
        });

        return me.tabPanel;
    },

    /**
     * Creates a container which holds off the upper toolbar and the
     * actual grid panel.
     *
     * The container will be used as the `overview` tab.
     *
     * @returns { Ext.container.Container }
     */
    createOverviewTab: function() {
        var me = this;

        me.overviewContainer = Ext.create('Ext.container.Container', {
            layout: 'border',
            title: me.snippets.tab.overview,
            items: [{
                xtype: 'emotion-list-toolbar',
                region: 'north'
            }, {
                xtype: 'emotion-list-grid',
                region: 'center'
            }]
        });

        return me.overviewContainer;
    },

    /**
     * Creates the container which represents the expert settings tab.
     *
     * The tab contains the custom grids and custom templates tabs.
     *
     * @returns { Ext.tab.Panel }
     */
    createExpertSettingsTab: function() {
        var me = this;

        me.expertSettingsTabPanel = Ext.create('Ext.tab.Panel', {
            title: me.snippets.tab.expert_settings,
            items: [ me.createCustomGridsTab(), me.createCustomTemplatesTab() ]
        });

        return me.expertSettingsTabPanel;
    },

    /**
     * Creates the container which represents the custom grids tab.
     *
     * @returns { Ext.container.Container }
     */
    createCustomGridsTab: function() {
        var me = this;

        me.customGridsContainer = Ext.create('Ext.container.Container', {
            layout: 'border',
            title: me.snippets.tab.custom_grids,
            items: [{
                xtype: 'emotion-grids-toolbar',
                region: 'north'
            }, {
                xtype: 'emotion-grids-list',
                region: 'center',
                store: Ext.create('Shopware.apps.Emotion.store.Grids').load()
            }]
        });

        return me.customGridsContainer;
    },

    /**
     * Creates the container which represents the custom templates tab.
     *
     * @returns { Ext.container.Container }
     */
    createCustomTemplatesTab: function() {
        var me = this;

        me.customTemplatesTab = Ext.create('Ext.container.Container', {
            layout: 'border',
            title: me.snippets.tab.custom_templates,
            items: [{
                xtype: 'emotion-templates-toolbar',
                region: 'north'
            }, {
                xtype: 'emotion-templates-list',
                region: 'center'
            }]
        });

        return me.customTemplatesTab;
    }
});
//{/block}