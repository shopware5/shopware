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
 *
 * @category   Shopware
 * @package    Category
 * @subpackage Main
 * @version    $Id$
 * @author shopware AG
 */

/* {namespace name=backend/category/main} */

/**
 * Shopware UI - Category Main Window
 *
 * Displays the Main Window of the Category Module
 */
//{block name="backend/category/view/main/window"}
Ext.define('Shopware.apps.Category.view.main.Window', {

    /**
     * Parent Element Enlight.app.Window
     * @string
     */
    extend: 'Enlight.app.Window',

    /**
     * Title of this window
     * @string
     */
    title: '{s name=window/main_title}Categories{/s}',

    /**
     * XType for this component
     * @string
     */
    alias: 'widget.category-main-window',

    /**
     * Enables  / Disables border
     * Default: false
     * @boolean
     */
    border: false,

    /**
     * Enabled / disables autoShow
     * Default: true
     * @boolean
     */
    autoShow: true,

    /**
     * Layout setting for this sub-application
     * Default: border
     * @string
     */
    layout: 'border',

    /**
     * Height setting for this window in pixel
     * Default: 600 px
     * @integer
     */
    height: '90%',

    /**
     * Width setting for this window in pixel
     * Default: 925 px
     * @integer
     */
    width: '80%',

    /**
     * A flag which causes the object to attempt to restore the state of internal properties from a saved state on startup.
     * @boolean
     */
    stateful:true,

    /**
     * The unique id for this object to use for state management purposes.
     */
    stateId:'shopware-category-main-window',

    /**
     * Containing the tabs with the overview and the settings
     * @array of Ext.tab.Panel
     */
    tabPanel : null,

    /**
     * Masks the viewport when the window is visible.
     * @boolean
     */
    modal: false,

    /**
     * Initializes the component and builds up the main interface
     *
     * @public
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.addEvents('saveDetail');

        me.tabPanel = Ext.create('Ext.tab.Panel', {
            items: me.getTabs(),
            split: true,
            flex: 1
        });

        me.formPanel = Ext.create('Ext.form.Panel', {
            items: [me.tabPanel],
            region: 'center',
            border: false,
            name: 'mainForm',
            layout: {
                type: 'hbox',
                align: 'stretch'
            },
            dockedItems: me.getDockedItems()
        });

        me.items = [
            {
                xtype:'category-category-tree',
                split: true,
                store:me.treeStore
            },
            me.formPanel
        ];

        me.callParent(arguments);
    },

    /**
     * Creates the tabs for the tab panel of the window.
     * Contains the detail form which is used to display the customer data for an existing customer
     * or to create a new customer.
     * Can contains additionally an second tab which displays the customer orders and a chart which
     * displays the orders grouped by the order year and month
     *
     * @public
     * @return Array of components
     */
    getTabs:function () {
        var me = this;

        me.articleMappingContainer = Ext.create('Ext.panel.Panel', {
            title:'{s name=tabs/article_mapping/title}Article-Mapping{/s}',
            disabled: true,
            layout: 'fit'
        });

        me.categoryRestrictionContainer = Ext.create('Ext.panel.Panel', {
            title:'{s name=tabs/restrict_category/title}Restrict Category{/s}',
            disabled: true,
            layout: 'fit'
        });

        me.customListing = Ext.create('Shopware.apps.Category.view.category.tabs.CustomListing', {
            disabled: true
        });

        me.customSortingTab = Ext.create('Shopware.apps.Category.view.sorting.Tab', {
            disabled: true
        });

        return [
            {
                xtype:'category-category-tabs-settings',
                templateStore : me.templateStore
            },
            me.articleMappingContainer,
            me.categoryRestrictionContainer,
            me.customListing,
            me.customSortingTab
        ];
    },

    /**
     * Build and returns the action toolbar in the footer of the form.
     *
     * @public
     * @return Array of docked items
     */
    getDockedItems : function() {
        var me = this,
            menu = ['->'];
        /* {if {acl_is_allowed privilege=update}} */
        menu.push({
            text: '{s name=main/window/button/save}Save{/s}',
            action: 'saveDetail',
            disabled: true,
            cls:'primary',
            handler: function() {
                me.fireEvent('saveDetail', me);
            }
        });
        /* {/if} */

        return [{
            xtype:'toolbar',
            cls: 'shopware-toolbar',
            dock:'bottom',
            ui: 'shopware-ui',
            items : menu
        }]
    }
});
//{/block}
