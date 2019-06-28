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
 * @category    Shopware
 * @package     Emotion
 * @subpackage  View
 * @version     $Id$
 * @author      shopware AG
 */

//{namespace name=backend/emotion/view/detail}
//{block name="backend/emotion/view/detail/widgets"}
Ext.define('Shopware.apps.Emotion.view.detail.Widgets', {

    extend: 'Ext.panel.Panel',
    alias: 'widget.emotion-detail-widgets',

    title: '{s name="title/widgets_tab"}{/s}',

    bodyPadding: 20,
    border: 0,
    bodyBorder: 0,
    autoScroll: true,
    style: 'background: #f0f2f4',

    snippets: {
        standardElementsLabel: '{s name="widgets/standard/label"}{/s}',
        thirdPartyElementsLabel: '{s name="widgets/developers/label"}{/s}'
    },

    initComponent: function () {
        var me = this;

        me.items = [
            me.createComponentList()
        ];

        me.on({
            'afterrender': me.onAfterRender
        });

        me.callParent(arguments);
    },

    onAfterRender: function() {
        var me = this;

        me.createDragZone();
    },

    createComponentList: function() {
        var me = this;

        return me.list = Ext.create('Ext.view.View', {
            tpl: me.createElementLibraryTemplate(),
            store: me.createComponentStore(),
            itemSelector: 'x-library-element'
        });
    },

    createElementLibraryTemplate: function() {
        return new Ext.XTemplate(
            '{literal}<tpl for=".">',
                '<tpl if="children.length &gt; 0">',
                '<fieldset class="x-fieldset x-fieldset-with-title x-fieldset-with-legend x-fieldset-default">',
                    '<legend class="x-fieldset-header x-fieldset-header-default">',
                        '<div class="x-component x-fieldset-header-text x-component-default">{headline}:</div>',
                    '</legend>',
                    '<div class="x-library-elements">',
                        '<ul>',
                            '<tpl for="children">',
                                '<li class="x-library-element" data-componentId="{data.id}">',
                                    '<span class="x-library-element-icon">',
                                        '{[this.getElementIcon(values)]}',
                                    '</span>',
                                    '<span class="x-library-element-label">{data.fieldLabel}</span>',
                                '</li>',
                            '</tpl>',
                        '</ul>',
                    '</div>',
                '</fieldset>',
                '</tpl>',
            '</tpl>{/literal}',
            {
                getElementIcon: function(values) {
                    var elementClassName = Ext.ClassManager.getNameByAlias('widget.detail-element-' + values.data.xType),
                        element;

                    if (elementClassName.length <= 0) {
                        return '';
                    }

                    element = Ext.create(elementClassName, { instanceOnly: true });

                    return Ext.String.format('<img src="[0]" />', element.getIcon());
                }
            }
        );
    },

    createComponentStore: function() {
        var me = this;

        me.shopwareComponents = me.getShopwareComponents();
        me.pluginComponents = me.getPluginComponents();

        return Ext.create('Ext.data.Store', {
            fields: [
                'headline', 'children'
            ],
            data: [{
                headline: me.snippets.standardElementsLabel,
                children: me.shopwareComponents
            }, {
                headline: me.snippets.thirdPartyElementsLabel,
                children: me.pluginComponents
            }]
        });
    },

    getShopwareComponents: function() {
        var me = this;

        me.libraryStore.clearFilter();
        me.libraryStore.filter({
            filterFn: function(item) {
                return item.get('pluginId') === null;
            }
        });

        return me.libraryStore.data.items;
    },

    getPluginComponents: function() {
        var me = this;

        me.libraryStore.clearFilter();
        me.libraryStore.filter({
            filterFn: function(item) {
                return item.get('pluginId') > 0;
            }
        });

        return me.libraryStore.data.items;
    },

    createDragZone: function() {
        var me = this,
            dragZoneEl = me.list.getEl();

        me.dragZone = Ext.create('Ext.dd.DragZone', dragZoneEl, {

            ddGroup: 'emotion-dd',

            proxyCls: Ext.baseCSSPrefix + 'emotion-dd-proxy',

            onStartDrag: function() {
                var sourceEl = me.dragZone.dragData.sourceEl,
                    element = Ext.get(sourceEl);

                element.addCls('is--dragging');
            },

            getDragData: function(e) {
                var sourceEl = e.getTarget('.x-library-element', 10),
                    element = Ext.get(sourceEl),
                    componentId = ~~(1 * element.getAttribute('data-componentId')),
                    component = me.libraryStore.getById(componentId),
                    dragEl, proxyEl = me.dragZone.proxy;

                proxyEl.getEl().addCls(Ext.baseCSSPrefix + 'shopware-dd-proxy');

                if(!sourceEl || !element) {
                    return false;
                }

                dragEl = sourceEl.cloneNode(true);
                dragEl.id = Ext.id();

                var model = Ext.create('Shopware.apps.Emotion.model.EmotionElement', {
                    id: Ext.id(),
                    componentId: componentId,
                    name: component.get('name'),
                    fieldLabel: component.get('fieldLabel')
                });

                model.getComponent().add(component);

                return {
                    ddel: dragEl,
                    sourceEl: sourceEl,
                    repairXY: Ext.fly(sourceEl).getXY(),
                    sourceStore: me.list.store,
                    draggedRecord: model
                }
            },

            getRepairXY: function(e) {
                var previewEl = me.mainWindow.designer.grid.previewElement,
                    sourceEl = me.dragZone.dragData.sourceEl,
                    element = Ext.get(sourceEl);

                if (previewEl) {
                    previewEl.remove();
                }

                if (element) {
                    element.removeCls('is--dragging');
                }

                return this.dragData.repairXY;
            },

            afterDragDrop: function() {
                var sourceEl = me.dragZone.dragData.sourceEl,
                    element = Ext.get(sourceEl);

                if (element) {
                    element.removeCls('is--dragging');
                }
            }
        });
    }
});
//{/block}
