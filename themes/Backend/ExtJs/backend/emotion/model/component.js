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

/**
 * Shopware Model - Emotion backend module.
 */

//{block name="backend/emotion/model/component"}
//{namespace name=backend/emotion/view/detail}
Ext.define('Shopware.apps.Emotion.model.Component', {
    /**
     * Extends the standard Ext Model
     * @string
     */
    extend: 'Ext.data.Model',

    snippets: {
        //{block name="backend/emotion/model/component/snippets"}{/block}

        'Artikel': '{s name=article}Article{/s}',
        'Kategorie-Teaser': '{s name=category_teaser}Category teaser{/s}',
        'Blog-Artikel' : '{s name=blog_article}Blog article{/s}',
        'Banner-Slider' : '{s name=banner_slider}Banner slider{/s}',
        'Youtube-Video' : '{s name=youtube}Youtube video{/s}',
        'Hersteller-Slider' : '{s name=manufacturer}Manufacturer slider{/s}',
        'Artikel-Slider' : '{s name=article_slider}Article slider{/s}',
        'HTML-Element' : '{s name=html_element}HTML element{/s}',
        'iFrame-Element' : '{s name=iframe}iFrame element{/s}',
        'HTML5 Video-Element' : '{s name=html_video}HTML5 video element{/s}',
        'Content Type' : '{s name=content_type}{/s}',
    },

    /**
     * The fields used for this model
     * @array
     */
    fields: [
        //{block name="backend/emotion/model/component/fields"}{/block}
        { name: 'id', type: 'int' },
        { name: 'pluginId', type: 'int', useNull: true },

        { name: 'fieldLabel', type: 'string', convert: function(value, record) {
            var name = record.get('name'),
                fieldLabel = name;

            if (record.snippets[name]) {
                fieldLabel = record.snippets[name];
            }
            return fieldLabel;
        } },

        { name: 'name', type: 'string' },
        { name: 'description', type: 'string' },
        { name: 'xType', type: 'string' },
        { name: 'template', type: 'string' },
        { name: 'cls', type: 'string' }
    ],

    associations: [
        { type: 'hasMany', model: 'Shopware.apps.Emotion.model.Field', name: 'getFields', associationKey: 'fields'}
    ]

});
//{/block}
