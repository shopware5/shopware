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
 * @package    Emotion
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/emotion/list/grid}

/**
 * Shopware UI - Emotion List Grid
 *
 * This file contains logic for the emotion list view.
 */
//{block name="backend/emotion/list/grid"}
Ext.define('Shopware.apps.Emotion.view.list.Grid', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.emotion-list-grid',

    viewConfig: {
        getRowClass: function() {
            return 'vertical-alignment';
        }
    },

    typeMapping: {
        'fluid': '{s name="list/mode/label/fluid"}{/s}',
        'resize': '{s name="list/mode/label/resize"}{/s}',
        'rows': '{s name="list/mode/label/rows"}{/s}',
        'masonry': '{s name="list/mode/label/masonry"}{/s}'
    },

    /**
     * Initializes the component and builds up the main interface
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.store = Ext.create('Shopware.apps.Emotion.store.List').load();
        me.registerEvents();
        me.columns = me.createColumns();
        me.bbar = me.createPagingToolbar();
        me.selModel = me.createSelectionModel();

        me.features = [me.createGroupingFeature()];
        me.plugins = me.createPlugins();

        me.visibilityStore = Ext.create('Shopware.apps.Emotion.store.Visibility');

        me.callParent(arguments);
    },

    createGroupingFeature: function() {
        var me = this;

        me.groupingFeature = Ext.create('Ext.grid.feature.Grouping', {
            id: 'grouping',
            groupHeaderTpl: new Ext.XTemplate(
                '{literal}{[this.formatName(values)]}{/literal}',
                {
                    formatName: function(values) {
                        var groupValue = values.groupValue;
                        if (Ext.isEmpty(groupValue) || groupValue === "-5") {
                            return '{s name=grid/grouping/emotions_in_subcategories}Shopping worlds is subcategories{/s}';
                        }
                        if (groupValue === "-10") {
                            return '{s name=grid/grouping/emotions_in_category}Shopping worlds in category{/s}' + ": " + me.categoryTree.getSelectionModel().getSelection()[0].get('name');
                        }
                        if (groupValue === "-15") {
                            return '{s name=grid/grouping/emotions_in_categories}Shopping worlds in categories{/s}';
                        }
                        return values.children[0].get('emotionGroup');
                    }
                }
            )
        });

        return me.groupingFeature;
    },

    createPlugins: function() {
        var me = this;

        return [
            Ext.create('Ext.grid.plugin.RowEditing', {
                clicksToEdit: 2,
                autoCancel: true,
                listeners: {
                    scope: me,
                    edit: function (editor, context) {
                        me.fireEvent('updateemotion', editor, context)
                    }
                }
            })
        ];
    },

    registerEvents: function() {
        this.addEvents(
            'deleteemotion',
            'selectionChange',
            'editemotion',
            'updateemotion',
            'duplicateemotion',
            'preview'
        )
    },

    createSelectionModel: function() {
        var me = this;

        return Ext.create('Ext.selection.CheckboxModel', {
            listeners:{
                // Unlocks the save button if the user has checked at least one checkbox
                selectionchange:function (sm, selections) {
                    me.fireEvent('selectionChange', selections);
                }
            }
        });
    },

    createPagingToolbar: function() {
        var me = this;

        return Ext.create('Ext.toolbar.Paging', {
            store: me.store
        });
    },

    createColumns: function() {
        var me = this;

        return [{
            header: '{s name=grid/column/name}Name{/s}',
            dataIndex: 'name',
            flex: 2,
            renderer: me.nameColumn,
            menuDisabled: true,
            draggable: false,
            sortable: false,
            groupable: false
        }, {
            header: '{s name=grid/column/type}Type{/s}',
            width: 120,
            tdCls: 'emotion-type-column',
            renderer: me.typeColumn,
            menuDisabled: true,
            draggable: false,
            sortable: false,
            groupable: false
        }, {
            header: '{s name=grid/column/categories}Categories{/s}',
            dataIndex: 'categoriesNames',
            flex: 2,
            renderer: me.categoriesColumn,
            menuDisabled: true,
            draggable: false,
            sortable: false,
            groupable: false
        }, {
            header: '{s name="grids/settings/mode" namespace="backend/emotion/view/detail"}Mode{/s}',
            dataIndex: 'mode',
            width: 140,
            renderer: me.modeColumn,
            menuDisabled: true,
            draggable: false,
            sortable: false,
            groupable: false
        }, {
            header: '{s name=grid/column/devices}Devices{/s}',
            dataIndex: 'device',
            width: 115,
            tdCls: 'emotion-device-column',
            renderer: me.deviceColumn,
            menuDisabled: true,
            draggable: false,
            sortable: false,
            groupable: false
        },{
            header: '{s name=list/visibility_in_categories}{/s}',
            dataIndex: 'listingVisibility',
            width: 115,
            renderer: me.listingVisibilityRenderer
        }, {
            xtype: 'datecolumn',
            header: '{s name=grid/column/date}Last edited{/s}',
            dataIndex: 'modified',
            width: 110,
            renderer: me.modifiedColumn,
            menuDisabled: true,
            draggable: false,
            sortable: false,
            groupable: false
        }, {
            header: '{s name=grid/column/active}Active{/s}',
            dataIndex: 'active',
            width: 50,
            renderer: me.statusColumn,
            menuDisabled: true,
            draggable: false,
            sortable: false,
            groupable: false,
            editor: {
                xtype: 'checkbox',
                inputValue: true,
                uncheckedValue: false
            }
        }, {
            header: '{s name=grid/column/position}Position{/s}',
            dataIndex: 'position',
            width: 60,
            align: 'center',
            renderer: me.positionColumn,
            menuDisabled: true,
            draggable: false,
            sortable: false,
            groupable: false,
            editor: {
                xtype: 'numberfield',
                allowBlank: false,
                minValue: 1
            }
        }, {
            xtype: 'actioncolumn',
            header: '{s name=grid/column/action}Actions{/s}',
            width: 100,
            border: 0,
            menuDisabled: true,
            draggable: false,
            sortable: false,
            groupable: false,
            items: [
                /*{if {acl_is_allowed privilege=delete}}*/
                {
                    iconCls: 'sprite-minus-circle',
                    tooltip:'{s name=list/action_column/delete}Delete shopping world{/s}',
                    handler: function (view, rowIndex, colIndex, item, opts, record) {
                        me.fireEvent('deleteemotion', record);
                    }
                },
                /*{/if}*/
                /*{if {acl_is_allowed privilege=update}}*/
                {
                    iconCls: 'sprite-pencil',
                    tooltip:'{s name=list/action_column/edit}Edit shopping world{/s}',
                    handler: function(view, rowIndex, colIndex) {
                        me.fireEvent('editemotion', me, view, rowIndex, colIndex);
                    }
                },
                /*{/if}*/
                {
                    iconCls: 'sprite-globe--arrow',
                    tooltip:'{s name=list/action_column/preview}Preview shopping world{/s}',
                    handler: function(view, rowIndex) {
                        var listStore = view.getStore(),
                            emotionId = listStore.getAt(rowIndex).get('id');

                        me.fireEvent('preview', emotionId);
                    }
                },
                {
                    iconCls: 'sprite-drive-download',
                    tooltip:'{s name=list/action_column/export}Export shopping world{/s}',
                    handler: function(view, rowIndex) {
                        var listStore = view.getStore(),
                            emotionId = listStore.getAt(rowIndex).get('id');

                        me.fireEvent('export', emotionId);
                    }
                }
            ]
        },
            me.createCopyDropdown()
        ];
    },

    /**
     * Creates the copy split button with the
     * device copy options.
     */
    createCopyDropdown: function() {
        var me = this;
        return {
            xtype: 'buttoncolumn',
            menuDisabled: true,
            draggable: false,
            sortable: false,
            groupable: false,
            width: 60,
            header: '',
            borderLeftWidth: 0,
            iconCls: 'sprite-document-copy',
            buttonText: '',
            tooltip: '{s name="list/action_column/tooltip"}Einkaufswelt kopieren{/s}',
            handler: function (view, rowIndex, colIndex) {
                var listStore = view.getStore();
                var record = listStore.getAt(rowIndex);
                var device = record.get('device');

                me.fireEvent('duplicateemotion', me, record, device);
            },
            stopSelection: true,        //don't select record on button click
            items: [
                {
                    iconCls: 'sprite-imac',
                    text: '{s name="list/action_column/copy_desktop"}Als Desktop Einkaufswelt{/s}',
                    handler: function (item, scope) {
                        var record = scope.record;
                        me.fireEvent('duplicateemotion', me, record, 0);
                    }
                },
                {
                    iconCls: 'sprite-ipad--landscape',
                    text: '{s name="list/action_column/copy_tablet_landscape"}Als Tablet Landscape Einkaufswelt{/s}',
                    handler: function (item, scope) {
                        var record = scope.record;
                        me.fireEvent('duplicateemotion', me, record, 1);
                    }
                },
                {
                    iconCls: 'sprite-ipad--portrait',
                    text: '{s name="list/action_column/copy_tablet_portrait"}Als Tablet Portrait Einkaufswelt{/s}',
                    handler: function (item, scope) {
                        var record = scope.record;
                        me.fireEvent('duplicateemotion', me, record, 2);
                    }
                },
                {
                    iconCls: 'sprite-iphone--landscape',
                    text: '{s name="list/action_column/copy_mobile_landscape"}Als mobile Landscape Einkaufswelt{/s}',
                    handler: function (item, scope) {
                        var record = scope.record;
                        me.fireEvent('duplicateemotion', me, record, 3);
                    }
                },
                {
                    iconCls: 'sprite-iphone--portrait',
                    text: '{s name="list/action_column/copy_mobile_portrait"}Als mobile Portrait Einkaufswelt{/s}',
                    handler: function (item, scope) {
                        var record = scope.record;
                        me.fireEvent('duplicateemotion', me, record, 4);
                    }
                }
            ]
        }
    },

    /**
     * Column renderer function for the emotion name column.
     * @param [string] value    - The field value
     * @param [string] metaData - The model meta data
     * @param [string] record   - The whole data model
     */
    nameColumn: function(value, metaData, record) {
        if (record.get('isLandingPage') && !record.get('parentId')) {
            return '<strong>'+value+'</strong>';
        } else {
            return value;
        }
    },


    /**
     * Column renderer function for the emotion type column.
     * @param [string] value    - The field value
     * @param [string] metaData - The model meta data
     * @param [string] record   - The whole data model
     */
    typeColumn: function(value, metaData, record) {
        if(!record) {
            return false;
        }

        var type = '{s name=grid/renderer/emotion}Emotion{/s}';

        // Type detection
        if(record.get('isLandingPage')) {
            type = '{s name=grid/renderer/landingpage}Landingpage{/s}'
        }

        if (record.get('parentId') == null && record.get('isLandingPage')) {
            type = '<strong>{s name=grid/renderer/landingpage_master}Master landing page{/s}</strong>'
        }

        return type;
    },

    /**
     * Column renderer function for the emotion category column.
     * @param [string] value    - The field value
     * @param [string] metaData - The model meta data
     * @param [string] record   - The whole data model
     */
    categoriesColumn: function(value, metaData, record) {
        metaData.tdAttr = 'data-qtip="' + value + '"';

        return Ext.String.ellipsis(value, 20, true);
    },

    modeColumn: function(value, metaData, record) {
        var me = this,
            mode = me.typeMapping[value] || value,
            warningIconCls = 'sprite-exclamation',
            warningIconStyle = 'width:16px; height:16px; display:inline-block; margin-left:5px;',
            warningIconToolTip = '{s name="list/mode/warning"}{/s}';

        if (value === 'masonry' || !me.typeMapping[value]) {
            mode = Ext.String.format(
                '<div class="[0]" style="[1]" data-qtip="[2]">&nbsp;</div> [3]',
                warningIconCls, warningIconStyle, warningIconToolTip, mode
            );
        }

        return mode;
    },

    deviceColumn: function(value, metaData, record) {
        if(!record) {
            return false;
        }

        var devices = '',
            iconStyling = 'width:16px; height:16px; display:inline-block; margin-right:5px;';

        var snippets = {
                desktop: '{s name=grid/renderer/desktop}For desktop{/s}',
                tabletLandscape: '{s name=grid/renderer/tabletLandscape}For tablet landscape{/s}',
                tablet: '{s name=grid/renderer/tablet}For tablet{/s}',
                mobileLandscape: '{s name=grid/renderer/mobileLandscape}For mobile landscape{/s}',
                mobile: '{s name=grid/renderer/mobile}For mobile{/s}'
        };

        // Device detection
        if(value.indexOf('0') >= 0) {
            devices += '<div class="sprite-imac" style="' + iconStyling + '" title="' + snippets.desktop + '">&nbsp;</div>';
        }
        if(value.indexOf('1') >= 0) {
            devices += '<div class="sprite-ipad--landscape" style="' + iconStyling + '" title="' + snippets.tabletLandscape + '">&nbsp;</div>';
        }
        if(value.indexOf('2') >= 0) {
            devices += '<div class="sprite-ipad--portrait" style="' + iconStyling + '" title="' + snippets.tablet + '">&nbsp;</div>';
        }
        if(value.indexOf('3') >= 0) {
            devices += '<div class="sprite-iphone--landscape" style="' + iconStyling + '" title="' + snippets.mobileLandscape + '">&nbsp;</div>';
        }
        if(value.indexOf('4') >= 0) {
            devices += '<div class="sprite-iphone--portrait" style="' + iconStyling + '" title="' + snippets.mobile + '">&nbsp;</div>';
        }

        return devices;
    },

    /**
     * Column renderer function for the modified column
     * @param [string] value    - The field value
     * @param [string] metaData - The model meta data
     * @param [string] record   - The whole data model
     */
    modifiedColumn: function(value, metaData, record) {
        return Ext.util.Format.date(value) + ' - ' + Ext.util.Format.date(value, 'H:i');
    },

    /**
     * Column renderer function for the emotion status column.
     * @param [string] value    - The field value
     * @param [string] metaData - The model meta data
     * @param [string] record   - The whole data model
     */
    statusColumn: function(value, metaData, record) {
        var cls = 'sprite-ui-check-box';
        metaData.tdAttr = 'data-qtip="{s name=grid/renderer/editable_tooltip}Doubleclick to edit{/s}"';
        if (!value) {
            cls = 'sprite-cross-small';
        }
        return '<div class="'+ cls +'" style="width: 16px; height: 16px; margin-left: 9px;">&nbsp;</div>';
    },

    /**
     * Column renderer function for the emotion position column.
     * @param [string] value    - The field value
     * @param [string] metaData - The model meta data
     * @param [string] record   - The whole data model
     */
    positionColumn: function(value, metaData, record) {
        metaData.tdAttr = 'data-qtip="{s name=grid/renderer/editable_tooltip}Doubleclick to edit{/s}"';

        return value;
    },

    /**
     * @param [string] value    - The field value
     * @param [string] metaData - The model meta data
     * @param [string] record   - The whole data model
     */
    listingVisibilityRenderer: function (value, metaData, record) {
        if (record.get('isLandingPage')) {
            return '';
        }

        var record = this.visibilityStore.findRecord('key', value);
        return record ? record.get('label') : value;
    }
});
//{/block}
