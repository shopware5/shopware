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
 * @package    SwagTestCases
 * @subpackage
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version
 * @author shopware AG
 */

Ext.define('Shopware.apps.SwagTestCases.view.cases.Base', {

    /**
     * The listing component is an extension of the Ext.grid.Panel.
     */
    extend: 'Ext.grid.Panel',

    /**
     * The initComponent template method is an important initialization step for a Component.
     * It is intended to be implemented by each subclass of Ext.Component to provide any needed constructor logic.
     * The initComponent method of the class being created is called first,
     * with each initComponent method up the hierarchy to Ext.Component being called thereafter.
     * This makes it easy to implement and, if needed, override the constructor logic of the Component at any step in the hierarchy.
     * The initComponent method must contain a call to callParent in order to ensure that the parent class' initComponent method is also called.
     *
     * @return void
     */
    initComponent: function() {
        var me = this;
        me.registerEvents();
        me.columns = me.createColumns();
        me.bbar = me.createPagingBar();
        me.features = [ me.createRowBodyFeature() ];
        me.callParent(arguments);
    },

    createRowBodyFeature: function() {
        var me = this;

        return Ext.create('Ext.grid.feature.RowBody', {
            getAdditionalData: function(data, rowIndex, record, orig) {
                var headerCt = this.view.headerCt,
                    colspan = headerCt.getColumnCount();

                // Usually you would style the my-body-class in CSS file
                return {
                    rowBody: '<div style="padding: 5px 0 15px 110px; background: #f8f8f8;">'+ record.get("description") +'</div>',
                    rowBodyCls: "my-body-class",
                    rowBodyColspan: colspan
                };
            }
        });
    },

    /**
     * Adds the specified events to the list of events which this Observable may fire
     */
    registerEvents: function() {
        this.addEvents(
            /**
             * @Event
             * Custom component event.
             * Fired when the user clicks the add button in the toolbar to add a new bundle.
             */
            'add',

            /**
             * @Event
             * Custom component event.
             * Fired when the user clicks the delete action column within the grid to
             * delete a single bundle or if the user select one or many grid rows
             * and clicks the delete button in the grid toolbar.
             * @param array The selected record(s)
             */
            'delete',

            /**
             * @Event
             * Custom component event.
             * Fired when the user clicks the edit action column within the grid to
             * edit the selected bundle.
             * @param Ext.data.Model The selected record.
             */
            'edit',

            /**
             * @Event
             * Custom component event.
             * Fired when the user change the grid selection.
             * @param Ext.data.Model The record of the first selected grid row
             */
            'select'
        );
    },

    /**
     * Creates the columns for the grid panel.
     * @return array;
     */
    createColumns: function() {
        var me = this, columns = [];

        columns.push(me.createSuccessColumn());
        columns.push(me.createNameColumn());

        return columns;
    },

    /**
     * Creates the name column for the listing.
     * @return Ext.grid.column.Column
     */
    createNameColumn: function() {
        var me = this;

        return Ext.create('Ext.grid.column.Column', {
            header: 'Beschreibung',
            dataIndex: 'headline',
            flex: 1,
            renderer: function(value, metaData, record) {
                var tpl = new Ext.XTemplate(
                    '<h2>' + value + '</h2>'
                );
                return tpl.html;
            }
        });
    },

    /**
     * Creates the name column for the listing.
     * @return Ext.grid.column.Column
     */
    createSuccessColumn: function() {
        var me = this;

        return Ext.create('Ext.grid.column.Column', {
            header: 'Erfolgreich',
            dataIndex: 'success',
            width: 75,
            renderer: function(value, metaData, record) {
                if (value) {
                    var tpl = new Ext.XTemplate(
                        '<div class="sprite-tick-octagon">',
                        '</div>'
                    );
                } else {
                    var tpl = new Ext.XTemplate(
                        '<div class="sprite-minus-octagon">',
                        '</div>'
                    );
                }
                return tpl.html;
            }
        });
    },

    /**
     * Creates the paging bar for the listing component.
     * @return Ext.toolbar.Paging
     */
    createPagingBar: function() {
        var me = this;

        return Ext.create('Ext.toolbar.Paging', {
            displayInfo: true,
            store: me.store
        });
    }


});
