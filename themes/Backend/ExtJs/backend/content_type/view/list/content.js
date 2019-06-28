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

// {block name="backend/content_type/view/list/content"}
Ext.define('Shopware.apps.{$controllerName}.view.list.Content', {
    extend: 'Shopware.grid.Panel',
    alias:  'widget.{$controllerName}-listing-grid',
    region: 'center',

    /**
     * configure the grid
     * @returns { Object }
     */
    configure: function() {
        return {
            /*{if {acl_is_allowed privilege=delete}}*/
            deleteColumn: true,
            deleteButton: true,
            /* {else}*/
            deleteColumn: false,
            deleteButton: false,
            /* {/if} */

            /*{if {acl_is_allowed privilege=edit}}*/
            editColumn: true,
            /* {else}*/
            editColumn: false,
            /* {/if} */

            /*{if {acl_is_allowed privilege=create}}*/
            addButton: true,
            /* {else}*/
            addButton: false,
            /* {/if} */

            detailWindow: 'Shopware.apps.{$controllerName}.view.detail.Window',
            columns: {$listColumns|json_encode}
        }
    },
});
// {/block}
