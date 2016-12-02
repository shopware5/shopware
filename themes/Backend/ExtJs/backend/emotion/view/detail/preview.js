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
//{block name="backend/emotion/view/detail/preview"}
Ext.define('Shopware.apps.Emotion.view.detail.Preview', {

    extend: 'Ext.panel.Panel',

    alias: 'widget.emotion-detail-preview',

    cls: Ext.baseCSSPrefix + 'emotion-preview-container',

    border: false,
    bodyBorder: false,

    autoShow: false,
    hidden: true,

    height: 9000,

    scrollBarOffset: 30,

    viewportPadding: {
        'xs': 10,
        's': 10,
        'm': 30,
        'l': 30,
        'xl': 50
    },

    initComponent: function() {
        var me = this;

        me.width = me.basicGridWidth;
        me.callParent(arguments);
    },

    showPreview: function(viewport, previewSrc) {
        var me = this,
            width = viewport.get('minWidth') || me.basicGridWidth,
            padding = me.viewportPadding[viewport.get('alias')] * 2;

        me.removeAll();

        me.setWidth(me.basicGridWidth + padding);

        me.previewElement = me.createPreviewElement(previewSrc, width + padding);

        me.add(me.previewElement);
        me.show();
    },

    hidePreview: function() {
        var me = this;

        me.removeAll();
        me.hide();
    },

    changePreview: function(viewport) {
        var me = this,
            width = viewport.get('minWidth') || me.basicGridWidth,
            padding = me.viewportPadding[viewport.get('alias')] * 2;

        me.setWidth(me.basicGridWidth + padding);
        me.previewElement.setWidth(width + padding);
    },

    createPreviewElement: function(src, width) {
        var me = this;

        return me.previewElement = Ext.create('Ext.Component', {
            autoEl : {
                tag : 'iframe',
                src : src,
                style: {
                    height: 'auto',
                    width: width + 'px'
                }
            }
        });
    },

    onReload: function() {
        var me = this,
            iframe = me.previewElement.getEl().dom;

        me.setLoading(true);

        iframe.contentWindow.location.reload();

        me.setLoading(false);
    }
});
//{/block}
