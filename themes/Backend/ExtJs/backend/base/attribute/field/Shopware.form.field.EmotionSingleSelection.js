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
 * @package     Base
 * @subpackage  Attribute
 * @version     $Id$
 * @author      shopware AG
 */

//{namespace name="backend/attributes/fields"}

Ext.define('Shopware.form.field.EmotionSingleSelection', {
    extend: 'Shopware.form.field.SingleSelection',
    alias: 'widget.shopware-form-field-emotion-single-selection',
    iconStyling: 'width:16px; height:16px; display:inline-block; margin-right:5px',
    snippets: {
        desktop: '{s namespace=backend/emotion/list/grid name=grid/renderer/desktop}{/s}',
        tabletLandscape: '{s namespace=backend/emotion/list/grid name=grid/renderer/tabletLandscape}{/s}',
        tablet: '{s namespace=backend/emotion/list/grid name=grid/renderer/tablet}{/s}',
        mobileLandscape: '{s namespace=backend/emotion/list/grid name=grid/renderer/mobileLandscape}{/s}',
        mobile: '{s namespace=backend/emotion/list/grid name=grid/renderer/mobile}{/s}'
    },

    getComboConfig: function() {
        var me = this;
        var config = me.callParent(arguments);

        config.tpl = Ext.create('Ext.XTemplate',
            '<tpl for=".">',
                '<div class="x-boundlist-item">' +
                '{literal}' +
                    '{name}   {[this.getDevices(values)]}' +
                '{/literal}' +
                '</div>',
            '</tpl>',
            {
                getDevices: function(values) {
                    var devices = '';

                    // Device detection
                    if(values.device.indexOf('0') >= 0) {
                        devices += '<div class="sprite-imac" style="' + me.iconStyling + '" title="' + me.snippets.desktop + '">&nbsp;</div>';
                    }
                    if(values.device.indexOf('1') >= 0) {
                        devices += '<div class="sprite-ipad--landscape" style="' + me.iconStyling + '" title="' + me.snippets.tabletLandscape + '">&nbsp;</div>';
                    }
                    if(values.device.indexOf('2') >= 0) {
                        devices += '<div class="sprite-ipad--portrait" style="' + me.iconStyling + '" title="' + me.snippets.tablet + '">&nbsp;</div>';
                    }
                    if(values.device.indexOf('3') >= 0) {
                        devices += '<div class="sprite-iphone--landscape" style="' + me.iconStyling + '" title="' + me.snippets.mobileLandscape + '">&nbsp;</div>';
                    }
                    if(values.device.indexOf('4') >= 0) {
                        devices += '<div class="sprite-iphone--portrait" style="' + me.iconStyling + '" title="' + me.snippets.mobile + '">&nbsp;</div>';
                    }

                    return '<div style="float: right;">' + devices + '</div>';
                }
            }
        );

        config.displayTpl = Ext.create('Ext.XTemplate',
            '<tpl for=".">',
                '{literal}{name}{/literal}',
            '</tpl>'
        );

        return config;
    }
});
