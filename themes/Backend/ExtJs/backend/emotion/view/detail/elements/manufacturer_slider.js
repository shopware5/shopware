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
//{block name="backend/emotion/view/detail/elements/manufacturer_slider"}
Ext.define('Shopware.apps.Emotion.view.detail.elements.ManufacturerSlider', {

    extend: 'Shopware.apps.Emotion.view.detail.elements.Base',

    alias: 'widget.detail-element-emotion-components-manufacturer-slider',

    componentCls: 'manufacturer-slider-element',

    icon: 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACQAAAAeCAYAAABE4bxTAAACgElEQVRYhdXXQWgcVRzH8U9C0iJ1KUloD6VoPHqo9CJVhM2lUHoo7UUEyU5yGsEaKUwvPYjioRbaOZR6SOfUzjYBL2Kwlx5EPJgcgtBLiQfBi9aAYBVaYqkQD/uSDJuQmd3sSv3BMO/933/+/++8ee/PmwFdKM3yAexP4ujv0H8LV/E61rCAD5M4etRp7MEuYE7id6ylWZ4H85d4E0OoYRKfdhpbCFAV5CD+wbsYC+ZGmuUX8AiH2x75qxugSjOUZvkJ/IZf8QW+wQqSJI7+wGncDrYf8JE+z9AbeCFcB5M4OlkcTOLoZ0x3A9At0B1M4Anu9iLx/0YDVZzCzprEOq4lcfSgX0BVP9k7mArtFfQNqGodGi20X+4HyIaqAhVrzHMBVJyh8T5wbKrqGhortF8qDqRZfgrvJ3F0NvT34SIkcXQ52I7iM8wmcfR9L4CKM1RLs3wU+3Edbxfg6pjFq1hIs3wIH2hV7ZpWhd8bUJrlNQy3mT/Rqsy1gt8tWzsRXsEyjpfl6AgIh3awzexgm2rrv9YJyIaqLOqxcpfeqQrQaLlL71QF6EAP862VOVRZQ9/hvtYi3Yt+xL09xngOlGbNF9OsWflou9dc7bbBNodxLGKbY5+0GHJuB0qzZl2rkB37j2CEXMshN8IBLc2a7+GGrYp8Bo93CfRLEjd+2i1TePPxEqBvw/0ZZpK4cXMozZqf43yb49clga7jQonPND4u8dnQMGbTrHlsEEt4WvHBfuoplgaTuDGHOlYLg+MY2eW6VCHBlZIYIwXfVdSTuDG3echPs+YRfKX1fz6SxI0/O37HDpVmzXWtjXQuiRsPKeyyYJjAfL9BCprHxAYM/AsZyZ28RZrdxQAAAABJRU5ErkJggg==',

    typeSnippets: {
        'manufacturers_by_cat': '{s name="manufacturer_type/store/manufacturer_by_cat" namespace="backend/emotion/view/components/manufacturer_type"}{/s}',
        'selected_manufacturers': '{s name="manufacturer_type/store/selected_manufacturers" namespace="backend/emotion/view/components/manufacturer_type"}{/s}'
    },

    createPreview: function() {
        var me = this,
            preview = '',
            content = '',
            type = me.getConfigValue('manufacturer_type');

        if (Ext.isDefined(type)) {
            content += Ext.String.format('<div class="x-emotion-preview-title">[0]:</div>', me.getLabel());

            if (type === 'manufacturers_by_cat') {
                content += Ext.String.format('<div class="manufacturer-type">[0]</div>', me.typeSnippets[type]);
            }

            if (type === 'selected_manufacturers') {
                var manufacturers = me.getConfigValue('selected_manufacturers');

                if (manufacturers.length > 0) {
                    Ext.each(manufacturers, function(manufacturer) {
                        content += Ext.String.format('<div class="manufacturer">[0]</div>', manufacturer.name);
                    });
                }
            }

            preview = Ext.String.format('<div class="x-emotion-manufacturer-preview">[0]</div>', content);
        }

        return preview;
    }
});
//{/block}
