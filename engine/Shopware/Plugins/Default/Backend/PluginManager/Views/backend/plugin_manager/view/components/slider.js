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

//{block name="backend/plugin_manager/view/components/slider"}
Ext.define('Shopware.apps.PluginManager.view.list.Slider', {
    extend: 'Ext.container.Container',
    cls: 'slider',
    alias: 'widget.plugin-slider',
    running: false,
    rotationSpeed: 500,
    itemWidth: 150,
    itemHeight: 185,
    headline : '',
    itemTemplate : '',
    loadMask: false,

    initComponent: function() {
        var me = this;

        me.items = me.createItems();

        if (me.store) {
            me.store.on('load', function() {
                me.slider.setWidth(me.store.getCount() * me.itemWidth);
            });
        }

        me.callParent(arguments);
    },

    createItems: function() {
        var me = this, items = [];

        if (me.headline) {
            items.push(me.createHeadline());
        }
        items.push(me.createSlider());
        items.push(me.createNextButton());
        items.push(me.createPreviousButton());

        return items;
    },

    createHeadline: function() {
        return Ext.create('Ext.container.Container', {
            html: this.headline,
            cls: 'headline'
        });
    },

    createSlider: function() {
        var me = this;

        me.slider = Ext.create('Ext.view.View', {
            cls: 'slider-view',
            itemSelector: '.clickable',
            tpl: me.createTemplate(),
            store: me.store,
            height: me.itemHeight,
            listeners: {
                itemclick: function(item, record) {
                    me.fireEvent('itemclick', me, record);
                }
            }
        });

        return Ext.create('Ext.container.Container', {
            height: me.itemHeight + 2,
            items: [ me.slider ],
            cls: 'slider-viewport'
        });
    },

    createNextButton: function() {
        var me = this;

        me.nextButton = Ext.create('PluginManager.container.Container', {
            cls: 'next-button',
            html: '>',
            listeners: {
                click: function(comp) {
                    me.slide(-1);
                }
            }
        });

        return me.nextButton;
    },

    createPreviousButton: function() {
        var me = this;

        me.previousButton = Ext.create('PluginManager.container.Container', {
            cls: 'previous-button',
            html: '<',
            listeners: {
                click: function() {
                    me.slide(1);
                }
            }
        });

        me.previousButton.hide();

        return me.previousButton;
    },

    slide: function(direction) {
        var me = this,
            left = 0,
            nextPosition,
            itemsPerPage = Math.floor(me.getWidth() / me.itemWidth) ,
            pageWidth;

        if (itemsPerPage < 1) itemsPerPage = 1;
        pageWidth = (itemsPerPage - 1) * me.itemWidth;

        if (me.running) return;
        me.running = true;

        if (me.slider.x !== undefined) left = me.slider.x;

        nextPosition = left + (pageWidth * direction);
        if (nextPosition > 0) nextPosition = 0;

        me.slider.animate({
            duration: me.rotationSpeed,
            to: { left: nextPosition },
            listeners: {
                afteranimate: function() {
                    me.running = false;
                }
            }
        });

        if ((nextPosition - pageWidth) * -1 >= me.slider.getWidth()) {
            me.nextButton.hide()
        } else if (me.nextButton.hidden) {
            me.nextButton.show();
        }

        if (nextPosition < 0) {
            me.previousButton.show();
        } else {
            me.previousButton.hide();
        }
    },

    createTemplate: function() {
        var me = this;

        return new Ext.XTemplate(
            '{literal}',
                '<tpl for=".">',
                    '<div class="plugin">',
                        me.itemTemplate,
                    '</div>',
                '</tpl>',
            '{/literal}'
        );
    }
});
//{/block}