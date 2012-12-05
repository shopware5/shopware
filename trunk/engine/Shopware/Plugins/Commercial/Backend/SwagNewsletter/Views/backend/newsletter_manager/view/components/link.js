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
 * @package    NewsletterManager
 * @subpackage View
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */
//{block name=backend/newsletter_manager/view/components/link}
//{namespace name=backend/newsletter_manager/view/components/link}
Ext.define('Shopware.apps.NewsletterManager.view.components.Link', {
    extend: 'Shopware.apps.NewsletterManager.view.components.Base',
    alias: 'widget.newsletter-components-links',

    /**
     * Snippets for the component.
     * @object
     */
    snippets: {
        'select_link': '{s name=select_link}Add link{/s}',
        'link_administration': '{s name=link_administration}Link administration{/s}',
        'target': '{s name=target}Link target{/s}',
        'actions': '{s name=actions}Action(s){/s}',
        'link': '{s name=link}Link{/s}',
        'description': '{s name=description}Description{/s}'
    },

    /**
     * Initiliaze the component.
     *
     * @public
     * @return void
     */
    initComponent: function() {
        var me = this;
        me.targetStore = me.createTargetStore();

        me.callParent(arguments);

        me.setDefaultValues();
        me.add(me.createLinkFieldset());
        me.getGridData();
        me.refreshHiddenValue();
    },

    /**
     * Creates the simple target store. As only two possible options are available,
     * its created 'in the fly'
     * @return Ext.data.ArrayStore
     */
    createTargetStore: function() {
        return new Ext.data.ArrayStore({
            fields: [ 'id', 'target', 'name' ],
            data: [
                [ 1, '_blank', 'Extern' ],
                [ 2, '_parent', 'Shopware' ]
            ]
        });
    },

    /**
     * Sets default values if the banner list
     * wasn't saved previously.
     *
     * @public
     * @return void
     */
    setDefaultValues: function() {
        var me = this,
            numberfields =  me.query('numberfield');

        Ext.each(numberfields, function(field) {
            if(!field.getValue()) {
                field.setValue(500);
            }
        });
    },

    /**
     * Creates the fieldset which holds the link administration. The method
     * also creates the link store and registers the drag and drop plugin
     * for the grid.
     *
     * @public
     * @return [object] Ext.form.FieldSet
     */
    createLinkFieldset: function() {
        var me = this;

        me.linkField = Ext.create('Ext.form.field.Text', {
            fieldLabel: me.snippets.select_link,
            labelWidth: 155,
            listeners: {
                specialkey: function(f,e){
                  if (e.getKey() == e.ENTER) {
                      me.onAddLinkToGrid(f);
                  }
                }
            }
        });

        me.linkStore = Ext.create('Ext.data.Store', {
            fields: [ 'position', 'link', 'description', 'target' ]
        });

        me.ddGridPlugin = Ext.create('Ext.grid.plugin.DragDrop');

        me.cellEditing = Ext.create('Ext.grid.plugin.RowEditing', {
            clicksToEdit: 2
        });

        me.linkGrid = Ext.create('Ext.grid.Panel', {
            columns: me.createColumns(),
            autoScroll: true,
            store: me.linkStore,
            height: 200,
            plugins: [ me.cellEditing ],
            viewConfig: {
                plugins: [ me.ddGridPlugin ],
                listeners: {
                    scope: me,
                    drop: me.onRepositionLink
                }
            },
            listeners: {
                scope: me,
                edit: function() {
                    me.refreshHiddenValue();
                }
            }
        });

        return me.linkFieldset = Ext.create('Ext.form.FieldSet', {
            title: me.snippets.link_administration,
            layout: 'anchor',
            defaults: { anchor: '100%' },
            items: [ me.linkField, me.linkGrid ]
        });
    },

    /**
     * Helper method which creates the column model
     * for the link administration grid panel.
     *
     * @public
     * @return Array computed columns
     */
    createColumns: function() {
        var me = this, snippets = me.snippets;

        return [{
            header: '&#009868;',
            width: 24,
            hideable: false,
            renderer : me.renderSorthandleColumn
        }, {
            dataIndex: 'link',
            header: snippets.link,
            flex: 2,
            editor: {
                xtype: 'textfield',
                allowBlank: false
            }
        }, {
            dataIndex: 'description',
            header: snippets.description,
            flex: 2,
            editor: {
                xtype: 'textfield',
                allowBlank: false
            }
        }, {
            dataIndex: 'target',
            header: snippets.target,
            flex: 1,
            editor: {
                xtype: 'combobox',
                queryMode: 'local',
                allowBlank: false,
                valueField: 'target',
                displayField: 'name',
                store : me.targetStore,
                editable: false
            },
            renderer: function(value) {
                var me = this, record,
                    parent = me.up('newsletter-components-links');

                if (value === Ext.undefined) {
                    return value;
                }

                record =  parent.targetStore.findRecord('target', value);

                if (record instanceof Ext.data.Model) {
                    return record.get('name');
                } else {
                    return value;
                }
            }
        }, {
            xtype: 'actioncolumn',
            header: snippets.actions,
            width: 60,
            items: [{
                iconCls: 'sprite-minus-circle',
                action: 'delete-link',
                scope: me,
                handler: me.onDeleteLink
            }]
        }];
    },

    /**
     * Event listener method which will be triggered when one (or more)
     * link are added to the link list.
     *
     * Creates new models based on the selected links and
     * assigns them to the link store.
     *
     * @public
     * @event selectMedia
     * @param Ext.form.field.Text field
     */
    onAddLinkToGrid: function(field) {
        var me = this, store = me.linkStore;

        if(Ext.util.Format.trim(field.getValue()) === "") {
            return;
        }

        var count = store.getCount();
        var model = Ext.create('Shopware.apps.NewsletterManager.model.Link', {
            description: field.getValue(),
            link: field.getValue(),
            position:count,
            target: '_parent'
        });

        store.add(model);

        // We need a defer due to early firing of the event
        Ext.defer(function() {
//            me.mediaSelection.inputEl.dom.value = '';
            me.linkField.setValue('');
            me.refreshHiddenValue();
        }, 10);

    },

    /**
     * Event listener method which will be triggered when the user
     * deletes a link from link administration grid panel.
     *
     * Removes the link from the link store.
     *
     * @event click#actioncolumn
     * @param [object] grid - Ext.grid.Panel
     * @param [integer] rowIndex - Index of the clicked row
     * @param [integer] colIndex - Index of the clicked column
     * @param [object] item - DOM node of the clicked row
     * @param [object] eOpts - additional event parameters
     * @param [object] record - Associated model of the clicked row
     */
    onDeleteLink: function(grid, rowIndex, colIndex, item, eOpts, record) {
        var me = this;
        var store = grid.getStore();
        store.remove(record);
        me.refreshHiddenValue();
    },

    /**
     * Event listener method which will be fired when the user
     * repositions a link through drag and drop.
     *
     * Sets the new position of the link in the link store
     * and saves the data to an hidden field.
     *
     * @public
     * @event drop
     * @return void
     */
    onRepositionLink: function() {
        var me = this;

        var i = 0;
        me.linkStore.each(function(item) {
            item.set('position', i);
            i++;
        });
        me.refreshHiddenValue();
    },

    /**
     * Refreshes the mapping field in the model
     * which contains all links in the grid.
     *
     * @public
     * @return void
     */
    refreshHiddenValue: function() {
        var me = this,
            store = me.linkStore,
            cache = [];

        store.each(function(item) {
            cache.push(item.data);
        });
        var record = me.getSettings('record');
        record.set('mapping', cache);
    },

    /**
     * Refactors the mapping field in the global record
     * which contains all link in the grid.
     *
     * Adds all links to the link administration grid
     * when the user opens the component.
     *
     * @return void
     */
    getGridData: function() {
        var me = this,
            elementStore = me.getSettings('record').get('data'), linkList;

        Ext.each(elementStore, function(element) {
            if(element.key === 'link_data') {
                linkList = element;
                return false;
            }
        });

        if(linkList && linkList.value) {
            Ext.each(linkList.value, function(item) {
                me.linkStore.add(Ext.create('Shopware.apps.NewsletterManager.model.Link', item));
            });
        }
    },

    /**
     * Renderer for sorthandle-column
     *
     * @param [string] value
     */
    renderSorthandleColumn: function() {
        return '<div style="cursor: move;">&#009868;</div>';
    }
});
//{/block}