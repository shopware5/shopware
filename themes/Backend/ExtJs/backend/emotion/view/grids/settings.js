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

//{namespace name=backend/emotion/view/detail}

/**
 * Shopware UI - Media Manager Main Window
 *
 * This file contains the business logic for the User Manager module. The module
 * handles the whole administration of the backend users.
 */
//{block name="backend/emotion/view/grids/settings"}
Ext.define('Shopware.apps.Emotion.view.grids.Settings', {
    extend: 'Enlight.app.Window',
    alias: 'widget.emotion-view-grids-settings',
    width: 600,
    height: 500,
    autoScroll: true,
    autoShow: true,
    layout: 'fit',

    /**
     * Snippets which are used by this component.
     * @Object
     */
    snippets: {
        title_new: '{s name=grids/settings/title_new}Create new grid{/s}',
        title_edit: '{s name=grids/settings/title_edit}Edit existing grid{/s}',
        fieldset_title: '{s name=grids/settings/fieldset}Define grid{/s}',
        fields: {
            name: '{s name=grids/settings/name}Name{/s}',
            cols: '{s name=grids/settings/cols}Number of columns{/s}',
            rows: '{s name=grids/settings/rows}Number of rows{/s}',
            cell_height: '{s name=grids/settings/cell_height}Cell height{/s}',
            article_height: '{s name=grids/settings/article_height}Article element height{/s}',
            gutter: '{s name=grids/settings/gutter}Gutter{/s}'
        },
        support: {
            name: '{s name=grid/settings/support/name}Initial label of the grid.{/s}',
            cols: '{s name=grid/settings/support/cols}Could not be modified within the designer.{/s}',
            rows: '{s name=grid/settings/support/rows}Initial number of rows. New rows can be added in the designer.{/s}',
            cell_height: '{s name=grid/settings/support/cell_height}Height of a cell (in px) in the storefront.{/s}',
            article_height: '{s name=grid/settings/support/article_height}Number of cells, which occupies the article element.{/s}',
            gutter: '{s name=grid/settings/support/gutter}Margin (in px) between the elements in the storefront.{/s}'
        },
        buttons: {
            cancel: '{s name=grids/button/cancel}Cancel{/s}',
            save: '{s name=grids/button/save}Save{/s}'
        }
    },

    /**
     * Initializes the component and builds up the main interface
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.title = me.snippets[(me.hasOwnProperty('record') ? 'title_edit' : 'title_new' )];

        me.formPanel = Ext.create('Ext.form.Panel', {
            bodyPadding: 20,
            border: 0,
            bodyBorder: 0,
            items: [{
                xtype: 'fieldset',
                defaults: {
                    labelWidth: '155',
                    anchor: '100%'
                },
                title: me.snippets.fieldset_title,
                items: me.createFormItems()
            }]
        });
        me.items = [ me.formPanel ];
        me.bbar = me.createActionButtons();

        if(me.hasOwnProperty('record')) {
            me.formPanel.loadRecord(me.record);
        }

        me.callParent(arguments);
    },

    /**
     * Creates the toolbar which contains the action buttons.
     *
     * @returns { Ext.toolbar.Toolbar }
     */
    createActionButtons: function() {
        var me = this;

        return Ext.create('Ext.toolbar.Toolbar', {
            docked: 'bottom',
            items: [ '->', {
                xtype: 'button',
                text: me.snippets.buttons.cancel,
                cls: 'secondary',
                handler: function() {
                    me.destroy();
                }
            }, {
                xtype: 'button',
                text: me.snippets.buttons.save,
                cls: 'primary',
                action: 'emotion-save-grid'
            }]
        });
    },

    /**
     * Creates the form items for the settings window.
     *
     *
     * @returns { Array }
     */
    createFormItems: function() {
        var me = this, label = me.snippets.fields,
            support = me.snippets.support;

        var name = Ext.create('Ext.form.field.Text', {
            name: 'name',
            fieldLabel: label.name,
            allowBlank: false,
            supportText: support.name
        });

        var colsCount = Ext.create('Ext.form.field.Number', {
            name: 'cols',
            fieldLabel: label.cols,
            allowBlank: false,
            minValue: 0,
            supportText: support.cols
        });

        var rowsCount = Ext.create('Ext.form.field.Number', {
            name: 'rows',
            fieldLabel: label.rows,
            allowBlank: false,
            minValue: 0,
            supportText: support.rows,
            hidden: false
        });

        var cellHeight = Ext.create('Ext.form.field.Number', {
            name: 'cellHeight',
            fieldLabel: label.cell_height,
            allowBlank: false,
            minValue: 0,
            supportText: support.cell_height
        });

        var articleHeight = Ext.create('Ext.form.field.Number', {
            name: 'articleHeight',
            fieldLabel: label.article_height,
            allowBlank: false,
            minValue: 0,
            supportText: support.article_height
        });

        var gutter = Ext.create('Ext.form.field.Number', {
            name: 'gutter',
            fieldLabel: label.gutter,
            allowBlank: false,
            minValue: 0,
            supportText: support.gutter
        });

        return [ name, colsCount, rowsCount, cellHeight, articleHeight, gutter ];
    }
});
//{/block}
