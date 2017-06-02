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
                splitFields: true,
                title: '{s name=stream_details}{/s}',
                fields: {
                    name: {
                        fieldLabel: '{s name=stream_name}{/s}',
                        allowBlank: false
                    },
                    description: {
                        xtype: 'textarea',
                        fieldLabel: '{s name=stream_description}{/s}'
                    },
                    type: me.createTypeCombo,
                    freezeUp: me.createFreezeUp
                }
            }, Ext.bind(me.createStaticFieldSet, me)]
        };
    },

    createStaticFieldSet: function() {
        var me = this;

        return Ext.create('Ext.form.FieldSet', {
            title: '{s name="static_field_set"}{/s}',
            height: 620,
            splitFields: false,
            hidden: !me.withAssignment,
            layout: { type: 'vbox', align: 'stretch' },
            items: [me.createAssignment()]
        });
    },

    changeAssignment: function() {
        var me = this;

        if (!me.assignmentGrid) {
            return;
        }

        me.assignmentGrid.setDisabled(
            (me.typeCombo.getValue() !== 'static' && me.freezeUpField.getValue() === null)
            ||
            me.record.get('id') === null
        );
    },

    createTypeCombo: function() {
        var me = this;

        me.typeCombo = Ext.create('Ext.form.field.ComboBox', {
            name: 'type',
            displayField: 'label',
            valueField: 'key',
            fieldLabel: '{s name="type"}{/s}',
            labelWidth: 130,
            value: 'dynamic',
            anchor: '100%',
            listConfig: {
                getInnerTpl: function () {
                    return '{literal}' +
                        '<div class="layout-info">' +
                            '<h1>{label}</h1>' +
                            '<div>{description}</div>' +
                        '</div>' +
                        '{/literal}';
                }
            },
            store: Ext.create('Ext.data.Store', {
                fields: ['key', 'label', 'description'],
                data: [
                    { key: 'static', label: '{s name="static_stream"}{/s}', description: '{s name="static_stream_description"}{/s}' },
                    { key: 'dynamic', label: '{s name="dynamic_stream"}{/s}', description: '{s name="dynamic_stream_description"}{/s}' }
                ]
            }),
            queryMode: 'local',
            listeners: {
                'change': Ext.bind(me.changeAssignment, me)
            }
        });
        return me.typeCombo;
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
            helpText: '{s name="freeze_up_help"}{/s}',
            listeners: {
                'change': Ext.bind(me.changeAssignment, me)
            }
        });

        return me.freezeUpField;
    },

    createAssignment: function() {
        var me = this;

        me.assignmentGrid = Ext.create('Shopware.apps.Customer.view.customer_stream.Assignment', {
            record: me.record,
            labelWidth: 130,
            height: 570,
            maxHeight: 570,
            disabled: true
        });
        return me.assignmentGrid;
    }
});
// {/block}
