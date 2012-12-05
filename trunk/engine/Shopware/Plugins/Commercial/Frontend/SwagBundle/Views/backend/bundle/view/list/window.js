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
 * @package    Bundle
 * @subpackage Detail
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware UI - Bundle application window
 */
//{namespace name="backend/bundle/bundle/view/main"}
//{block name="backend/bundle/view/list/window"}
Ext.define('Shopware.apps.Bundle.view.list.Window', {
    /**
     * Define that the order main window is an extension of the enlight application window
     * @string
     */
    extend:'Enlight.app.Window',
    /**
     * Set base css class prefix and module individual css class for css styling
     * @string
     */
    cls:Ext.baseCSSPrefix + 'bundle-list-window',
    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.bundle-list-window',
    /**
     * Set no border for the window
     * @boolean
     */
    border:false,
    /**
     * True to automatically show the component upon creation.
     * @boolean
     */
    autoShow:true,
    /**
     * True to display the 'maximize' tool button and allow the user to maximize the window, false to hide the button and disallow maximizing the window.
     * @boolean
     */
    maximizable:true,

    /**
     * True to display the 'minimize' tool button and allow the user to minimize the window, false to hide the button and disallow minimizing the window.
     * @boolean
     */
    minimizable:true,

    /**
     * A flag which causes the object to attempt to restore the state of internal properties from a saved state on startup.
     */
    stateful:false,

    /**
     * The unique id for this object to use for state management purposes.
     */
    stateId:'shopware-bundle-list-window',

    layout: 'border',

    width: 1200,

    height: '90%',

    /**
     * Contains all snippets for the component
     * @object
     */
    snippets: {

    },

    /**
     * The initComponent template method is an important initialization step for a Component.
     * It is intended to be implemented by each subclass of Ext.Component to provide any needed constructor logic.
     * The initComponent method of the class being created is called first,
     * with each initComponent method up the hierarchy to Ext.Component being called thereafter.
     * This makes it easy to implement and, if needed, override the constructor logic of the Component at any step in the hierarchy.
     * The initComponent method must contain a call to callParent in order to ensure that the parent class' initComponent method is also called.
     *
     * @return void
     */
    initComponent:function () {
        var me = this;
        me.createItems();
        me.title = '{s name=window/title}Bundle overview{/s}';
        me.callParent(arguments);
    },

    /**
     * Creates the items for the list window.
     */
    createItems: function() {
        var me = this;

        me.items = [
            me.createBundleList(),
            me.createDetailPanel()
        ];
    },

    /**
     * Creates the listing grid for the bundles.
     * Displayed in the center region of the window
     * @return Shopware.apps.Bundle.view.list.List
     */
    createBundleList: function() {
        var me = this;

        me.bundleList = Ext.create('Shopware.apps.Bundle.view.list.List', {
            store: me.bundleStore,
            title: '{s name=window/bundle_list_title}Defined bundles{/s}',
            region: 'center'
        });

        return me.bundleList;
    },

    /**
     * Creates the detail panel for the listing window.
     * Displayed in the east region of the list window.
     * @return Ext.panel.Panel
     */
    createDetailPanel: function() {
        var me = this;

        me.detailPanel = Ext.create('Ext.panel.Panel', {
            region: 'east',
            width: 450,
            title: '{s name=window/bundle_details}Bundle details{/s}',
            bodyPadding: 10,
            layout: {
                type: 'vbox',
                align: 'stretch'
            },
            collapsible: true,
            items: [
                me.createPriceGrid(),
                me.createArticleGrid()
            ]
        });

        return me.detailPanel;
    },

    /**
     * Creates the price grid for the listing window.
     *
     * @return Shopware.apps.Bundle.view.list.Prices
     */
    createPriceGrid: function() {
        var me = this;

        me.priceGrid = Ext.create('Shopware.apps.Bundle.view.list.Prices', {
            flex: 1
        });

        return me.priceGrid;
    },

    /**
     * Creates the article grid for the listing window.
     *
     * @return Shopware.apps.Bundle.view.list.Articles
     */
    createArticleGrid: function() {
        var me = this;

        me.articleGrid = Ext.create('Shopware.apps.Bundle.view.list.Articles', {
            flex: 1,
            margin: '10 0 0'
        });

        return me.articleGrid;
    }


});
//{/block}
