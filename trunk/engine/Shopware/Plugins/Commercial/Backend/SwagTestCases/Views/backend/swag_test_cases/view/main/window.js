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
 * @package    TestCases
 * @subpackage Detail
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

/**
 *
 */
//{namespace name=backend/swag_test_cases/view/main}
//{block name="backend/swag_test_cases/view/main/window"}
Ext.define('Shopware.apps.SwagTestCases.view.main.Window', {
    /**
     * Define that the order main window is an extension of the enlight application window
     * @string
     */
    extend:'Enlight.app.Window',
    /**
     * Set base css class prefix and module individual css class for css styling
     * @string
     */
    cls:Ext.baseCSSPrefix + 'swag-test-cases-list-window',
    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.swag-test-cases-list-window',
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

    layout: 'border',

    width: 1200,

    height: 600,

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
        me.title = 'Test Case Übersicht';
        me.callParent(arguments);
    },

    /**
     * Creates the items for the list window.
     */
    createItems: function() {
        var me = this;

        me.items = [
            me.createTestCaseTabPanel()
        ];
    },

    createTestCaseTabPanel: function() {
        var me = this;

        me.testCaseStores = [];


        return Ext.create('Ext.tab.Panel', {
            region: 'center',
            items: [
                me.createSelfHealingTestCase(),
                me.createAttributeExtensionCase(),
            ]
        });
    },

    createAttributeExtensionCase: function() {
        var me = this,
            store = Ext.create('Shopware.apps.SwagTestCases.store.AttributeExtension');

        me.testCaseStores.push(store);

        return Ext.create('Shopware.apps.SwagTestCases.view.cases.Base', {
            title: 'Attribute Extension Test Case',
            store: store
        });
    },

    createSelfHealingTestCase: function() {
        var me = this,
            store = Ext.create('Shopware.apps.SwagTestCases.store.SelfHealing');

        me.testCaseStores.push(store);

        return Ext.create('Shopware.apps.SwagTestCases.view.cases.Base', {
            title: 'Self Healing Test Case',
            store: store
        });
    }


});
//{/block}
