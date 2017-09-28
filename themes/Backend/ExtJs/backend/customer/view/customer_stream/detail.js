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
                        fieldLabel: '{s name="stream_name"}{/s}',
                        allowBlank: false,
                        listeners: {
                            scope: me,
                            blur: me.onBlurStripTags
                        }
                    },
                    description: {
                        xtype: 'textarea',
                        fieldLabel: '{s name="stream_description"}{/s}',
                        listeners: {
                            scope: me,
                            blur: me.onBlurStripTags
                        }
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
            fieldLabel: '{s name="static"}{/s}',
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

        me.freezeUpDate = Ext.create('Ext.form.field.Date', {
            fieldLabel: '{s name="freeze_up_label"}{/s}',
            submitFormat: 'Y-m-d',
            name: 'freezeUpDate',
            labelWidth: 130,
            minValue: new Date(),
            allowBlank: true,
            disabled: true
        });

        me.freezeUpTime = Ext.create('Ext.form.field.Time', {
            submitFormat: 'H:i',
            xtype: 'timefield',
            name: 'freezeUpTime',
            minDate: new Date(),
            helpText: '{s name="freeze_up_help"}{/s}',
            margin: '0 0 0 135',
            disabled: true
        });

        me.freezeUpContainer = Ext.create('Ext.container.Container', {
            layout: {
                type: 'vbox',
                align: 'stretch'
            },
            anchor: '100%',
            name: 'freezeUp',
            items: [
                me.freezeUpDate,
                me.freezeUpTime
            ]}
        );

        return me.freezeUpContainer;
    },

    createWarningMessageBox: function(newValue, oldValue) {
        Ext.MessageBox.alert('{s name="stream_name_tags_stripped_notice"}{/s}', Ext.String.format('{s name="stream_name_tags_stripped"}{/s}', Ext.util.Format.htmlEncode(oldValue), newValue));
    },

    onBlurStripTags: function(comp) {
        var me = this,
            val = comp.getValue(),
            html;

        html = Ext.util.Format.stripTags(val);
        html = html.replace(/"/g, '');

        if (html === val) {
            return;
        }

        comp.setRawValue(html);
        comp.setValue(html);

        me.createWarningMessageBox(html, val);
    }
});
// {/block}
