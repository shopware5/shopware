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

//{namespace name=backend/emotion/templates/list}

/**
 * Shopware UI - Emotion Toolbar
 *
 * This file contains the business logic for the User Manager module. The module
 * handles the whole administration of the backend users.
 */
//{block name="backend/emotion/templates/list"}
Ext.define('Shopware.apps.Emotion.view.templates.Toolbar', {
    extend: 'Ext.toolbar.Toolbar',
    alias: 'widget.emotion-templates-toolbar',
    ui: 'shopware-ui',

    /**
     * Snippets which are used by this component.
     * @Object
     */
    snippets: {
        addBtn: '{s name=grids/btn/add}Add new template{/s}',
        delBtn: '{s name=grids/btn/del}Delete selected template(s){/s}',
        searchField: '{s name=toolbar/search_grids}Search template...{/s}'
    },

    /**
     * Initializes the component and builds up the main interface
     *
     * @returns { Void }
     */
    initComponent: function() {
        var me = this;

        me.addEvents('searchGrids');
        me.items = me.createButtons();
        me.callParent(arguments);
    },

    /**
     * Creates the action buttons for the component.
     *
     * @returns { Array }
     */
    createButtons: function() {
        var me = this;

        me.addBtn = Ext.create('Ext.button.Button', {
            text: me.snippets.addBtn,
            action: 'emotion-templates-new-template',
            iconCls: 'sprite-plus-circle'
        });

        me.deleteBtn = Ext.create('Ext.button.Button', {
            text: me.snippets.delBtn,
            action: 'emotion-templates-delete-marked-templates',
            iconCls: 'sprite-minus-circle',
            disabled: true
        });

        me.searchField = Ext.create('Ext.form.field.Text', {
            emptyText: me.snippets.searchField,
            cls: 'searchfield',
            width: 200,
            enableKeyEvents:true,
            checkChangeBuffer:500,
            listeners: {
                change: function(field, value) {
                    me.fireEvent('searchGrids', value);
                }
            }
        });

        return [ me.addBtn, me.deleteBtn, { xtype: 'tbfill' }, me.searchField, { xtype: 'tbspacer', width: 6 } ];
    }
});
//{/block}
