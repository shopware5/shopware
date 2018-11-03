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
 * @package    Article
 * @subpackage Detail
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware UI - Article Image Mapping window.
 */
//{namespace name=backend/article/view/main}
//{block name="backend/article/view/variant/configurator/price_variation_rule"}
Ext.define('Shopware.apps.Article.view.variant.configurator.PriceVariationRule', {
    /**
     * Define that the order main window is an extension of the enlight application window
     * @string
     */
    extend:'Enlight.app.Window',
    /**
     * Set base css class prefix and module individual css class for css styling
     * @string
     */
    cls:Ext.baseCSSPrefix + 'article-price-variation-rule-window',
    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.article-price-variation-rule-window',
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
     * Set border layout for the window
     * @string
     */
    layout: {
        align: 'stretch',
        type: 'vbox'
    },
    height: 440,
    width: 360,
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
    stateful:true,

    /**
     * The unique id for this object to use for state management purposes.
     */
    stateId:'shopware-article-image-rule-window',

    /**
     * The associated price variation model
     */
    record: null,

    /**
     * Contains all snippets for this component
     * @object
     */
    snippets: {
        title: '{s name=price/variation/rule/title}Create new price variation rule{/s}',
        cancel: '{s name=cancel_button}Cancel{/s}',
        save: '{s name=general/save_button}Save{/s}',
        notice: '{s name=price/variation/rule/notice}To create a new price variation, please select the associated configurator options. <br><br>You can only select one option per group. <br><br>After saving you will no longer be able modify this variation\'s configurator options.{/s}'
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
    initComponent: function() {
        var me = this;
        me.title = me.snippets.title;
        me.items = [ me.createNotice(), me.createTree() ];
        me.dockedItems = [ me.createBottomBar() ];
        me.callParent(arguments);
    },

    createNotice: function() {
        var me = this;

        return Ext.create('Ext.container.Container', {
            html: me.snippets.notice,
            style: 'color: #999; font-style: italic; margin: 0 0 15px 0; text-align: center;',
            height: 85
        });
    },

    /**
     * Creates the tree for the assignment.
     * @return Ext.tree.Panel
     */
    createTree: function() {
        var me = this;

        var store = Ext.create('Ext.data.TreeStore', {
            root: {
                expanded: true
            }
        });

        me.configuratorTree = Ext.create('Ext.tree.Panel', {
            rootVisible: false,
            store: store,
            flex: 1,
            cls: Ext.baseCSSPrefix + 'article-image-assignments-tree',
            title: me.snippets.treeTitle,
            listeners: {
                checkchange: function(node, checked) {
                    me.fireEvent('optionCheck', node, checked);
                }
            }
        });
        me.refreshConfiguratorTree(me.configuratorGroupStore);
        return me.configuratorTree;
    },

    /**
     *
     * @param configuratorGroupStore
     */
    refreshConfiguratorTree: function(configuratorGroupStore) {
        var me = this, groupNode = null, optionNode = null;

        var nodes = [];
        if (configuratorGroupStore) {
            configuratorGroupStore.each(function(group) {
                if (group.get('active')) {
                    groupNode = { text: group.get('name'), leaf: false, expanded: true };
                    var optionNodes = [];
                    group.getConfiguratorOptions().each(function(option) {
                        if (option.get('active')) {
                            optionNodes.push({ text: option.get('name'), leaf: true, checked: false, id: option.get('id') });
                        }
                    });
                    if (optionNodes.length > 0) {
                        groupNode.children = optionNodes;
                        nodes.push(groupNode);
                    }
                }
            });
        }
        if (nodes.length > 0) {
            var rootNode = me.configuratorTree.getRootNode();
            rootNode.removeAll();
            rootNode.appendChild(nodes);
        }
    },

    /**
     * Creates the bottom toolbar which contains the cancel and save button.
     * @return Ext.toolbar.Toolbar
     */
    createBottomBar: function() {
        var me = this;

        return Ext.create('Ext.toolbar.Toolbar', {
            dock: 'bottom',
            items: [
                '->',
                {
                    xtype: 'button',
                    cls: 'secondary',
                    text: me.snippets.cancel,
                    handler: function() {
                        me.destroy();
                    }
                }, {
                    xtype: 'button',
                    cls: 'primary',
                    text: me.snippets.save,
                    handler: function() {
                        me.fireEvent('assignConfiguratorOptions', me);
                    }
                }
            ]
        });
    }
});
//{/block}
