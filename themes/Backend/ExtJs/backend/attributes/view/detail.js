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
 * @package    ProductStream
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name="backend/attributes/main"}

Ext.define('Shopware.apps.Attributes.view.Detail', {
    extend: 'Shopware.model.Container',
    alias: 'widget.attributes-detail',
    cls: 'attributes-detail',
    title: '{s name="detail_title"}{/s}',
    layout: 'anchor',
    defaults: {
        anchor: '100%'
    },

    blacklist: [
        'id',
        'filter',
        'categories',
        'articleid',
        'articledownloadid',
        'articleesdid',
        'articleimageid',
        'articlelinkid',
        'articlepriceid',
        'articlesupplierid',
        'supplierid',
        'bannerid',
        'blogid',
        'categoryid',
        'countryid',
        'countrystateid',
        'customerid',
        'customerbillingid',
        'customergroupid',
        'customershippingid',
        'dispatchid',
        'documentid',
        'emotionid',
        'formid',
        'mailid',
        'mediaid',
        'orderid',
        'orderbasketid',
        'orderbillingid',
        'orderdetailid',
        'ordershippingid',
        'paymentid',
        'productfeedid',
        'productstreamid',
        'propertygroupid',
        'propertyoptionid',
        'propertyvalueid',
        'siteid',
        'templateid',
        'templatepriceid',
        'userid',
        'voucherid',
        'article',
        'articledownload',
        'articleesd',
        'articleimage',
        'articlelink',
        'articleprice',
        'articlesupplier',
        'banner',
        'blog',
        'category',
        'country',
        'countrystate',
        'customer',
        'customerbilling',
        'customergroup',
        'customershipping',
        'dispatch',
        'document',
        'emotion',
        'form',
        'mail',
        'media',
        'order',
        'orderbasket',
        'orderbilling',
        'orderdetail',
        'ordershipping',
        'payment',
        'productfeed',
        'productstream',
        'propertygroup',
        'propertyoption',
        'propertyvalue',
        'site',
        'template',
        'templateprice',
        'user',
        'voucher',
        'add',
        'external',
        'procedure',
        'all',
        'fetch',
        'public',
        'alter',
        'file',
        'raiserror',
        'and',
        'fillfactor',
        'read',
        'beliebig',
        'for',
        'readtext',
        'as',
        'foreign',
        'reconfigure',
        'asc',
        'freetext',
        'references',
        'authorization',
        'freetexttable',
        'replication',
        'backup',
        'from',
        'restore',
        'begin',
        'full',
        'restrict',
        'between',
        'function',
        'return',
        'break',
        'goto',
        'revert',
        'browse',
        'grant',
        'revoke',
        'bulk',
        'group',
        'right',
        'by',
        'having',
        'rollback',
        'cascade',
        'holdlock',
        'rowcount',
        'case',
        'identity',
        'rowguidcol',
        'check',
        'identity_insert',
        'rule',
        'checkpoint',
        'identitycol',
        'save',
        'close',
        'if',
        'schema',
        'clustered',
        'in',
        'securityaudit',
        'coalesce',
        'index',
        'select',
        'collate',
        'inner',
        'semantickeyphrasetable',
        'column',
        'insert',
        'semanticsimilaritydetailstable',
        'commit',
        'intersect',
        'semanticsimilaritytable',
        'compute',
        'into',
        'session_user',
        'constraint',
        'is',
        'set',
        'contains',
        'join',
        'setuser',
        'containstable',
        'key',
        'shutdown',
        'continue',
        'kill',
        'some',
        'convert',
        'left',
        'statistics',
        'create',
        'like',
        'system_user',
        'cross',
        'lineno',
        'table',
        'current',
        'load',
        'tablesample',
        'current_date',
        'merge',
        'textsize',
        'current_time',
        'national',
        'then',
        'current_timestamp',
        'nocheck',
        'aufgabe',
        'current_user',
        'nonclustered',
        'top',
        'cursor',
        'not',
        'tran',
        'database',
        'null',
        'transaction',
        'dbcc',
        'nullif',
        'trigger',
        'deallocate',
        'of',
        'truncate',
        'declare',
        'off',
        'try_convert',
        'default',
        'offsets',
        'tsequal',
        'delete',
        'on',
        'union',
        'deny',
        'open',
        'unique',
        'desc',
        'opendatasource',
        'unpivot',
        'disk',
        'openquery',
        'update',
        'distinct',
        'openrowset',
        'updatetext',
        'distributed',
        'openxml',
        'befehl',
        'double',
        'option',
        'user',
        'drop',
        'oder',
        'values',
        'dump',
        'order',
        'varying',
        'else',
        'outer',
        'view',
        'end',
        'over',
        'waitfor',
        'errlvl',
        'percent',
        'when',
        'escape',
        'pivot',
        'where',
        'except',
        'plan',
        'while',
        'exec',
        'precision',
        'mit',
        'execute',
        'primary',
        'within group',
        'exists',
        'print',
        'writetext',
        'exit',
        'proc',
        'bigint',
        'numeric',
        'bit',
        'smallint',
        'smallmoney',
        'int',
        'tinyint',
        'money',
        'float',
        'real',
        'date',
        'datetimeoffset',
        'datetime2',
        'smalldatetime',
        'datetime',
        'time',
        'varchar',
        'nvarchar',
        'varbinary',
        'text',
        'ntext',
        'image',
        'varchar',
        'nvarchar',
        'varbinary',
        'xml'
    ],

    configure: function() {
        var me = this;

        return {
            splitFields: false,
            title: false,
            fieldSets: [
                {
                    title: '{s name="database_configuration"}{/s}',
                    fields: {
                        columnName: {
                            fieldLabel: '{s name="column_name"}{/s}',
                            validator: function(value) {
                                return me.validateName(value);
                            }
                        },
                        columnType: me.createColumnType,
                        entity: me.createEntitySelection,

                        /*{if {acl_is_allowed privilege=update}}*/
                        deleteButton: me.createResetButton
                        /*{/if}*/
                    }
                }, {
                    title: '{s name="view_configuration"}{/s}',
                    fields: {
                        label: '{s name="label"}{/s}',
                        supportText: '{s name="support_text"}{/s}',
                        helpText: '{s name="help_text"}{/s}',
                        position: '{s name="position"}{/s}',
                        displayInBackend: '{s name="display_in_backend"}{/s}',
                        translatable: '{s name="translatable"}{/s}',
                        arrayStore: me.createArrayStore
                    }
                }
            ]
        }
    },

    validateName: function(value) {
        var me = this;
        var reg = new RegExp(/^[a-z][a-z0-9_]+$/);

        if (!reg.test(value)) {
            return '{s name="name_validation"}{/s}';
        }
        var name = value.toLowerCase();

        if (me.blacklist.indexOf(name) >= 0) {
            return Ext.String.format('{s name="name_validation_keyword"}{/s}', value);
        }
        return true;
    },

    initComponent: function() {
        var me = this;
        me.record = Ext.create('Shopware.model.AttributeConfig');
        me.callParent(arguments);
    },

    createResetButton: function() {
        var me = this;

        me.resetButton = Ext.create('Ext.button.Button', {
            cls: 'secondary reset-button',
            text: '{s name="reset_data"}{/s}',
            anchor: '100%',
            style: 'margin: 10px 0; background: #d75250;',
            handler: function() {
                me.fireEvent('reset-column');
            }
        });

        return me.resetButton;
    },

    createEntitySelection: function(container, field) {
        var me = this;

        me.entitySelection = Ext.create('Ext.form.field.ComboBox', {
            store: Ext.create('Shopware.apps.Attributes.store.Entities').load(),
            fieldLabel: 'Entity',
            anchor: '100%',
            labelWidth: 130,
            displayField: 'label',
            valueField: 'entity',
            hidden: true,
            name: 'entity',
            tpl: Ext.create('Ext.XTemplate',
                '<tpl for=".">',
                    '<tpl if="entity!==label">',
                        '<div class="x-boundlist-item">{literal}<b>{label}</b> <i>- {entity}</i>{/literal}</div>',
                    '<tpl else>',
                        '<div class="x-boundlist-item">{literal}<b>{label}</b>{/literal}</div>',
                    '</tpl>',
                '</tpl>'
            ),
            displayTpl: Ext.create('Ext.XTemplate',
                '<tpl for=".">',
                    '<tpl if="entity!==label">',
                        '{literal}{label} - {entity}{/literal}',
                    '<tpl else>',
                        '<{literal}{label}{/literal}',
                    '</tpl>',
                '</tpl>'
            ),
            listeners: {
                'change': function(field, value) {
                    if (!value) {
                        me.entitySelection.clearValue();
                    }
                }
            }
        });

        return me.entitySelection;
    },

    createArrayStore: function() {
        var me = this;

        me.arrayStore = Ext.create('Ext.data.Store', {
            fields: ['key', 'value'],
            pageSize: 50000
        });

        me.arrayStoreField = Ext.create('Shopware.apps.Attributes.view.ArrayStoreField', {
            store: me.arrayStore,
            searchStore: me.arrayStore,
            fieldLabel: '{s name="array_store_field_label"}{/s}',
            anchor: '100%',
            labelWidth: 130,
            height: 200,
            name: 'arrayStore',
            hidden: true
        });

        return me.arrayStoreField;
    },

    createColumnType: function(container, field) {
        var me = this;

        field.xtype = 'combobox';
        field.store = Ext.create('Shopware.apps.Attributes.store.Types').load();
        field.forceSelection = true;
        field.editable = false;
        field.listConfig = {
            getInnerTpl: function() {
                return '{literal}' +
                    '<tpl if="values.label">' +
                        '<b>{label}</b> - <i>[{sql}]</i>' +
                    '</tpl>' +
                    '{/literal}'
            }
        };
        field.displayTpl = Ext.create('Ext.XTemplate',
            '{literal}<tpl for=".">' +
                '{label} - {sql}' +
            '</tpl>{/literal}'
        );

        field.listeners = {
            change: me.onTypeChange,
            scope: me
        };

        field.valueField = 'unified';
        field.fieldLabel = '{s name="column_type"}{/s}';
        return field;
    },

    onTypeChange: function(combo, value) {
        var me = this;

        me.arrayStoreField.hide();
        me.entitySelection.hide();

        switch (value) {
            case 'multi_selection':
            case 'single_selection':
                me.entitySelection.show();
                break;

            case 'combobox':
                me.arrayStoreField.show();
                break;
        }
    }
});