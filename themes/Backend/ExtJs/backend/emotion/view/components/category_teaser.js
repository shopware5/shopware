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
//{block name="backend/emotion/view/components/category_teaser"}
//{namespace name=backend/emotion/view/components/category_teaser}
Ext.define('Shopware.apps.Emotion.view.components.CategoryTeaser', {
    extend: 'Shopware.apps.Emotion.view.components.Base',
    alias: 'widget.emotion-components-category-teaser',

    snippets: {
        blog_category: {
            fieldLabel: '{s name=blog_category}Blog category{/s}',
            supportText: '{s name=blog_category_support}The selected category is a blog Category{/s}'
        },
        image: '{s name=image}Image{/s}',
        category_selection: '{s name=category_selection}Select category{/s}'
    },

    /**
     * Base path which will be used from the component.
     * @string
     */
    basePath: '',

    /**
     * Initialize the component.
     *
     * @public
     * @return void
     */
    initComponent: function() {
        var me = this;
        me.callParent(arguments);

        me.mediaSelection = me.down('mediaselectionfield');

        var value = '';
        Ext.each(me.getSettings('record').get('data'), function(item) {
            if(item.key == 'image_type') {
                value = item.value;
                return false;
            }
        });

        if(!value || value !== 'selected_image') {
            me.mediaSelection.hide();
        }
    }
});
//{/block}
