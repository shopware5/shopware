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
//{block name="backend/emotion/model/preset"}
Ext.define('Shopware.apps.Emotion.model.Preset', {
    extend: 'Ext.data.Model',

    fields: [
        //{block name="backend/emotion/model/preset/fields"}{/block}
        { name: 'id', type: 'int', useNull: true },
        { name: 'name', type: 'string' },
        { name: 'premium', type: 'bool', defaultValue: false },
        { name: 'custom', type: 'bool', defaultValue: true },
        { name: 'thumbnail', type: 'string' },
        { name: 'thumbnailUrl', type: 'string', persist: false },
        { name: 'preview', type: 'string' },
        { name: 'previewUrl', type: 'string', persist: false },
        { name: 'presetData', type: 'string' },
        { name: 'label', type: 'string', persist: false },
        { name: 'description', type: 'string', persist: false },
        { name: 'translations', type: 'array' },
        { name: 'requiredPlugins', type: 'array' },
        { name: 'emotionTranslations', type: 'string' },
        { name: 'assetsImported', type: 'boolean', defaultValue: true, persist: false },
        {
            name: 'pluginsInstalled',
            persist: false,
            mapping: 'requiredPlugins',
            convert: function(plugins) {
                var installed = true;
                Ext.each(plugins, function(plugin) {
                    if (!plugin['valid']) {
                        installed = false;

                        return false;
                    }
                });

                return installed;
            }
        }
    ],

    allowUsage: function() {
        return this.get('assetsImported') && this.get('pluginsInstalled');
    },

    proxy:{
        /**
         * Set proxy type to ajax
         * @string
         */
        type:'ajax',

        /**
         * Configure the url mapping for the different
         * store operations based on
         * @object
         */
        api: {
            create: '{url controller="EmotionPreset" action="save"}',
            update: '{url controller="EmotionPreset"  action="save"}',
            destroy: '{url controller="EmotionPreset" action="delete"}'
        },

        /**
         * Configure the data reader
         * @object
         */
        reader:{
            type:'json',
            root:'data',
            totalProperty:'total'
        }
    }

});
//{/block}
