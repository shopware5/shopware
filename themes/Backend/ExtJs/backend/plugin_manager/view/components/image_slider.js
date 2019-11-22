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
 * @subpackage Components
 * @version    $Id$
 * @author shopware AG
 */

//{block name="backend/plugin_manager/view/components/image_slider"}
Ext.define('Shopware.apps.PluginManager.view.components.ImageSlider', {
    extend: 'Ext.container.Container',
    cls: 'plugin-manager-image-slider',
    alias: 'widget.plugin-image-slider',
    layout: {
        type: 'vbox',
        align: 'stretch'
    },

    pageSize: 5,
    running: false,
    openFullImage: true,

    initComponent: function () {
        var me = this;

        me.items = [
            me.createPreviewContainer(),
            me.createNavigation()
        ];

        me.navigation.on('resize', function() {
            me.resizePages();
        });

        me.navigation.on('afterrender', function() {
            Ext.Function.defer(function(){
                me.resizePages();
            }, 1000);
        });

        if (me.store && me.store.first()) {
            var first = me.store.first();

            try {
                me.previewContainer.removeCls('default-icon');
            } catch (e) { }

            me.previewContainer.update(first.data);
        } else {
            me.previewContainer.addCls('default-icon');
            me.previewContainer.update({
                remoteLink: '{link file="themes/Backend/ExtJs/backend/_resources/resources/themes/images/shopware-ui/plugin_manager/default_icon.png"}'
            });
        }

        me.callParent(arguments);
    },

    createPreviousButton: function() {
        var me = this;

        me.previousButton = Ext.create('PluginManager.container.Container', {
            cls: 'previous-button',
            html: '<',
            listeners: {
                click: function() {
                    me.rotateNavigation(-1)
                }
            }
        });

        return me.previousButton;
    },

    createNextButton: function() {
        var me = this;

        me.nextButton = Ext.create('PluginManager.container.Container', {
            cls: 'next-button',
            html: '>',
            listeners: {
                click: function() {
                    me.rotateNavigation(1)
                }
            }
        });

        return me.nextButton;
    },

    createPreviewContainer: function() {
        var me = this;

        me.previewContainer = Ext.create('Ext.view.View', {
            tpl: me.createPreviewTemplate(),
            itemSelector: 'preview-image',
            flex: 1,
            cls: 'preview-container',
            listeners: {
                afterrender: function(comp) {
                    comp.el.on('click', function() {
                        me.displayFullImage();
                    });
                }
            }
        });

        return me.previewContainer;
    },

    displayFullImage: function() {
        var me = this;

        if (!me.openFullImage) {
            return;
        }

        if (!me.store || me.store && me.store.getCount() <= 0 ) {
            return;
        }

        var slider = Ext.create('Shopware.apps.PluginManager.view.components.ImageSlider', {
            store: me.store,
            pageSize: 12,
            flex: 1,
            openFullImage: false
        });

        me.fullImageWindow = Ext.create('Ext.window.Window', {
            modal: true,
            height: '90%',
            width: '75%',
            cls: 'plugin-manager-full-image-window',
            layout: {
                type: 'hbox',
                align: 'stretch'
            },
            items: [ slider ]
        }).show();
    },

    createPreviewTemplate: function() {
        return new Ext.XTemplate(
            '{literal}',
                '<tpl for=".">',
                    '<img class="preview-image" src="{remoteLink}" />',
                '</tpl>',
            '{/literal}'
        );
    },

    createNavigation: function() {
        var me = this, pages = [], items = [];

        var chunks = me.getStoreChunks(
            me.store,
            me.pageSize
        );

        Ext.each(chunks, function(chunk) {
            pages.push(me.createNavigationPage(chunk));
        });

        me.navigation = Ext.create('Ext.container.Container', {
            layout: 'card',
            items: pages,
            height: 80
        });

        items.push(me.navigation);

        if (chunks.length > 1) {
            items.push(me.createPreviousButton());
            items.push(me.createNextButton());
        }

        return Ext.create('Ext.container.Container', {
            items: items,
            cls: 'navigation-wrapper',
            height: 80
        });
    },

    createNavigationPage: function(chunk) {
        var me = this, items = [];

        Ext.each(chunk, function(record) {
            items.push(
                me.createNavigationItem(record)
            );
        });

        var page = Ext.create('Ext.container.Container', {
            items: items,
            cls: 'slider-page-inner',
            width: items.length * 70
        });

        return Ext.create('Ext.container.Container', {
            items: [ page ],
            cls: 'slider-page',
            padding: 10
        });
    },

    createNavigationItem: function(record) {
        var me = this;

        return Ext.create('Ext.container.Container', {
            cls: 'slider-item',
            width: 60,
            height: 60,
            html: '<img src="' + record.get('remoteLink') + '" class="slider-item-image" />',
            listeners: {
                afterrender: function(comp) {
                    comp.el.on('click', function() {
                        me.itemClick(record);
                    });
                }
            }
        });
    },

    itemClick: function(record) {
        var me = this;

        me.previewContainer.update(record.data);
    },

    getStoreChunks: function(store, size) {
        var me = this, items = [], start = 0;

        if (!(store instanceof Ext.data.Store)) {
            return items;
        }

        var count = Math.ceil(store.getCount() / size);

        for (var i=1; i<= count; i++) {
            items.push(
                store.getRange(start, size + start - 1)
            );
            start += size;
        }

        return items;
    },

    rotateNavigation: function(direction, callback) {
        var me = this,
            slider = me.navigation,
            next, current;

        if (me.running) {
            return;
        }

        me.running = true;

        //next
        if (direction == 1) {
            current = slider.getLayout().getActiveItem();
            current.getEl().slideOut('l', {
                duration : 500
            });

            next = slider.getLayout().getNext();
            if (next === false) next = slider.items.items[0];

            next.getEl().slideIn('r', {
                duration : 500,
                callback: function() {
                    slider.getLayout().setActiveItem(next);
                    me.running = false;
                    if (Ext.isFunction(callback)) {
                        callback();
                    }
                }
            });

        } else {
            current = slider.getLayout().getActiveItem();
            current.getEl().slideOut('r', {
                duration : 500
            });

            next = slider.getLayout().getPrev();
            if (next === false) {
                var count = slider.items.length;
                next = slider.items.items[count - 1]
            }

            next.getEl().slideIn('l', {
                duration : 500,
                callback: function() {
                    slider.getLayout().setActiveItem(next);
                    me.running = false;
                    if (Ext.isFunction(callback)) {
                        callback();
                    }
                }
            });
        }
    },

    resizePages: function() {
        var me = this;

        var active = me.navigation.getLayout().getActiveItem();

        if (!active) {
            return;
        }

        Ext.each(me.navigation.items.items, function(item) {
            item.getEl().setWidth(active.getWidth());
        });
    }
});
//{/block}
