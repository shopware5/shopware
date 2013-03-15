/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/emotion/grids/list}

/**
 * Shopware UI - Emotion Toolbar
 *
 * This file contains the business logic for the User Manager module. The module
 * handles the whole administration of the backend users.
 */
//{block name="backend/emotion/grids/list"}
Ext.define('Shopware.apps.Emotion.view.grids.List', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.emotion-grids-list',

    /**
     * Snippets which are used by this component.
     * @Object
     */
    snippets: {

    },

    /**
     * Initializes the component and builds up the main interface
     *
     * @returns { Void }
     */
    initComponent: function() {
        var me = this;

        me.addEvents('selectionChange');

        me.store = Ext.create('Ext.data.Store', {
            fields: [ 'id', 'name' ]
        });
        me.columns = me.createColumns();
        me.selModel = me.createSelectionModel();

        me.callParent(arguments);
    },

    /**
     * Creates the column model for the grid panel
     *
     * @returns { Array } columns
     */
    createColumns: function() {
        var me = this;

        return [{
            dataIndex: 'name',
            header: 'Name',
            flex: 1
        }, {
            dataIndex: 'cols',
            header: 'Columns',
            flex: 1
        }, {
            dataIndex: 'rows',
            header: 'Rows',
            flex: 1
        }, {
            dataIndex: 'cellHeight',
            header: 'Cell height (in px)',
            flex: 1
        }, {
            dataIndex: 'articleHeight',
            header: 'Article element height',
            flex: 1
        }];
    },

    /**
     * Creates the selection model.
     *
     * @returns { Ext.selection.CheckboxModel }
     */
    createSelectionModel: function() {
        var me = this;

        return Ext.create('Ext.selection.CheckboxModel', {
            listeners:{
                selectionchange:function (sm, selections) {

                    // todo - add checks for default one's
                    me.fireEvent('selectionChange', selections);
                }
            }
        });
    }
});
//{/block}