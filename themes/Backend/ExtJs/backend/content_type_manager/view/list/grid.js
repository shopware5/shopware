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

// {namespace name="backend/content_type_manager/main"}
// {block name="backend/content_type_manager/view/list/grid"}
Ext.define('Shopware.apps.ContentTypeManager.view.list.Grid', {
    extend: 'Shopware.grid.Panel',
    alias: 'widget.content-type-manager-listing-grid',
    region: 'center',

    /**
     * configure the grid
     * @returns { Object }
     */
    configure: function () {
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
            addButton: true,
            /* {else}*/
            editColumn: false,
            addButton: false,
            /* {/if} */


            pagingbar: false,
            columns: {
                name: {},
                source: {
                    header: 'Plugin'
                }
            },
            detailWindow: 'Shopware.apps.ContentTypeManager.view.detail.Window'
        };
    },

    createDeleteColumn: function () {
        var me = this,
            parent = me.callParent(arguments);

        parent.getClass = function (_, _2, record) {
            if (record.get('source')) {
                return 'x-hidden';
            }
        };

        return parent;
    },

    createEditColumn: function () {
        var me = this,
            parent = me.callParent(arguments);

        parent.getClass = function (_, _2, record) {
            if (record.get('source')) {
                return 'x-hidden';
            }
        };

        return parent;
    },

    createFeatures: function () {
        var me = this,
            items = me.callParent(arguments);

        me.groupingFeature = Ext.create('Ext.grid.feature.Grouping', {
            groupHeaderTpl: new Ext.XTemplate(
                '{literal}{name:this.formatName} ({rows.length}){/literal}',
                {
                    formatName: function(name) {
                        switch (name) {
                            case 1:
                                return '{s name="list/managed_by_plugins"}{/s}';
                            case 0:
                                return '{s name="list/managed_by_me"}{/s}';
                        }
                    }
                }
            )
        });

        items.push(me.groupingFeature);
        return [me.groupingFeature];
    },
});
// {/block}
