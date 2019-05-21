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

Ext.define('Shopware.form.field.EmotionGrid', {
    extend: 'Shopware.form.field.Grid',
    alias: 'widget.shopware-form-field-emotion-grid',
    mixins: ['Shopware.model.Helper'],

    createColumns: function() {
        var me = this;
        var activeColumn = { dataIndex: 'active', width: 30 };
        me.applyBooleanColumnConfig(activeColumn);
        return [
            me.createSortingColumn(),
            activeColumn,
            { dataIndex: 'name', flex: 2 },
            { dataIndex: 'type', flex: 1 },
            { dataIndex: 'device', flex: 2, renderer: me.deviceRenderer },
            me.createActionColumn()
        ];
    },

    createSearchField: function() {
        return Ext.create('Shopware.form.field.EmotionSingleSelection', this.getComboConfig());
    },

    deviceRenderer: function(value, meta, record) {
        var devices = '',
            iconStyling = 'width:16px; height:16px; display:inline-block; margin-right:5px';

        var snippets = {
            desktop: '{s namespace=backend/emotion/list/grid name=grid/renderer/desktop}For desktop{/s}',
            tabletLandscape: '{s namespace=backend/emotion/list/grid name=grid/renderer/tabletLandscape}For tablet landscape{/s}',
            tablet: '{s namespace=backend/emotion/list/grid name=grid/renderer/tablet}For tablet{/s}',
            mobileLandscape: '{s namespace=backend/emotion/list/grid name=grid/renderer/mobileLandscape}For mobile landscape{/s}',
            mobile: '{s namespace=backend/emotion/list/grid name=grid/renderer/mobile}For mobile{/s}'
        };

        // Device detection
        if(value.indexOf('0') >= 0) {
            devices += '<div class="sprite-imac" style="' + iconStyling + '" title="' + snippets.desktop + '">&nbsp;</div>';
        }
        if(value.indexOf('1') >= 0) {
            devices += '<div class="sprite-ipad--landscape" style="' + iconStyling + '" title="' + snippets.tabletLandscape + '">&nbsp;</div>';
        }
        if(value.indexOf('2') >= 0) {
            devices += '<div class="sprite-ipad--portrait" style="' + iconStyling + '" title="' + snippets.tablet + '">&nbsp;</div>';
        }
        if(value.indexOf('3') >= 0) {
            devices += '<div class="sprite-iphone--landscape" style="' + iconStyling + '" title="' + snippets.mobileLandscape + '">&nbsp;</div>';
        }
        if(value.indexOf('4') >= 0) {
            devices += '<div class="sprite-iphone--portrait" style="' + iconStyling + '" title="' + snippets.mobile + '">&nbsp;</div>';
        }

        return devices;
    }
});
