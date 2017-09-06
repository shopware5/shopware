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
 * @category    Shopware
 * @package     Base
 * @subpackage  Attribute
 * @version     $Id$
 * @author      shopware AG
 */

// {namespace name=backend/customer/view/main}

// {block name="backend/base/attribute/field/Shopware.form.field.CustomerStreamGrid"}

Ext.define('Shopware.form.field.CustomerStreamGrid', {
    extend: 'Shopware.form.field.Grid',
    alias: 'widget.shopware-form-field-customer-stream-grid',
    mixins: ['Shopware.model.Helper'],
    displayNewsletterCount: false,

    createColumns: function() {
        var me = this;

        return [
            me.createSortingColumn(),
            { dataIndex: 'name', flex: 1, renderer: Ext.bind(me.nameRenderer, me) },
            me.createActionColumn()
        ];
    },

    nameRenderer: function (value, meta, record) {
        var qtip = '<b>' + record.get('name') + '</b>';
        qtip += ' - ' + record.get('customer_count') + ' {s name="customer_count_suffix"}{/s}';

        if (record.get('freezeUp')) {
            qtip += '<p>{s name="frozen"}{/s}: ' + Ext.util.Format.date(record.get('freezeUp')) + '</p>';
        }

        qtip += '<br><p>' + record.get('description') +'</p>';

        meta.tdAttr = 'data-qtip="' + qtip + '"';

        if (this.displayNewsletterCount) {
            return '<span class="stream-name-column"><b>' + value + '</b> - ' + record.get('newsletter_count') + ' {s name="newsletter_count_suffix"}{/s}</span>';
        } else {
            return '<span class="stream-name-column"><b>' + value + '</b> - ' + record.get('customer_count') + ' {s name="customer_count_suffix"}{/s}</span>';
        }
    },

    createSearchField: function() {
        var config = this.getComboConfig();
        config.displayNewsletterCount = this.displayNewsletterCount;
        return Ext.create('Shopware.form.field.CustomerStreamSingleSelection', config);
    },

    createActionColumnItems: function() {
        var me = this,
            items = me.callParent(arguments);

        /*{if {acl_is_allowed resource=customerstream privilege=read}}*/
            items.push(me.createModuleIcon());
        /*{/if}*/

        return items;
    },

    createModuleIcon: function() {
        return {
            action: 'open-customer',
            iconCls: 'sprite-customer-streams',
            handler: function (view, rowIndex, colIndex, item, opts, record) {
                Shopware.app.Application.addSubApplication({
                    name: 'Shopware.apps.Customer',
                    action: 'customer_stream',
                    params: {
                        streamId: record.get('id')
                    }
                });
            }
        };
    }
});
// {/block}
