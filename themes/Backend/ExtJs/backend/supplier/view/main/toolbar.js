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
 * @package    Supplier
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

/*{namespace name=backend/supplier/view/main}*/

/**
 * Shopware View - Supplier
 *
 * Backend - Management for Suppliers. Create | Modify | Delete and Logo Management.
 * Create a new supplier view
 */
//{block name="backend/supplier/view/main/toolbar"}
Ext.define('Shopware.apps.Supplier.view.main.Toolbar', {
    extend : 'Ext.toolbar.Toolbar',
    alias : 'widget.supplier-main-toolbar',
    ui: 'shopware-ui',
    items : [
        /*{if {acl_is_allowed privilege=create}}*/
        {
            iconCls : 'sprite-plus-circle-frame',
            text : '{s name=add}Add{/s}',
            action : 'addSupplier'
        },
        /*{/if}*/
        /*{if {acl_is_allowed privilege=delete}}*/
        {
            iconCls : 'sprite-minus-circle-frame',
            text : '{s name=delete}Delete selected suppliers{/s}',
            disabled : true,
            action : 'deleteSupplier'
        },
        /*{/if}*/
        '->',
        {
            xtype : 'textfield',
            name : 'searchfield',
            action : 'searchSupplier',
            width: 170,
            cls: 'searchfield',
            enableKeyEvents : true,
            emptyText : '{s name=search_empty}Search...{/s}'
        }, {
            xtype: 'tbspacer',
            width: 6
        }
    ]
});
//{/block}
