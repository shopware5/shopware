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
            toolbar: true,
            deleteButton: false,
            searchField: false,
            editColumn: false,
            displayProgressOnSingleDelete: false,
            deleteColumn: false,

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

    createAddButton: function() {
        var me = this,
            button = me.callParent(arguments);

        Ext.apply(button, {
            text: '{s name="add_stream"}{/s}',
            margin: 5,
            handler: function() {
                me.fireEvent('add-stream');
            }
        });
        me.addButton = button;
        return button;
    },

    createSelectionModel: function() {
        var me = this;

        me.selModel = Ext.create('Ext.selection.RowModel', {
            mode: 'SINGLE',
            allowDeselect: true
        });
        return me.selModel;
    },

    createPlugins: function() {
        return [{
            ptype: 'grid-attributes',
            table: 's_customer_streams_attributes'
        }];
    },

    createColumns: function() {
        var me = this,
            columns = me.callParent(arguments);

        /*{if !{acl_is_allowed resource=customerstream privilege=save}}*/
            return columns;
        /*{/if}*/

        columns.push({
            xtype: 'actioncolumn',
            width: 0,
            items: []
        });

        return columns;
    },

    createActionColumnItems: function() {
        var me = this, items = me.callParent(arguments);

        items.push({
            iconCls: 'sprite-minus-circle-frame',
            action: 'deleteStream',
            handler: function (view, rowIndex, colIndex, item, ops, record) {
                me.fireEvent('delete-stream', record);
            },
            getClass: function (value, metadata, record) {
                if (!record.phantom) {
                    /*{if !{acl_is_allowed resource=customerstream privilege=delete}}*/
                    return 'x-hidden';
                    /*{/if}*/
                    return '';
                }
            }
        });

        /*{if !{acl_is_allowed resource=customerstream privilege=save}}*/
            return items;
        /*{/if}*/

        items.push({
            iconCls: 'sprite-duplicate-article',
            action: 'duplicateStream',
            handler: function (view, rowIndex, colIndex, item, ops, record) {
                me.fireEvent('save-as-new-stream', record);
            },
            getClass: function (value, metadata, record) {
                if (record.get('static') || record.phantom) {
                    return 'x-hidden';
                }
            }
        });

        items.push({
            iconCls: 'sprite-arrow-circle-315',
            tooltip: '{s name="index_stream"}{/s}',
            handler: function (view, rowIndex, colIndex, item, opts, record) {
                var node = me.getView().getNode(record),
                    el = Ext.get(node);
                el.addCls('rotate');

                me.fireEvent('save-stream-selection');

                me.fireEvent('index-stream', record, function () {
                    el.removeCls('rotate');
                    me.fireEvent('reset-progressbar');
                    me.getStore().load({
                        callback: function () {
                            me.fireEvent('restore-stream-selection');
                        }
                    });
                });
            },
            getClass: function (value, metadata, record) {
                if (record.get('freezeUp') || record.get('static') || record.phantom) {
                    return 'x-hidden';
                }
            }
        });

        return items;
    },

    freezeUpRenderer: function(value, meta, record) {
        var lockIcon = 'sprite-lock-unlock', freezeUp = '';

        if (value) {
            freezeUp = Ext.util.Format.date(value);
        }
        if (value || record.get('static')) {
            lockIcon = 'sprite-lock';
        }

        return '<span class="lock-icon ' + lockIcon + '">&nbsp;</span>' + freezeUp;
    },

    nameRenderer: function (value, meta, record) {
        var qtip = '<b>' + record.get('name') + '</b>';
        qtip += ' - ' + record.get('customer_count') + ' {s name="customer_count_suffix"}{/s}';

        if (record.get('freezeUp')) {
            qtip += '<p>{s name="freeze_up_label"}{/s}: ' + Ext.util.Format.date(record.get('freezeUp')) + '</p>';
        }

        qtip += '<br><p>' + record.get('description') + '</p>';

        meta.tdAttr = 'data-qtip="' + qtip + '"';

        if (record.get('id') === null) {
            return record.get('name') + ' <span class="stream-name-column"><i style="color: #999;">({s name="stream/not_saved"}{/s})</i></span>';
        }

        return '<span class="stream-name-column"><b>' + value + '</b> - ' + record.get('customer_count') + ' {s name="customer_count_suffix"}{/s}</span>';
    }
});
// {/block}
