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

//{namespace name=backend/article_list/main}
//{block name="backend/article_list/view/main/multi_edit_menu"}
Ext.define('Shopware.apps.ArticleList.view.main.MultiEditMenu', {
    extend: 'Ext.panel.Panel',
    alias: 'widget.multi-edit-menu',

    layout: {
        type: 'vbox',
        pack: 'start',
        align: 'stretch'
    },

    title: '{s name=bulkChange}Bulk change{/s}',

    initComponent: function () {
        var me = this;

        me.items = me.getItems();


        me.addEvents(
                /**
                 * Fired when the user clicks the "batch process" button
                 */
                'openBatchProcessWindow'
        );

        me.callParent(arguments);
    },

    /**
     * Returns the batch edit as well as the revert button
     *
     * @returns Array
     */
    getItems: function () {
        var me = this,
                items = [];


        /*{if {acl_is_allowed privilege=doMultiEdit}}*/
        items.push(
                Ext.create('Ext.button.Button', {
                    text: '{s name=window/multiEdit}Mehrfachänderung{/s}',
                    action: 'batchEdit',
                    padding: '10px',
                    margin: '5px',
                    name: 'batchEdit',
                    iconCls: 'sprite-multi-edit',
                    handler: function () {
                        me.fireEvent('openBatchProcessWindow');
                    }
                })
        );
        /*{/if}*/

        /*{if {acl_is_allowed privilege=doBackup}}*/
        items.push({
            xtype: 'button',
            text: '{s name=restoreBackup}Revert changes{/s}',
            name: 'backup',
            action: 'backup',
            padding: '10px',
            margin: '5px',
            iconCls: 'sprite-arrow-circle-225-left'
        });
        /*{/if}*/


        return items;
    }

});
//{/block}
