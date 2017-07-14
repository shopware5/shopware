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
// {block name="backend/customer/view/customer_stream/detail"}
Ext.define('Shopware.apps.Customer.view.customer_stream.Detail', {
    extend: 'Shopware.model.Container',
    alias: 'widget.customer-stream-detail',
    layout: 'anchor',
    defaults: { anchor: '100%' },
    withAssignment: true,

    configure: function () {
        var me = this;

        return {
            fieldSets: [{
                splitFields: false,
                border: false,
                padding: 0,
                title: '',
                fields: {
                    name: {
                        fieldLabel: '{s name=stream_name}{/s}',
                        allowBlank: false
                    },
                    description: {
                        xtype: 'textarea',
                        fieldLabel: '{s name=stream_description}{/s}'
                    },
                    static: me.createStaticCheckbox,
                    freezeUp: me.createFreezeUp
                }
            }]
        };
    },

    createStaticCheckbox: function() {
        var me = this;

        me.staticCheckbox = Ext.create('Ext.form.field.Checkbox', {
            name: 'static',
            value: false,
            uncheckedValue: false,
            inputValue: true,
            labelWidth: 130,
            anchor: '100%',
            fieldLabel: 'Statisch',
            listeners: {
                'change': function(field, newValue) {
                    me.fireEvent('static-changed', newValue);
                }
            }
        });

        return me.staticCheckbox;
    },

    createFreezeUp: function() {
        var me = this;

        me.freezeUpField = Ext.create('Shopware.apps.Base.view.element.Date', {
            submitFormat: 'Y-m-d',
            dateCfg: { submitFormat: 'Y-m-d' },
            name: 'freezeUp',
            labelWidth: 130,
            anchor: '100%',
            fieldLabel: '{s name="freeze_up_label"}{/s}',
            helpText: '{s name="freeze_up_help"}{/s}'
        });

        return me.freezeUpField;
    }
});
// {/block}
