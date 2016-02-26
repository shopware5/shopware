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
 */

/**
 * Shopware Application
 *
 * @category  Shopware
 * @package   Shopware
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */

//{namespace name=backend/theme/main}

//{block name="backend/theme/view/settings/settings"}

Ext.define('Shopware.apps.Theme.view.settings.Settings', {
    extend: 'Shopware.model.Container',
    padding: 15,
    flex: 1,
    layout: {
        type: 'vbox',
        align: 'stretch'
    },

    configure: function() {
        return {
            fieldSets: [
                {
                    title: '{s name=general_configuration}General configuration{/s}',
                    padding: 15,
                    layout: 'anchor',
                    height: 75,
                    flex: 0,
                    fields: {
                        reloadSnippets: {
                            fieldLabel: '{s name=reload_snippets}Force snippet reload{/s}',
                            labelWidth: 250
                        }
                    }
                },
                {
                    title: '{s name=compiler_configuration}Compiler configuration{/s}',
                    padding: 15,
                    layout: 'anchor',
                    flex:1,
                    fields: {
                        forceCompile: {
                            fieldLabel: '{s name=force_compile_field}Disable compiler caching{/s}',
                            labelWidth: 250
                        },
                        createSourceMap: {
                            fieldLabel: '{s name=create_source_map_field}Create a css source map{/s}',
                            labelWidth: 250
                        },
                        compressCss: {
                            fieldLabel: '{s name=compress_css_files}Compress css files{/s}',
                            labelWidth: 250
                        },
                        compressJs: {
                            fieldLabel: '{s name=compress_js_files}Compress javascript files{/s}',
                            labelWidth: 250
                        }
                    }
                }
            ]
        };
    }
});

//{/block}
