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
 * @package    UserManager
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */
//{block name="backend/emotion/view/components/article"}
//{namespace name=backend/emotion/view/components/article}
Ext.define('Shopware.apps.Emotion.view.components.Article', {
    extend: 'Shopware.apps.Emotion.view.components.Base',
    alias: 'widget.emotion-components-article',

    snippets: {
        article: '{s name=article}Search article{/s}',
        productImageOnly: {
            fieldLabel: '{s name=productImageOnly/label}Do not add styling{/s}',
            supportText: '{s name=productImageOnly/support}If selected, no other layout styling is applied.{/s}'
        },
        no_border: {
            fieldLabel: '{s name="noBorder/label"}{/s}',
            supportText: '{s name="noBorder/supportText"}{/s}'
        }
    },

    /**
     * Base path which will be used from the component.
     * @string
     */
    basePath: '{link file=""}',

    /**
     * Initialize the component.
     *
     * @public
     * @return void
     */
    initComponent: function() {
        var me = this;
        me.callParent(arguments);

        me.articleSearch = me.down('emotion-components-fields-article');
        me.articleSearch.searchField.setValue(me.articleSearch.hiddenField.getValue());

        me.articleCategory = me.down('emotion-components-fields-category-selection');

        me.articleTypeField = me.down('emotion-components-fields-article-type');
        me.articleTypeField.on('change', me.onTypeChange, me);

        me.articleType = me.getElementDataByName('article_type');

        me.setVisibleFields(me.articleType);
    },

    /**
     * Event callback on article type change.
     *
     * @param typeField
     * @param newValue
     */
    onTypeChange: function(typeField, newValue) {
        var me = this;

        me.setVisibleFields(newValue);
    },

    /**
     * Shows the corresponding config fields by selected article type.
     *
     * @param articleType
     */
    setVisibleFields: function(articleType) {
        var me = this,
            type = articleType || me.articleType;

        me.articleSearch.setVisible(type === 'selected_article');
        me.articleSearch.searchField.allowBlank = (type !== 'selected_article');
        me.articleSearch.hiddenField.allowBlank = (type !== 'selected_article');

        me.articleCategory.setVisible(type !== 'selected_article');
        me.articleCategory.allowBlank = (type === 'selected_article');
    },

    /**
     * Get the value of element data by field name.
     *
     * @param fieldName
     * @returns string|undefined
     */
    getElementDataByName: function(fieldName) {
        var me = this,
            data = me.getSettings('record').get('data'),
            value = Ext.undefined;

        Ext.each(data, function(item) {
            if (item.key === fieldName) {
                value = item.value;
                return false;
            }
        });

        return value;
    }
});
//{/block}
