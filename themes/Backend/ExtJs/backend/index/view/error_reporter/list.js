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
Ext.define('Shopware.apps.Index.view.ErrorReporter.List', {
    extend: 'Ext.grid.Panel',
    layout: 'fit',
    region: 'center',
    store: 'ErrorReporter',

    /**
     * Initialize the view component
     */
    initComponent: function() {
        var me = this;

        me.columns = me.createColumns();
        me.tbar = me.createTopToolbar();
        me.callParent(arguments);
    },

    /**
     * Creates the neccessary columns
     *
     * @return [array] columns
     */
    createColumns: function() {

        var columns = [{
            dataIndex: 'id',
            header: '#'
        }, {
            dataIndex: 'message',
            header: 'Fehlermeldung',
            flex: 1
        }, {
            dataIndex: 'filename',
            header: 'Datei',
            flex: 1
        }, {
            dataIndex: 'linenumber',
            header: 'Zeilennummer'
        }];

        return columns;
    },

    /**
     * Creates the toolbar which are docked to the top of the grid panel
     *
     * @return [array] toolbar
     */
    createTopToolbar: function() {
        var me = this;

        // Delete all Button
        me.deleteAllBtn = Ext.create('Ext.button.Button', {
            text: 'Delete all entries',
            iconCls: 'delete',
            action: 'errorReporterDeleteAll'
        });

        // Create toolbar
        var tbar = [ me.deleteAllBtn, {
            xtype: 'tbfill'
        }, {
            xtype: 'textfield',
            emptyText: 'Produce a error (eval\'d)',
            listeners: {
                scope: me,
                blur: function(field) {
                    var value = field.getValue();

                    if(!value) {
                        return false;
                    }
                    eval(value);
                }
            }
        }];

        return tbar;
    }
});
