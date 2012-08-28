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
 * @package    Site
 * @subpackage View
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/countries/view/main}

/**
 * Shopware UI - Site site Tree View
 *
 * This file contains the layout of the navigation tree.
 */
//{block name="backend/countries/view/tree"}
Ext.define('Shopware.apps.Countries.view.main.Tree', {
	extend: 'Ext.tree.Panel',
	alias : 'widget.country-tree',
    rootVisible: false,
    autoScroll: true,
    width: 250,

    /**
     * Define snippets for multilingual support
     */
    snippets: {
        treeCreateGroupButton: '{s name=ui/treeCreateAreaButton}Add area{/s}',
        treeDeleteGroupButton: '{s name=ui/treeDeleteArea}Delete area{/s}',
        treeDeleteCountryButton: '{s name=ui/treeDeleteCountry}Delete country{/s}',
        treeCreateCountryButton: '{s name=ui/treeCreateCountry}Add country{/s}',
        treeMenuCreateButton: '{s name=ui/treeMenuCreate}Add...{/s}',
        treeMenuRemoveButton: '{s name=ui/treeMenuRemove}Delete...{/s}'
    },

    initComponent: function() {
        var me = this;
        me.dockedItems = [{
            xtype: 'toolbar',
            ui: 'shopware-ui',
            dock: 'top',
            items: me.getTopToolbar()
        }];

        me.callParent(arguments);
    },

    getTopToolbar: function() {
        return [{
            xtype: 'splitbutton',
            text: this.snippets.treeMenuCreateButton,
            clickEvent: 'mouseover',
            iconCls: 'sprite-map--plus',
            action : 'splitAddButton',
            menu: {
                items: [{
                    text: this.snippets.treeCreateGroupButton,
                    action: 'onCreateArea'
                },  {
                    text: this.snippets.treeCreateCountryButton,
                    action: 'onAddCountry'
                }]
            }
        }, '->', {
            xtype: 'splitbutton',
            text: this.snippets.treeMenuRemoveButton,
            clickEvent: 'mouseover',
            iconCls: 'sprite-map--minus',
            menu: {
                items: [{
                    text: this.snippets.treeDeleteGroupButton,
                    action: 'onDeleteArea',
                    disabled: true
                },
                {
                   text: this.snippets.treeDeleteCountryButton,
                   action: 'onDeleteCountry',
                   disabled: true
                }]
            }
        }];
    }
});
//{/block}