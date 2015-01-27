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
 * Shopware Controller - Price Variation
 * The price variation controller handles all events of the views in the price variation namespace.
 */
//{namespace name=backend/article/view/main}
//{block name="backend/article/controller/price_variation"}
Ext.define('Shopware.apps.Article.controller.PriceVariation', {

    /**
     * The parent class that this class extends.
     * @string
     */
    extend:'Ext.app.Controller',

    snippets: {
        growlMessage: '{s name=growl_message}Article{/s}',
        failure: {
            title: '{s name=variant/failure/title}Failure{/s}',
            onlyOneCanBeChecked: '{s name=media/failure/only_one_node}You can only activate one configurator option per configurator group.{/s}'
        }

    },

    /**
     * A template method that is called when your application boots.
     * It is called before the Application's launch function is executed
     * so gives a hook point to run any code before your Viewport is created.
     *
     * @return void
     */
    init:function () {
        var me = this;
        me.control({
            //global event of the configurator tab
            'article-detail-window article-variant-configurator': {
                openPriceVariation: me.onOpenListPriceVariation
            },
            'article-price-variation-mapping-window': {
                displayNewPriceVariationWindow: me.onDisplayNewPriceVariationWindow,
                closeListPriceVariation: me.onCloseListPriceVariation
            },
            'article-price-variation-rule-window': {
                optionCheck: me.onOptionCheck,
                assignConfiguratorOptions: me.onAssignConfiguratorOptions
            }
        });
        me.callParent(arguments);
    },

    /**
     * Event listener function which fired when the user wants to list price variations.
     * The event will be fired over the toolbar button in the configurator tab.
     */
    onOpenListPriceVariation: function() {
        var me = this,
            article = me.subApplication.article;

        // Load variations
        var variationsStore = Ext.create('Shopware.apps.Article.store.Variation');
        variationsStore.getProxy().extraParams.configuratorSetId = article.get('configuratorSetId');
        variationsStore.load({
            callback: function()  {
                me.getView('variant.configurator.PriceVariation').create({
                    article: article,
                    variationsStore: variationsStore
                });
            }
        });
    },

    /**
     * Fired when the user wants to add a new variation
     * Displays the configurator options tree window
     *
     * @param window
     */
    onDisplayNewPriceVariationWindow: function(window) {
        var me = this,
            mainWindow = me.subApplication.articleWindow,
            configuratorGroupStore = mainWindow.configuratorGroupStore;

        me.getView('variant.configurator.PriceVariationRule').create({
            store: window.variationsStore,
            configuratorGroupStore: configuratorGroupStore
        }).show();
    },

    /**
     * Event listener function of the mapping window. Fired when the user
     * clicks the cancel button.
     */
    onCloseListPriceVariation: function(mappingWindow) {
        mappingWindow.destroy();
    },

    /**
     * Event listener function of the configurator option window. Fired when the user select/deselect a tree node.
     * The user can only select one node per configurator group. So we have to check
     * if the checked node is the only node in the configurator group which is checked.
     *
     * @param node
     * @param checked
     * @return Boolean
     */
    onOptionCheck: function(node, checked) {
        var me = this, onlyOneChecked = true,
            groupNode = null;

        //first we check if the checked parameter is true and the node has a parent node.
        if (checked && node && node.parentNode) {
            //if this is the case the parent node is the configurator group node.
            groupNode = node.parentNode;
            //we have to iterate the child nodes of the group node.
            Ext.each(groupNode.childNodes, function(childNode) {
                //if the queue node not equals the checked node we have to check the "checked" property
                if (childNode !== node && childNode.get('checked')) {
                    //if the checked property is set to true, an other node was already checked in the group
                    onlyOneChecked = false;
                    return false;
                }
            });
            //if the checked node isn't the only checked we have to remove the checked property.
            if (!onlyOneChecked) {
                node.set('checked', false);
                Shopware.Notification.createGrowlMessage(me.snippets.failure.title, me.snippets.failure.onlyOneCanBeChecked, me.snippets.growlMessage);
            }
        }
        return onlyOneChecked;
    },

    /**
     * Event listener function of the "newRule" window. Fired when the user selects
     * some options in the tree panel and clicks the save button to save the new mapping rule.
     *
     * @param variationsWindow
     */
    onAssignConfiguratorOptions: function(variationsWindow) {
        var me = this,
            tree = variationsWindow.configuratorTree,
            nodes = tree.getChecked();

        if (nodes.length === 0) {
            return false;
        }

        var record = Ext.create('Shopware.apps.Article.model.PriceVariation');
        var store = Ext.create('Ext.data.Store', {
            model: 'Shopware.apps.Article.model.ConfiguratorOption'
        });
        Ext.each(nodes, function(node) {
            store.add(node);
        });

        record['getOptionsStore'] = store;
        record.set('configuratorSetId', me.subApplication.article.get('configuratorSetId'));

        record.save({
            callback: function(updated) {
                variationsWindow.store.add(updated);
                variationsWindow.destroy();
            }
        });
    }
});
//{/block}
