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
//{block name="backend/emotion/view/components/banner_slider"}
//{namespace name=backend/emotion/view/components/banner_slider}
Ext.define('Shopware.apps.Emotion.view.components.BannerSlider', {
    extend: 'Shopware.apps.Emotion.view.components.Base',
    alias: 'widget.emotion-components-banner-slider',

    /**
     * Snippets for the component.
     * @object
     */
    snippets: {
        'select_banner': '{s name=select_banner}Select banner(s){/s}',
        'banner_administration': '{s name=banner_administration}Banner administration{/s}',
        'path': '{s name=path}Image path{/s}',
        'actions': '{s name=actions}Action(s){/s}',
        'link': '{s name=link}Link{/s}',
        'altText': '{s name=altText}Alternative text{/s}',
        'title': '{s name=title}Title{/s}',

        banner_slider_title: '{s name=banner_slider_title}Title{/s}',
        banner_slider_arrows: '{s name=banner_slider_arrows}Display arrows{/s}',
        banner_slider_numbers: {
            fieldLabel: '{s name=banner_slider_numbers/label}Display numbers{/s}',
            supportText: '{s name=banner_slider_numbers/support}Please note that this setting only affects the "emotion" template.{/s}'
        },
        banner_slider_scrollspeed: '{s name=banner_slider_scrollspeed}Scroll speed{/s}',
        banner_slider_rotation: '{s name=banner_slider_rotation}Rotate automatically{/s}',
        banner_slider_rotatespeed: '{s name=banner_slider_rotatespeed}Rotation speed{/s}'
    },

    /**
     * Initialize the component.
     *
     * @public
     * @return void
     */
    initComponent: function() {
        var me = this;
        me.callParent(arguments);

        me.setDefaultValues();
        me.add(me.createBannerFieldset());
        me.getGridData();
        me.refreshHiddenValue();
    },

    /**
     * Sets default values if the banner slider
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
     * Creates the fieldset which holds the banner administration. The method
     * also creates the banner store and registers the drag and drop plugin
     * for the grid.
     *
     * @public
     * @return [object] Ext.form.FieldSet
     */
    createBannerFieldset: function() {
        var me = this;

        me.mediaSelection = Ext.create('Shopware.form.field.MediaSelection', {
            fieldLabel: me.snippets.select_banner,
            labelWidth: 155,
            albumId: -3,
            listeners: {
                scope: me,
                selectMedia: me.onAddBannerToGrid
            }
        });

        me.bannerStore = Ext.create('Ext.data.Store', {
            fields: [ 'position', 'path', 'link', 'altText', 'title', 'mediaId' ]
        });

        me.ddGridPlugin = Ext.create('Ext.grid.plugin.DragDrop');

        me.cellEditing = Ext.create('Ext.grid.plugin.RowEditing', {
            clicksToEdit: 2
        });

        me.bannerGrid = Ext.create('Ext.grid.Panel', {
            columns: me.createColumns(),
            autoScroll: true,
            store: me.bannerStore,
            height: 200,
            plugins: [ me.cellEditing ],
            viewConfig: {
                plugins: [ me.ddGridPlugin ],
                listeners: {
                    scope: me,
                    drop: me.onRepositionBanner
                }
            },
            listeners: {
                scope: me,
                edit: function() {
                    me.refreshHiddenValue();
                }
            }
        });

        return me.bannerFieldset = Ext.create('Ext.form.FieldSet', {
            title: me.snippets.banner_administration,
            layout: 'anchor',
            defaults: { anchor: '100%' },
            items: [ me.mediaSelection, me.bannerGrid ]
        });
    },

    /**
     * Helper method which creates the column model
     * for the banner administration grid panel.
     *
     * @public
     * @return [array] computed columns
     */
    createColumns: function() {
        var me = this, snippets = me.snippets;

        return [{
            header: '&#009868;',
            width: 24,
            hideable: false,
            renderer : me.renderSorthandleColumn
        }, {
            dataIndex: 'path',
            header: snippets.path,
            flex: 1
        }, {
            dataIndex: 'link',
            header: snippets.link,
            flex: 1,
            editor: {
                xtype: 'textfield',
                allowBlank: true
            }
        }, {
            dataIndex: 'altText',
            header: snippets.altText,
            flex: 1,
            editor: {
                xtype: 'textfield',
                allowBlank: true
            }
        }, {
            dataIndex: 'title',
            header: snippets.title,
            flex: 1,
            editor: {
                xtype: 'textfield',
                allowBlank: true
            }
        }, {
            xtype: 'actioncolumn',
            header: snippets.actions,
            width: 60,
            items: [{
                iconCls: 'sprite-minus-circle',
                action: 'delete-banner',
                scope: me,
                handler: me.onDeleteBanner
            }]
        }];
    },

    /**
     * Event listener method which will be triggered when one (or more)
     * banner are added to the banner slider.
     *
     * Creates new models based on the selected banners and
     * assigns them to the banner store.
     *
     * @public
     * @event selectMedia
     * @param [object] field - Shopware.MediaManager.MediaSelection
     * @param [array] records - array of the selected media
     */
    onAddBannerToGrid: function(field, records) {
        var me = this, store = me.bannerStore;

        Ext.each(records, function(record) {
            var count = store.getCount();
            var model = Ext.create('Shopware.apps.Emotion.model.BannerSlider', {
                position: count,
                path: record.get('path'),
                mediaId: record.get('id'),
                link: record.get('link'),
                altText: record.get('altText'),
                title: record.get('title')
            });
            store.add(model);
        });

        // We need a defer due to early firing of the event
        Ext.defer(function() {
            me.mediaSelection.inputEl.dom.value = '';
            me.refreshHiddenValue();
        }, 10);

    },

    /**
     * Event listener method which will be triggered when the user
     * deletes a banner from banner administration grid panel.
     *
     * Removes the banner from the banner store.
     *
     * @event click#actioncolumn
     * @param [object] grid - Ext.grid.Panel
     * @param [integer] rowIndex - Index of the clicked row
     * @param [integer] colIndex - Index of the clicked column
     * @param [object] item - DOM node of the clicked row
     * @param [object] eOpts - additional event parameters
     * @param [object] record - Associated model of the clicked row
     */
    onDeleteBanner: function(grid, rowIndex, colIndex, item, eOpts, record) {
        var me = this;
        var store = grid.getStore();
        store.remove(record);
        me.refreshHiddenValue();
    },

    /**
     * Event listener method which will be fired when the user
     * repositions a banner through drag and drop.
     *
     * Sets the new position of the banner in the banner store
     * and saves the data to an hidden field.
     *
     * @public
     * @event drop
     * @return void
     */
    onRepositionBanner: function() {
        var me = this;

        var i = 0;
        me.bannerStore.each(function(item) {
            item.set('position', i);
            i++;
        });
        me.refreshHiddenValue();
    },

    /**
     * Refreshes the mapping field in the model
     * which contains all banners in the grid.
     *
     * @public
     * @return void
     */
    refreshHiddenValue: function() {
        var me = this,
            store = me.bannerStore,
            cache = [];

        store.each(function(item) {
            cache.push(item.data);
        });
        var record = me.getSettings('record');
        record.set('mapping', cache);
    },

    /**
     * Refactor sthe mapping field in the global record
     * which contains all banner in the grid.
     *
     * Adds all banners to the banner administration grid
     * when the user opens the component.
     *
     * @return void
     */
    getGridData: function() {
        var me = this,
            elementStore = me.getSettings('record').get('data'), bannerSlider;

        Ext.each(elementStore, function(element) {
            if(element.key === 'banner_slider') {
                bannerSlider = element;
                return false;
            }
        });

        if(bannerSlider && bannerSlider.value) {
            Ext.each(bannerSlider.value, function(item) {
                me.bannerStore.add(Ext.create('Shopware.apps.Emotion.model.BannerSlider', item));
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
