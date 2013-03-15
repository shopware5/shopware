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
 * @subpackage Controller
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/emotion/view/detail}

/**
 * Shopware UI - Emotion Main Controller
 *
 * This file contains the business logic for the Emotion module.
 */
//{block name="backend/emotion/controller/grids"}
Ext.define('Shopware.apps.Emotion.controller.Grids', {

    /**
     * Extend from the standard ExtJS 4 controller
     * @String
     */
	extend: 'Ext.app.Controller',

    /**
     * References to components
     * @Array
     */
    refs: [
        { ref: 'list', selector: 'emotion-grids-list' },
        { ref: 'toolbar', selector: 'emotion-grids-toolbar' }
    ],

    /**
     * Creates the necessary event listener for this
     * specific controller and opens a new Ext.window.Window
     * to display the subapplication
     *
     * @return void
     */
    init: function() {
        var me = this;

        me.control({
            'emotion-grids-list': {
                'selectionChange': me.onSelectionChange,
                'edit': me.onEdit,
                'duplicate': me.onDuplicate,
                'remove': me.onRemove
            }
        });
    },

    onSelectionChange: function(selection) {
        var me = this,
            toolbar = me.getToolbar(),
            btn = toolbar.deleteBtn;

        btn.setDisabled(!selection.length);
    },

    onEdit: function(grid, rec, row, col) {
        alert('Open Edit Window');
    },

    onDuplicate: function(grid, rec, row, col) {
        var me = this;
    },

    onRemove: function(grid, rec, row, col) {
        var store = grid.getStore();

        store.remove(rec);
        grid.setLoading(true);
        rec.destroy({
            callback: function() {
                grid.setLoading(false);
            }
        });
    }

});
//{/block}