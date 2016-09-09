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

/**
 * todo@all: Documentation
 */

//{namespace name=backend/config/view/cron_job}

//{block name="backend/config/view/form/cron_job"}
Ext.define('Shopware.apps.Config.view.form.CronJob', {
    extend: 'Shopware.apps.Config.view.base.Form',
    alias: 'widget.config-form-cronjob',

    getItems: function() {
        var me = this;
        return [{
            xtype: 'config-base-table',
            store: 'form.CronJob',
            columns: me.getColumns()
        },{
            xtype: 'config-base-detail',
            items: me.getFormItems()
        }];
    },

    getColumns: function() {
        var me = this;
        return [{
            dataIndex: 'name',
            text: '{s name=table/name_text}Name{/s}',
            flex: 1
        }, {
            dataIndex: 'action',
            text: '{s name=table/action_text}Action{/s}',
            flex: 1
        }, {
            xtype: 'booleancolumn',
            dataIndex: 'active',
            text: '{s name=table/active_text}Active{/s}',
            flex: 1
        }, me.getActionColumn()];
    },

    getFormItems: function() {
        var me = this;
        return [{
            xtype: 'hidden',
            name: 'pluginId',
            listeners:{
                change: function(field, value) {
                    var form = field.up('form'),
                        hideFields = form.query('[isReadOnlyField]');
                    Ext.each(hideFields, function(hideField) {
                        hideField.setReadOnly(value === null || value === '');
                    })
                }
            }
        }, {
            name: 'name',
            allowBlank: false,
            fieldLabel: '{s name=detail/name_label}Name{/s}'
        },{
            readOnly: true,
            isReadOnlyField: true,
            allowBlank: false,
            name: 'action',
            fieldLabel: '{s name=detail/action_label}Action{/s}'
        },{
            xtype: 'config-element-textarea',
            name: 'data',
            readOnly: true,
            isReadOnlyField: true,
            fieldLabel: '{s name=detail/data_label}Data{/s}'
        },{
            xtype: 'config-element-datetime',
            name: 'start',
            fieldLabel: '{s name=detail/last_label}Last{/s}'
        },{
            xtype: 'config-element-datetime',
            name: 'next',
            fieldLabel: '{s name=detail/next_label}Next{/s}'
        },{
            xtype: 'config-element-interval',
            name: 'interval',
            allowBlank: false,
            fieldLabel: '{s name=detail/interval_label}Interval{/s}'
        },{
            xtype: 'config-element-boolean',
            name: 'active',
            fieldLabel: '{s name=detail/active_label}Active{/s}'
        },{
            xtype: 'config-element-boolean',
            name: 'disableOnError',
            fieldLabel: '{s name=detail/disable_on_error_label}Disable on error{/s}'
        },{
            name: 'informMail',
            fieldLabel: '{s name=detail/inform_mail_label}Email recipient{/s}'
        },{
            name: 'informTemplate',
            fieldLabel: '{s name=detail/inform_template_label}Email template{/s}'
        }];
    }
});
//{/block}
//taxinput
