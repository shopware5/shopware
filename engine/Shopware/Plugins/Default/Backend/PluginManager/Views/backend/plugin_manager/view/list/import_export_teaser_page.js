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
 * @package    PluginManager
 * @subpackage List
 * @version    $Id$
 * @author shopware AG
 */
//{namespace name=backend/plugin_manager/translation}

//{block name="backend/plugin_manager/view/list/import_export_teaser_page"}
Ext.define('Shopware.apps.PluginManager.view.list.ImportExportTeaserPage', {
    extend: 'Ext.container.Container',
    alias: 'widget.plugin-manager-importexport-teaser-page',

    border: false,
    cls: 'plugin-manager-listing-page',
    autoScroll: true,
    layout: 'anchor',
    defaults: {
        anchor: '100%'
    },

    initComponent: function() {
        var me = this;

        me.items = me.buildItems();

        me.callParent(arguments);
    },

    buildItems: function() {
        var me = this,
            image1 = '{link file="themes/Backend/ExtJs/backend/_resources/resources/themes/images/shopware-ui/importexport_plugin.png"}',
            image2 = '{link file="themes/Backend/ExtJs/backend/_resources/resources/themes/images/shopware-ui/migration_plugin.png"}';

        me.headlineCt = Ext.create('Ext.container.Container', {
            cls: 'headline',
            margin: '20px 0 0 20px',
            html: '{s name="import_export_teaser/headline"}{/s}'
        });

        me.importExportFieldSet = Ext.create('Ext.form.FieldSet', {
            margin: '20px 20px 0 20px',
            padding: '40px 0 40px 0',
            layout: 'fit',
            items: [{
                xtype: 'container',
                layout: {
                    type: 'hbox',
                    align: 'stretch'
                },
                items: [{
                    xtype: 'container',
                    margins: '0 0 0 140px;',
                    html: '<img class="teaser-icon" src="'+ image1 +'" />'
                }, {
                    xtype: 'container',
                    flex: 1,
                    margins: '0 0 0 20px;',
                    items: [{
                        xtype: 'container',
                        cls: 'teaser-headline',
                        html: '{s name="import_export_teaser/import_export_header"}{/s}'
                    }, {
                        xtype: 'container',
                        margin: '10 0 0 0',
                        padding: '0 120 0 0',
                        html: '{s name="import_export_teaser/import_export_description"}{/s}'
                    }, {
                        xtype: 'button',
                        width: 252,
                        margin: '13 0 0 0',
                        text: '{s name="import_export_teaser/import_export_button_text"}{/s}',
                        cls: 'primary',
                        handler: function() {
                            me.fireEvent('install-import-export-plugin');
                        }
                    }]
                }]
            }]
        });

        me.migrationFieldSet = Ext.create('Ext.form.FieldSet', {
            itemId: 'migrationteaser',
            margin: '20px 20px 0 20px',
            padding: '40px 0 40px 0',
            items: [{
                xtype: 'container',
                layout: {
                    type: 'hbox',
                    align: 'stretch'
                },
                items: [{
                    xtype: 'container',
                    margins: '0 0 0 140px;',
                    html: '<img class="teaser-icon" src="'+ image2 +'" />'
                }, {
                    xtype: 'container',
                    flex: 1,
                    margins: '0 0 0 20px;',
                    items: [{
                        xtype: 'container',
                        cls: 'teaser-headline',
                        html: '{s name="import_export_teaser/migration_header"}{/s}'
                    }, {
                        xtype: 'container',
                        margin: '10 0 0 0',
                        padding: '0 120 0 0',
                        html: '{s name="import_export_teaser/migration_description"}{/s}'
                    }, {
                        xtype: 'button',
                        width: 252,
                        margin: '13 0 0 0',
                        text: '{s name="import_export_teaser/migration_button_text"}{/s}',
                        cls: 'primary',
                        handler: function() {
                            me.fireEvent('install-migration-plugin');
                        }
                    }]
                }]
            }]
        });

        return [
            me.headlineCt,
            me.importExportFieldSet,
            me.migrationFieldSet
        ];
    }
});
//{/block}