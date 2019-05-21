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
//{block name="backend/emotion/view/detail/elements/category_teaser"}
Ext.define('Shopware.apps.Emotion.view.detail.elements.CategoryTeaser', {

    extend: 'Shopware.apps.Emotion.view.detail.elements.Base',

    alias: 'widget.detail-element-emotion-components-category-teaser',

    componentCls: 'category-teaser-element',

    icon: 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABwAAAAkCAYAAACaJFpUAAAA8klEQVRIie2UMQrCMBhGX8VJlHoC8QbirB5B8QRVofQC3R3cc4EsSm7QwRu4uOkJBA8gCo6KDjoUlyRtKQXztoSP78H/h3hCqhd6kjgKpgY5LbUiSpzQCZ0wF3UgSZ07QB94ANvU/b4ooZc+CKnmwBq4xVHQLkqS5j92WAhCqhUwKU3I58H1dKHKjLQmpOpadjXzCFvAyVJoRGVGegdGll0rYJxV+Iyj4GBjE1JdTHKVGWkWzsCxNGEcBUtgqcu5z9sYIVUIDEsTfmUzXeh/d+gLqa6WXY08QgDfUmjEr3AHLDJ2hcBAF/J0AVOEVBvcKwXeiYcpt1BhW0cAAAAASUVORK5CYII=',

    typeSnippets: {
        'random_article_image': '{s name="article/store/random_article" namespace="backend/emotion/view/components/category_image_type"}{/s}'
    },

    createPreview: function() {
        var me = this,
            preview = '',
            content = '',
            style = '',
            type = me.getConfigValue('image_type');

        if (Ext.isDefined(type)) {

            if (type === 'selected_image') {
                style = Ext.String.format('background-image: url([0]);', me.getConfigValue('image'));

                preview = Ext.String.format('<div class="x-emotion-banner-element-preview" style="[0]"></div>', style);

            } else {
                content += Ext.String.format('<div class="x-emotion-preview-title">[0]:</div>', me.getLabel());
                content += Ext.String.format('<div class="teaser-type">[0]</div>', me.typeSnippets[me.getConfigValue('image_type')]);

                preview = Ext.String.format('<div class="x-emotion-category-teaser-preview">[0]</div>', content);
            }
        }

        return preview;
    }
});
//{/block}
