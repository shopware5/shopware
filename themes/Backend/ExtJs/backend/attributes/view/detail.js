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
        'add',
        'all',
        'alter',
        'and',
        'article',
        'articledownload',
        'articledownloadid',
        'articleesd',
        'articleesdid',
        'articleid',
        'articleimage',
        'articleimageid',
        'articlelink',
        'articlelinkid',
        'articleprice',
        'articlepriceid',
        'articlesupplier',
        'articlesupplierid',
        'articleTaxID',
        'as',
        'asc',
        'aufgabe',
        'authorization',
        'backup',
        'banner',
        'bannerid',
        'befehl',
        'begin',
        'beliebig',
        'between',
        'bigint',
        'bit',
        'blog',
        'blogid',
        'break',
        'browse',
        'bulk',
        'by',
        'cascade',
        'case',
        'categories',
        'category',
        'categoryid',
        'changetime',
        'check',
        'checkpoint',
        'close',
        'clustered',
        'coalesce',
        'collate',
        'column',
        'commit',
        'compute',
        'constraint',
        'contains',
        'containstable',
        'continue',
        'convert',
        'country',
        'countryid',
        'countrystate',
        'countrystateid',
        'create',
        'cross',
        'current',
        'current_date',
        'current_time',
        'current_timestamp',
        'current_user',
        'cursor',
        'customer',
        'customerbilling',
        'customerbillingid',
        'customergroup',
        'customergroupid',
        'customerid',
        'customershipping',
        'customershippingid',
        'database',
        'date',
        'datetime',
        'datetime2',
        'datetimeoffset',
        'datum',
        'dbcc',
        'deallocate',
        'declare',
        'default',
        'delete',
        'deny',
        'desc',
        'disk',
        'dispatch',
        'dispatchid',
        'distinct',
        'distributed',
        'document',
        'documentid',
        'double',
        'drop',
        'dump',
        'else',
        'emotion',
        'emotionid',
        'end',
        'errlvl',
        'escape',
        'except',
        'exec',
        'execute',
        'exists',
        'exit',
        'external',
        'fetch',
        'file',
        'fillfactor',
        'filter',
        'float',
        'for',
        'foreign',
        'form',
        'formid',
        'freetext',
        'freetexttable',
        'from',
        'full',
        'function',
        'goto',
        'grant',
        'group',
        'having',
        'holdlock',
        'id',
        'identity',
        'identity_insert',
        'identitycol',
        'if',
        'image',
        'in',
        'index',
        'inner',
        'insert',
        'int',
        'intersect',
        'into',
        'is',
        'join',
        'key',
        'kill',
        'left',
        'like',
        'lineno',
        'load',
        'mail',
        'mailid',
        'media',
        'mediaid',
        'merge',
        'metaTitle',
        'mit',
        'money',
        'name',
        'national',
        'nocheck',
        'nonclustered',
        'not',
        'ntext',
        'null',
        'nullif',
        'numeric',
        'nvarchar',
        'nvarchar',
        'objectdata',
        'objectdataFallback',
        'oder',
        'of',
        'off',
        'offsets',
        'on',
        'open',
        'opendatasource',
        'openquery',
        'openrowset',
        'openxml',
        'option',
        'order',
        'order',
        'orderbasket',
        'orderbasketid',
        'orderbilling',
        'orderbillingid',
        'orderdetail',
        'orderdetailid',
        'orderid',
        'ordernumber',
        'ordershipping',
        'ordershippingid',
        'outer',
        'over',
        'payment',
        'paymentid',
        'percent',
        'pivot',
        'plan',
        'precision',
        'primary',
        'print',
        'proc',
        'procedure',
        'productfeed',
        'productfeedid',
        'productstream',
        'productstreamid',
        'propertygroup',
        'propertygroupid',
        'propertyoption',
        'propertyoptionid',
        'propertyvalue',
        'propertyvalueid',
        'public',
        'raiserror',
        'read',
        'readtext',
        'real',
        'reconfigure',
        'references',
        'releasedate',
        'replication',
        'restore',
        'restrict',
        'return',
        'revert',
        'revoke',
        'right',
        'rollback',
        'rowcount',
        'rowguidcol',
        'rule',
        'save',
        'schema',
        'securityaudit',
        'select',
        'semantickeyphrasetable',
        'semanticsimilaritydetailstable',
        'semanticsimilaritytable',
        'session_user',
        'set',
        'setuser',
        'shutdown',
        'site',
        'siteid',
        'smalldatetime',
        'smallint',
        'smallmoney',
        'some',
        'statistics',
        'supplier',
        'supplierid',
        'suppliernumber',
        'system_user',
        'table',
        'tablesample',
        'template',
        'templateid',
        'templateprice',
        'templatepriceid',
        'text',
        'textsize',
        'then',
        'time',
        'tinyint',
        'top',
        'tran',
        'transaction',
        'trigger',
        'truncate',
        'try_convert',
        'tsequal',
        'union',
        'unique',
        'unpivot',
        'update',
        'updatetext',
        'user',
        'user',
        'userid',
        'values',
        'varbinary',
        'varbinary',
        'varchar',
        'varchar',
        'varying',
        'view',
        'voucher',
        'voucherid',
        'waitfor',
        'when',
        'where',
        'while',
        'within group',
        'writetext',
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
                        label: { fieldLabel: '{s name="label"}{/s}', translatable: true },
                        supportText: { fieldLabel: '{s name="support_text"}{/s}', translatable: true },
                        helpText: { fieldLabel: '{s name="help_text"}{/s}', translatable: true },
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
