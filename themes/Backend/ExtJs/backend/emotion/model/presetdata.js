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
 * @subpackage Main
 * @version    $Id$
 * @author shopware AG
 */
//{block name="backend/emotion/model/presetdata"}
Ext.define('Shopware.apps.Emotion.model.Presetdata', {
    extend: 'Ext.data.Model',

    requiredPlugins: null,

    constructor: function() {
        var me = this;

        me.callParent(arguments);

        me.requiredPlugins = [];
    },

    fields: [
        //{block name="backend/emotion/model/presetdata/fields"}{/block}
        { name: 'id', type: 'int', useNull: true },
        { name: 'active', type: 'boolean', defaultValue: false },
        { name: 'articleHeight', type: 'int' },
        { name: 'cellHeight', type: 'int' },
        { name: 'cellSpacing', type: 'int' },
        { name: 'cols', type: 'int' },
        { name: 'device' },
        { name: 'fullscreen', type: 'int' },
        { name: 'isLandingPage', type: 'boolean'},
        { name: 'mode' },
        { name: 'position', type: 'int' },
        { name: 'rows', type: 'int' },
        { name: 'showListing', type: 'boolean' },
        { name: 'templateId', type: 'int' },
        { name: 'elements', type: 'array' }
    ],

    /**
     * Contains serialized model data with associations included.
     *
     * @param { Object } data
     */
    createFromEmotionData: function(data) {
        var me = this;

        if (data['getElements']) {
            data.elements = me.cleanupElements(data['getElements']);
            delete data['getElements'];
        }

        me.set({
            id: null,
            active: data.active,
            articleHeight: data.articleHeight,
            cellHeight: data.cellHeight,
            cellSpacing: data.cellSpacing,
            cols: data.cols,
            device: data.device,
            fullscreen: data.fullscreen,
            isLandingPage: data.isLandingPage,
            mode: data.mode,
            position: data.position,
            rows: data.rows,
            showListing: data.showListing,
            templateId: data.templateId,
            elements: data.elements || []
        });
    },

    cleanupElements: function(elements) {
        var me = this,
            cleanedElements = [],
            i, count;

        for (i = 0, count = elements.length; i < count; i++) {
            var element = elements[i],
                viewports = element['getViewports'] || [],
                component = element['getComponent'][0],
                cleanedElement = {
                    componentId: element['componentId'],
                    startRow: element['startRow'],
                    startCol: element['startCol'],
                    endRow: element['endRow'],
                    endCol: element['endCol'],
                    data: element['data'],
                    viewports: me.prepareViewports(viewports),
                    component: me.prepareComponent(component)
                };

            cleanedElements.push(cleanedElement);
        }

        return cleanedElements;
    },

    prepareViewports: function(viewports) {
        var cleanedViewports = [],
            i, count;

        for (i = 0, count = viewports.length; i < count; i++) {
            var viewport = viewports[i],
                cleanedViewport = {
                    alias: viewport['alias'],
                    startRow: viewport['startRow'],
                    startCol: viewport['startCol'],
                    endRow: viewport['endRow'],
                    endCol: viewport['endCol'],
                    visible: viewport['visible']
                };

            cleanedViewports.push(cleanedViewport);
        }

        return cleanedViewports;
    },

    prepareComponent: function(component) {
        var me = this;

        if (component['getFields']) {
            component['fields'] = component['getFields'];
            delete component['getFields'];
        }

        if (!Ext.isEmpty(component['pluginId']) && !Ext.Array.contains(me.requiredPlugins, component['pluginId'])) {
            me.requiredPlugins.push(component['pluginId']);
        }

        return component;
    },

    getRequiredPlugins: function() {
        var me = this;

        return me.requiredPlugins;
    }
});
//{/block}
