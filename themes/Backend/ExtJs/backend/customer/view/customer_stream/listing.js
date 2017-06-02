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
 * @package    Customer
 * @subpackage CustomerStream
 * @version    $Id$
 * @author shopware AG
 */

// {namespace name=backend/customer/view/main}
// {block name="backend/customer/view/customer_stream/listing"}
Ext.define('Shopware.apps.Customer.view.customer_stream.Listing', {
    extend: 'Shopware.grid.Panel',
    alias: 'widget.customer-stream-listing',
    cls: 'stream-listing',

    configure: function() {
        var me = this;

        return {
            pagingbar: false,
            toolbar: false,
            displayProgressOnSingleDelete: false,

            /*{if !{acl_is_allowed resource=customerstream privilege=delete}}*/
                deleteColumn: false,
            /*{/if}*/

            /*{if !{acl_is_allowed resource=customerstream privilege=save}}*/
                editColumn: false,
            /*{/if}*/
            
            
            columns: {
                name: {
                    flex: 2,
                    renderer: me.nameRenderer
                },
                freezeUp: {
                    flex: 1,
                    renderer: me.freezeUpRenderer
                }
            }
        };
    },

    createSelectionModel: function() {
        var me = this;

        me.selModel = Ext.create('Ext.selection.CheckboxModel', {
            mode: 'SINGLE',
            allowDeselect: true
        });
        return me.selModel;
    },

    createActionColumnItems: function() {
        var me = this, items = me.callParent(arguments);

        /*{if !{acl_is_allowed resource=customerstream privilege=save}}*/
            return items;
        /*{/if}*/
        
        items = Ext.Array.insert(items, 0, [
            {
                iconCls: 'sprite-arrow-circle-315',
                tooltip: '{s name="index_stream"}{/s}',
                handler: function(view, rowIndex, colIndex, item, opts, record) {
                    var node = me.getView().getNode(record);
                    var el = Ext.get(node);
                    el.addCls('rotate');

                    me.fireEvent('index-stream', record, function() {
                        el.removeCls('rotate');
                        me.fireEvent('reset-progressbar');
                        me.getStore().load();
                    });
                },
                getClass: function(value, metadata, record) {
                    if (record.get('freezeUp') || record.get('type') !== 'dynamic') {
                        return 'x-hidden';
                    }
                }
            }
        ]);
        return items;
    },

    freezeUpRenderer: function(value, meta, record) {
        var lockIcon = 'sprite-lock-unlock', freezeUp = '';

        if (value) {
            freezeUp = Ext.util.Format.date(value);
        }
        if (value || record.get('type') === 'static') {
            lockIcon = 'sprite-lock';
        }

        return '<span class="lock-icon '+ lockIcon +'">&nbsp;</span>' + freezeUp;
    },

    nameRenderer: function (value, meta, record) {
        return '<span class="stream-name-column">' +
                '<b>' + value + '</b> - '+
                record.get('customer_count') +
                ' {s name="customer_count_suffix"}{/s}</span>';
    }
});
// {/block}
